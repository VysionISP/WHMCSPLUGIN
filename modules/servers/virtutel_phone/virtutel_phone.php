<?php

/**
 * Virtutel Phone — thin WHMCS server module for voice/mobile services on
 * the Virtutel Customer API.
 *
 * Deliberately minimal: it shares virtutel_nbn's lib/ (client, token
 * cache, ServiceLinker, migrations) and exists so phone products get
 * their own product-catalogue module, a phone-shaped client page, and
 * honest manual-action messages for lifecycle operations the API doesn't
 * automate yet. Server record: same host/port/credentials as the NBN one.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Api\VirtutelClient;
use WHMCS\Module\Server\VirtutelNbn\Migrations;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/../virtutel_nbn/lib/Autoloader.php';

function virtutel_phone_MetaData(): array
{
    return [
        'DisplayName' => 'Virtutel Phone',
        'APIVersion' => '1.1',
        'RequiresServer' => true,
        'ListAccountsUniqueIdentifierField' => 'domain',
        'ListAccountsUniqueIdentifierDisplayName' => 'Phone Number',
    ];
}

function virtutel_phone_ConfigOptions(): array
{
    return [
        'plan_note' => [
            'FriendlyName' => 'Plan Note',
            'Type' => 'text',
            'Size' => '40',
            'Default' => '',
            'Description' => 'Optional plan description shown to the customer (e.g. "Unlimited national + mobile calls").',
        ],
    ];
}

function virtutel_phone_TestConnection(array $params): array
{
    try {
        Migrations::ensure();
        $client = VirtutelClient::fromModuleParams($params);
        $client->ensureAccessToken();

        return ['success' => true, 'error' => ''];
    } catch (\Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * No automated phone ordering yet (Virtutel's mobile ordering endpoints
 * are ALPHA): new services are provisioned in the Virtutel portal, then
 * linked here. The explicit error keeps the order visibly pending instead
 * of silently "active" with nothing behind it.
 */
function virtutel_phone_CreateAccount(array $params): string
{
    try {
        Migrations::ensure();
        $linked = Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', (int) $params['serviceid'])
            ->whereNotNull('vt_service_id')->where('vt_service_id', '!=', '')
            ->exists();
        if ($linked) {
            return 'success'; // already linked to a real Virtutel service
        }
    } catch (\Throwable $e) {
        // fall through to the manual message
    }

    return 'Manual step: provision the phone service in the Virtutel portal, '
        . 'then paste its VT service ID into the Link box on this service and Save.';
}

function virtutel_phone_SuspendAccount(array $params): string
{
    return 'Manual action required: suspend this phone service in the Virtutel portal '
        . '(no phone suspension API yet).';
}

function virtutel_phone_UnsuspendAccount(array $params): string
{
    return 'Manual action required: unsuspend this phone service in the Virtutel portal '
        . '(no phone suspension API yet).';
}

function virtutel_phone_TerminateAccount(array $params): string
{
    return 'Manual action required: cancel this phone service in the Virtutel portal, '
        . 'then mark it terminated here.';
}

/** Client area: phone-shaped overview (number, status, plan). */
function virtutel_phone_ClientArea(array $params): array
{
    $serviceId = (int) $params['serviceid'];
    $number = trim((string) ($params['domain'] ?? ''));
    $status = '';
    $description = '';
    try {
        Migrations::ensure();
        $row = Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)->first();
        $status = (string) ($row->carrier_status ?? '');

        $raw = json_decode((string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
            'svcraw_' . $serviceId,
            ''
        ) ?? ''), true);
        if (is_array($raw)) {
            $description = trim((string) ($raw['description'] ?? ''));
            if ($number === '') {
                foreach (['mobileNumber', 'msisdn', 'phoneNumber', 'fnn', 'serviceNumber', 'did'] as $key) {
                    if (!empty($raw[$key]) && is_scalar($raw[$key])) {
                        $number = trim((string) $raw[$key]);
                        break;
                    }
                }
            }
        }
    } catch (\Throwable $e) {
        // page still renders with whatever we have
    }

    $planNote = trim((string) ($params['configoption1'] ?? ''));

    return [
        'templatefile' => 'templates/overview',
        'vars' => [
            'vp_number' => $number,
            'vp_status' => $status !== '' ? $status : 'Active',
            'vp_state' => stripos($status, 'active') !== false || $status === '' ? 'active' : 'attention',
            'vp_plan' => $planNote !== '' ? $planNote : $description,
        ],
    ];
}

