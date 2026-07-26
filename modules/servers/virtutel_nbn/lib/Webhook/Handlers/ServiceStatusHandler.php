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
            // Churn-away/disconnect: never keep billing a dead service.
            // Loud on purpose: to-do AND an admin email with the direct
            // service link and a stop-billing prompt.
            $userId = (int) (Capsule::table('tblhosting')
                ->where('id', (int) $service->whmcs_service_id)->value('userid') ?? 0);
            $svcUrl = 'clientsservices.php?userid=' . $userId . '&id=' . (int) $service->whmcs_service_id;
            $reason = $envelope->reason !== '' ? $envelope->reason : 'no reason given';

            localAPI('AddTodoItem', [
                'date' => date('Y-m-d'),
                'title' => 'CHURNED AWAY: ' . $vtServiceId . ' — stop billing?',
                'description' => sprintf(
                    'Virtutel service %s (AVC %s, WHMCS service #%d — %s) was disconnected/churned away: %s. '
                    . 'Decide: terminate the WHMCS service to stop billing, or investigate if unexpected.',
                    $vtServiceId,
                    (string) ($service->avc_id ?? '?'),
                    $service->whmcs_service_id,
                    $svcUrl,
                    $reason
                ),
                'status' => 'Pending',
                'duedate' => date('Y-m-d'),
            ]);

            localAPI('SendAdminEmail', [
                'customsubject' => 'Korvix NBN: customer churned away — ' . $vtServiceId,
                'custommessage' => '<p><strong>' . htmlspecialchars($vtServiceId) . '</strong> (AVC '
                    . htmlspecialchars((string) ($service->avc_id ?? '?'))
                    . ') was disconnected/churned away carrier-side.</p>'
                    . '<p>Reason: ' . htmlspecialchars($reason) . '</p>'
                    . '<p>WHMCS service: <a href="' . htmlspecialchars($svcUrl) . '">#'
                    . (int) $service->whmcs_service_id . '</a> — billing is still running until '
                    . 'someone terminates it.</p>',
                'type' => 'system',
            ]);
        }

        return true;
    }
}
