<?php

namespace Vysion\VirtutelNbn\Repository;

use WHMCS\Database\Capsule;

/**
 * Cached provider access tokens (mod_virtutel_token). Tokens are stored
 * encrypted with WHMCS's own encryption (EncryptPassword/DecryptPassword
 * local API) so a database dump does not expose live credentials.
 */
final class TokenRepository
{
    private const TABLE = 'mod_virtutel_token';

    public function get(string $provider): ?object
    {
        return Capsule::table(self::TABLE)->where('provider', $provider)->first();
    }

    public function put(string $provider, string $accessToken, string $expiresAt): void
    {
        Capsule::table(self::TABLE)->updateOrInsert(
            ['provider' => $provider],
            [
                'access_token' => $this->conceal($accessToken),
                'expires_at' => $expiresAt,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        );
    }

    public function conceal(string $plain): string
    {
        if ($plain !== '' && function_exists('localAPI')) {
            $result = localAPI('EncryptPassword', ['password2' => $plain]);
            if (($result['result'] ?? '') === 'success' && !empty($result['password'])) {
                return 'enc:' . $result['password'];
            }
        }

        return $plain;
    }

    public function reveal(string $stored): string
    {
        if (str_starts_with($stored, 'enc:')) {
            if (!function_exists('localAPI')) {
                return '';
            }
            $result = localAPI('DecryptPassword', ['password2' => substr($stored, 4)]);

            return ($result['result'] ?? '') === 'success' ? (string) ($result['password'] ?? '') : '';
        }

        return $stored;
    }
}
