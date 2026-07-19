<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Api\ApiException;
use WHMCS\Module\Server\VirtutelNbn\Api\VirtutelClient;

/**
 * Orchestrates the connect-order flow from WHMCS CreateAccount:
 * inputs (custom fields + config options) -> SQ validation -> device/port
 * auto-selection -> connect order -> local state rows.
 *
 * Custom fields read from the service (create them on the product,
 * admin-only unless used by checkout): Location ID, Churn AVC, Authority
 * Date, NTD ID, UNI-D Port, Copper Pair ID.
 */
class ProvisioningService
{
    public function __construct(private readonly VirtutelClient $client)
    {
    }

    /**
     * @param array $params WHMCS module params for CreateAccount
     * @return string the Virtutel order ID
     */
    public function createService(array $params): string
    {
        $serviceId = (int) $params['serviceid'];
        $fields = self::customFields($params);

        $locationId = strtoupper($fields['location id'] ?? '');
        if ($locationId === '') {
            throw new ApiException('Missing "Location ID" custom field on the service — run qualification first');
        }

        $speed = trim((string) (
            ($params['configoptions']['Speed Tier'] ?? '')
                ?: ($params['configoption1'] ?? '')
        ));
        if ($speed === '') {
            throw new ApiException('No speed tier configured (product config option or "Speed Tier" configurable option)');
        }

        $churnAvc = strtoupper($fields['churn avc'] ?? '');
        $authorityDate = $fields['authority date'] ?? '';

        // Qualify (Enhanced SQ for churns so serviceIDMatch guides selection).
        $sqParams = [];
        if ($churnAvc !== '') {
            $sqParams['serviceID'] = $churnAvc;
            $sqParams['customerAuthorityDate'] = $authorityDate !== '' ? $authorityDate : date('Y-m-d');
        }
        $qualification = (new QualificationService($this->client))->qualify($locationId, $sqParams);

        if (!$qualification['orderable']) {
            throw new ApiException(sprintf(
                'Location %s is not orderable (service class %s, status "%s")',
                $locationId,
                $qualification['service_class'] ?? '?',
                $qualification['serviceability_status']
            ));
        }
        if (!in_array($speed, $qualification['speeds'], true)) {
            throw new ApiException(sprintf(
                'Speed %s is not available at %s (available: %s)',
                $speed,
                $locationId,
                implode(', ', array_slice($qualification['speeds'], 0, 12))
            ));
        }

        $selection = self::selectDevice($qualification, [
            'ntdId' => $fields['ntd id'] ?? '',
            'uniDPortId' => $fields['uni-d port'] ?? '',
            'copperPairId' => $fields['copper pair id'] ?? '',
        ], $churnAvc !== '');

        $client = $params['clientsdetails'] ?? [];
        $payload = OrderPayloadBuilder::build([
            'locationId' => $locationId,
            'serviceType' => $qualification['service_type'],
            'speed' => $speed,
            'customerName' => trim(($client['firstname'] ?? '') . ' ' . ($client['lastname'] ?? '')),
            'orderRef' => 'WHMCS-' . $serviceId,
            'business' => false,
            'contact' => [
                'firstName' => (string) ($client['firstname'] ?? ''),
                'lastName' => (string) ($client['lastname'] ?? ''),
                'phoneNumber' => (string) ($client['phonenumberformatted'] ?? ($client['phonenumber'] ?? '')),
                'emailAddress' => (string) ($client['email'] ?? ''),
            ],
            'churnAvc' => $churnAvc,
            'authorityDate' => $authorityDate,
            'notes' => '',
        ] + $selection);

        $response = $this->client->request(
            'POST',
            VirtutelClient::PATH_PRODUCT_ORDERS . '?orderType=connect',
            ['action' => 'SubmitConnectOrder', 'json' => $payload]
        );

        $vtOrderId = (string) $response->get('vtOrderId', '');
        if ($vtOrderId === '') {
            throw new ApiException('Connect order accepted but no vtOrderId returned');
        }

        $this->persist($serviceId, $vtOrderId, $qualification, $speed, $churnAvc !== '', $payload, $response->data);

        return $vtOrderId;
    }

