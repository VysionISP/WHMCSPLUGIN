<?php

namespace Vysion\VirtutelNbn\Repository;

use WHMCS\Database\Capsule;

final class OrderRepository
{
    private const TABLE = 'mod_virtutel_order';

    public function findByProviderOrderId(string $providerOrderId): ?object
    {
        return Capsule::table(self::TABLE)->where('provider_order_id', $providerOrderId)->first();
    }

    /** @return object[] */
    public function recentForService(int $serviceId, int $limit = 10): array
    {
        return Capsule::table(self::TABLE)
            ->where('service_id', $serviceId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->all();
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
