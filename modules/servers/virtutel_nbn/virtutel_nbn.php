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

        // Step 3 of the build plan: ProvisioningService::createService().
        return 'Provisioning is not implemented yet (build step 3)';
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