    /**
     * Device/port selection per docs/SIGNUP-FLOW.md: churns follow the
     * serviceIDMatch marker; new connections auto-pick the first free
     * UNI-D port (or supplied values verbatim).
     *
     * @return array{ntdId?: string, uniDPortId?: string, copperPairId?: string}
     */
    public static function selectDevice(array $qualification, array $chosen, bool $isChurn): array
    {
        $selection = array_filter([
            'ntdId' => trim((string) ($chosen['ntdId'] ?? '')),
            'uniDPortId' => trim((string) ($chosen['uniDPortId'] ?? '')),
            'copperPairId' => trim((string) ($chosen['copperPairId'] ?? '')),
        ], fn ($v) => $v !== '');

        if ($isChurn) {
            foreach ($qualification['ntds'] as $ntd) {
                foreach ($ntd['ports'] as $port) {
                    if ($port['service_id_match']) {
                        return ['ntdId' => $ntd['id'], 'uniDPortId' => $port['id']] + $selection;
                    }
                }
            }
            foreach ($qualification['copper_pairs'] as $pair) {
                if ($pair['service_id_match']) {
                    return ['copperPairId' => $pair['id']] + $selection;
                }
            }
        }

        // An explicit port choice must still be free in the fresh SQ —
        // a stale pick (taken since qualification) falls back to auto-pick.
        if (isset($selection['uniDPortId']) && $qualification['ntds'] !== []) {
            $stillFree = false;
            foreach ($qualification['ntds'] as $ntd) {
                foreach ($ntd['ports'] as $port) {
                    if (strcasecmp($port['id'], $selection['uniDPortId']) === 0) {
                        $stillFree = $port['free'];
                        break 2;
                    }
                }
            }
            if (!$stillFree) {
                unset($selection['uniDPortId'], $selection['ntdId']);
            }
        }

        if (!isset($selection['uniDPortId'])) {
            foreach ($qualification['ntds'] as $ntd) {
                foreach ($ntd['ports'] as $port) {
                    if ($port['free']) {
                        return ['ntdId' => $ntd['id'], 'uniDPortId' => $port['id']] + $selection;
                    }
                }
            }
        }

        if (!isset($selection['copperPairId']) && $qualification['copper_pairs'] !== []) {
            $selection['copperPairId'] = $qualification['copper_pairs'][0]['id'];
        }

        return $selection;
    }

    private function persist(
        int $serviceId,
        string $vtOrderId,
        array $qualification,
        string $speed,
        bool $isChurn,
        array $requestPayload,
        array $responsePayload,
    ): void {
        $now = date('Y-m-d H:i:s');

        Capsule::table('mod_virtutel_services')->updateOrInsert(
            ['whmcs_service_id' => $serviceId],
            [
                'nbn_location_id' => $qualification['location_id'],
                'technology_type' => $qualification['service_type'],
                'service_class' => $qualification['service_class'],
                'network_layer' => str_starts_with($speed, 'L3') ? 'layer3' : 'layer2',
                'speed_tier' => $speed,
                'carrier_status' => 'ordering',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $moduleServiceId = (int) Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)->value('id');

        Capsule::table('mod_virtutel_orders')->insert([
            'service_id' => $moduleServiceId,
            'order_type' => $isChurn ? 'churn' : 'connect',
            'vt_order_id' => $vtOrderId,
            'status' => 'NEW',
            'whmcs_status' => 'pending',
            'request_payload' => json_encode($requestPayload, JSON_UNESCAPED_SLASHES),
            'response_payload' => json_encode($responsePayload, JSON_UNESCAPED_SLASHES),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /** @return array<string,string> custom field values keyed by lowercase field name */
    private static function customFields(array $params): array
    {
        $fields = [];
        foreach ((array) ($params['customfields'] ?? []) as $name => $value) {
            $fields[strtolower(trim((string) $name))] = trim((string) $value);
        }

        return $fields;
    }
}
