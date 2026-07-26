<?php

/**
 * Virtutel NBN provisioning module for WHMCS.
 *
 * Server record fields:
 *   hostname = Virtutel API host
 *   port     = 8443 (sandbox) or 443 (production)
 *   username = client_id
 *   password = client_secret
 *
 * All logic lives in lib/ (see docs/ARCHITECTURE.md); this file only
 * translates WHMCS module calls into lib calls.
 */

use WHMCS\Module\Server\VirtutelNbn\Api\VirtutelClient;
use WHMCS\Module\Server\VirtutelNbn\Migrations;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/Autoloader.php';

function virtutel_nbn_MetaData(): array
{
    return [
        'DisplayName' => 'Virtutel NBN',
        'APIVersion' => '1.1',
        'RequiresServer' => true,
        'DefaultSSLPort' => (string) VirtutelClient::PRODUCTION_PORT,
        'ServiceSingleSignOnLabel' => false,
        'ListAccountsUniqueIdentifierDisplayName' => 'AVC ID',
        'ListAccountsUniqueIdentifierField' => 'domain',
    ];
}

function virtutel_nbn_ConfigOptions(): array
{
    return [
        'speed_tier' => [
            'FriendlyName' => 'Speed Tier',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Virtutel speed enumeration (e.g. 100/20, 500/50). '
                . 'Leave blank when using configurable options for speed selection.',
        ],
        'radius_group' => [
            'FriendlyName' => 'RADIUS Group',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'FreeRADIUS group (radusergroup) applied to services on this product.',
        ],
    ];
}

/**
 * Verify API credentials by ensuring a valid access token exists
 * (reuses the cached token; generates one on first run).
 */
function virtutel_nbn_TestConnection(array $params): array
{
    $client = null;
    try {
        Migrations::ensure();

        $client = VirtutelClient::fromModuleParams($params);
        $client->ensureAccessToken();

        // Auth works — also make sure our callback URL is registered so the
        // asynchronous order flow can function. The Access Hash field on the
        // server record carries the callback base URL (e.g.
        // https://backend.korvix.co).
        try {
            $registrar = new WHMCS\Module\Server\VirtutelNbn\Service\CallbackRegistrar(
                $client,
                (string) ($params['serveraccesshash'] ?? '')
            );
            $registrationId = $registrar->ensureRegistered();

            $registeredUrl = (string) WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
                'callback_registered_url',
                ''
            );
            logActivity(sprintf(
                'Virtutel NBN: Test Connection OK — environment: %s, callback registration ID: %s, URL: %s',
                $client->getEnvironment(),
                $registrationId !== '' ? $registrationId : '(none returned)',
                preg_replace('/token=[0-9a-f]{8}\K[0-9a-f]+/', '…', $registeredUrl)
            ));
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'API authentication OK, but callback registration failed: '
                    . $e->getMessage(),
            ];
        }

        // Full round-trip: have Virtutel POST a test callback to our endpoint
        // and confirm it was received and stored. Proves DNS, vhost, TLS,
        // token auth, and event persistence in one go (and satisfies the
        // received-callback certification requirement).
        try {
            $lastEventId = (int) (WHMCS\Database\Capsule::table('mod_virtutel_callback_events')->max('id') ?? 0);
            $registrar->sendTest();

            $received = false;
            for ($i = 0; $i < 5 && !$received; $i++) {
                sleep(1);
                $received = WHMCS\Database\Capsule::table('mod_virtutel_callback_events')
                    ->where('id', '>', $lastEventId)
                    ->exists();
            }

            if (!$received) {
                return [
                    'success' => false,
                    'error' => 'Virtutel accepted and sent a test callback, but no event was '
                        . 'recorded in this WHMCS database — check that the callback domain '
                        . 'serves THIS WHMCS installation (vhost/docroot) and try again.',
                ];
            }

            logActivity('Virtutel NBN: test callback received and stored — full callback loop verified');
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'API auth and callback registration OK, but the test callback failed: '
                    . $e->getMessage()
                    . ' — check the callback URL is reachable over HTTPS with a valid certificate.',
            ];
        }

        return ['success' => true, 'error' => ''];
    } catch (\Throwable $e) {
        $message = $e->getMessage();

        if ($client !== null) {
            $message = sprintf('[%s environment] %s', $client->getEnvironment(), $message);
        }
        if ($e instanceof WHMCS\Module\Server\VirtutelNbn\Api\ApiException) {
            if ($e->getShortError() !== '') {
                $message .= ' (vt_short_error: ' . $e->getShortError() . ')';
            }
            if ($e->getHttpStatus() > 0) {
                $message .= ' (HTTP ' . $e->getHttpStatus() . ')';
            }
            if ($e->isAuthError()) {
                $message .= ' — check that the Port matches the credentials: '
                    . '8443 needs SANDBOX client_id/secret, 443 needs PRODUCTION ones.';
            }
        }

        return ['success' => false, 'error' => $message];
    }
}

