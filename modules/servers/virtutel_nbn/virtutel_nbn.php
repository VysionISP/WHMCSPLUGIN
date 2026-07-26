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

        // Customer self-serve diagnostics (only when linked with an AVC).
        $state['vt_serviceid'] = (int) $params['serviceid'];
        $state['vt_customer_tests'] = [];
        if (!empty($state['vt_avc'])) {
            $state['vt_customer_tests'] = WHMCS\Module\Server\VirtutelNbn\Service\Diagnostics::customerTests(
                (string) ($state['vt_technology'] ?? '')
            );
        }

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
 * Non-button client custom functions (AJAX endpoints for self-serve
 * diagnostics). WHMCS only invokes these for services the logged-in
 * client owns.
 */
function virtutel_nbn_ClientAreaAllowedFunctions(): array
{
    return ['runtest' => 'runtest', 'teststatus' => 'teststatus'];
}

/** Emits a sentinel-wrapped JSON payload for the client overlay and stops. */
function virtutel_nbn_client_json(array $payload): void
{
    echo '@@KXJSON@@' . json_encode($payload) . '@@ENDKXJSON@@';
    exit;
}

function virtutel_nbn_runtest(array $params): array
{
    try {
        Migrations::ensure();
        $serviceId = (int) $params['serviceid'];
        $testType = strtoupper(trim((string) ($_REQUEST['testtype'] ?? '')));

        $row = WHMCS\Database\Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)->first();
        $allowed = $row ? WHMCS\Module\Server\VirtutelNbn\Service\Diagnostics::customerTests(
            (string) ($row->technology_type ?? '')
        ) : [];
        if (!isset($allowed[$testType])) {
            virtutel_nbn_client_json(['ok' => false, 'error' => 'That test isn\'t available for your service.']);
        }

        // 5 customer-initiated tests per service per day.
        $key = 'ctests_' . $serviceId . '_' . date('Ymd');
        $count = (int) (WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get($key, '0') ?? '0');
        if ($count >= 5) {
            virtutel_nbn_client_json(['ok' => false,
                'error' => 'Daily test limit reached — contact us if you\'re still having trouble.']);
        }
        WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set($key, (string) ($count + 1));

        virtutel_nbn_client_json(
            WHMCS\Module\Server\VirtutelNbn\Service\Diagnostics::queue($serviceId, $testType)
        );
    } catch (\Throwable $e) {
        virtutel_nbn_client_json(['ok' => false, 'error' => 'Test could not be started — please try again.']);
    }

    return []; // unreachable
}

