<?php

namespace WHMCS\Module\Server\VirtutelNbn\Repository;

use WHMCS\Database\Capsule;

/**
 * Key/value store on mod_virtutel_settings. Values flagged secret are
 * encrypted at rest via the WHMCS localAPI.
 */
class Settings
{
    public static function get(string $name, ?string $default = null): ?string
    {
        $value = Capsule::table('mod_virtutel_settings')->where('name', $name)->value('value');

        return $value === null ? $default : (string) $value;
    }

    public static function set(string $name, string $value): void
    {
        Capsule::table('mod_virtutel_settings')->updateOrInsert(
            ['name' => $name],
            ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')]
        );
    }

    public static function getSecret(string $name): ?string
    {
        $stored = self::get($name);
        if ($stored === null || $stored === '') {
            return null;
        }
        if (function_exists('localAPI')) {
            $result = localAPI('DecryptPassword', ['password2' => $stored]);
            if (($result['result'] ?? '') === 'success') {
                $plain = (string) ($result['password'] ?? '');
                return $plain === '' ? null : $plain;
            }
        }

        $plain = base64_decode($stored, true);

        return $plain === false || $plain === '' ? null : $plain;
    }

    public static function setSecret(string $name, string $value): void
    {
        $encrypted = null;
        if (function_exists('localAPI')) {
            $result = localAPI('EncryptPassword', ['password2' => $value]);
            if (($result['result'] ?? '') === 'success' && !empty($result['password'])) {
                $encrypted = (string) $result['password'];
            }
        }

        self::set($name, $encrypted ?? base64_encode($value));
    }
}