function virtutel_nbn_CreateAccount(array $params): string
{
    try {
        Migrations::ensure();

        // Guard: a service already linked to a live Virtutel service (e.g.
        // via the admin link tool or go-live import) must never lodge a
        // fresh connect order for the same premises.
        $linked = WHMCS\Database\Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', (int) $params['serviceid'])
            ->value('vt_service_id');
        if ((string) $linked !== '') {
            return 'Refused: this service is already linked to Virtutel service ' . $linked
                . ' — running Create would lodge a NEW connect order for the same premises.';
        }

        $client = VirtutelClient::fromModuleParams($params);
        $vtOrderId = (new WHMCS\Module\Server\VirtutelNbn\Service\ProvisioningService($client))
            ->createService($params);

        // NBN orders complete asynchronously: hold the service in Pending
        // until the VTOrderCompleted callback activates it.
        localAPI('UpdateClientProduct', [
            'serviceid' => (int) $params['serviceid'],
            'status' => 'Pending',
        ]);
        logActivity(sprintf(
            'Virtutel NBN: connect order %s lodged for service #%d (Pending until VTOrderCompleted)',
            $vtOrderId,
            (int) $params['serviceid']
        ));

        return 'success';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
}

function virtutel_nbn_SuspendAccount(array $params): string
{
    try {
        Migrations::ensure();
        (new WHMCS\Module\Server\VirtutelNbn\Service\LifecycleService())
            ->suspend((int) $params['serviceid']);

        return 'success';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
}

function virtutel_nbn_UnsuspendAccount(array $params): string
{
    try {
        Migrations::ensure();
        (new WHMCS\Module\Server\VirtutelNbn\Service\LifecycleService())
            ->unsuspend((int) $params['serviceid']);

        return 'success';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
}

function virtutel_nbn_TerminateAccount(array $params): string
{
    try {
        Migrations::ensure();
        (new WHMCS\Module\Server\VirtutelNbn\Service\LifecycleService())
            ->terminate(VirtutelClient::fromModuleParams($params), (int) $params['serviceid']);

        return 'success';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
}

function virtutel_nbn_ChangePackage(array $params): string
{
    try {
        Migrations::ensure();

        $newSpeed = trim((string) (
            ($params['configoptions']['Speed Tier'] ?? '')
                ?: ($params['configoption1'] ?? '')
        ));
        if ($newSpeed === '') {
            return 'No speed tier configured on the new package';
        }

        (new WHMCS\Module\Server\VirtutelNbn\Service\LifecycleService())
            ->changeSpeed(VirtutelClient::fromModuleParams($params), (int) $params['serviceid'], $newSpeed);

        return 'success';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
}

/**
 * Client area overview panel: connection status, order progress, and the
 * appointment booking prompt when an order is waiting on one.
 */
function virtutel_nbn_ClientArea(array $params): array
{
    try {
        Migrations::ensure();

        $state = WHMCS\Module\Server\VirtutelNbn\Service\ClientAreaState::forService((int) $params['serviceid']);

        return [
            'tabOverviewReplacementTemplate' => 'templates/overview',
            'templateVariables' => $state,
        ];
    } catch (\Throwable $e) {
        return [
            'tabOverviewReplacementTemplate' => 'templates/overview',
            'templateVariables' => ['vt_error' => $e->getMessage()],
        ];
    }
}

function virtutel_nbn_ClientAreaCustomButtonArray(): array
{
    return ['Book Installation Appointment' => 'bookappointment'];
}

/**
 * Client area appointment booking page (also handles NBN-initiated
 * reschedules). GET renders the timeslot picker; POST reserves/reschedules.
 */
function virtutel_nbn_bookappointment(array $params): array
{
    try {
        Migrations::ensure();

        $booking = new WHMCS\Module\Server\VirtutelNbn\Service\BookingPage(
            VirtutelClient::fromModuleParams($params),
            $params
        );

        return [
            'templatefile' => 'templates/book',
            'breadcrumb' => ['clientarea.php?action=productdetails&id=' . (int) $params['serviceid'] => 'Book Appointment'],
            'vars' => $booking->handle($_POST),
        ];
    } catch (\Throwable $e) {
        return [
            'templatefile' => 'templates/book',
            'vars' => ['vt_error' => $e->getMessage()],
        ];
    }
}

function virtutel_nbn_AdminServicesTabFields(array $params): array
{
    try {
        Migrations::ensure();

        $serviceId = (int) $params['serviceid'];
        $row = WHMCS\Database\Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)
            ->first();

        $fields = [];
        if ($row) {
            $fields = [
                'VT Service ID' => htmlspecialchars((string) ($row->vt_service_id ?? '—')),
                'AVC ID' => htmlspecialchars((string) ($row->avc_id ?? '—')),
                'NBN Location ID' => htmlspecialchars((string) ($row->nbn_location_id ?? '—')),
                'Technology' => htmlspecialchars((string) ($row->technology_type ?? '—')),
                'Carrier Status' => htmlspecialchars((string) ($row->carrier_status ?? '—')),
            ];
        } else {
            $fields['Virtutel'] = 'Not linked to a Virtutel service yet — paste an ID below and Save Changes.';
        }

        // Link an existing Virtutel service by ID (no order lodged): the
        // Save handler looks it up via GET /services. Hidden once linked.
        if (!$row || (string) ($row->vt_service_id ?? '') === '') {
            $linkMsg = (string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
                'linkmsg_' . $serviceId,
                ''
            ) ?? '');
            $fields['Link Virtutel Service'] =
                '<input type="text" name="vt_link_ref" size="34" value="" '
                . 'placeholder="VT... / PRI... / AVC..." autocomplete="off" />'
                . '<br><small>Paste a Virtutel VT / PRI / AVC ID and click Save Changes to link this '
                . 'service (no order is lodged).'
                . ($linkMsg !== '' ? ' <strong>' . htmlspecialchars($linkMsg) . '</strong>' : '')
                . '</small>';
        }

        // Diagnostic tests: per-technology picker (runs on Save Changes)
        // and the last few results.
        if ($row && (string) ($row->avc_id ?? '') !== '') {
            $testMsg = (string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
                'testmsg_' . $serviceId,
                ''
            ) ?? '');
            $options = '<option value="">— choose a diagnostic test —</option>';
            foreach (virtutel_nbn_test_catalogue((string) ($row->technology_type ?? '')) as $value => $label) {
                $options .= '<option value="' . htmlspecialchars($value) . '">'
                    . htmlspecialchars($label) . '</option>';
            }
            $fields['Run Diagnostic Test'] =
                '<select name="vt_test_type">' . $options . '</select>'
                . '<br><small>Select a test and click Save Changes to queue it — results arrive '
                . 'via callback and show below.'
                . ($testMsg !== '' ? ' <strong>' . htmlspecialchars($testMsg) . '</strong>' : '')
                . '</small>';

            $tests = json_decode((string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
                'tests_' . $serviceId,
                ''
            ) ?? ''), true);
            if (is_array($tests) && $tests !== []) {
                $fields['Recent Diagnostics'] = virtutel_nbn_render_tests($tests);
            }
        }

        // Last service health check (requested via the module button).
        $health = json_decode((string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
            'health_' . $serviceId,
            ''
        ) ?? ''), true);
        if (is_array($health) && !empty($health['id'])) {
            $html = 'Status: <strong>' . htmlspecialchars((string) ($health['status'] ?? '?'))
                . '</strong> &mdash; ' . htmlspecialchars((string) $health['id'])
                . (isset($health['at']) ? ', ' . date('Y-m-d H:i', (int) $health['at']) : '');
            if (!empty($health['report_error'])) {
                $html .= '<br><span style="color:#c0392b">Report fetch failed: '
                    . htmlspecialchars((string) $health['report_error']) . '</span>';
            }
            if (!empty($health['report']) && is_array($health['report'])) {
                $html .= virtutel_nbn_render_health($health['report']);
            }
            $fields['Service Health'] = $html;
        }

        return $fields;
    } catch (\Throwable $e) {
        return ['Virtutel' => 'Error: ' . htmlspecialchars($e->getMessage())];
    }
}

