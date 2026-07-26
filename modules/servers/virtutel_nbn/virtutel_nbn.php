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
/**
 * Custom client-area actions. Diagnostics moved to the dedicated
 * pages/service-test-api.php endpoint (the old runtest/teststatus
 * functions are gone) — booking is the one modop=custom page left.
 */
function virtutel_nbn_ClientAreaAllowedFunctions(): array
{
    return ['bookappointment' => 'bookappointment'];
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
            // Full raw API record (captured at link time): the source of
            // truth for mapping service types we haven't hardcoded yet
            // (voice / mobile numbers) and for details we don't column-ise
            // (POI, VLAN tags). Services linked before raw capture existed
            // are back-filled with one lookup, then cached.
            $raw = json_decode((string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
                'svcraw_' . $serviceId,
                ''
            ) ?? ''), true);
            if ((!is_array($raw) || $raw === []) && (string) ($row->vt_service_id ?? '') !== '') {
                try {
                    $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($serviceId);
                    $svc = \WHMCS\Module\Server\VirtutelNbn\Service\ServiceLinker::lookup(
                        $client,
                        (string) $row->vt_service_id
                    );
                    if (is_array($svc) && $svc !== []) {
                        $raw = $svc;
                        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
                            'svcraw_' . $serviceId,
                            (string) json_encode($svc)
                        );
                    }
                } catch (\Throwable $e) {
                    $raw = null; // tab still renders without it
                }
            }

            $poi = is_array($raw) ? trim((string) ($raw['poiId'] ?? '')) : '';
            $csa = is_array($raw) ? trim((string) ($raw['csaId'] ?? '')) : '';

            // Plan: friendly speed-tier label (from the stored enum) plus
            // Virtutel's own wholesale product description underneath.
            $speedEnum = trim((string) ($row->speed_tier ?? ''));
            $tier = $speedEnum !== ''
                ? \WHMCS\Module\Server\VirtutelNbn\Service\SpeedTier::describe($speedEnum)
                : null;
            $planLabel = trim((string) ($tier['label'] ?? '')) ?: $speedEnum;
            $wholesale = is_array($raw) ? trim((string) ($raw['description'] ?? '')) : '';
            $planHtml = htmlspecialchars($planLabel !== '' ? $planLabel : '—')
                . ($speedEnum !== '' && $planLabel !== $speedEnum
                    ? ' <code style="font-size:11px">' . htmlspecialchars($speedEnum) . '</code>' : '')
                . ($wholesale !== ''
                    ? '<br><small style="color:#667">' . htmlspecialchars($wholesale) . '</small>' : '');

            // Live margin: Virtutel wholesale (ex GST, from the raw record)
            // vs the WHMCS recurring price. Monthly cycles only — that's
            // every residential plan.
            $marginHtml = '';
            $wholesaleEx = is_array($raw) && is_numeric($raw['chargeExPerMonth'] ?? null)
                ? (float) $raw['chargeExPerMonth'] : 0.0;
            if ($wholesaleEx > 0) {
                $hosting = WHMCS\Database\Capsule::table('tblhosting')
                    ->where('id', $serviceId)->first(['amount', 'billingcycle']);
                if ($hosting && strtolower((string) $hosting->billingcycle) === 'monthly'
                    && (float) $hosting->amount > 0) {
                    $retailInc = (float) $hosting->amount;
                    $retailEx = $retailInc / 1.1;
                    $margin = $retailEx - $wholesaleEx;
                    $marginColor = $margin < 0 ? '#c0392b' : ($margin < 10 ? '#a3690e' : '#1d9e55');
                    $marginHtml = sprintf(
                        '<span style="color:%s;font-weight:700">$%.2f/mo</span>'
                        . ' <small style="color:#667">retail $%.2f inc GST ($%.2f ex) &minus; wholesale $%.2f ex</small>%s',
                        $marginColor,
                        $margin,
                        $retailInc,
                        $retailEx,
                        $wholesaleEx,
                        $margin < 0 ? ' <strong style="color:#c0392b">UNDERWATER</strong>' : ''
                    );
                }
            }

            $fields = [
                'VT Service ID' => htmlspecialchars((string) ($row->vt_service_id ?? '—')),
                'AVC ID' => htmlspecialchars((string) ($row->avc_id ?? '—')),
                'NBN Location ID' => htmlspecialchars((string) ($row->nbn_location_id ?? '—')),
                'Service Address' => htmlspecialchars(
                    (string) (($row->service_address ?? '') !== '' ? $row->service_address : '—')
                ),
                'Technology' => htmlspecialchars((string) ($row->technology_type ?? '—')),
                'Plan' => $planHtml,
                'Margin' => $marginHtml,
                'POI' => htmlspecialchars($poi !== '' ? $poi : '—')
                    . ($csa !== '' ? ' <small style="color:#667">(CSA ' . htmlspecialchars($csa) . ')</small>' : ''),
                'Carrier Status' => htmlspecialchars((string) ($row->carrier_status ?? '—')),
                'Customer Checks Today' => (int) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
                    'ctests_' . $serviceId . '_' . date('Ymd'),
                    '0'
                ) ?? '0') . ' of 5 <small>(reset with the Reset Daily Test Limit button below)</small>',
            ];
            if ($marginHtml === '') {
                unset($fields['Margin']);
            }
            if (is_array($raw) && $raw !== []) {
                $flat = [];
                foreach ($raw as $rk => $rv) {
                    if (is_array($rv)) {
                        foreach ($rv as $sk => $sv) {
                            if (is_scalar($sv) && (string) $sv !== '') {
                                $flat[$rk . '.' . $sk] = (string) $sv;
                            }
                        }
                    } elseif (is_scalar($rv) && (string) $rv !== '') {
                        $flat[(string) $rk] = (string) $rv;
                    }
                }
                if ($flat !== []) {
                    $rawRows = '';
                    foreach ($flat as $rk => $rv) {
                        $rawRows .= '<tr><td style="color:#667;padding:2px 14px 2px 0;white-space:nowrap">'
                            . htmlspecialchars($rk) . '</td><td><code>'
                            . htmlspecialchars($rv) . '</code></td></tr>';
                    }
                    $fields['Virtutel Data'] = '<details><summary style="cursor:pointer">'
                        . 'Everything Virtutel returns for this service ('
                        . count($flat) . ' fields)</summary>'
                        . '<table style="font-size:12px;margin-top:6px;text-align:left">'
                        . $rawRows . '</table></details>';
                }
            }

            // Change Speed: pick a tier + Save Changes lodges the Modify
            // Speed order (RADIUS follows on completion). The API rejects
            // tiers the address can't support — that error shows here.
            if ((string) ($row->vt_service_id ?? '') !== '') {
                $tech = strtoupper((string) ($row->technology_type ?? ''));
                $speedOptions = in_array($tech, ['FW', 'FIXED WIRELESS'], true)
                    ? ['TC4FWP', 'TC4FWHF', 'TC4FWSF']
                    : ['TC425D5U', 'TC425D10U', 'TC450D20U', 'TC4100D20U', 'TC4100D40U',
                        'TC4250D25U', 'TC4500D50U', 'TC4750D50U', 'TC41000D50U', 'TC41000D100U'];
                $speedSelect = '<select name="vt_speed_change"><option value="">— keep current —</option>';
                foreach ($speedOptions as $enum) {
                    if ($enum === $speedEnum) {
                        continue;
                    }
                    $optTier = \WHMCS\Module\Server\VirtutelNbn\Service\SpeedTier::describe($enum);
                    $speedSelect .= '<option value="' . htmlspecialchars($enum) . '">'
                        . htmlspecialchars(($optTier['label'] ?? $enum) . ' (' . $enum . ')') . '</option>';
                }
                $speedMsg = (string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
                    'speedmsg_' . $serviceId,
                    ''
                ) ?? '');
                $fields['Change Speed'] = $speedSelect . '</select>'
                    . '<br><small>Select a tier and click Save Changes to lodge a Modify Speed order. '
                    . 'Billing/product stays as-is — repackage in WHMCS separately if the price changes.'
                    . ($speedMsg !== '' ? ' <strong>' . htmlspecialchars($speedMsg) . '</strong>' : '')
                    . '</small>';
            }

            // Order history: everything ever lodged for this service.
            $orders = WHMCS\Database\Capsule::table('mod_virtutel_orders')
                ->where('service_id', (int) $row->id)
                ->orderByDesc('id')->limit(25)->get();

            // Appointment for the latest order: slot + status + the
            // customer's self-booking link (copyable for phone support).
            $latestOrder = $orders->first();
            $appt = $latestOrder ? WHMCS\Database\Capsule::table('mod_virtutel_appointments')
                ->where('order_id', (int) $latestOrder->id)
                ->orderByDesc('id')->first() : null;
            if ($appt || ($latestOrder && in_array((string) ($latestOrder->action_required ?? ''), ['appointment', 'appointment_reschedule'], true))) {
                $bookUrl = \WHMCS\Module\Server\VirtutelNbn\Service\EmailNotifier::bookingUrl($serviceId);
                $apptHtml = '';
                if ($appt) {
                    $slot = $appt->slot_start
                        ? date('D j M Y, g:ia', strtotime((string) $appt->slot_start))
                            . ($appt->slot_end ? ' &ndash; ' . date('g:ia', strtotime((string) $appt->slot_end)) : '')
                        : 'no slot reserved yet';
                    $apptHtml = '<code>' . htmlspecialchars((string) ($appt->appointment_id ?: '(pending)')) . '</code> '
                        . htmlspecialchars(ucwords(str_replace('_', ' ', (string) $appt->status)))
                        . ' &mdash; ' . $slot . '<br>';
                } else {
                    $apptHtml = '<span style="color:#a3690e">Booking required — no appointment reserved yet.</span><br>';
                }
                $fields['Appointment'] = $apptHtml
                    . '<input type="text" readonly value="' . htmlspecialchars($bookUrl) . '" size="46" '
                    . 'onclick="this.select()" style="font-size:11px"> '
                    . '<button type="button" class="btn btn-default btn-xs" '
                    . 'onclick="var i=this.previousElementSibling;i.select();'
                    . 'navigator.clipboard.writeText(i.value);this.textContent=\'Copied\'">Copy link</button>'
                    . '<br><small>Customer self-booking/reschedule link — paste into a ticket or read out.</small>';
            }

            // Live outage check by AVC (GET /outages?search=), cached an
            // hour so the tab doesn't hit the API on every load. Only
            // rendered when something is actually affecting the service.
            if ((string) ($row->avc_id ?? '') !== '') {
                $outCacheKey = 'outagechk_' . $serviceId;
                $outCache = json_decode((string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
                    $outCacheKey,
                    ''
                ) ?? ''), true);
                if (!is_array($outCache) || (int) ($outCache['at'] ?? 0) < time() - 3600) {
                    $outages = is_array($outCache) ? (array) ($outCache['outages'] ?? []) : [];
                    try {
                        $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($serviceId);
                        $outResp = $client->request('GET', '/outages', [
                            'action' => 'OutageCheck',
                            'query' => ['search' => (string) $row->avc_id],
                            'timeout' => 12,
                            'attempts' => 1,
                        ]);
                        $outages = (array) $outResp->get('outages', []);
                    } catch (\Throwable $e) {
                        // keep last known list; retry after the hour
                    }
                    $outCache = ['at' => time(), 'outages' => $outages];
                    \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
                        $outCacheKey,
                        (string) json_encode($outCache)
                    );
                }
                $outList = (array) ($outCache['outages'] ?? []);
                if ($outList !== []) {
                    $outRows = '';
                    foreach (array_slice($outList, 0, 8) as $outage) {
                        $outRows .= '<tr>'
                            . '<td style="padding:2px 12px 2px 0">' . htmlspecialchars((string) ($outage['type'] ?? '')) . '</td>'
                            . '<td style="padding:2px 12px 2px 0"><code>' . htmlspecialchars((string) ($outage['id'] ?? '')) . '</code></td>'
                            . '<td style="padding:2px 12px 2px 0">' . htmlspecialchars((string) ($outage['status'] ?? '')) . '</td>'
                            . '<td style="padding:2px 0;color:#667">' . htmlspecialchars(substr((string) ($outage['created'] ?? ''), 0, 10)) . '</td></tr>';
                    }
                    $fields['Outages'] = '<span style="color:#c0392b;font-weight:bold">'
                        . count($outList) . ' outage' . (count($outList) === 1 ? '' : 's')
                        . ' affecting this service</span>'
                        . '<table style="font-size:12px;margin-top:4px;text-align:left">' . $outRows . '</table>';
                }
            }

            if (count($orders) > 0) {
                // Cancel button per in-flight row. The API has NO
                // self-serve order cancel (only appointments DELETE), so
                // the handler cancels any booked appointment, flags the
                // order cancel-requested, and raises a contact-Virtutel
                // to-do. The tab renders inside the service form, so a
                // named submit button drives the Save handler directly.
                $hasInFlight = false;
                $orderRows = '';
                foreach ($orders as $order) {
                    $inFlight = in_array((string) $order->whmcs_status, ['pending', 'in_progress', 'action_required'], true);
                    $cancelRequested = (string) ($order->action_required ?? '') === 'cancel_requested';
                    $hasInFlight = $hasInFlight || ($inFlight && !$cancelRequested);
                    // Action-required orders the API can resolve directly:
                    // approve the charge / confirm liability / resume.
                    $resolveCell = '';
                    $resolveLabels = [
                        'install_fee' => 'Approve install fee',
                        'development_charge' => 'Approve NDC',
                        'fibre_upgrade_liability' => 'Confirm liability',
                        'rsp_action' => 'Resume order',
                        'device_online' => 'Resume order',
                    ];
                    $actReq = (string) ($order->action_required ?? '');
                    if ($inFlight && isset($resolveLabels[$actReq]) && (string) ($order->vt_order_id ?? '') !== '') {
                        $resolveCell = '<button type="submit" name="vt_order_resolve" value="'
                            . htmlspecialchars((string) $order->vt_order_id) . '" '
                            . 'class="btn btn-success btn-xs" onclick="return confirm(\''
                            . htmlspecialchars($resolveLabels[$actReq]) . ' for '
                            . htmlspecialchars((string) $order->vt_order_id) . '? This tells Virtutel/NBN '
                            . 'to continue the order.\')">' . htmlspecialchars($resolveLabels[$actReq]) . '</button> ';
                    }

                    $cancelCell = '';
                    if ($inFlight && !$cancelRequested && (string) ($order->vt_order_id ?? '') !== '') {
                        $cancelCell = '<button type="submit" name="vt_cancel_order" value="'
                            . htmlspecialchars((string) $order->vt_order_id) . '" '
                            . 'class="btn btn-danger btn-xs" onclick="return confirm(\'Cancel '
                            . htmlspecialchars((string) $order->vt_order_id) . '? This cancels any booked '
                            . 'appointment, marks the order cancel-requested, and raises a to-do to contact '
                            . 'Virtutel (their API has no direct order cancel).\')">Cancel</button>';
                    } elseif ($cancelRequested) {
                        $cancelCell = '<span style="color:#a3690e;font-size:11px">cancel requested</span>';
                    }
                    $orderRows .= '<tr>'
                        . '<td style="padding:2px 12px 2px 0">' . htmlspecialchars((string) $order->order_type) . '</td>'
                        . '<td style="padding:2px 12px 2px 0"><code>' . htmlspecialchars((string) ($order->vt_order_id ?? '—')) . '</code></td>'
                        . '<td style="padding:2px 12px 2px 0">' . htmlspecialchars((string) ($order->status ?? '—'))
                        . ' <span style="color:#667">(' . htmlspecialchars((string) $order->whmcs_status) . ')</span></td>'
                        . '<td style="padding:2px 12px 2px 0;color:#667">' . htmlspecialchars(substr((string) $order->created_at, 0, 16))
                        . ($order->completed_at ? ' &rarr; ' . htmlspecialchars(substr((string) $order->completed_at, 0, 16)) : '')
                        . '</td>'
                        . '<td style="padding:2px 0;white-space:nowrap">' . $resolveCell . $cancelCell . '</td></tr>';
                }
                $orderMsg = (string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
                    'ordermsg_' . $serviceId,
                    ''
                ) ?? '');
                $fields['Order History'] = '<details' . ((count($orders) <= 3 || $hasInFlight) ? ' open' : '') . '>'
                    . '<summary style="cursor:pointer">' . count($orders) . ' order'
                    . (count($orders) === 1 ? '' : 's') . '</summary>'
                    . ($orderMsg !== ''
                        ? '<div style="margin:6px 0"><strong>' . htmlspecialchars($orderMsg) . '</strong></div>' : '')
                    . '<table style="font-size:12px;margin-top:6px;text-align:left">'
                    . '<tr style="color:#667"><th style="text-align:left;padding-right:12px">Type</th>'
                    . '<th style="text-align:left;padding-right:12px">VT Order</th>'
                    . '<th style="text-align:left;padding-right:12px">Status</th>'
                    . '<th style="text-align:left;padding-right:12px">Lodged &rarr; Completed</th>'
                    . '<th></th></tr>'
                    . $orderRows . '</table></details>';
            }

            // Recent callbacks: what Virtutel actually sent us for this
            // service — ends "did the webhook arrive?" debugging.
            $orderVtIds = [];
            foreach ($orders as $order) {
                if ((string) ($order->vt_order_id ?? '') !== '') {
                    $orderVtIds[] = (string) $order->vt_order_id;
                }
            }
            $events = WHMCS\Database\Capsule::table('mod_virtutel_callback_events')
                ->where(function ($q) use ($row, $serviceId, $orderVtIds) {
                    $q->where('service_id', (int) $row->id)
                        ->orWhere('service_id', $serviceId);
                    if ((string) ($row->vt_service_id ?? '') !== '') {
                        $q->orWhere('vt_object_id', (string) $row->vt_service_id);
                    }
                    if ((string) ($row->avc_id ?? '') !== '') {
                        $q->orWhere('vt_object_id', (string) $row->avc_id);
                    }
                    if ($orderVtIds !== []) {
                        $q->orWhereIn('vt_object_id', $orderVtIds);
                    }
                })
                ->orderByDesc('id')->limit(15)->get();
            if (count($events) > 0) {
                $eventRows = '';
                foreach ($events as $event) {
                    $payload = trim((string) ($event->payload ?? ''));
                    $eventRows .= '<tr>'
                        . '<td style="padding:2px 12px 2px 0;color:#667;white-space:nowrap">'
                        . htmlspecialchars(substr((string) ($event->event_time ?: $event->created_at), 0, 16)) . '</td>'
                        . '<td style="padding:2px 12px 2px 0">' . htmlspecialchars((string) ($event->notification_type ?: $event->event_type)) . '</td>'
                        . '<td style="padding:2px 12px 2px 0"><code>' . htmlspecialchars((string) ($event->vt_object_id ?? '')) . '</code></td>'
                        . '<td style="padding:2px 12px 2px 0">' . htmlspecialchars((string) $event->status) . '</td>'
                        . '<td style="padding:2px 0">'
                        . ($payload !== ''
                            ? '<details><summary style="cursor:pointer;font-size:11px;color:#667">payload</summary>'
                                . '<pre style="max-height:180px;overflow:auto;font-size:10.5px;max-width:560px">'
                                . htmlspecialchars(substr($payload, 0, 2000)) . '</pre></details>'
                            : '')
                        . '</td></tr>';
                }
                $fields['Recent Callbacks'] = '<details><summary style="cursor:pointer">'
                    . count($events) . ' event' . (count($events) === 1 ? '' : 's')
                    . ' received</summary>'
                    . '<table style="font-size:12px;margin-top:6px;text-align:left">'
                    . '<tr style="color:#667"><th style="text-align:left;padding-right:12px">When</th>'
                    . '<th style="text-align:left;padding-right:12px">Notification</th>'
                    . '<th style="text-align:left;padding-right:12px">Object</th>'
                    . '<th style="text-align:left;padding-right:12px">Status</th><th></th></tr>'
                    . $eventRows . '</table></details>';
            }
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
    // Authoritative testType list (from the API's own validation error,
    // 2026-07-26): DPU_PORT_STATUS DPU_PORT_RESET DPU_STATUS
    // DYNAMIC_LINE_MANAGEMENT_STATUS EE_* LINE_QUALITY_DIAGNOSTIC
    // LINE_STATE_DIAGNOSTIC LOOPBACK NCD_PORT_RESET NCD_RESET
    // NCD_UNI_D_STATUS NNI_LINK_OPERATIONAL_STATUS NTD_RESET NTD_STATUS
    // PORT_RESET SINGLE_END_LINE_TEST UNI_D_STATUS UNI_V_STATUS
    // WNTD_RESET WNTD_STATUS
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
            'WNTD_STATUS' => 'WNTD Status',
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
    virtutel_nbn_handle_speed_change((int) $params['serviceid']);
    virtutel_nbn_handle_order_cancel((int) $params['serviceid']);
    virtutel_nbn_handle_order_resolve((int) $params['serviceid']);

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
/**
 * Approve/Resume buttons on action-required orders. The PATCH keys come
 * from the API docs: newDevelopmentsChargeConfirmed and
 * fibreUpgradeLiabilityConfirmed on the order update, ?resume=true for
 * RSP-action continuation. The install-fee key isn't documented — the
 * closest schema name is tried and any rejection (which names the
 * allowed attributes) is surfaced verbatim.
 */
