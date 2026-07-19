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
        $address = trim((string) ($input['address'] ?? ''));
        if (strlen($address) < 8) {
            $respond(422, ['error' => 'Please enter your full street address.']);
        }

        $results = $service->searchAddress([
            'unstructured' => ['address' => strtoupper($address), 'fuzzy' => true],
        ]);

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

        $q = $service->qualify($locId);

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

        $respond(200, [
            'locId' => $q['location_id'],
            'technology' => $technologyNames[$q['service_type']]
                ?? ('NBN ' . ($q['technology'] !== '' ? $q['technology'] : 'Fixed Line')),
            'serviceClass' => $q['service_class'],
            'readiness' => ConnectReadiness::assess($q),
            'tiers' => SpeedTier::customerTiers($q['speeds']),
            'newDevelopmentCharge' => $q['new_development_charge'],
            'freePorts' => $q['ntds'] !== [] ? $freePorts : null,
        ]);
    }

    $respond(422, ['error' => 'Unknown action']);
} catch (\Throwable $e) {
    if (function_exists('logActivity')) {
        logActivity('Virtutel NBN: public qualify API error: ' . $e->getMessage());
    }
    $respond(500, ['error' => 'Address check is temporarily unavailable — please try again in a few minutes.']);
}
