<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Api\ApiException;
use WHMCS\Module\Server\VirtutelNbn\Api\VirtutelClient;
use WHMCS\Module\Server\VirtutelNbn\Radius\FreeRadiusSqlProvisioner;
use WHMCS\Module\Server\VirtutelNbn\Radius\RadiusConfig;
use WHMCS\Module\Server\VirtutelNbn\Radius\RadiusProvisioner;

/**
 * Post-activation lifecycle operations.
 *
 * Suspend/unsuspend/terminate act on FreeRADIUS (profile swap or removal +
 * CoA disconnect) — Virtutel's Layer 3 suspension API is not used because
 * all services are Layer 2 IPoE. Terminations and speed changes also lodge
 * the corresponding Virtutel order.
 */
class LifecycleService
{
    private ?RadiusProvisioner $radius;

    public function __construct(?RadiusProvisioner $radius = null)
    {
        if ($radius !== null) {
            $this->radius = $radius;
        } else {
            $config = RadiusConfig::load();
            $this->radius = RadiusConfig::isConfigured($config)
                ? new FreeRadiusSqlProvisioner($config)
                : null;
        }
    }

    public function suspend(int $whmcsServiceId): void
    {
        $service = $this->serviceRow($whmcsServiceId);
        $avc = $this->requireAvc($service);

        $this->radiusOrFail()->suspend($avc);
        $this->radius->disconnectSession($avc);

        $this->touchStatus($service, 'suspended');
        logActivity("Virtutel NBN: suspended AVC {$avc} (service #{$whmcsServiceId}) via RADIUS");
    }

    public function unsuspend(int $whmcsServiceId): void
    {
        $service = $this->serviceRow($whmcsServiceId);
        $avc = $this->requireAvc($service);

        $this->radiusOrFail()->unsuspend($avc);
        // Drop the (walled-garden) session so the BNG re-authorises into
        // the full profile.
        $this->radius->disconnectSession($avc);

        $this->touchStatus($service, 'Active');
        logActivity("Virtutel NBN: unsuspended AVC {$avc} (service #{$whmcsServiceId}) via RADIUS");
    }

    /**
     * Lodge the Virtutel disconnect order and cut access immediately.
     */
    public function terminate(VirtutelClient $client, int $whmcsServiceId): void
    {
        $service = $this->serviceRow($whmcsServiceId);

        if (!empty($service->vt_service_id)) {
            $response = $client->request(
                'POST',
                VirtutelClient::PATH_PRODUCT_ORDERS . '?orderType=disconnect',
                [
                    'action' => 'SubmitDisconnectOrder',
                    'json' => ['service' => ['vtServiceId' => (string) $service->vt_service_id]],
                ]
            );
            $this->recordOrder($service, 'disconnect', (string) $response->get('vtOrderId', ''), [
                'service' => ['vtServiceId' => (string) $service->vt_service_id],
            ]);
        } else {
            // No VT service ID on file — staff must disconnect carrier-side.
            localAPI('AddTodoItem', [
                'date' => date('Y-m-d'),
                'title' => 'Virtutel NBN: manual disconnect needed',
                'description' => "Service #{$whmcsServiceId} terminated in WHMCS but has no VT service ID — lodge the Virtutel disconnect manually.",
                'status' => 'Pending',
                'duedate' => date('Y-m-d', strtotime('+1 day')),
            ]);
        }

        // Access stops now regardless of the carrier order's async progress.
        if ($this->radius !== null && !empty($service->avc_id)) {
            $this->radius->terminate((string) $service->avc_id);
            $this->radius->disconnectSession((string) $service->avc_id);
        }

        $this->touchStatus($service, 'terminating');
        logActivity("Virtutel NBN: termination started for service #{$whmcsServiceId}");
    }

