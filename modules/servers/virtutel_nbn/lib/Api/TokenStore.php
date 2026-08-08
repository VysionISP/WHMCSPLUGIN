<?php

namespace WHMCS\Module\Server\VirtutelNbn\Api;

use WHMCS\Database\Capsule;

/**
 * Caches Virtutel access tokens (30-day lifetime) per environment and
 * credential identity, encrypted at rest.
 *
 * Tokens are refreshed proactively once REFRESH_THRESHOLD of their lifetime
 * has elapsed, and a MySQL advisory lock prevents concurrent WHMCS requests
 * from double-generating.
 */
class TokenStore
{
    /** Refresh once this fraction of the token's lifetime has elapsed. */
    private const REFRESH_THRESHOLD = 0.8;

    /** Fallback lifetime when the API doesn't state one (docs: ~30 days). */
    public const DEFAULT_LIFETIME_SECONDS = 30 * 86400;

    private const LOCK_TIMEOUT_SECONDS = 20;

    public function __construct(
        private readonly string $environment,   // sandbox | production
        private readonly string $apiIdentity,   // sha256(baseUrl|client_id)
    ) {
    }

    public static function identity(string $baseUrl, string $clientId): string
    {
        return hash('sha256', $baseUrl . '|' . $clientId);
    }

    /**
     * Return a valid token, invoking $generator when a fresh one is needed.
     *
     * @param callable():array{token:string,expires_at:int} $generator
     */
    public function getValidToken(callable $generator, bool $forceNew = false): string
    {
        if (!$forceNew) {
            $cached = $this->fetchUsable();
            if ($cached !== null) {
                return $cached;
            }
        }

        $lockName = 'virtutel_token_' . substr($this->apiIdentity, 0, 32);
        $this->acquireLock($lockName);
        try {
            // Another request may have refreshed while we waited on the lock.
            if (!$forceNew) {
                $cached = $this->fetchUsable();
                if ($cached !== null) {
                    return $cached;
                }
            }

            $fresh = $generator();
            $this->store($fresh['token'], $fresh['expires_at']);

            return $fresh['token'];
        } finally {
            $this->releaseLock($lockName);
        }
    }

    /** Discard the cached token (e.g. after an auth_error on a request). */
    public function invalidate(): void
    {
        Capsule::table('mod_virtutel_tokens')
            ->where('environment', $this->environment)
            ->where('api_identity', $this->apiIdentity)
            ->delete();
    }

    /** True when the cached token is past the proactive-refresh threshold. */
    public function needsRefresh(): bool
    {
        return $this->fetchUsable() === null;
    }

    private function fetchUsable(): ?string
    {
        $row = Capsule::table('mod_virtutel_tokens')
            ->where('environment', $this->environment)
            ->where('api_identity', $this->apiIdentity)
            ->first();

        if (!$row) {
            return null;
        }

        $expiresAt = strtotime((string) $row->expires_at);
        $issuedAt = strtotime((string) $row->created_at) ?: ($expiresAt - self::DEFAULT_LIFETIME_SECONDS);
        $refreshAt = $issuedAt + (int) (($expiresAt - $issuedAt) * self::REFRESH_THRESHOLD);

        if ($expiresAt === false || time() >= $refreshAt) {
            return null;
        }

        $token = self::decrypt((string) $row->access_token);

        return $token !== '' ? $token : null;
    }

    private function store(string $token, int $expiresAt): void
    {
        $now = date('Y-m-d H:i:s');
        Capsule::table('mod_virtutel_tokens')->updateOrInsert(
            [
                'environment' => $this->environment,
                'api_identity' => $this->apiIdentity,
            ],
            [
                'access_token' => self::encrypt($token),
                'expires_at' => date('Y-m-d H:i:s', $expiresAt),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function acquireLock(string $name): void
    {
        $result = Capsule::select('SELECT GET_LOCK(?, ?) AS l', [$name, self::LOCK_TIMEOUT_SECONDS]);
        if ((int) ($result[0]->l ?? 0) !== 1) {
            throw new ApiException('Timed out waiting for the token refresh lock');
        }
    }

    private function releaseLock(string $name): void
    {
        Capsule::select('SELECT RELEASE_LOCK(?) AS l', [$name]);
    }

    private static function encrypt(string $value): string
    {
        if (function_exists('localAPI')) {
            $result = localAPI('EncryptPassword', ['password2' => $value]);
            if (($result['result'] ?? '') === 'success' && !empty($result['password'])) {
                return $result['password'];
            }
            // localAPI can refuse admin-level commands in client-session
            // contexts — the legacy helper encrypts identically.
            if (function_exists('encrypt')) {
                try {
                    return (string) encrypt($value);
                } catch (\Throwable $e) {
                    // fall through
                }
            }
        }

        return base64_encode($value); // outside WHMCS (unit tests)
    }

    private static function decrypt(string $value): string
    {
        if (function_exists('localAPI')) {
            $result = localAPI('DecryptPassword', ['password2' => $value]);
            if (($result['result'] ?? '') === 'success' && (string) ($result['password'] ?? '') !== '') {
                return (string) $result['password'];
            }
            if (function_exists('decrypt')) {
                try {
                    $plain = (string) decrypt($value);
                    if ($plain !== '') {
                        return $plain;
                    }
                } catch (\Throwable $e) {
                    // fall through
                }
            }

            // Inside WHMCS with both decrypt paths failed: return empty
            // (caller regenerates) rather than base64 garbage as a token.
            return '';
        }

        return (string) base64_decode($value, true);
    }
}
