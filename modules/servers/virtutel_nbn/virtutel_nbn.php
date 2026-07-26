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

        // Link / re-link an existing Virtutel service by ID (no order
        // lodged): the Save handler looks it up via GET /services.
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
            if (!empty($health['report'])) {
                $pretty = (string) json_encode($health['report'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $html .= '<br><pre style="max-height:280px;overflow:auto;font-size:11px;'
                    . 'background:#f6f8fb;border:1px solid #dde3ee;padding:8px;border-radius:4px">'
                    . htmlspecialchars(substr($pretty, 0, 8000))
                    . (strlen($pretty) > 8000 ? "\n… (truncated)" : '')
                    . '</pre>';
            }
            $fields['Service Health'] = $html;
        }

        return $fields;
    } catch (\Throwable $e) {
        return ['Virtutel' => 'Error: ' . htmlspecialchars($e->getMessage())];
    }
}

/**
 * Save handler for the tab fields: performs the link when an ID was pasted.
 */
function virtutel_nbn_AdminServicesTabFieldsSave(array $params): void
{
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
