<?php

namespace Vysion\VirtutelNbn\Repository;

use WHMCS\Database\Capsule;

final class ServiceRepository
{
    private const TABLE = 'mod_virtutel_service';

    public function findByWhmcsServiceId(int $whmcsServiceId): ?object
    {
        return Capsule::table(self::TABLE)->where('whmcs_service_id', $whmcsServiceId)->first();
    }

    public function findByProviderServiceId(string $providerServiceId): ?object
    {
        return Capsule::table(self::TABLE)->where('provider_service_id', $providerServiceId)->first();
    }

    public function create(array $attributes): int
    {
        $now = date('Y-m-d H:i:s');

        return (int) Capsule::table(self::TABLE)->insertGetId(
            $attributes + ['created_at' => $now, 'updated_at' => $now]
        );
    }

    public function update(int $id, array $attributes): void
    {
        Capsule::table(self::TABLE)
            ->where('id', $id)
            ->update($attributes + ['updated_at' => date('Y-m-d H:i:s')]);
    }
}
