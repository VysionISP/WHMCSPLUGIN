<?php

namespace WHMCS\Module\Server\VirtutelNbn\Webhook\Handlers;

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Webhook\CallbackEnvelope;

/**
 * Applies service-level callbacks (docs/api/09_services.md):
 *  - ProductInstanceUpdated: NBN changed something (e.g. UNI-D port swap)
 *  - ProductInstanceDisconnected: churned away or disconnected
 */
class ServiceStatusHandler
{
    public function handle(CallbackEnvelope $envelope): bool
    {
        $vtServiceId = $envelope->objectId;
        if ($vtServiceId === '') {
            return false;
        }

        $service = Capsule::table('mod_virtutel_services')
            ->where('vt_service_id', $vtServiceId)
            ->first();

        if (!$service) {
            if (function_exists('logActivity')) {
                logActivity(sprintf(
                    'Virtutel NBN: service callback %s for unknown service %s (%s)',
                    $envelope->eventUuid,
                    $vtServiceId,
                    $envelope->notificationType
                ));
            }

            return false;
        }

        $now = date('Y-m-d H:i:s');
        Capsule::table('mod_virtutel_services')->where('id', $service->id)->update([
            'carrier_status' => substr($envelope->notificationType, 0, 64),
            'updated_at' => $now,
        ]);

        Capsule::table('mod_virtutel_callback_events')
            ->where('event_uuid', $envelope->eventUuid)
            ->update(['service_id' => $service->id]);

        if ($envelope->notificationType === 'ProductInstanceDisconnected' && function_exists('localAPI')) {
            // Churn-away/disconnect: never keep billing a dead service — flag
            // staff to cancel. (Automated cancellation policy is a step-4 item.)
            localAPI('AddTodoItem', [
                'date' => date('Y-m-d'),
                'title' => 'Virtutel NBN: service disconnected',
                'description' => sprintf(
                    'Virtutel service %s (WHMCS service #%d) was disconnected/churned away: %s. Review billing/cancellation.',
                    $vtServiceId,
                    $service->whmcs_service_id,
                    $envelope->reason !== '' ? $envelope->reason : 'no reason given'
                ),
                'status' => 'Pending',
                'duedate' => date('Y-m-d', strtotime('+1 day')),
            ]);
        }

        return true;
    }
}