/** Admin tab: link an existing Virtutel service + raw record view (same
 *  flow as the NBN module, via the shared ServiceLinker). */
function virtutel_phone_AdminServicesTabFields(array $params): array
{
    try {
        Migrations::ensure();
        $serviceId = (int) $params['serviceid'];
        $row = Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)->first();

        $fields = [];
        if ($row) {
            $fields = [
                'VT Service ID' => htmlspecialchars((string) ($row->vt_service_id ?? '—')),
                'Type' => htmlspecialchars((string) ($row->technology_type ?? '—')),
                'Carrier Status' => htmlspecialchars((string) ($row->carrier_status ?? '—')),
            ];

            $raw = json_decode((string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
                'svcraw_' . $serviceId,
                ''
            ) ?? ''), true);
            if (is_array($raw) && $raw !== []) {
                $rawRows = '';
                foreach ($raw as $rk => $rv) {
                    if (is_array($rv)) {
                        foreach ($rv as $sk => $sv) {
                            if (is_scalar($sv) && (string) $sv !== '') {
                                $rawRows .= '<tr><td style="color:#667;padding:2px 14px 2px 0;white-space:nowrap">'
                                    . htmlspecialchars($rk . '.' . $sk) . '</td><td><code>'
                                    . htmlspecialchars((string) $sv) . '</code></td></tr>';
                            }
                        }
                    } elseif (is_scalar($rv) && (string) $rv !== '') {
                        $rawRows .= '<tr><td style="color:#667;padding:2px 14px 2px 0;white-space:nowrap">'
                            . htmlspecialchars((string) $rk) . '</td><td><code>'
                            . htmlspecialchars((string) $rv) . '</code></td></tr>';
                    }
                }
                if ($rawRows !== '') {
                    $fields['Virtutel Data'] = '<details><summary style="cursor:pointer">'
                        . 'Everything Virtutel returns for this service</summary>'
                        . '<table style="font-size:12px;margin-top:6px;text-align:left">'
                        . $rawRows . '</table></details>';
                }
            }
        }

        if (!$row || (string) ($row->vt_service_id ?? '') === '') {
            $linkMsg = (string) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get(
                'linkmsg_' . $serviceId,
                ''
            ) ?? '');
            $fields['Link Virtutel Service'] =
                '<input type="text" name="vt_link_ref" size="34" value="" '
                . 'placeholder="VT... / PRI... / phone number" autocomplete="off" />'
                . '<br><small>Paste the Virtutel service ID (or number) and click Save Changes to link '
                . 'this phone service.'
                . ($linkMsg !== '' ? ' <strong>' . htmlspecialchars($linkMsg) . '</strong>' : '')
                . '</small>';
        }

        return $fields;
    } catch (\Throwable $e) {
        return ['Virtutel' => 'Error: ' . htmlspecialchars($e->getMessage())];
    }
}

function virtutel_phone_AdminServicesTabFieldsSave(array $params): void
{
    $ref = trim((string) ($_POST['vt_link_ref'] ?? ''));
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
            'Linked to ' . (string) ($svc['vtServiceId'] ?? '?')
            . ' (' . (string) ($svc['description'] ?? '') . ').'
        );
        if (function_exists('logActivity')) {
            logActivity(sprintf(
                'Virtutel Phone: linked WHMCS service #%d to %s',
                $serviceId,
                (string) ($svc['vtServiceId'] ?? '?')
            ));
        }
    } catch (\Throwable $e) {
        \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set(
            'linkmsg_' . $serviceId,
            'Link failed: ' . $e->getMessage()
        );
    }
}
