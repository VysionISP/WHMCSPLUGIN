<?php

namespace Vysion\VirtutelNbn\Provider\Virtutel;

use Vysion\VirtutelNbn\Exception\ApiException;
use Vysion\VirtutelNbn\Exception\AuthException;
use Vysion\VirtutelNbn\Repository\ApiLogRepository;

/**
 * Thin, dependency-free HTTP client for the VirtuTel API.
 *
 * Security properties:
 *  - HTTPS enforced upstream (Config), TLS peer + host verification ON.
 *  - Bearer auth; the token is never logged (headers are redacted).
 *  - Bounded timeouts and bounded retries with backoff on transient errors.
 *  - Request/response bodies logged with sensitive keys redacted.
 */
final class VirtutelClient
{
    private const CONNECT_TIMEOUT = 10;
    private const TIMEOUT = 30;
    private const MAX_ATTEMPTS = 3;
    private const REDACT_KEYS = ['password', 'secret', 'token', 'api_key', 'apikey', 'authorization'];

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly ?ApiLogRepository $log = null,
    ) {
    }

    /**
     * @throws ApiException
     */
    public function request(string $method, string $path, ?array $body = null): array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        $json = $body === null ? null : json_encode($body, JSON_UNESCAPED_SLASHES);

        $attempt = 0;
        $lastError = null;

        while ($attempt < self::MAX_ATTEMPTS) {
            $attempt++;
            $started = microtime(true);

            [$status, $responseBody, $curlError] = $this->execute($method, $url, $json);

            $durationMs = (int) round((microtime(true) - $started) * 1000);
            $decoded = $responseBody !== null ? json_decode($responseBody, true) : null;

            $this->log?->record(
                $method,
                $path,
                $status,
                $body !== null ? self::redact($body) : null,
                is_array($decoded) ? self::redact($decoded) : $responseBody,
                $durationMs,
            );

            if ($curlError !== null || ($status !== null && $status >= 500)) {
                // Transient: retry with backoff.
                $lastError = $curlError ?? "HTTP {$status}";
                if ($attempt < self::MAX_ATTEMPTS) {
                    sleep($attempt); // 1s, 2s
                    continue;
                }
                throw new ApiException(
                    "VirtuTel API request failed after {$attempt} attempts: {$lastError}",
                    $status,
                    is_array($decoded) ? $decoded : null,
                );
            }

            if ($status === 401 || $status === 403) {
                throw new AuthException('VirtuTel API rejected the credentials (HTTP ' . $status . ').', $status);
            }

            if ($status >= 400) {
                $message = is_array($decoded)
                    ? (string) ($decoded['message'] ?? $decoded['error'] ?? 'Unknown API error')
                    : 'Unknown API error';
                throw new ApiException("VirtuTel API error (HTTP {$status}): {$message}", $status, is_array($decoded) ? $decoded : null);
            }

            return is_array($decoded) ? $decoded : [];
        }

        throw new ApiException('VirtuTel API request failed: ' . ($lastError ?? 'unknown error'));
    }

    /**
     * @return array{0: ?int, 1: ?string, 2: ?string} [httpStatus, body, curlError]
     */
    private function execute(string $method, string $url, ?string $json): array
    {
        $ch = curl_init($url);
        $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];
        if ($json !== null) {
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        if ($json !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            $error = curl_error($ch) ?: 'connection error';
            curl_close($ch);

            return [null, null, $error];
        }

        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [$status, (string) $responseBody, null];
    }

    /** Recursively mask sensitive keys before anything is persisted to logs. */
    public static function redact(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::redact($value);
            } elseif (is_string($key) && in_array(strtolower($key), self::REDACT_KEYS, true)) {
                $data[$key] = '[REDACTED]';
            }
        }

        return $data;
    }
}