/**
 * Renders an NBN service health report (traffic-light overview, condition
 * banner with next actions, per-category tables) with the raw JSON behind
 * a collapsible for anything the layout doesn't cover.
 */
function virtutel_nbn_render_health(array $r): string
{
    $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES);
    $dot = function ($status) {
        $c = match (strtolower((string) $status)) {
            'green' => '#27ae60',
            'amber', 'yellow', 'orange' => '#e67e22',
            'red' => '#c0392b',
            default => '#95a5a6',
        };
        return '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;'
            . 'background:' . $c . ';margin-right:6px;vertical-align:middle"></span>';
    };

    $html = '';

    $overview = (array) ($r['overviewIndicator'] ?? []);
    if ($overview !== []) {
        $html .= '<div style="display:flex;gap:10px;flex-wrap:wrap;margin:8px 0 10px">';
        foreach ($overview as $k => $v) {
            $html .= '<span style="background:#f6f8fb;border:1px solid #dde3ee;border-radius:999px;'
                . 'padding:4px 12px;font-size:12px">' . $dot($v) . ucfirst($e($k))
                . ': <strong>' . $e($v !== '' && $v !== null ? $v : '—') . '</strong></span>';
        }
        $html .= '</div>';
    }

    $condition = (array) ($r['currentCondition'] ?? []);
    if ($condition !== []) {
        $col = match (strtolower((string) ($condition['status'] ?? ''))) {
            'red' => '#c0392b',
            'amber', 'yellow', 'orange' => '#e67e22',
            'green' => '#27ae60',
            default => '#7f8c8d',
        };
        $html .= '<div style="border-left:4px solid ' . $col . ';background:#f6f8fb;'
            . 'padding:8px 12px;margin-bottom:10px;border-radius:0 4px 4px 0">'
            . '<strong>' . $dot($condition['status'] ?? null)
            . $e($condition['alertMessage'] ?? ($condition['status'] ?? 'Condition')) . '</strong>';
        if (!empty($condition['summary'])) {
            $html .= '<br>' . $e($condition['summary']);
        }
        foreach ((array) ($condition['nextAction'] ?? []) as $action) {
            if (!empty($action['description'])) {
                $html .= '<br><em style="color:#667">Next: ' . $e($action['description']) . '</em>';
            }
        }
        $html .= '</div>';
    }

    foreach ((array) ($r['healthCategory'] ?? []) as $category) {
        $items = (array) ($category['healthCategoryItem'] ?? []);
        if ($items === []) {
            continue;
        }
        $html .= '<div style="margin:10px 0 4px;font-weight:700;font-size:12.5px;'
            . 'text-transform:uppercase;letter-spacing:.04em;color:#556">'
            . $e($category['type'] ?? 'Category') . '</div>'
            . '<table style="border-collapse:collapse;font-size:12px;width:100%;max-width:660px">';
        foreach ($items as $item) {
            $label = trim((string) ($item['@type'] ?? '') . ' ' . (string) ($item['id'] ?? ''));
            $value = trim((string) ($item['value'] ?? '') . ' ' . (string) ($item['unit'] ?? ''));
            $status = $item['status'] ?? null;
            $html .= '<tr style="border-bottom:1px solid #eef1f6">'
                . '<td style="padding:4px 12px 4px 0;color:#667;white-space:nowrap">' . $e($label) . '</td>'
                . '<td style="padding:4px 12px 4px 0"><strong>' . $e($value !== '' ? $value : '—') . '</strong></td>'
                . '<td style="padding:4px 0;white-space:nowrap">'
                . ($status !== null && $status !== '' ? $dot($status) . $e($status) : '')
                . '</td></tr>';
        }
        $html .= '</table>';
    }

    $pretty = (string) json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $raw = '<details style="margin-top:8px"><summary style="cursor:pointer;font-size:12px;'
        . 'color:#667">Raw response</summary>'
        . '<pre style="max-height:240px;overflow:auto;font-size:11px;background:#f6f8fb;'
        . 'border:1px solid #dde3ee;padding:8px;border-radius:4px">'
        . htmlspecialchars(substr($pretty, 0, 12000))
        . (strlen($pretty) > 12000 ? "\n… (truncated)" : '') . '</pre></details>';

    if ($html === '') {
        return $raw; // unknown shape — raw is all we have
    }

    return '<div style="max-height:420px;overflow:auto;margin-top:4px">' . $html . $raw . '</div>';
}

