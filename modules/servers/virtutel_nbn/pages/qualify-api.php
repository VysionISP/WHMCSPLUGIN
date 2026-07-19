<?php

/**
 * Public JSON API behind the customer qualification page.
 *
 * Actions (POST, application/json or form-encoded):
 *  - action=search  address=<street address>    -> candidate locations
 *  - action=qualify locId=<LOC...>              -> customer-safe qualification
 *
 * Customer-safe means: friendly technology + connect-readiness wording,
 * Layer 2 speed tiers with friendly labels, a free-port count — and none of
 * the internal SQ detail (NTD serials, port maps, supportingProduct data).
 * Rate limited per IP; token-free because it exposes nothing sensitive.
 */

use WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory;
use WHMCS\Module\Server\VirtutelNbn\Migrations;
use WHMCS\Module\Server\VirtutelNbn\Service\ConnectReadiness;
use WHMCS\Module\Server\VirtutelNbn\Service\QualificationService;
use WHMCS\Module\Server\VirtutelNbn\Service\RateLimiter;
use WHMCS\Module\Server\VirtutelNbn\Service\SpeedTier;

require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../lib/Autoloader.php';

header('Content-Type: application/json');
header('X-Robots-Tag: noindex');

$respond = function (int $status, array $data): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $respond(405, ['error' => 'POST only']);
}

