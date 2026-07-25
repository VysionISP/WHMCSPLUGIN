<?php

/**
 * Virtutel NBN admin tools (addon module).
 *
 * Admin page: Addons -> Virtutel NBN Tools. Address search -> service
 * qualification with the full picture rendered: technology, service class,
 * orderability, speed tiers, NTD/UNI-D port map, copper pairs, churn
 * matches, and the exact values to copy into a service's custom fields.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory;
use WHMCS\Module\Server\VirtutelNbn\Migrations;
use WHMCS\Module\Server\VirtutelNbn\Service\ProvisioningService;
use WHMCS\Module\Server\VirtutelNbn\Service\QualificationService;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/../../servers/virtutel_nbn/lib/Autoloader.php';

function virtutel_nbn_admin_config(): array
{
    return [
        'name' => 'Virtutel NBN Tools',
        'description' => 'Qualification lookups (technology, service class, speed tiers, NTD ports) against the Virtutel Customer API.',
        'author' => 'Vysion',
        'version' => '1.1',
        'fields' => [
            'portal_dark' => [
                'FriendlyName' => 'Dark Portal Theme',
                'Type' => 'yesno',
                'Description' => 'Apply the Korvix dark skin over the client area (keeps the Twenty-One template).',
            ],
            'google_places_key' => [
                'FriendlyName' => 'Google Places API Key',
                'Type' => 'password',
                'Size' => '50',
                'Description' => 'Enables address autocomplete on the customer qualification page. '
                    . 'Restrict the key to your domains and the Maps JavaScript + Places APIs.',
            ],
            'radius_db_host' => [
                'FriendlyName' => 'FreeRADIUS DB Host',
                'Type' => 'text',
                'Size' => '30',
                'Description' => 'MySQL host of the FreeRADIUS SQL backend.',
            ],
            'radius_db_port' => [
                'FriendlyName' => 'FreeRADIUS DB Port',
                'Type' => 'text',
                'Size' => '6',
                'Default' => '3306',
            ],
            'radius_db_name' => [
                'FriendlyName' => 'FreeRADIUS DB Name',
                'Type' => 'text',
                'Size' => '20',
                'Default' => 'radius',
            ],
            'radius_db_user' => [
                'FriendlyName' => 'FreeRADIUS DB Username',
                'Type' => 'text',
                'Size' => '20',
            ],
            'radius_db_pass' => [
                'FriendlyName' => 'FreeRADIUS DB Password',
                'Type' => 'password',
                'Size' => '30',
            ],
            'radius_default_group' => [
                'FriendlyName' => 'Active Group',
                'Type' => 'text',
                'Size' => '20',
                'Default' => 'nbn-active',
                'Description' => 'radusergroup group for active services.',
            ],
            'radius_suspend_group' => [
                'FriendlyName' => 'Suspended Group',
                'Type' => 'text',
                'Size' => '20',
                'Default' => 'nbn-suspended',
                'Description' => 'Group applied on suspension (walled garden or reject — defined in FreeRADIUS).',
            ],
            'radius_rate_attr' => [
                'FriendlyName' => 'Rate Limit Attribute',
                'Type' => 'text',
                'Size' => '30',
                'Default' => 'Mikrotik-Rate-Limit',
                'Description' => 'radreply attribute carrying the speed profile.',
            ],
            'radius_rate_format' => [
                'FriendlyName' => 'Rate Limit Format',
                'Type' => 'text',
                'Size' => '30',
                'Default' => '{up}M/{down}M',
                'Description' => 'Placeholders: {down} {up} in Mbps, {down_k} {up_k} in Kbps.',
            ],
            'coa_host' => [
                'FriendlyName' => 'CoA Target (BNG IP)',
                'Type' => 'text',
                'Size' => '30',
                'Description' => 'Where Disconnect-Requests are sent to drop live sessions.',
            ],
            'coa_port' => [
                'FriendlyName' => 'CoA Port',
                'Type' => 'text',
                'Size' => '6',
                'Default' => '3799',
            ],
            'coa_secret' => [
                'FriendlyName' => 'CoA Shared Secret',
                'Type' => 'password',
                'Size' => '30',
            ],
        ],
    ];
}

function virtutel_nbn_admin_activate(): array
{
    try {
        Migrations::ensure();

        return ['status' => 'success', 'description' => 'Virtutel NBN Tools activated.'];
    } catch (\Throwable $e) {
        return ['status' => 'error', 'description' => $e->getMessage()];
    }
}

function virtutel_nbn_admin_deactivate(): array
{
    return ['status' => 'success', 'description' => 'Virtutel NBN Tools deactivated (no data removed).'];
}

function virtutel_nbn_admin_output(array $vars): void
{
    $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES);
    $self = 'addonmodules.php?module=virtutel_nbn_admin';

    $address = trim((string) ($_REQUEST['vt_address'] ?? ''));
    $locationId = strtoupper(trim((string) ($_REQUEST['vt_locid'] ?? '')));
    $churnAvc = strtoupper(trim((string) ($_REQUEST['vt_churn_avc'] ?? '')));
    $authorityDate = trim((string) ($_REQUEST['vt_authority_date'] ?? ''));

    echo '<h2>Virtutel NBN — Service Qualification</h2>';

    // Environment banner + client.
    try {
        Migrations::ensure();
        $server = Capsule::table('tblservers')
            ->where('type', 'virtutel_nbn')->where('disabled', 0)->first();
        if (!$server) {
            throw new \RuntimeException('No enabled Virtutel NBN server configured (Setup > Servers).');
        }
        $client = ClientFactory::forServerRow($server);
        echo '<p><span class="label label-' . ($client->getEnvironment() === 'production' ? 'danger' : 'info') . '">'
            . $e(strtoupper($client->getEnvironment())) . '</span> using server "' . $e($server->name) . '"</p>';
    } catch (\Throwable $ex) {
        echo '<div class="alert alert-danger">' . $e($ex->getMessage()) . '</div>';

        return;
    }

    // FreeRADIUS connectivity test.
    if (isset($_POST['vt_test_radius'])) {
        echo '<h3>FreeRADIUS Test</h3>';
        try {
            $config = WHMCS\Module\Server\VirtutelNbn\Radius\RadiusConfig::load();
            if (!WHMCS\Module\Server\VirtutelNbn\Radius\RadiusConfig::isConfigured($config)) {
                echo '<div class="alert alert-warning">FreeRADIUS settings are not configured yet (Configure button above).</div>';
            } else {
                $radius = new WHMCS\Module\Server\VirtutelNbn\Radius\FreeRadiusSqlProvisioner($config);
                $health = $radius->healthCheck();
                echo '<div class="alert alert-success">Database connection OK.</div>';
                if ($health['missing_tables'] !== []) {
                    echo '<div class="alert alert-warning">Missing tables: '
                        . $e(implode(', ', $health['missing_tables'])) . '</div>';
                }
                if ($config['coa_host'] !== '') {
                    $acked = null;
                    $responded = (new WHMCS\Module\Server\VirtutelNbn\Radius\CoaClient(
                        $config['coa_host'],
                        (int) $config['coa_port'],
                        $config['coa_secret']
                    ))->disconnect('VT-COA-TEST-NONEXISTENT', $acked);
                    echo $responded
                        ? '<div class="alert alert-success">CoA target responded ('
                            . ($acked ? 'ACK' : 'NAK — expected for a test user') . ') — reachability and shared secret OK.</div>'
                        : '<div class="alert alert-danger">No response from the CoA target — check BNG IP, port, firewall, and shared secret.</div>';
                } else {
                    echo '<div class="alert alert-info">No CoA target configured — session disconnects will be skipped.</div>';
                }
            }
        } catch (\Throwable $ex) {
            echo '<div class="alert alert-danger">RADIUS test failed: ' . $e($ex->getMessage()) . '</div>';
        }
    }

    echo '<form method="post" action="' . $self . '" style="margin-bottom:18px">'
        . '<button type="submit" name="vt_test_radius" value="1" class="btn btn-default">Test FreeRADIUS Connection</button>'
        . '</form>';

    // Search / qualify forms.
    echo '<form method="post" action="' . $self . '" class="form-inline" style="margin-bottom:10px">'
        . '<input type="text" name="vt_address" size="60" class="form-control" placeholder="Street address e.g. 546 FLINDERS ST MELBOURNE VIC 3000" value="' . $e($address) . '"> '
        . '<button type="submit" class="btn btn-primary">Search Address</button>'
        . '</form>';

    echo '<form method="post" action="' . $self . '" class="form-inline" style="margin-bottom:20px">'
        . '<input type="text" name="vt_locid" size="20" class="form-control" placeholder="LOC000000000000" value="' . $e($locationId) . '"> '
        . '<input type="text" name="vt_churn_avc" size="18" class="form-control" placeholder="Churn AVC (optional)" value="' . $e($churnAvc) . '"> '
        . '<input type="text" name="vt_authority_date" size="12" class="form-control" placeholder="YYYY-MM-DD" value="' . $e($authorityDate) . '"> '
        . '<button type="submit" class="btn btn-success">Run Qualification</button>'
        . '</form>';

    try {
        // Address search results.
        if ($address !== '' && $locationId === '') {
            $results = (new QualificationService($client))->searchAddress([
                'unstructured' => ['address' => $address, 'fuzzy' => true],
            ]);

            if ($results === []) {
                echo '<div class="alert alert-warning">No NBN locations matched that address.</div>';
            } else {
                echo '<h3>Matches (best first)</h3><table class="table table-bordered table-striped"><thead><tr>'
                    . '<th>LOC ID</th><th>Address</th><th></th></tr></thead><tbody>';
                foreach (array_slice($results, 0, 15) as $row) {
                    $rowLoc = (string) ($row['id'] ?? '');
                    $label = (string) (($row['fullAddress'] ?? '') ?: ($row['formattedAddress'] ?? ''));
                    echo '<tr><td>' . $e($rowLoc) . '</td><td>' . $e($label) . '</td>'
                        . '<td><a class="btn btn-sm btn-success" href="' . $self . '&vt_locid=' . $e(rawurlencode($rowLoc)) . '">Qualify</a></td></tr>';
                }
                echo '</tbody></table>';
            }
        }

        // Qualification.
        if ($locationId !== '') {
            $params = [];
            if ($churnAvc !== '') {
                $params['serviceID'] = $churnAvc;
                $params['customerAuthorityDate'] = $authorityDate !== '' ? $authorityDate : date('Y-m-d');
            }
            $result = (new QualificationService($client))->qualifyWithRaw($locationId, $params);
            $q = $result['parsed'];

            $badge = $q['orderable']
                ? '<span class="label label-success">ORDERABLE</span>'
                : '<span class="label label-danger">NOT ORDERABLE</span>';

            echo '<h3>Qualification — ' . $e($q['location_id']) . ' ' . $badge . '</h3>';
            echo '<table class="table table-bordered" style="max-width:760px"><tbody>'
                . '<tr><th style="width:220px">Technology</th><td>' . $e($q['technology']) . ' (' . $e(strtoupper($q['service_type'])) . ')</td></tr>'
                . '<tr><th>Service Class</th><td>' . $e($q['service_class'] ?? '?') . '</td></tr>'
                . '<tr><th>Serviceability</th><td>' . $e($q['serviceability_status']) . '</td></tr>'
                . '<tr><th>POI</th><td>' . $e($q['poi_id']) . '</td></tr>'
                . '<tr><th>New Development Charge</th><td>' . ($q['new_development_charge'] ? '<span class="label label-warning">APPLIES</span>' : 'No') . '</td></tr>'
                . '<tr><th>Fibre Upgrade Available</th><td>' . ($q['fibre_upgrade_available'] ? '<span class="label label-info">YES</span>' : 'No') . '</td></tr>'
                . '</tbody></table>';

            echo '<h4>Available Speed Tiers</h4>';
            if ($q['speeds'] === []) {
                echo '<div class="alert alert-warning">No speed tiers orderable at this location.</div>';
            } else {
                echo '<p style="max-width:900px">';
                foreach ($q['speeds'] as $speed) {
                    $isL3 = str_starts_with($speed, 'L3');
                    echo '<span class="label label-' . ($isL3 ? 'default' : 'primary') . '" '
                        . 'style="display:inline-block;margin:2px;font-size:12px">' . $e($speed) . '</span> ';
                }
                echo '</p><p class="text-muted">Blue = Layer 2 (ours). Grey = Layer 3 variants.</p>';
            }

            if ($q['ntds'] !== []) {
                echo '<h4>NTDs &amp; UNI-D Ports</h4>';
                foreach ($q['ntds'] as $ntd) {
                    echo '<h5>NTD <code>' . $e($ntd['id']) . '</code></h5>'
                        . '<table class="table table-bordered" style="max-width:560px"><thead><tr>'
                        . '<th>Port</th><th>Status</th><th>Churn Match</th></tr></thead><tbody>';
                    foreach ($ntd['ports'] as $port) {
                        $statusLabel = $port['free']
                            ? '<span class="label label-success">Free</span>'
                            : '<span class="label label-danger">' . $e($port['status'] ?: 'Used') . '</span>';
                        $match = $port['service_id_match']
                            ? '<span class="label label-info">MATCHES CHURN AVC</span>' : '';
                        echo '<tr><td><code>' . $e($port['id']) . '</code></td><td>' . $statusLabel . '</td><td>' . $match . '</td></tr>';
                    }
                    echo '</tbody></table>';
                    if ($ntd['speed_tiers_supported'] !== []) {
                        echo '<p class="text-muted">NTD speed support: '
                            . $e(json_encode($ntd['speed_tiers_supported'])) . '</p>';
                    }
                }
            }

            if ($q['copper_pairs'] !== []) {
                echo '<h4>Copper Pairs</h4><table class="table table-bordered" style="max-width:640px"><thead><tr>'
                    . '<th>Pair</th><th>Status</th><th>Churn Match</th><th>POTS Match</th></tr></thead><tbody>';
                foreach ($q['copper_pairs'] as $pair) {
                    echo '<tr><td><code>' . $e($pair['id']) . '</code></td><td>' . $e($pair['status']) . '</td>'
                        . '<td>' . ($pair['service_id_match'] ? '<span class="label label-info">MATCH</span>' : '') . '</td>'
                        . '<td>' . ($pair['pots_match'] ? '<span class="label label-info">MATCH</span>' : '') . '</td></tr>';
                }
                echo '</tbody></table>';
            }

            foreach ($q['notes'] as $note) {
                echo '<div class="alert alert-info"><strong>' . $e($note['code'] ?? 'NOTE') . ':</strong> '
                    . $e($note['reason'] ?? '') . '</div>';
            }

            // What to put on the service.
            $auto = ProvisioningService::selectDevice($q, [], $churnAvc !== '');
            echo '<h4>Values for the service custom fields</h4>'
                . '<table class="table table-bordered" style="max-width:560px"><tbody>'
                . '<tr><th style="width:180px">Location ID</th><td><code>' . $e($q['location_id']) . '</code></td></tr>';
            if (isset($auto['ntdId'])) {
                echo '<tr><th>NTD ID</th><td><code>' . $e($auto['ntdId']) . '</code> (auto-selected)</td></tr>';
            }
            if (isset($auto['uniDPortId'])) {
                echo '<tr><th>UNI-D Port</th><td><code>' . $e($auto['uniDPortId']) . '</code> (auto-selected'
                    . ($churnAvc !== '' ? ' from churn match' : ' — first free') . ')</td></tr>';
            }
            if (isset($auto['copperPairId'])) {
                echo '<tr><th>Copper Pair ID</th><td><code>' . $e($auto['copperPairId']) . '</code> (auto-selected)</td></tr>';
            }
            if ($churnAvc !== '') {
                echo '<tr><th>Churn AVC</th><td><code>' . $e($churnAvc) . '</code></td></tr>'
                    . '<tr><th>Authority Date</th><td><code>' . $e($params['customerAuthorityDate']) . '</code></td></tr>';
            }
            echo '</tbody></table>'
                . '<p class="text-muted">These are what CreateAccount would use; set any of them explicitly on the service to override.</p>';

            echo '<details style="margin-top:14px"><summary>Raw SQ response (JSON)</summary><pre style="max-height:420px;overflow:auto">'
                . $e(json_encode($result['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                . '</pre></details>';
        }
    } catch (\Throwable $ex) {
        echo '<div class="alert alert-danger">Virtutel API error: ' . $e($ex->getMessage()) . '</div>';
    }
}
