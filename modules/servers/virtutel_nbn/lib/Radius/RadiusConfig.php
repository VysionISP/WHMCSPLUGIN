<?php

namespace WHMCS\Module\Server\VirtutelNbn\Radius;

use WHMCS\Database\Capsule;

/**
 * FreeRADIUS + CoA settings, configured on the addon module
 * (Addons -> Virtutel NBN Tools -> Configure).
 */
class RadiusConfig
{
    /** @return array<string,string> */
    public static function load(): array
    {
        $settings = Capsule::table('tbladdonmodules')
            ->where('module', 'virtutel_nbn_admin')
            ->pluck('value', 'setting');

        $get = fn (string $key, string $default = '') => trim((string) ($settings[$key] ?? $default));

        return [
            'db_host' => $get('radius_db_host'),
            'db_port' => $get('radius_db_port', '3306'),
            'db_name' => $get('radius_db_name', 'radius'),
            'db_user' => $get('radius_db_user'),
            'db_pass' => (string) ($settings['radius_db_pass'] ?? ''),
            'default_group' => $get('radius_default_group', 'nbn-active'),
            'suspend_group' => $get('radius_suspend_group', 'nbn-suspended'),
            'rate_limit_attr' => $get('radius_rate_attr', 'Mikrotik-Rate-Limit'),
            'rate_limit_format' => $get('radius_rate_format', '{up}M/{down}M'),
            'coa_host' => $get('coa_host'),
            'coa_port' => $get('coa_port', '3799'),
            'coa_secret' => (string) ($settings['coa_secret'] ?? ''),
        ];
    }

    public static function isConfigured(?array $config = null): bool
    {
        $config ??= self::load();

        return $config['db_host'] !== '' && $config['db_user'] !== '';
    }
}