function virtutel_nbn_handle_order_resolve(int $serviceId): void
{
    $vtOrderId = strtoupper(trim((string) ($_REQUEST['vt_order_resolve'] ?? '')));
    if ($vtOrderId === '') {
        return;
    }

    $msg = function (string $text) use ($serviceId): void {
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set('ordermsg_' . $serviceId, $text);
    };

    try {
        Migrations::ensure();
        $link = WHMCS\Database\Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)->first();
        $order = $link ? WHMCS\Database\Capsule::table('mod_virtutel_orders')
            ->where('service_id', (int) $link->id)
            ->where('vt_order_id', $vtOrderId)
            ->first() : null;
        if (!$order) {
            $msg('Order ' . $vtOrderId . ' not found on this service.');

            return;
        }

        $action = (string) ($order->action_required ?? '');
        $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($serviceId);
        $path = VirtutelClient::PATH_PRODUCT_ORDERS . '/' . rawurlencode($vtOrderId);

        switch ($action) {
            case 'development_charge':
                $client->request('PATCH', $path, [
                    'action' => 'ApproveNdc',
                    'json' => ['newDevelopmentsChargeConfirmed' => true],
                ]);
                $done = 'New Development Charge approved';
                break;
            case 'fibre_upgrade_liability':
                $client->request('PATCH', $path, [
                    'action' => 'ConfirmFibreLiability',
                    'json' => ['fibreUpgradeLiabilityConfirmed' => true],
                ]);
                $done = 'Fibre upgrade liability confirmed';
                break;
            case 'install_fee':
                $client->request('PATCH', $path, [
                    'action' => 'ApproveInstallFee',
                    'json' => ['subsequentInstallationChargeConfirmed' => true],
                ]);
                $done = 'Install fee approved';
                break;
            case 'rsp_action':
            case 'device_online':
                $client->request('PATCH', $path, [
                    'action' => 'ResumeOrder',
                    'query' => ['resume' => 'true'],
                ]);
                $done = 'Resume requested — NBN will continue the order';
                break;
            default:
                $msg('Order ' . $vtOrderId . ' has no API-resolvable action (' . $action . ').');

                return;
        }

        WHMCS\Database\Capsule::table('mod_virtutel_orders')
            ->where('id', (int) $order->id)
            ->update([
                'action_required' => null,
                'whmcs_status' => 'in_progress',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        logActivity(sprintf(
            'Virtutel NBN: %s for order %s (service #%d)',
            $done,
            $vtOrderId,
            $serviceId
        ));
        $msg($done . ' for ' . $vtOrderId . '.');
    } catch (\Throwable $e) {
        $msg('Action failed for ' . $vtOrderId . ': ' . $e->getMessage());
    }
}

/**
 * Cancel-in-flight-order dropdown: cancels the booked appointment (the
 * only cancel the API supports), flags the order cancel-requested, and
 * raises a to-do to get Virtutel support to withdraw the order itself.
 */
function virtutel_nbn_handle_order_cancel(int $serviceId): void
{
    $vtOrderId = strtoupper(trim((string) ($_REQUEST['vt_cancel_order'] ?? '')));
    if ($vtOrderId === '') {
        return;
    }

    $msg = function (string $text) use ($serviceId): void {
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set('ordermsg_' . $serviceId, $text);
    };

    try {
        Migrations::ensure();
        $link = WHMCS\Database\Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)->first();
        $order = $link ? WHMCS\Database\Capsule::table('mod_virtutel_orders')
            ->where('service_id', (int) $link->id)
            ->where('vt_order_id', $vtOrderId)
            ->first() : null;
        if (!$order) {
            $msg('Order ' . $vtOrderId . ' not found on this service.');

            return;
        }

        // Cancel a booked appointment if the order has one — that part IS
        // supported by the API.
        $apptNote = '';
        $appt = WHMCS\Database\Capsule::table('mod_virtutel_appointments')
            ->where('order_id', (int) $order->id)
            ->whereNotNull('appointment_id')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderByDesc('id')->first();
        if ($appt) {
            try {
                $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($serviceId);
                (new \WHMCS\Module\Server\VirtutelNbn\Service\AppointmentService($client))
                    ->cancel((string) $appt->appointment_id);
                WHMCS\Database\Capsule::table('mod_virtutel_appointments')
                    ->where('id', (int) $appt->id)
                    ->update(['status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s')]);
                $apptNote = ' Appointment ' . (string) $appt->appointment_id . ' cancelled.';
            } catch (\Throwable $e) {
                $apptNote = ' Appointment cancel FAILED: ' . $e->getMessage();
            }
        }

        WHMCS\Database\Capsule::table('mod_virtutel_orders')
            ->where('id', (int) $order->id)
            ->update(['action_required' => 'cancel_requested', 'updated_at' => date('Y-m-d H:i:s')]);

        localAPI('AddTodoItem', [
            'date' => date('Y-m-d'),
            'title' => 'Virtutel NBN: withdraw order ' . $vtOrderId,
            'description' => sprintf(
                'Cancellation requested for %s order %s (WHMCS service #%d). The API has no order '
                . 'cancel — contact Virtutel support to withdraw it.%s',
                (string) $order->order_type,
                $vtOrderId,
                $serviceId,
                $apptNote
            ),
            'status' => 'Pending',
            'duedate' => date('Y-m-d'),
        ]);

        logActivity(sprintf(
            'Virtutel NBN: cancel requested for order %s (service #%d).%s',
            $vtOrderId,
            $serviceId,
            $apptNote
        ));
        $msg('Cancel requested for ' . $vtOrderId . '.' . $apptNote
            . ' To-do raised: contact Virtutel to withdraw the order.');
    } catch (\Throwable $e) {
        $msg('Cancel request failed: ' . $e->getMessage());
    }
}