/**
 * Diagnostic tests by NBN technology (testType => label). The API's
 * serviceType is derived separately; unknown technologies get the full
 * catalogue so nothing is unreachable.
 */
function virtutel_nbn_test_catalogue(string $subType): array
{
    $byTech = [
        'FTTP' => [
            'NTD_RESET' => 'Reboot NBN connection box (NTD Reset)',
            'NTD_STATUS' => 'NTD Status',
            'PORT_RESET' => 'UNI-D Port Reset',
            'UNI_D_STATUS' => 'UNI-D Port Status',
            'LOOPBACK' => 'Loopback Test',
        ],
        'HFC' => [
            'NTD_RESET' => 'Reboot NBN connection box (NTD Reset)',
            'NTD_STATUS' => 'NTD Status',
        ],
        'FTTC' => [
            'DPU_PORT_RESET' => 'DPU Port Reset',
            'DPU_PORT_STATUS' => 'DPU Port Status',
            'DPU_STATUS' => 'DPU Status',
            'NCD_RESET' => 'Reboot NBN connection device (NCD Reset)',
            'NCD_PORT_RESET' => 'NCD Port Reset',
            'NCD_UNI_D_STATUS' => 'NCD UNI-D Status',
        ],
        'FTTN' => [
            'LINE_STATE_DIAGNOSTIC' => 'Line State Diagnostic',
            'LINE_QUALITY_DIAGNOSTIC' => 'Line Quality Diagnostic',
            'SINGLE_END_LINE_TEST' => 'Single End Line Test (SELT)',
            'DYNAMIC_LINE_MANAGEMENT_STATUS' => 'Dynamic Line Management Status',
        ],
        'FW' => [
            'WNTD_RESET' => 'Reboot wireless NTD (WNTD Reset)',
            'WNTD_MEASURE' => 'WNTD Measure',
        ],
    ];
    $byTech['FTTB'] = $byTech['FTTN'];
    $byTech['FIXED WIRELESS'] = $byTech['FW'];

    $tech = strtoupper(trim($subType));
    if (isset($byTech[$tech])) {
        return $byTech[$tech];
    }

    $all = [];
    foreach ($byTech as $tests) {
        $all += $tests;
    }
    return $all;
}