$input = $_POST;
if (str_contains((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
    $decoded = json_decode((string) file_get_contents('php://input'), true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

try {
    Migrations::ensure();

    if (!RateLimiter::allow((string) ($_SERVER['REMOTE_ADDR'] ?? ''))) {
        $respond(429, ['error' => 'Too many requests — please try again shortly.']);
    }

    $client = ClientFactory::forWhmcsService(0); // first enabled server
    $service = new QualificationService($client);
    $action = (string) ($input['action'] ?? '');

    if ($action === 'search') {
        $lat = $input['lat'] ?? null;
        $lng = $input['lng'] ?? null;

        if (is_numeric($lat) && is_numeric($lng)) {
            // Preferred path: Google Places picked the address; NBN's
            // coordinate search returns the exact premises (incl. units).
            $lat = (float) $lat;
            $lng = (float) $lng;
            if ($lat < -44.5 || $lat > -9.0 || $lng < 112.0 || $lng > 154.5) {
                $respond(422, ['error' => 'That location is outside Australia.']);
            }
            $search = ['coordinates' => [
                'latitude' => (string) $lat,
                'longitude' => (string) $lng,
            ]];
        } else {
            $address = trim((string) ($input['address'] ?? ''));
            if (strlen($address) < 8) {
                $respond(422, ['error' => 'Please enter your full street address.']);
            }
            $search = ['unstructured' => ['address' => strtoupper($address), 'fuzzy' => true]];
        }

        $results = $service->searchAddress($search);

        $matches = [];
        foreach (array_slice($results, 0, 10) as $row) {
            $label = (string) (($row['fullAddress'] ?? '') ?: ($row['formattedAddress'] ?? ''));
            if (($row['id'] ?? '') !== '' && $label !== '') {
                $matches[] = ['locId' => (string) $row['id'], 'address' => $label];
            }
        }

        $respond(200, ['matches' => $matches]);
    }

    if ($action === 'qualify') {
        $locId = strtoupper(trim((string) ($input['locId'] ?? '')));
        if (!preg_match('/^LOC\d{9,15}$/', $locId)) {
            $respond(422, ['error' => 'Invalid location reference.']);
        }

        // Optional churn validation: full AVC ID or its last 5 digits, with
        // the customer's transfer authorisation implied by submitting it
        // (authority date = today).
        $avcId = strtoupper(trim((string) ($input['avcId'] ?? '')));
        $sqParams = [];
        if ($avcId !== '') {
            if (!preg_match('/^(AVC\d{12}|\d{5})$/', $avcId)) {
                $respond(422, ['error' => 'AVC IDs look like AVC123456789012 (or just the last 5 digits).']);
            }
            $sqParams = ['serviceID' => $avcId, 'customerAuthorityDate' => date('Y-m-d')];
        }

        $q = $service->qualify($locId, $sqParams);

        $churn = null;
        if ($avcId !== '') {
            $match = QualificationService::findChurnMatch($q);
            if ($match !== null) {
                $churn = ['matched' => true, 'attempted' => $avcId];
            } else {
                $reasons = [
                    'RJ002003' => 'We couldn\'t find an active service with that AVC ID. Double-check it with your current provider.',
                    'RJ002004' => 'That service appears to have recently been disconnected, so there\'s nothing to transfer — you can order a new connection instead.',
                    'RJ002005' => 'That AVC ID belongs to a different address. Check the ID, or search for the address it\'s connected at.',
                    'RJ002006' => 'That AVC ID was recently disconnected at a different address — you can order a new connection here instead.',
                ];
                $code = $q['restriction_error']['code'] ?? '';
                $churn = [
                    'matched' => false,
                    'attempted' => $avcId,
                    'error' => $reasons[$code]
                        ?? 'We couldn\'t match that AVC ID to a service at this address. Check the ID with your current provider, or continue with a new connection.',
                ];
            }
        }

        $freePorts = 0;
        foreach ($q['ntds'] as $ntd) {
            foreach ($ntd['ports'] as $port) {
                $freePorts += $port['free'] ? 1 : 0;
            }
        }

        $technologyNames = [
            'nfas' => 'NBN Fibre to the Premises (FTTP)',
            'nhas' => 'NBN Hybrid Fibre Coaxial (HFC)',
            'nwas' => 'NBN Fixed Wireless',
            'nsas' => 'NBN Satellite',
        ];

        // WHMCS products on this module whose configured speed enum is
        // orderable here become the plan cards, with live pricing.
        $plans = [];
        if ($q['speeds'] !== []) {
            try {
                $currency = \WHMCS\Database\Capsule::table('tblcurrencies')
                    ->orderByDesc('default')->orderBy('id')->first();
                $systemUrl = rtrim((string) (\WHMCS\Database\Capsule::table('tblconfiguration')
                    ->where('setting', 'SystemURL')->value('value') ?? ''), '/');

                $products = \WHMCS\Database\Capsule::table('tblproducts')
                    ->where('servertype', 'virtutel_nbn')
                    ->where('hidden', 0)
                    ->get(['id', 'name', 'configoption1']);

                foreach ($products as $product) {
                    $enum = trim((string) $product->configoption1);
                    if ($enum === '' || !in_array($enum, $q['speeds'], true)) {
                        continue;
                    }
                    $tier = SpeedTier::describe($enum);
                    $monthly = $currency ? \WHMCS\Database\Capsule::table('tblpricing')
                        ->where('type', 'product')->where('currency', $currency->id)
                        ->where('relid', $product->id)->value('monthly') : null;
                    if ($monthly === null || (float) $monthly < 0) {
                        continue;
                    }

                    $plans[] = [
                        'name' => (string) $product->name,
                        'speedLabel' => $tier['label'] ?? $enum,
                        'down' => $tier['down'] ?? 0,
                        'price' => ($currency->prefix ?? '$') . number_format((float) $monthly, 2)
                            . ($currency->suffix ? ' ' . $currency->suffix : '') . '/mo',
                        'orderUrl' => $systemUrl . '/cart.php?a=add&pid=' . (int) $product->id
                            . '&vt_locid=' . rawurlencode($q['location_id'])
                            . ($churn !== null && $churn['matched']
                                ? '&vt_avc=' . rawurlencode($avcId) : ''),
                    ];
                }
                usort($plans, fn ($a, $b) => $a['down'] <=> $b['down']);
            } catch (\Throwable $e) {
                $plans = []; // pricing lookup must never break qualification
            }
        }

        $readiness = ConnectReadiness::assess($q);
        if ($churn !== null && $churn['matched']) {
            // Validated transfer: the existing port/pair carries over, so no
            // technician is needed regardless of port availability.
            $readiness = [
                'code' => 'transfer_ready',
                'label' => 'Ready to transfer',
                'description' => 'We\'ve matched your current service at this address. Transfers are done remotely '
                    . '— no technician visit, and your connection typically switches over within a day.',
            ];
        }

        $respond(200, [
            'locId' => $q['location_id'],
            'technology' => $technologyNames[$q['service_type']]
                ?? ('NBN ' . ($q['technology'] !== '' ? $q['technology'] : 'Fixed Line')),
            'serviceClass' => $q['service_class'],
            'readiness' => $readiness,
            'tiers' => SpeedTier::customerTiers($q['speeds']),
            'plans' => $plans,
            'newDevelopmentCharge' => $q['new_development_charge'],
            'freePorts' => $q['ntds'] !== [] ? $freePorts : null,
            'churn' => $churn,
            'hasExistingService' => $q['ntds'] !== [] && $freePorts === 0,
        ]);
    }

    $respond(422, ['error' => 'Unknown action']);
} catch (\Throwable $e) {
    if (function_exists('logActivity')) {
        logActivity('Virtutel NBN: public qualify API error: ' . $e->getMessage());
    }
    $respond(500, ['error' => 'Address check is temporarily unavailable — please try again in a few minutes.']);
}
