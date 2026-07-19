<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Module\Server\VirtutelNbn\Api\VirtutelClient;

/**
 * Address search and service qualification (docs/api/03, 04).
 *
 * parseQualification() is pure so checkout/UI logic can be unit-tested
 * against the documented SQ fixtures.
 */
class QualificationService
{
    /** Service classes that cannot take an order at all. */
    private const NOT_ORDERABLE_CLASSES = [0, 4, 7, 10, 20, 30];

    public function __construct(private readonly VirtutelClient $client)
    {
    }

    /**
     * Address search. $search is one of the documented body shapes:
     * ['unstructured' => [...]], ['structured' => [...]], ['GNAF' => ...],
     * ['coordinates' => [...]], or ['locationId' => 'LOC...'].
     *
     * @return array[] result rows, best match first ({id, fullAddress, ...})
     */
    public function searchAddress(array $search): array
    {
        $response = $this->client->request('POST', VirtutelClient::PATH_LOCATIONS, [
            'action' => 'AddressSearch',
            'json' => $search,
        ]);

        return array_values((array) $response->get('responseData', []));
    }

    /**
     * Run a Normal or Enhanced SQ and return the parsed summary.
     *
     * @param array $params optional: customerAuthorityDate, serviceID (churn
     *                      validation), POTSInterconnect, productType
     */
    public function qualify(string $locationId, array $params = []): array
    {
        return $this->qualifyWithRaw($locationId, $params)['parsed'];
    }

    /** @return array{parsed: array, raw: array} */
    public function qualifyWithRaw(string $locationId, array $params = []): array
    {
        $response = $this->client->request(
            'GET',
            VirtutelClient::PATH_SERVICE_QUALIFICATIONS . '/' . rawurlencode($locationId),
            ['action' => 'ServiceQualification', 'query' => $params]
        );

        return ['parsed' => self::parseQualification($response->data), 'raw' => $response->data];
    }

    /**
     * Reduce a raw SQ response to the decisions checkout needs.
     */
    public static function parseQualification(array $sq): array
    {
        $site = $sq['siteRestriction'] ?? [];
        $tech = $site['supportingTechnology'] ?? [];
        $features = $site['supportingRelatedLocationFeatures'] ?? [];

        $serviceClass = is_numeric($tech['serviceabilityClass'] ?? null)
            ? (int) $tech['serviceabilityClass']
            : null;
        $speeds = array_values((array) ($sq['virtutelSpeedsAvailable'] ?? []));

        return [
            'location_id' => (string) ($sq['id'] ?? ''),
            'service_type' => (string) ($sq['serviceType'] ?? ''), // nfas/ncas/nhas/nwas/nsas
            'technology' => (string) ($tech['primaryAccessTechnology'] ?? ''),
            'service_class' => $serviceClass,
            'serviceability_status' => (string) ($site['serviceabilityStatus'] ?? ''),
            'orderable' => $serviceClass !== null
                && !in_array($serviceClass, self::NOT_ORDERABLE_CLASSES, true)
                && $speeds !== [],
            'speeds' => $speeds,
            'new_development_charge' => (bool) ($features['newDevelopmentsChargeApplies'] ?? false),
            'fibre_upgrade_available' =>
                (($tech['alternativeTechnology'] ?? '') === 'Fibre'),
            'ntds' => self::parseNtds($site['supportingResource'] ?? []),
            'copper_pairs' => self::parseCopperPairs($site['supportingResource'] ?? []),
            'notes' => array_values((array) ($site['notes'] ?? [])),
            'poi_id' => (string) ($site['supportingRelatedSiteBoundaries']['poiId'] ?? ''),
        ];
    }

    /**
     * NTDs with their UNI-D ports. Selection rule (docs/SIGNUP-FLOW.md):
     * auto-pick the first Free port; surface a choice only when >1 NTD or
     * the customer wants a specific port. serviceIDMatch marks the churn
     * match when an Enhanced SQ was run.
     *
     * @return array[] [{id, ports: [{id, status, free, service_id_match}], speed_tiers_supported}]
     */
    private static function parseNtds(array $resources): array
    {
        $ntds = [];
        foreach ($resources as $resource) {
            if (($resource['type'] ?? '') !== 'NTD') {
                continue;
            }
            $ports = [];
            foreach ((array) ($resource['uniPortD'] ?? []) as $port) {
                $ports[] = [
                    'id' => (string) ($port['id'] ?? ''),
                    'status' => (string) ($port['status'] ?? ''),
                    'free' => strcasecmp((string) ($port['status'] ?? ''), 'Free') === 0,
                    'service_id_match' => (bool) ($port['serviceIDMatch'] ?? false),
                ];
            }
            $ntds[] = [
                'id' => (string) ($resource['id'] ?? ''),
                'ports' => $ports,
                'speed_tiers_supported' => array_values((array) ($resource['speedTiersSupported'] ?? [])),
            ];
        }

        return $ntds;
    }

    /**
     * Copper pairs for NCAS (FTTB/FTTN/FTTC) sites, with POTS/churn match
     * markers from Enhanced SQs.
     *
     * @return array[] [{id, status, service_id_match, pots_match}]
     */
    private static function parseCopperPairs(array $resources): array
    {
        $pairs = [];
        foreach ($resources as $resource) {
            if (($resource['type'] ?? '') !== 'CopperLineResource') {
                continue;
            }
            $pairs[] = [
                'id' => (string) ($resource['id'] ?? ''),
                'status' => (string) ($resource['copperPairStatus'] ?? ($resource['NBNServiceStatus'] ?? '')),
                'service_id_match' => (bool) ($resource['serviceIDMatch'] ?? false),
                'pots_match' => (bool) ($resource['POTSInterconnectMatch'] ?? false),
            ];
        }

        return $pairs;
    }
}