/** Maps the stored technology subtype to the API serviceType base. */
function virtutel_nbn_service_type(string $subType): string
{
    return match (strtoupper(trim($subType))) {
        'FTTP' => 'NFAS',
        'HFC' => 'NHAS',
        'FW', 'FIXED WIRELESS' => 'NWAS',
        default => 'NCAS', // FTTN / FTTB / FTTC copper family
    };
}

/** Renders the recent diagnostics list with per-test results. */
function virtutel_nbn_render_tests(array $tests): string
{
    $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES);
    $html = '';
    foreach ($tests as $test) {
        $status = (string) ($test['status'] ?? '?');
        $col = match ($status) {
            'TestCompleted' => '#27ae60',
            'TestCancelled', 'TestRejected' => '#c0392b',
            default => '#e67e22',
        };
        $html .= '<div style="border-left:4px solid ' . $col . ';background:#f6f8fb;'
            . 'padding:6px 12px;margin-bottom:8px;border-radius:0 4px 4px 0;font-size:12.5px">'
            . '<strong>' . $e($test['type'] ?? '?') . '</strong> &mdash; ' . $e($status)
            . ' <span style="color:#889">(' . $e($test['id'] ?? '')
            . (isset($test['at']) ? ', ' . date('Y-m-d H:i', (int) $test['at']) : '') . ')</span>';
        if (!empty($test['results'])) {
            $pretty = (string) json_encode($test['results'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $flat = [];
            foreach ((array) $test['results'] as $result) {
                foreach ((array) $result as $key => $value) {
                    if (is_scalar($value) && (string) $value !== '') {
                        $flat[] = '<span style="color:#667">' . $e($key) . ':</span> <strong>'
                            . $e($value) . '</strong>';
                    }
                }
            }
            if ($flat !== []) {
                $html .= '<br>' . implode(' &middot; ', array_slice($flat, 0, 10));
            }
            $html .= '<details style="margin-top:4px"><summary style="cursor:pointer;font-size:11.5px;'
                . 'color:#667">Full result</summary><pre style="max-height:200px;overflow:auto;'
                . 'font-size:11px;background:#fff;border:1px solid #dde3ee;padding:6px;'
                . 'border-radius:4px">' . $e(substr($pretty, 0, 8000)) . '</pre></details>';
        }
        $html .= '</div>';
    }
    return '<div style="max-height:340px;overflow:auto;margin-top:4px">' . $html . '</div>';
}

/**
 * Save handler for the tab fields: performs the link when an ID was pasted
 * and/or queues a diagnostic test when one was selected.
 */
function virtutel_nbn_AdminServicesTabFieldsSave(array $params): void
{
    virtutel_nbn_handle_test_request((int) $params['serviceid']);

    $ref = trim((string) ($_REQUEST['vt_link_ref'] ?? ''));
    if ($ref === '') {
        return;
    }

    $serviceId = (int) $params['serviceid'];
    try {
        Migrations::ensure();
        $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($serviceId);
        $svc = \WHMCS\Module\Server\VirtutelNbn\Service\ServiceLinker::lookup($client, $ref);
        if ($svc === null) {
            \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
                'linkmsg_' . $serviceId,
                'No unambiguous Virtutel match for "' . $ref . '" — check the ID.'
            );
            return;
        }

        \WHMCS\Module\Server\VirtutelNbn\Service\ServiceLinker::link($serviceId, $svc);
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
            'linkmsg_' . $serviceId,
            'Linked to ' . (string) ($svc['vtServiceId'] ?? '?') . ' / '
            . (string) ($svc['avcId'] ?? '?') . ' (' . (string) ($svc['description'] ?? '') . ').'
        );
        logActivity(sprintf(
            'Virtutel NBN: service #%d linked to %s / %s by admin',
            $serviceId,
            (string) ($svc['vtServiceId'] ?? '?'),
            (string) ($svc['avcId'] ?? '?')
        ));
    } catch (\Throwable $e) {
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
            'linkmsg_' . $serviceId,
            'Link failed: ' . $e->getMessage()
        );
    }
}

