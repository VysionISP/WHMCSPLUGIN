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
    // RADIUS-side suspension (build step 4).
    return 'Suspension is not implemented yet (build step 4)';
}

function virtutel_nbn_UnsuspendAccount(array $params): string
{
    // RADIUS-side unsuspension (build step 4).
    return 'Unsuspension is not implemented yet (build step 4)';
}

function virtutel_nbn_TerminateAccount(array $params): string
{
    // Disconnect order + RADIUS removal (build step 4).
    return 'Termination is not implemented yet (build step 4)';
}

function virtutel_nbn_ChangePackage(array $params): string
{
    // Modify Speed order + RADIUS attribute update (build step 4).
    return 'Package changes are not implemented yet (build step 4)';
}

function virtutel_nbn_AdminServicesTabFields(array $params): array
{
    try {
        Migrations::ensure();

        $row = WHMCS\Database\Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', (int) $params['serviceid'])
            ->first();

        if (!$row) {
            return ['Virtutel' => 'Not linked to a Virtutel service yet.'];
        }

        return [
            'VT Service ID' => htmlspecialchars((string) ($row->vt_service_id ?? '—')),
            'AVC ID' => htmlspecialchars((string) ($row->avc_id ?? '—')),
            'NBN Location ID' => htmlspecialchars((string) ($row->nbn_location_id ?? '—')),
            'Technology' => htmlspecialchars((string) ($row->technology_type ?? '—')),
            'Carrier Status' => htmlspecialchars((string) ($row->carrier_status ?? '—')),
        ];
    } catch (\Throwable $e) {
        return ['Virtutel' => 'Error: ' . htmlspecialchars($e->getMessage())];
    }
}