    /**
     * Lodge a Modify Speed order. RADIUS attributes are applied when the
     * VTOrderCompleted callback lands (OrderCompletion), keeping billing,
     * carrier, and AAA state in step.
     *
     * @return string the Virtutel order ID
     */
    public function changeSpeed(VirtutelClient $client, int $whmcsServiceId, string $newSpeed): string
    {
        $service = $this->serviceRow($whmcsServiceId);
        if (empty($service->vt_service_id)) {
            throw new ApiException('No VT service ID on file — cannot lodge a speed change');
        }
        if (SpeedTier::describe($newSpeed) === null) {
            throw new ApiException("'{$newSpeed}' is not a recognised speed tier enum");
        }
        if ((string) $service->speed_tier === $newSpeed) {
            throw new ApiException('The service is already on that speed tier');
        }

        $payload = ['service' => ['vtServiceId' => (string) $service->vt_service_id, 'nbn' => ['speed' => $newSpeed]]];
        $response = $client->request(
            'POST',
            VirtutelClient::PATH_PRODUCT_ORDERS . '?orderType=modify',
            ['action' => 'SubmitModifySpeedOrder', 'json' => $payload]
        );

        $vtOrderId = (string) $response->get('vtOrderId', '');
        if ($vtOrderId === '') {
            throw new ApiException('Modify Speed order accepted but no vtOrderId returned');
        }
        $this->recordOrder($service, 'modify_speed', $vtOrderId, $payload);

        logActivity(sprintf(
            'Virtutel NBN: speed change to %s lodged for service #%d (order %s)',
            $newSpeed,
            $whmcsServiceId,
            $vtOrderId
        ));

        return $vtOrderId;
    }

    /** Provision AAA for a newly-completed connect order. */
    public function provisionRadius(string $avcId, string $speedTier): void
    {
        if ($this->radius === null) {
            logActivity("Virtutel NBN: RADIUS not configured — AVC {$avcId} NOT provisioned in AAA");

            return;
        }

        $this->radius->provision($avcId, $speedTier);
        logActivity("Virtutel NBN: provisioned AVC {$avcId} in FreeRADIUS ({$speedTier})");
    }

    /** Apply a completed speed change to AAA and drop the session. */
    public function applyRadiusSpeed(string $avcId, string $speedTier): void
    {
        if ($this->radius === null) {
            logActivity("Virtutel NBN: RADIUS not configured — speed for AVC {$avcId} NOT updated in AAA");

            return;
        }

        $this->radius->applySpeed($avcId, $speedTier);
        $this->radius->disconnectSession($avcId);
        logActivity("Virtutel NBN: RADIUS speed updated for AVC {$avcId} ({$speedTier})");
    }

    private function radiusOrFail(): RadiusProvisioner
    {
        if ($this->radius === null) {
            throw new ApiException(
                'FreeRADIUS is not configured (Addons > Virtutel NBN Tools > Configure) — cannot perform RADIUS-side operations'
            );
        }

        return $this->radius;
    }

    private function serviceRow(int $whmcsServiceId): object
    {
        $service = Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $whmcsServiceId)->first();
        if (!$service) {
            throw new ApiException('Service is not linked to a Virtutel service record');
        }

        return $service;
    }

    private function requireAvc(object $service): string
    {
        $avc = (string) ($service->avc_id ?? '');
        if ($avc === '') {
            throw new ApiException('No AVC ID on file for this service — it may not be active yet');
        }

        return $avc;
    }

    private function touchStatus(object $service, string $status): void
    {
        Capsule::table('mod_virtutel_services')->where('id', $service->id)->update([
            'carrier_status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function recordOrder(object $service, string $type, string $vtOrderId, array $payload): void
    {
        $now = date('Y-m-d H:i:s');
        Capsule::table('mod_virtutel_orders')->insert([
            'service_id' => $service->id,
            'order_type' => $type,
            'vt_order_id' => $vtOrderId !== '' ? $vtOrderId : null,
            'status' => 'NEW',
            'whmcs_status' => 'pending',
            'request_payload' => json_encode($payload, JSON_UNESCAPED_SLASHES),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