/** Change Speed dropdown on the admin tab: lodge the Modify Speed order. */
function virtutel_nbn_handle_speed_change(int $serviceId): void
{
    $newSpeed = strtoupper(trim((string) ($_REQUEST['vt_speed_change'] ?? '')));
    if ($newSpeed === '') {
        return;
    }

    try {
        Migrations::ensure();
        $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($serviceId);
        $orderId = (new \WHMCS\Module\Server\VirtutelNbn\Service\LifecycleService())
            ->changeSpeed($client, $serviceId, $newSpeed);
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
            'speedmsg_' . $serviceId,
            'Speed change to ' . $newSpeed . ' lodged — order ' . $orderId
            . '. RADIUS updates automatically when it completes.'
        );
    } catch (\Throwable $e) {
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
            'speedmsg_' . $serviceId,
            'Speed change failed: ' . $e->getMessage()
        );
    }
}

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

    return [
        'Run Service Health Check' => 'runhealthcheck',
        'Refresh from Virtutel' => 'refreshfromvt',
        'AVC Utilisation' => 'avcutil',
        'Reset Daily Test Limit' => 'resetdailytests',
        'Cancel Service (Disconnect)' => 'cancelservice',
    ];
}

/**
 * Latest AVC utilisation for this service from Virtutel's (beta) daily
 * reports: finds the newest report, scans it for the AVC, returns a
 * one-line summary. Requires the read:avc-utilisation scope.
 */
