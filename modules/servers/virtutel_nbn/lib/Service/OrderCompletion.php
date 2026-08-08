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

        // Non-connect orders have their own completion semantics.
        if ($order->order_type === 'modify_speed') {
            $this->completeSpeedChange($order, $service);

            return;
        }
        if ($order->order_type === 'disconnect') {
            $this->completeDisconnect($order, $service);

            return;
        }

        $avcId = '';
        $vtServiceId = '';
        $address = '';
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

            if ((string) ($service->service_address ?? '') === '') {
                $address = ServiceLinker::resolveAddress($client, (string) ($service->nbn_location_id ?? ''));
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
            'service_address' => $address !== '' ? $address : null,
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

        // Billing starts at ACTIVATION, not order date (decided 2026-07-26):
        // the month paid at checkout covers activation -> activation + one
        // cycle, so push the next due date out to that. Forward-only — an
        // already-later due date (manual adjustment) is never pulled back.
        $this->anchorBillingToActivation((int) $service->whmcs_service_id);

        // One-time "you're connected" welcome email with IPoE setup steps
        // (guarded — completion can fire from both callback and poll).
        try {
            $welcomeKey = 'welcomed_' . (int) $service->whmcs_service_id;
            if ((string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get($welcomeKey, '') ?? '') === '') {
                $tier = !empty($service->speed_tier)
                    ? SpeedTier::describe((string) $service->speed_tier) : null;
                EmailNotifier::serviceActivated((int) $service->whmcs_service_id, [
                    'nbn_avc' => $avcId !== '' ? $avcId : (string) ($service->avc_id ?? ''),
                    'nbn_speed' => (string) ($tier['label'] ?? ($service->speed_tier ?? '')),
                    'nbn_address' => $address !== '' ? $address : (string) ($service->service_address ?? ''),
                ]);
                \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set($welcomeKey, (string) time());
            }
        } catch (\Throwable $e) {
            if (function_exists('logActivity')) {
                logActivity('Virtutel NBN: welcome email failed for service #'
                    . $service->whmcs_service_id . ': ' . $e->getMessage());
            }
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

        // AAA: the customer's session authorises against this entry the
        // moment they plug in.
        if ($avcId !== '' && !empty($service->speed_tier)) {
            try {
                (new LifecycleService())->provisionRadius($avcId, (string) $service->speed_tier);
            } catch (\Throwable $e) {
                if (function_exists('localAPI')) {
                    localAPI('AddTodoItem', [
                        'date' => date('Y-m-d'),
                        'title' => 'Virtutel NBN: RADIUS provisioning FAILED',
                        'description' => sprintf(
                            'AVC %s (service #%d) is active carrier-side but could not be provisioned in FreeRADIUS: %s',
                            $avcId,
                            $service->whmcs_service_id,
                            $e->getMessage()
                        ),
                        'status' => 'Pending',
                        'duedate' => date('Y-m-d'),
                    ]);
                }
            }
        }
    }

    private function anchorBillingToActivation(int $whmcsServiceId): void
    {
        try {
            $hosting = Capsule::table('tblhosting')->where('id', $whmcsServiceId)
                ->first(['billingcycle', 'nextduedate', 'nextinvoicedate']);
            if (!$hosting) {
                return;
            }

            $interval = match (strtolower((string) ($hosting->billingcycle ?? ''))) {
                'quarterly' => '+3 months',
                'semi-annually' => '+6 months',
                'annually' => '+1 year',
                'biennially' => '+2 years',
                'triennially' => '+3 years',
                'free account', 'one time' => '',
                default => '+1 month', // monthly (all residential plans)
            };
            if ($interval === '') {
                return;
            }

            $newDue = date('Y-m-d', strtotime($interval));
            $current = (string) ($hosting->nextduedate ?? '');
            if ($current !== '' && $current !== '0000-00-00' && $current >= $newDue) {
                return; // already at or beyond the activation-anchored date
            }

            Capsule::table('tblhosting')->where('id', $whmcsServiceId)->update([
                'nextduedate' => $newDue,
                'nextinvoicedate' => $newDue,
            ]);

            if (function_exists('logActivity')) {
                logActivity(sprintf(
                    'Virtutel NBN: billing anchored to activation for service #%d — next due date %s (was %s)',
                    $whmcsServiceId,
                    $newDue,
                    $current !== '' ? $current : 'unset'
                ));
            }
        } catch (\Throwable $e) {
            // Billing adjustment must never block activation.
            if (function_exists('logActivity')) {
                logActivity(sprintf(
                    'Virtutel NBN: could not anchor billing to activation for service #%d: %s',
                    $whmcsServiceId,
                    $e->getMessage()
                ));
            }
        }
    }

    private function completeSpeedChange(object $order, object $service): void
    {
        $payload = json_decode((string) ($order->request_payload ?? ''), true);
        $newSpeed = (string) ($payload['service']['nbn']['speed'] ?? '');
        if ($newSpeed === '') {
            return;
        }

        Capsule::table('mod_virtutel_services')->where('id', $service->id)->update([
            'speed_tier' => $newSpeed,
            'network_layer' => str_starts_with($newSpeed, 'L3') ? 'layer3' : 'layer2',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!empty($service->avc_id)) {
            try {
                (new LifecycleService())->applyRadiusSpeed((string) $service->avc_id, $newSpeed);
            } catch (\Throwable $e) {
                if (function_exists('logActivity')) {
                    logActivity(sprintf(
                        'Virtutel NBN: speed change %s completed carrier-side but RADIUS update failed: %s',
                        $order->vt_order_id,
                        $e->getMessage()
                    ));
                }
            }
        }

        if (function_exists('logActivity')) {
            logActivity(sprintf(
                'Virtutel NBN: speed change complete for service #%d — now %s',
                $service->whmcs_service_id,
                $newSpeed
            ));
        }
    }

    private function completeDisconnect(object $order, object $service): void
    {
        Capsule::table('mod_virtutel_services')->where('id', $service->id)->update([
            'carrier_status' => 'disconnected',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (function_exists('logActivity')) {
            logActivity(sprintf(
                'Virtutel NBN: disconnect order %s complete — service #%d fully disconnected carrier-side',
                $order->vt_order_id,
                $service->whmcs_service_id
            ));
        }
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
