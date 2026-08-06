<?php

namespace Vysion\VirtutelNbn\Repository;

use WHMCS\Database\Capsule;

final class WebhookEventRepository
{
    private const TABLE = 'mod_virtutel_webhook_event';

    /**
     * Store an inbound event. Returns the new row id, or null when the
     * external_event_id was already seen (idempotent duplicate delivery).
     */
    public function storeIfNew(array $attributes): ?int
    {
        $exists = Capsule::table(self::TABLE)
            ->where('external_event_id', $attributes['external_event_id'])
            ->exists();
        if ($exists) {
            return null;
        }

        try {
            return (int) Capsule::table(self::TABLE)->insertGetId(
                $attributes + ['received_at' => date('Y-m-d H:i:s')]
            );
        } catch (\Throwable $e) {
            // Unique-index race with a concurrent duplicate delivery.
            return null;
        }
    }

    /** @return object[] */
    public function pending(int $limit = 25): array
    {
        return Capsule::table(self::TABLE)
            ->where('status', 'pending')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function markProcessed(int $id, ?int $orderId = null): void
    {
        Capsule::table(self::TABLE)->where('id', $id)->update([
            'status' => 'processed',
            'order_id' => $orderId,
            'processed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markFailed(int $id, string $error): void
    {
        Capsule::table(self::TABLE)->where('id', $id)->update([
            'status' => 'failed',
            'error' => mb_substr($error, 0, 2000),
            'processed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markSkipped(int $id, string $reason): void
    {
        Capsule::table(self::TABLE)->where('id', $id)->update([
            'status' => 'skipped',
            'error' => mb_substr($reason, 0, 2000),
            'processed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return object[] */
    public function recentForOrderIds(array $orderIds, int $limit = 10): array
    {
        if ($orderIds === []) {
            return [];
        }

        return Capsule::table(self::TABLE)
            ->whereIn('order_id', $orderIds)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->all();
    }
}
