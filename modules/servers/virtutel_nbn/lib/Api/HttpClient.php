<?php

namespace WHMCS\Module\Server\VirtutelNbn\Api;

/**
 * cURL wrapper for the Virtutel Customer API.
 *
 * Responsibilities:
 *  - TLS-only with certificate verification (never disabled)
 *  - JSON request/response handling and vt_* envelope decoding
 *  - Retry with exponential backoff on network errors, 429 and 5xx
 *  - Module-log every call with secrets masked
 */
class HttpClient
{
    private const MAX_ATTEMPTS = 3;
    private const CONNECT_TIMEOUT = 10;
    private const TIMEOUT = 45;

    /** @var string e.g. https://api.example.com:8443 (no trailing slash) */
    private string $baseUrl;

    /** @var string[] values to mask in module logs */
    private array $maskValues;

    private string $moduleName;

    public function __construct(string $baseUrl, array $maskValues = [], string $moduleName = 'virtutel_nbn')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->maskValues = array_values(array_filter($maskValues, fn ($v) => is_string($v) && $v !== ''));
        $this->moduleName = $moduleName;

        if (!str_starts_with($this->baseUrl, 'https://')) {
            throw new ApiException('Virtutel API base URL must use https://');
        }
    }

    public function addMaskValue(string $value): void
    {
        if ($value !== '' && !in_array($value, $this->maskValues, true)) {
            $this->maskValues[] = $value;
        }
    }

    /**
     * @param array $options ['json' => array, 'query' => array, 'headers' => array<string,string>,
     *                        'bearer' => string, 'action' => string (log label),
     *                        'allowVtFailure' => bool (return instead of throwing on vt_success=false),
     *                        'timeout' => int (seconds, overrides default),
     *                        'attempts' => int (overrides retry count — use 1 for
     *                        interactive/customer-facing requests so slow upstream
     *                        calls can't pile up PHP workers)]
     */
    public function request(string $method, string $path, array $options = []): ApiResponse
    {
        $maxAttempts = max(1, (int) ($options['attempts'] ?? self::MAX_ATTEMPTS));
        $timeout = max(5, (int) ($options['timeout'] ?? self::TIMEOUT));
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        if (!empty($options['query'])) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($options['query']);
        }

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];
        if (!empty($options['bearer'])) {
            $headers[] = 'Authorization: Bearer ' . $options['bearer'];
        }
        foreach ($options['headers'] ?? [] as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        $body = null;
        if (array_key_exists('json', $options)) {
            $body = json_encode($options['json'], JSON_UNESCAPED_SLASHES);
        }

        $attempt = 0;
        $lastError = '';
        while (true) {
            $attempt++;
            [$status, $rawBody, $responseHeaders, $curlError] = $this->execute($method, $url, $headers, $body, $timeout);

            $logAction = $options['action'] ?? (strtoupper($method) . ' ' . $path);
            $this->log($logAction, $method, $url, $options['json'] ?? null, $status, $rawBody, $curlError);

            if ($curlError !== '') {
                $lastError = $curlError;
                if ($attempt < $maxAttempts) {
                    $this->backoff($attempt);
                    continue;
                }
                throw new ApiException("Virtutel API request failed: {$lastError}");
            }

            if (($status === 429 || $status >= 500) && $attempt < $maxAttempts) {
                $this->backoff($attempt, $this->retryAfterSeconds($responseHeaders));
                continue;
            }

            $decoded = json_decode($rawBody, true);
            if (!is_array($decoded)) {
                throw new ApiException(
                    "Virtutel API returned a non-JSON response (HTTP {$status})",
                    '',
                    $status
                );
            }

            $response = new ApiResponse($status, $decoded, $responseHeaders);

            if (!$response->vtSuccess() && empty($options['allowVtFailure'])) {
                throw new ApiException(
                    $response->vtErrorDesc() !== ''
                        ? $response->vtErrorDesc()
                        : "Virtutel API reported failure (HTTP {$status})",
                    $response->vtShortError(),
                    $status
                );
            }

            return $response;
        }
    }

    /**
     * @return array{0:int,1:string,2:array<string,string>,3:string} [status, body, headers, curlError]
     */
    private function execute(string $method, string $url, array $headers, ?string $body, int $timeout = self::TIMEOUT): array
    {
        $ch = curl_init();

        $responseHeaders = [];
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => min(self::CONNECT_TIMEOUT, $timeout),
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$responseHeaders) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }
                return strlen($line);
            },
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $rawBody = curl_exec($ch);
        $curlError = $rawBody === false ? (curl_error($ch) ?: 'unknown cURL error') : '';
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [$status, is_string($rawBody) ? $rawBody : '', $responseHeaders, $curlError];
    }

    private function retryAfterSeconds(array $headers): ?int
    {
        $value = $headers['retry-after'] ?? null;
        return (is_string($value) && ctype_digit($value)) ? (int) $value : null;
    }

    private function backoff(int $attempt, ?int $retryAfter = null): void
    {
        $seconds = $retryAfter ?? (2 ** ($attempt - 1)); // 1s, 2s, 4s
        sleep(min($seconds, 30));
    }

    private function log(string $action, string $method, string $url, ?array $requestJson, int $status, string $rawBody, string $curlError): void
    {
        if (!function_exists('logModuleCall')) {
            return; // outside WHMCS (unit tests)
        }

        $request = [
            'method' => $method,
            'url' => $url,
            'body' => $requestJson,
        ];
        $response = $curlError !== ''
            ? ['curl_error' => $curlError]
            : ['status' => $status, 'body' => substr($rawBody, 0, 65535)];

        logModuleCall($this->moduleName, $action, $request, $response, '', $this->maskValues);
    }
}