function virtutel_nbn_teststatus(array $params): array
{
    try {
        Migrations::ensure();
        virtutel_nbn_client_json(
            WHMCS\Module\Server\VirtutelNbn\Service\Diagnostics::statusHtml(
                (int) $params['serviceid'],
                (string) ($_REQUEST['testid'] ?? '')
            )
        );
    } catch (\Throwable $e) {
        virtutel_nbn_client_json(['done' => false, 'html' => '']);
    }

    return []; // unreachable
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
                'Service Address' => htmlspecialchars(
                    (string) (($row->service_address ?? '') !== '' ? $row->service_address : '—')
                ),
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

        // On-prem equipment as last seen by NBN (health check extraction).
        $cpe = \WHMCS\Module\Server\VirtutelNbn\Service\Diagnostics::cpe($serviceId);
        if ($cpe !== null && !empty($cpe['groups'])) {
            $cpeLines = [];
            foreach ($cpe['groups'] as $group => $items) {
                $cpeParts = [];
                foreach ($items as $label => $value) {
                    $cpeParts[] = '<span style="color:#667">' . htmlspecialchars($label) . ':</span> <code>'
                        . htmlspecialchars($value) . '</code>';
                }
                $cpeLines[] = '<strong>' . htmlspecialchars($group) . '</strong> &mdash; '
                    . implode(' &middot; ', $cpeParts);
            }
            $fields['On-Prem Equipment'] = implode('<br>', $cpeLines)
                . ' <small style="color:#889">(from health check '
                . date('Y-m-d H:i', (int) ($cpe['at'] ?? 0)) . ')</small>';
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
                '<select name="vt_test_type" id="vtTestSel">' . $options . '</select> '
                . '<button type="button" class="btn btn-default btn-sm" id="vtRunTest">Run Test</button>'
                . '<br><small>Runs live — a progress overlay shows until NBN returns the result. '
                . '<span style="color:#aab">(diag v1.10.4)</span>'
                . ($testMsg !== '' ? ' <strong>' . htmlspecialchars($testMsg) . '</strong>' : '')
                . '</small>'
                . virtutel_nbn_test_overlay_js($serviceId);

            $tests = \WHMCS\Module\Server\VirtutelNbn\Service\Diagnostics::history($serviceId);
            if ($tests !== []) {
                $fields['Previous Tests'] =
                    '<button type="button" class="btn btn-default btn-sm" id="vtHistBtn">'
                    . 'View previous tests (' . count($tests) . ')</button>'
                    . '<div id="vtHistData" style="display:none">'
                    . virtutel_nbn_render_tests($tests) . '</div>';
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
                $html .= ' <button type="button" class="btn btn-default btn-sm" id="vtHealthBtn" '
                    . 'style="margin-left:8px">View full report</button>'
                    . '<div id="vtHealthData" style="display:none">'
                    . virtutel_nbn_render_health($health['report']) . '</div>';
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
 * Run Test button behaviour: queue via the addon AJAX endpoint, lock the
 * screen with a progress overlay, poll until the callback delivers the
 * result, then render it in place.
 */
function virtutel_nbn_test_overlay_js(int $serviceId): string
{
    $sid = (int) $serviceId;

    return <<<HTML
<script>
(function(){
  var btn=document.getElementById('vtRunTest');
  if(!btn||btn.dataset.kxBound){return;}
  btn.dataset.kxBound='1';
  var base='addonmodules.php?module=virtutel_nbn_admin&serviceid={$sid}';
  // The addon endpoint's JSON arrives embedded in the admin page chrome —
  // extract it by sentinel.
  function kxFetch(url){
    return fetch(url,{credentials:'same-origin'}).then(function(r){
      return r.text().then(function(t){
        var m=t.match(/@@KXJSON@@([\\s\\S]*?)@@ENDKXJSON@@/);
        if(m){return JSON.parse(m[1]);}
        // Surface what actually came back so failures are diagnosable.
        var snip=t.replace(/<script[\\s\\S]*?<\\/script>/gi,' ')
          .replace(/<[^>]*>/g,' ').replace(/\\s+/g,' ').trim().slice(0,220);
        throw new Error('HTTP '+r.status+' without payload. Response starts: "'+snip+'"');
      });
    });
  }
  function kxOverlay(spin,msg,sub,closeLabel){
    var ov=document.createElement('div');
    ov.style.cssText='position:fixed;inset:0;background:rgba(10,14,24,.7);z-index:99999;'
      +'display:flex;align-items:center;justify-content:center';
    ov.innerHTML='<div style="background:#fff;padding:26px 34px;border-radius:10px;text-align:center;'
      +'max-width:620px;width:92%;max-height:82vh;overflow:auto;box-shadow:0 20px 60px rgba(0,0,0,.4)">'
      +'<div id="vtOvSpin" style="'+(spin?'':'display:none;')+'width:38px;height:38px;border:4px solid #dde3ee;'
      +'border-top-color:#1a5fd0;border-radius:50%;margin:0 auto 14px;animation:vtspin 1s linear infinite"></div>'
      +'<style>@keyframes vtspin{to{transform:rotate(360deg)}}</style>'
      +'<div id="vtOvMsg" style="font-weight:600;color:#222">'+msg+'</div>'
      +'<div id="vtOvSub" style="color:#667;font-size:12px;margin-top:6px">'+sub+'</div>'
      +'<button type="button" id="vtOvClose" class="btn btn-default btn-sm" style="margin-top:14px">'
      +closeLabel+'</button></div>';
    document.body.appendChild(ov);
    return ov;
  }
  // The history/report buttons render in LATER tab rows than this script,
  // so they don't exist yet at parse time — delegate instead of binding.
  document.addEventListener('click',function(e){
    var t=e.target&&e.target.closest?e.target.closest('#vtHistBtn,#vtHealthBtn'):null;
    if(!t){return;}
    e.preventDefault();
    var data=document.getElementById(t.id==='vtHealthBtn'?'vtHealthData':'vtHistData');
    var ov=kxOverlay(false,'<div style="text-align:left;font-weight:400">'
      +(data?data.innerHTML:'Nothing recorded yet.')+'</div>','','Close');
    ov.querySelector('#vtOvClose').addEventListener('click',function(){ov.remove();});
    ov.addEventListener('click',function(ev){if(ev.target===ov){ov.remove();}});
  });
  btn.addEventListener('click',function(){
    var type=document.getElementById('vtTestSel').value;
    if(!type){alert('Choose a diagnostic test first.');return;}
    var ov=kxOverlay(true,'Queuing '+type+'&hellip;',
      'Results come back from NBN &mdash; usually under a couple of minutes.','Run in background');
    var closed=false;
    function shut(){closed=true;ov.remove();}
    ov.querySelector('#vtOvClose').addEventListener('click',shut);
    function fail(msg){
      ov.querySelector('#vtOvSpin').style.display='none';
      ov.querySelector('#vtOvMsg').textContent=msg;
      ov.querySelector('#vtOvClose').textContent='Close';
    }
    kxFetch(base+'&kxajax=run_test&testtype='+encodeURIComponent(type))
      .then(function(j){
        if(!j.ok){fail('Failed: '+(j.error||'unknown error'));return;}
        ov.querySelector('#vtOvMsg').textContent='Test '+j.id+' running…';
        var tries=0;
        (function poll(){
          if(closed){return;}
          if(++tries>60){fail('Still running — the result will appear in Recent Diagnostics.');return;}
          kxFetch(base+'&kxajax=test_status&testid='+encodeURIComponent(j.id))
            .then(function(s){
              if(closed){return;}
              if(s.done){
                ov.querySelector('#vtOvSpin').style.display='none';
                ov.querySelector('#vtOvMsg').innerHTML=s.html;
                ov.querySelector('#vtOvSub').textContent='';
                var c=ov.querySelector('#vtOvClose');
                c.textContent='Close';
                c.addEventListener('click',function(){location.reload();});
              }else{setTimeout(poll,3000);}
            })
            .catch(function(){setTimeout(poll,4000);});
        })();
      })
      .catch(function(){fail('Request failed — check the addon module is active.');});
  });
})();
</script>
HTML;
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
            // NTD_RESET is NHAS-only (API-validated) — the FTTP reboot
            // equivalent is the UNI-D port reset.
            'PORT_RESET' => 'UNI-D Port Reset (reboots the port)',
            'NTD_STATUS' => 'NTD Status',
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

/** Renders the recent diagnostics list with per-test results. */
function virtutel_nbn_render_tests(array $tests): string
{
    $html = '';
    foreach ($tests as $test) {
        $html .= \WHMCS\Module\Server\VirtutelNbn\Service\Diagnostics::renderOne($test);
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

        \WHMCS\Module\Server\VirtutelNbn\Service\ServiceLinker::link($serviceId, $svc, $client);
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
        $result = \WHMCS\Module\Server\VirtutelNbn\Service\Diagnostics::queue($serviceId, $testType);
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
            'testmsg_' . $serviceId,
            $result['ok']
                ? 'Test ' . $result['id'] . ' (' . $testType . ') queued — results arrive via callback.'
                : 'Test rejected: ' . ($result['error'] ?? 'unknown error')
        );
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
function virtutel_nbn_AdminCustomButtonArray(array $params = []): array
{
    // Only offer the health check once the service is linked to Virtutel.
    try {
        $serviceId = (int) ($params['serviceid'] ?? 0);
        if ($serviceId > 0) {
            $linked = WHMCS\Database\Capsule::table('mod_virtutel_services')
                ->where('whmcs_service_id', $serviceId)
                ->value('vt_service_id');
            if ((string) $linked === '') {
                return [];
            }
        }
    } catch (\Throwable $e) {
        // fall through — show the button rather than hide functionality
    }

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