/**
 * Queues the diagnostic test selected on the admin tab (POST
 * /service-tests); results arrive via ServiceTestStateChangeNotification.
 */
function virtutel_nbn_handle_test_request(int $serviceId): void
{
    $testType = strtoupper(trim((string) ($_REQUEST['vt_test_type'] ?? '')));
    if ($testType === '' || !preg_match('/^[A-Z0-9_]{3,48}$/', $testType)) {
        return;
    }

    try {
        Migrations::ensure();
        $row = WHMCS\Database\Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)
            ->first();
        if (!$row || (string) ($row->avc_id ?? '') === '') {
            \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
                'testmsg_' . $serviceId,
                'Not linked to a Virtutel service with an AVC — link it first.'
            );
            return;
        }

        $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($serviceId);
        $response = $client->request('POST', '/service-tests', [
            'action' => 'RequestServiceTest',
            'json' => [
                'serviceType' => virtutel_nbn_service_type((string) ($row->technology_type ?? '')),
                'avcId' => (string) $row->avc_id,
                'testType' => $testType,
            ],
        ]);

        $testId = strtoupper((string) $response->get('id', ''));
        if ($testId === '') {
            \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
                'testmsg_' . $serviceId,
                'Test rejected: ' . ($response->vtErrorDesc() ?: $response->vtShortError() ?: 'unknown error')
            );
            return;
        }

        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set('svctest_' . $testId, (string) $serviceId);

        $tests = json_decode((string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
            'tests_' . $serviceId,
            ''
        ) ?? ''), true);
        $tests = is_array($tests) ? $tests : [];
        array_unshift($tests, [
            'id' => $testId,
            'type' => $testType,
            'status' => 'Requested',
            'at' => time(),
        ]);
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
            'tests_' . $serviceId,
            (string) json_encode(array_slice($tests, 0, 10))
        );
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
            'testmsg_' . $serviceId,
            'Test ' . $testId . ' (' . $testType . ') queued — results arrive via callback.'
        );
        logActivity(sprintf(
            'Virtutel NBN: diagnostic test %s (%s) requested for service #%d (%s)',
            $testId,
            $testType,
            $serviceId,
            (string) $row->avc_id
        ));
    } catch (\Throwable $e) {
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
            'testmsg_' . $serviceId,
            'Test request failed: ' . $e->getMessage()
        );
    }
}

