<?php

/**
 * Client-area diagnostics API (JSON): runs/polls customer self-serve line
 * checks. Auth is explicit: a logged-in client session whose account owns
 * the service — WHMCS's modop=custom dispatch bounced fetch requests to
 * the login page, so this endpoint replaces it.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Migrations;
use WHMCS\Module\Server\VirtutelNbn\Repository\Settings;
use WHMCS\Module\Server\VirtutelNbn\Service\Diagnostics;

require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../lib/Autoloader.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, max-age=0');

$respond = function (int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
};

$uid = (int) ($_SESSION['uid'] ?? 0);
if ($uid === 0) {
    $respond(401, ['ok' => false, 'error' => 'Please log in and try again.']);
}

$serviceId = (int) ($_REQUEST['serviceid'] ?? 0);
$owned = $serviceId > 0 && Capsule::table('tblhosting')
    ->where('id', $serviceId)
    ->where('userid', $uid)
    ->exists();
if (!$owned) {
    $respond(403, ['ok' => false, 'error' => 'That service isn\'t on your account.']);
}

try {
    Migrations::ensure();
    $action = (string) ($_REQUEST['do'] ?? '');

    if ($action === 'run') {
        $testType = strtoupper(trim((string) ($_REQUEST['testtype'] ?? '')));

        $row = Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)
            ->first();
        $allowed = $row
            ? Diagnostics::customerTests((string) ($row->technology_type ?? ''))
            : [];
        if (!isset($allowed[$testType])) {
            $respond(422, ['ok' => false, 'error' => 'That test isn\'t available for your service.']);
        }

        // 5 customer-initiated tests per service per day.
        $key = 'ctests_' . $serviceId . '_' . date('Ymd');
        $count = (int) (Settings::get($key, '0') ?? '0');
        if ($count >= 5) {
            $respond(429, ['ok' => false,
                'error' => 'Daily test limit reached — contact us if you\'re still having trouble.']);
        }
        Settings::set($key, (string) ($count + 1));

        $respond(200, Diagnostics::queue($serviceId, $testType));
    }

    if ($action === 'status') {
        $respond(200, Diagnostics::statusHtml($serviceId, (string) ($_REQUEST['testid'] ?? '')));
    }

    $respond(422, ['ok' => false, 'error' => 'Unknown action.']);
} catch (\Throwable $e) {
    if (function_exists('logActivity')) {
        logActivity('Virtutel NBN: client test API error: ' . $e->getMessage());
    }
    $respond(500, ['ok' => false, 'error' => 'The check could not be started — please try again shortly.']);
}
