<?php

namespace WHMCS\Module\Server\VirtutelNbn\Webhook;

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Webhook\Handlers\AppointmentHandler;
use WHMCS\Module\Server\VirtutelNbn\Webhook\Handlers\OrderStatusHandler;
use WHMCS\Module\Server\VirtutelNbn\Webhook\Handlers\ServiceStatusHandler;

/**
 * Persists inbound callbacks idempotently and routes them to handlers.
 *
 * Every event is stored first (audit trail), keyed on the unique eventUuid —
 * a duplicate delivery is recorded as a no-op. Unrecognised event families
 * are kept with status 'skipped' so nothing is silently lost.
 */
class CallbackDispatcher
{
    /**
     * @return string one of: processed | duplicate | skipped | failed
     */
    public function dispatch(CallbackEnvelope $envelope): string
    {
        $now = date('Y-m-d H:i:s');

        $inserted = Capsule::table('mod_virtutel_callback_events')->insertOrIgnore([
            'event_uuid' => $envelope->eventUuid,
            'event_time' => $envelope->eventTimeSql(),
            'event_type' => substr($envelope->eventType, 0, 96),
            'notification_type' => substr($envelope->notificationType, 0, 96),
            'vt_object_id' => substr($envelope->objectId, 0, 64),
            'payload' => json_encode($envelope->raw, JSON_UNESCAPED_SLASHES),
            'auth_ok' => true, // only authenticated requests reach the dispatcher
            'status' => 'received',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($inserted === 0) {
            return 'duplicate'; // eventUuid already seen — retry delivery
        }

        try {
            $handled = match ($envelope->family()) {
                'order' => (new OrderStatusHandler())->handle($envelope),
                'appointment' => (new AppointmentHandler())->handle($envelope),
                'service' => (new ServiceStatusHandler())->handle($envelope),
                default => false,
            };

            $status = $handled ? 'processed' : 'skipped';

            if ($envelope->family() === 'other' && function_exists('logActivity')) {
                // Test callbacks and not-yet-handled families (service
                // health, outages, ...) — visible in the Activity Log.
                logActivity(sprintf(
                    'Virtutel NBN: callback received and stored (%s / %s, uuid %s)',
                    $envelope->eventType !== '' ? $envelope->eventType : 'unknown type',
                    $envelope->notificationType !== '' ? $envelope->notificationType : 'unknown notification',
                    $envelope->eventUuid
                ));
            }
        } catch (\Throwable $e) {
            $this->markEvent($envelope->eventUuid, 'failed');
            if (function_exists('logActivity')) {
                logActivity(sprintf(
                    'Virtutel NBN: callback %s (%s/%s) failed: %s',
                    $envelope->eventUuid,
                    $envelope->eventType,
                    $envelope->notificationType,
                    $e->getMessage()
                ));
            }

            return 'failed';
        }

        $this->markEvent($envelope->eventUuid, $status, processed: $status === 'processed');

        return $status;
    }

    private function markEvent(string $uuid, string $status, bool $processed = false): void
    {
        $update = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($processed) {
            $update['processed_at'] = date('Y-m-d H:i:s');
        }

        Capsule::table('mod_virtutel_callback_events')
            ->where('event_uuid', $uuid)
            ->update($update);
    }
}