function virtutel_nbn_avcutil(array $params): string
{
    $serviceId = (int) $params['serviceid'];
    try {
        Migrations::ensure();
        $row = WHMCS\Database\Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)->first();
        $avc = (string) ($row->avc_id ?? '');
        if ($avc === '') {
            return 'No AVC ID on file for this service.';
        }

        $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($serviceId);

        $list = $client->request('GET', '/avc-utilisation/list-reports', [
            'action' => 'UtilListReports',
            'query' => ['limit' => 10],
            'timeout' => 20,
            'attempts' => 1,
        ]);
        $newest = null;
        foreach ((array) $list->get('reports', []) as $report) {
            if ($newest === null || (string) ($report['createdUtc'] ?? '') > (string) ($newest['createdUtc'] ?? '')) {
                $newest = $report;
            }
        }
        $uuid = (string) ($newest['uuid'] ?? '');
        if ($uuid === '') {
            return 'No utilisation reports available yet (beta — check the scope is enabled).';
        }

        for ($page = 1; $page <= 6; $page++) {
            $records = $client->request('GET', '/avc-utilisation/by-uuid/' . rawurlencode($uuid), [
                'action' => 'UtilRecords',
                'query' => ['page' => $page, 'limit' => 500],
                'timeout' => 25,
                'attempts' => 1,
            ]);
            $rows = (array) $records->get('reports', []);
            foreach ($rows as $record) {
                if (strcasecmp((string) ($record['avcId'] ?? ''), $avc) !== 0) {
                    continue;
                }
                $pct = fn ($v) => is_numeric($v) ? round((float) $v * 100, 2) . '%' : '?';

                return sprintf(
                    'Utilisation for %s (report %s): AVC peak %s at %s, during CSA peak (%s) %s, overage %s, breach: %s.',
                    $avc,
                    (string) ($newest['reportDate'] ?? '?'),
                    $pct($record['avcUtilAvcPeakHr'] ?? null),
                    (string) ($record['avcPeakHour'] ?? '?'),
                    (string) ($record['csaPeakHour'] ?? '?'),
                    $pct($record['avcUtilCsaPeakHr'] ?? null),
                    (string) ($record['overage'] ?? '0'),
                    !empty($record['breach']) ? 'YES' : 'no'
                );
            }
            if (count($rows) < 500) {
                break; // last page
            }
        }

        return 'AVC ' . $avc . ' not found in the latest report ('
            . (string) ($newest['reportDate'] ?? '?') . ') — it may predate the service.';
    } catch (\Throwable $e) {
        return 'Utilisation lookup failed: ' . $e->getMessage();
    }
}

