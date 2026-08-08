<?php

namespace Vysion\VirtutelNbn\Repository;

use WHMCS\Database\Capsule;

/** Key/value module settings (mod_virtutel_setting): schema version, update-check state. */
final class SettingRepository
{
    private const TABLE = 'mod_virtutel_setting';

    public function get(string $name): ?string
    {
        $row = Capsule::table(self::TABLE)->where('name', $name)->first();

        return $row !== null ? (string) $row->value : null;
    }

    public function put(string $name, string $value): void
    {
        Capsule::table(self::TABLE)->updateOrInsert(
            ['name' => $name],
            ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')],
        );
    }
}
