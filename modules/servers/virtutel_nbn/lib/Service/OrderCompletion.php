<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory;
use WHMCS\Module\Server\VirtutelNbn\Api\VirtutelClient;

/**
 * Terminal order transitions, shared by the callback handler and the cron
 * poll backstop.
 *
 * On VTOrderCompleted: pull the order for the AVC ID, resolve the VT
 * service ID via /services, store both, and activate the WHMCS service.
 * (RADIUS provisioning hooks in here in build step 4.)
 */
class OrderCompletion
{
    /** @param object $order row from mod_virtutel_orders */
    public function complete(object $order): void
    {
        $service = Capsule::table('mod_virtutel_services')->where('id', $order->service_id)->first();
        if (!$service) {
            return;
        }

        $avcId = '';
        $vtServiceId = '';
        try {
            $client = ClientFactory::forWhmcsService((int) $service->whmcs_service_id);

            $orderDetails = $client->request(
                'GET',
                VirtutelClient::PATH_PRODUCT_ORDERS . '/' . rawurlencode((string) $order->vt_order_id),
                ['action' => 'FetchOrder']
            );
            $avcId = (string) ($orderDetails->get('service.nbn.avcId') ?? '');

            if ($avcId !== '') {
                $services = $client->request('GET', VirtutelClient::PATH_SERVICES, [
                    'action' => 'LookupServiceByAvc',
                    'query' => ['searchString' => $avcId, 'page' => 1, 'limit' => 5],
                ]);
                foreach ((array) $services->get('services', []) as $candidate) {
                    if (($candidate['avcId'] ?? '') === $avcId) {
                        $vtServiceId = (string) ($candidate['vtServiceId'] ?? '');
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Enrichment failure must not block activation; the daily sync
            // can fill in identifiers later.
            if (function_exists('logActivity')) {
                logActivity(sprintf(
                    'Virtutel NBN: completed order %s but identifier lookup failed: %s',
                    $order->vt_order_id,
                    $e->getMessage()
                ));
            }
        }

        Capsule::table('mod_virtutel_services')->where('id', $service->id)->update(array_filter([
            'avc_id' => $avcId !== '' ? $avcId : null,
            'vt_service_id' => $vtServiceId !== '' ? $vtServiceId : null,
            'carrier_status' => 'Active',
            'updated_at' => date('Y-m-d H:i:s'),
        ], fn ($v) => $v !== null));

        if (function_exists('localAPI')) {
            localAPI('UpdateClientProduct', array_filter([
                'serviceid' => (int) $service->whmcs_service_id,
                'status' => 'Active',
                // AVC ID doubles as the service identifier ("domain" column).
                'domain' => $avcId !== '' ? $avcId : null,
            ]));
        }

        if (function_exists('logActivity')) {
            logActivity(sprintf(
                'Virtutel NBN: order %s complete — service #%d activated (AVC %s, VT %s)',
                $order->vt_order_id,
                $service->whmcs_service_id,
                $avcId !== '' ? $avcId : 'pending',
                $vtServiceId !== '' ? $vtServiceId : 'pending'
            ));
        }

        // Step 4: RadiusProvisioner->provision($avcId, $service->speed_tier) goes here.
    }

    /** @param object $order row from mod_virtutel_orders */
    public function cancelled(object $order, string $reason = ''): void
    {
        $service = Capsule::table('mod_virtutel_services')->where('id', $order->service_id)->first();
        if (!$service) {
            return;
        }

        Capsule::table('mod_virtutel_services')->where('id', $service->id)->update([
            'carrier_status' => 'order_cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (function_exists('localAPI')) {
            localAPI('AddTodoItem', [
                'date' => date('Y-m-d'),
                'title' => 'Virtutel NBN: order cancelled',
                'description' => sprintf(
                    'Virtutel cancelled order %s for WHMCS service #%d%s. Review the service and refund/re-order as appropriate.',
                    $order->vt_order_id,
                    $service->whmcs_service_id,
                    $reason !== '' ? ' — ' . $reason : ''
                ),
                'status' => 'Pending',
                'duedate' => date('Y-m-d', strtotime('+1 day')),
            ]);

            logActivity(sprintf(
                'Virtutel NBN: order %s CANCELLED for service #%d%s',
                $order->vt_order_id,
                $service->whmcs_service_id,
                $reason !== '' ? ' — ' . $reason : ''
            ));
        }
    }
}