/**
 * Re-pulls the /services record on demand and refreshes the stored link
 * (status, speed, address, domain, raw data) — no waiting on callbacks.
 */
function virtutel_nbn_refreshfromvt(array $params): string
{
    $serviceId = (int) $params['serviceid'];
    try {
        Migrations::ensure();
        $row = WHMCS\Database\Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)->first();
        if (!$row || (string) ($row->vt_service_id ?? '') === '') {
            return 'Not linked to a Virtutel service — link it first.';
        }

        $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($serviceId);
        $svc = \WHMCS\Module\Server\VirtutelNbn\Service\ServiceLinker::lookup(
            $client,
            (string) $row->vt_service_id
        );
        if ($svc === null) {
            return 'Virtutel no longer returns ' . (string) $row->vt_service_id
                . ' — the service may be disconnected carrier-side.';
        }

        \WHMCS\Module\Server\VirtutelNbn\Service\ServiceLinker::link($serviceId, $svc, $client);
        logActivity(sprintf(
            'Virtutel NBN: refreshed service #%d from Virtutel (%s, status %s)',
            $serviceId,
            (string) ($svc['vtServiceId'] ?? '?'),
            (string) ($svc['status'] ?? '?')
        ));

        return 'success';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
}

/**
 * Lodges the Virtutel disconnect order — two clicks required (the second
 * within 5 minutes) since module command buttons have no confirm dialog.
 * The customer stays online until the carrier completes the order; WHMCS
 * billing termination stays a separate, deliberate step.
 */
