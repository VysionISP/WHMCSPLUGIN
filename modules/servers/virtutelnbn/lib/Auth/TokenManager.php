<?php

namespace Vysion\VirtutelNbn\Auth;

use Vysion\VirtutelNbn\Exception\AuthException;
use Vysion\VirtutelNbn\Repository\TokenRepository;

/**
 * OAuth2-style client-credentials flow for VirtuTel.
 *
 * The client ID + client secret (WHMCS server record: Username + Password)
 * are exchanged for an access token that VirtuTel keeps valid for ~28 days.
 * The token is cached encrypted in mod_virtutel_token and renewed:
 *   - automatically, RENEW_BUFFER_SECONDS before its expiry;
 *   - immediately, when the API answers 401 (VirtutelClient forces refresh);
 *   - proactively on every WHMCS cron run (hooks.php keep-alive), so it
 *     never lapses even if no orders are placed for weeks.
 */
final class TokenManager implements TokenProviderInterface
{
    // TODO(virtutel-spec): confirm the token endpoint path and payload shape.
    private const TOKEN_PATH = '/v1/auth/token';
    private const DEFAULT_TTL_SECONDS = 28 * 86400;   // VirtuTel tokens last ~28 days
    public const RENEW_BUFFER_SECONDS = 2 * 86400;    // renew 2 days early

    private ?string $inMemory = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly TokenRepository $tokens,
        private readonly string $provider = 'virtutel',
    ) {
    }

    public function token(bool $forceRefresh = false): string
    {
        if (!$forceRefresh) {
            if ($this->inMemory !== null) {
                return $this->inMemory;
            }
            $row = $this->tokens->get($this->provider);
            if ($row && self::isFresh((string) $row->expires_at, time())) {
                $cached = $this->tokens->reveal((string) $row->access_token);
                if ($cached !== '') {
                    return $this->inMemory = $cached;
                }
            }
        }

        return $this->inMemory = $this->requestNewToken();
    }

    /** A stored token is usable while more than the renewal buffer remains. */
    public static function isFresh(string $expiresAt, int $now): bool
    {
        $expiry = strtotime($expiresAt);

        return $expiry !== false && ($expiry - $now) > self::RENEW_BUFFER_SECONDS;
    }

    private function requestNewToken(): string
    {
        $ch = curl_init($this->baseUrl . self::TOKEN_PATH);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $body = curl_exec($ch);
        if ($body === false) {
            $error = curl_error($ch) ?: 'connection error';
            curl_close($ch);
            throw new AuthException('Could not reach the VirtuTel token endpoint: ' . $error);
        }
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $data = json_decode((string) $body, true);
        if ($status >= 400 || !is_array($data)) {
            throw new AuthException(
                'VirtuTel rejected the client credentials (HTTP ' . $status . '). '
                . 'Check the Client ID (Username) and Client Secret (Password) on the server record.',
                $status,
            );
        }

        // TODO(virtutel-spec): confirm response field names.
        $token = (string) ($data['access_token'] ?? $data['token'] ?? '');
        if ($token === '') {
            throw new AuthException('VirtuTel token response did not contain an access token.', $status);
        }

        $expiresIn = (int) ($data['expires_in'] ?? 0);
        if ($expiresIn <= 0) {
            $expiresIn = self::DEFAULT_TTL_SECONDS;
        }

        $this->tokens->put($this->provider, $token, date('Y-m-d H:i:s', time() + $expiresIn));

        return $token;
    }
}
