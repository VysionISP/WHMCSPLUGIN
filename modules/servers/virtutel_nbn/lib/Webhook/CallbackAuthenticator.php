<?php

namespace WHMCS\Module\Server\VirtutelNbn\Webhook;

use WHMCS\Module\Server\VirtutelNbn\Repository\Settings;

/**
 * Authenticates inbound callbacks.
 *
 * Virtutel does not sign callbacks, so authentication is a shared-secret
 * token embedded as a query parameter in the registered callback URL
 * (?token=...), compared in constant time. The token is generated once,
 * stored encrypted, and included in the URL we register via the API.
 */
class CallbackAuthenticator
{
    public const SETTING_NAME = 'callback_token';

    /** Returns the current token, generating and storing one on first use. */
    public static function ensureToken(): string
    {
        $token = Settings::getSecret(self::SETTING_NAME);
        if ($token !== null && $token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        Settings::setSecret(self::SETTING_NAME, $token);

        return $token;
    }

    /** Rotate the token. The callback URL must be re-registered afterwards. */
    public static function rotateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        Settings::setSecret(self::SETTING_NAME, $token);

        return $token;
    }

    public static function verify(?string $presented): bool
    {
        if (!is_string($presented) || $presented === '') {
            return false;
        }

        $expected = Settings::getSecret(self::SETTING_NAME);
        if ($expected === null || $expected === '') {
            return false; // no token provisioned -> nothing can authenticate
        }

        return hash_equals($expected, $presented);
    }
}