function virtutel_nbn_cancelservice(array $params): string
{
    $serviceId = (int) $params['serviceid'];
    try {
        Migrations::ensure();
        $row = WHMCS\Database\Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)->first();
        if (!$row || (string) ($row->vt_service_id ?? '') === '') {
            return 'Not linked to a Virtutel service — nothing to disconnect.';
        }

        $inFlight = WHMCS\Database\Capsule::table('mod_virtutel_orders')
            ->where('service_id', (int) $row->id)
            ->where('order_type', 'disconnect')
            ->whereIn('whmcs_status', ['pending', 'in_progress', 'action_required'])
            ->orderByDesc('id')->first();
        if ($inFlight) {
            return 'A disconnect order is already in flight ('
                . (string) ($inFlight->vt_order_id ?? 'no ID yet') . ').';
        }

        $key = 'cancelreq_' . $serviceId;
        $asked = (int) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get($key, '0') ?? '0');
        if ($asked < time() - 300) {
            \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set($key, (string) time());

            return 'CONFIRM: this lodges a DISCONNECT order with Virtutel for '
                . (string) $row->vt_service_id
                . '. Click "Cancel Service (Disconnect)" again within 5 minutes to proceed.';
        }
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set($key, '0');

        $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($serviceId);
        $orderId = (new \WHMCS\Module\Server\VirtutelNbn\Service\LifecycleService())
            ->lodgeDisconnect($client, $serviceId);

        return 'Disconnect order lodged'
            . ($orderId !== '' ? ' — ' . $orderId : '')
            . '. The service stays online until Virtutel completes it; terminate in WHMCS '
            . 'separately when billing should stop.';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
}

/**
 * Clears today's customer self-serve check counter (5/day cap) for this
 * service — for testing, or after walking a customer through a fault.
 */
function virtutel_nbn_resetdailytests(array $params): string
{
    $serviceId = (int) $params['serviceid'];
    try {
        Migrations::ensure();
        $key = 'ctests_' . $serviceId . '_' . date('Ymd');
        $used = (int) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get($key, '0') ?? '0');
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set($key, '0');

        if (function_exists('logActivity')) {
            logActivity(sprintf(
                'Virtutel NBN: daily customer test limit reset for service #%d (was %d/5)',
                $serviceId,
                $used
            ));
        }

        return 'success';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
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