/**
 * Admin buttons on the service: request an async NBN health check for the
 * linked service (result arrives via callback and shows in the tab).
 */
function virtutel_nbn_AdminCustomButtonArray(): array
{
    return ['Run Service Health Check' => 'runhealthcheck'];
}

function virtutel_nbn_runhealthcheck(array $params): string
{
    $serviceId = (int) $params['serviceid'];
    try {
        Migrations::ensure();
        $row = WHMCS\Database\Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)
            ->first();
        if (!$row || (string) ($row->vt_service_id ?? '') === '') {
            return 'Not linked to a Virtutel service — link it on the module tab first.';
        }

        $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($serviceId);
        $response = $client->request('POST', '/service-health-checks', [
            'action' => 'RequestHealthCheck',
            'json' => ['vtServiceId' => (string) $row->vt_service_id],
        ]);

        $testId = strtoupper((string) $response->get('id', ''));
        if ($testId === '') {
            return 'Health check request rejected: ' . $response->vtErrorDesc();
        }

        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set('healthtest_' . $testId, (string) $serviceId);
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set('health_' . $serviceId, (string) json_encode([
            'id' => $testId,
            'status' => (string) $response->get('status', 'Requested'),
            'at' => time(),
        ]));
        logActivity(sprintf(
            'Virtutel NBN: health check %s requested for service #%d (%s)',
            $testId,
            $serviceId,
            (string) $row->vt_service_id
        ));

        return 'success';
    } catch (\Throwable $e) {
        return 'Health check failed: ' . $e->getMessage();
    }
}
