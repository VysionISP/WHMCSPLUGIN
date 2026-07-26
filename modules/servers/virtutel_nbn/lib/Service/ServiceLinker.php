<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Api\VirtutelClient;

/**
 * Links an EXISTING Virtutel service to a WHMCS service without lodging an
 * order: looks the service up via GET /services (by VT service ID, PRI
 * supplier ID, or AVC), then writes the mod_virtutel_services row and the
 * informational custom fields. This is the building block the go-live bulk
 * import reuses per service.
 */
class ServiceLinker
{
    /**
     * @param string $ref VT0000123 | PRI000000001234 | AVC000000001234
     * @return array|null the matched service from the API, null when no
     *                    unambiguous match exists
     */
    public static function lookup(VirtutelClient $client, string $ref): ?array
    {
        $ref = strtoupper(trim($ref));
        if ($ref === '') {
            return null;
        }

        if (preg_match('/^VT\d+$/', $ref)) {
            $query = ['vtServiceId' => $ref];
        } elseif (preg_match('/^PRI[0-9A-Z]+$/', $ref)) {
            $query = ['supplierServiceId' => $ref];
        } else {
            // AVC (or anything else): free-text search, exact-match below.
            $query = ['searchString' => $ref, 'limit' => 10];
        }

        $response = $client->request('GET', VirtutelClient::PATH_SERVICES, [
            'action' => 'LinkLookup',
            'query' => $query,
        ]);

        $services = (array) $response->get('services', []);
        if ($services === []) {
            return null;
        }

        foreach ($services as $svc) {
            if (in_array($ref, [
                strtoupper((string) ($svc['vtServiceId'] ?? '')),
                strtoupper((string) ($svc['supplierServiceId'] ?? '')),
                strtoupper((string) ($svc['avcId'] ?? '')),
            ], true)) {
                return $svc;
            }
        }

        return count($services) === 1 ? $services[0] : null;
    }

    /** Writes the link row + custom fields for a looked-up service. */
    public static function link(int $whmcsServiceId, array $svc): void
    {
        $now = date('Y-m-d H:i:s');
        $trim = fn ($v, int $len) => ($s = substr(trim((string) $v), 0, $len)) !== '' ? $s : null;

        $values = [
            'vt_service_id' => $trim($svc['vtServiceId'] ?? '', 32),
            'avc_id' => $trim($svc['avcId'] ?? '', 32),
            'nbn_location_id' => $trim($svc['locationId'] ?? '', 32),
            'technology_type' => $trim($svc['accessTechnology']['subType'] ?? '', 16),
            'speed_tier' => $trim($svc['speed'] ?? '', 32),
            'carrier_status' => $trim($svc['status'] ?? '', 64),
            'external_ref' => $trim($svc['supplierServiceId'] ?? '', 64),
            'updated_at' => $now,
        ];

        $exists = Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $whmcsServiceId)->exists();
        if ($exists) {
            Capsule::table('mod_virtutel_services')
                ->where('whmcs_service_id', $whmcsServiceId)->update($values);
        } else {
            Capsule::table('mod_virtutel_services')->insert(
                $values + ['whmcs_service_id' => $whmcsServiceId, 'created_at' => $now]
            );
        }

        CustomFields::writeServiceValues($whmcsServiceId, [
            'Location ID' => (string) ($svc['locationId'] ?? ''),
            'NTD ID' => (string) ($svc['cpiNtdId'] ?? ''),
            'UNI-D Port' => (string) ($svc['uniDPortId'] ?? ''),
        ]);
    }
}
