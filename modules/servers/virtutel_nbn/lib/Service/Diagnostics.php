<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory;
use WHMCS\Module\Server\VirtutelNbn\Repository\Settings;

/**
 * Diagnostic test runner shared by the admin service tab (Save fallback)
 * and the addon AJAX endpoint (Run Test button with live overlay): queues
 * tests via POST /service-tests, tracks them in the per-service history
 * (results land via the ServiceTest callback family), and renders entries.
 */
class Diagnostics
{
    /** @return array{ok: bool, id?: string, error?: string} */
    public static function queue(int $serviceId, string $testType): array
    {
        $testType = strtoupper(trim($testType));
        if (!preg_match('/^[A-Z0-9_]{3,48}$/', $testType)) {
            return ['ok' => false, 'error' => 'Invalid test type.'];
        }

        $row = Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $serviceId)
            ->first();
        if (!$row || (string) ($row->avc_id ?? '') === '') {
            return ['ok' => false, 'error' => 'Not linked to a Virtutel service with an AVC.'];
        }

        $client = ClientFactory::forWhmcsService($serviceId);
        $response = $client->request('POST', '/service-tests', [
            'action' => 'RequestServiceTest',
            'json' => [
                'serviceType' => self::serviceType((string) ($row->technology_type ?? '')),
                'avcId' => (string) $row->avc_id,
                'testType' => $testType,
            ],
        ]);

        $testId = strtoupper((string) $response->get('id', ''));
        if ($testId === '') {
            return ['ok' => false, 'error' => $response->vtErrorDesc()
                ?: ($response->vtShortError() ?: 'Test request rejected.')];
        }

        Settings::set('svctest_' . $testId, (string) $serviceId);

        $tests = self::history($serviceId);
        array_unshift($tests, [
            'id' => $testId,
            'type' => $testType,
            'status' => 'Requested',
            'at' => time(),
        ]);
        Settings::set('tests_' . $serviceId, (string) json_encode(array_slice($tests, 0, 10)));

        if (function_exists('logActivity')) {
            logActivity(sprintf(
                'Virtutel NBN: diagnostic test %s (%s) requested for service #%d (%s)',
                $testId,
                $testType,
                $serviceId,
                (string) $row->avc_id
            ));
        }

        return ['ok' => true, 'id' => $testId];
    }

    /** @return array{done: bool, html: string} current state of one test */
    public static function statusHtml(int $serviceId, string $testId): array
    {
        $testId = strtoupper(trim($testId));
        foreach (self::history($serviceId) as $test) {
            if (($test['id'] ?? '') === $testId) {
                $done = in_array((string) ($test['status'] ?? ''), [
                    'TestCompleted', 'TestCancelled', 'TestRejected',
                ], true);

                return ['done' => $done, 'html' => self::renderCustomer($test)];
            }
        }

        return ['done' => false, 'html' => ''];
    }

    /** @return array[] newest-first test history for a service */
    public static function history(int $serviceId): array
    {
        $tests = json_decode((string) (Settings::get('tests_' . $serviceId, '') ?? ''), true);

        return is_array($tests) ? $tests : [];
    }

    /**
     * Customer-safe tests per technology: a status check plus a "restart my
     * connection" action. Server-side allowlist — the client area may only
     * queue what this returns.
     *
     * @return array<string,string> testType => customer-facing label
     */
    public static function customerTests(string $subType): array
    {
        return match (strtoupper(trim($subType))) {
            'FTTP' => [
                'NTD_STATUS' => 'Check my connection box status',
                'PORT_RESET' => 'Restart my connection (brief dropout)',
            ],
            'HFC' => [
                'NTD_STATUS' => 'Check my connection box status',
                'NTD_RESET' => 'Restart my connection box (brief dropout)',
            ],
            'FTTC' => [
                'DPU_PORT_STATUS' => 'Check my connection status',
                'DPU_PORT_RESET' => 'Restart my connection (brief dropout)',
            ],
            'FTTN', 'FTTB' => [
                'LINE_STATE_DIAGNOSTIC' => 'Check my line status',
                'LINE_QUALITY_DIAGNOSTIC' => 'Run a line quality check',
            ],
            'FW', 'FIXED WIRELESS' => [
                'WNTD_STATUS' => 'Check my wireless connection',
                'WNTD_RESET' => 'Restart my connection box (brief dropout)',
            ],
            default => [],
        };
    }

    /**
     * Manufacturer for a MAC address from the bundled IEEE OUI registry
     * (a MAC encodes the maker in its first three octets — the model
     * isn't derivable). Friendly names for common registrants.
     */
    public static function macVendor(string $mac): ?string
    {
        static $table = null;

        $oui = strtoupper(preg_replace('/[^0-9A-Fa-f]/', '', $mac) ?? '');
        if (strlen($oui) < 6) {
            return null;
        }

        if ($table === null) {
            $file = __DIR__ . '/../../data/oui.php';
            $table = is_file($file) ? (array) (require $file) : [];
        }

        $vendor = $table[substr($oui, 0, 6)] ?? null;
        if ($vendor === null) {
            return null;
        }

        // Registry names customers actually recognise.
        $friendly = [
            'Routerboard.com' => 'MikroTik (RouterBOARD)',
        ];

        return $friendly[$vendor] ?? $vendor;
    }

    /**
     * Pulls the on-premises equipment out of a service health report,
     * grouped per device: the NBN connection box (NTD category — id,
     * serial, port, state, install location) and the customer's router
     * (Cpe category — MAC address).
     *
     * @return array<string,array<string,string>> group => [label => value]
     */
    public static function extractCpe(array $report): array
    {
        $groups = [];
        foreach ((array) ($report['healthCategory'] ?? []) as $category) {
            $type = (string) ($category['type'] ?? '');
            if (preg_match('/^cpe$/i', $type)) {
                $group = 'Your router';
            } elseif (preg_match('/ntd|ncd|dpu|btd/i', $type)) {
                $group = 'NBN connection box';
            } else {
                continue;
            }

            foreach ((array) ($category['healthCategoryItem'] ?? []) as $item) {
                $value = $item['value'] ?? null;
                if (!is_scalar($value) || (string) $value === '') {
                    continue;
                }
                $label = trim((string) ($item['id'] ?? ''));
                // camelCase -> words; drop redundant Main prefixes.
                $label = preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', ' ', $label) ?? $label;
                $label = trim(str_ireplace(['CPEMain', 'NTDMain', 'Main'], '', ucfirst($label)));
                $label = $label !== '' ? ucwords(strtolower($label)) : 'Detail';
                $label = match ($label) {
                    'Mac Address' => 'MAC Address',
                    'Ntd Id' => 'NTD ID',
                    'Port Id' => 'Port',
                    default => $label,
                };
                $groups[$group][$label] = (string) $value;
            }
        }

        // Derive the router's make from its MAC (model isn't in a MAC).
        if (isset($groups['Your router']['MAC Address'])) {
            $vendor = self::macVendor($groups['Your router']['MAC Address']);
            if ($vendor !== null) {
                $groups['Your router'] = ['Make' => $vendor] + $groups['Your router'];
            }
        }

        foreach ($groups as $group => $items) {
            $groups[$group] = array_slice($items, 0, 8, true);
        }

        return $groups;
    }

    /**
     * Last known on-prem equipment for a service: the cached extraction,
     * else mined live from the stored health report.
     *
     * @return array{groups: array<string,array<string,string>>, at: int}|null
     */
    public static function cpe(int $serviceId): ?array
    {
        $cached = json_decode((string) (Settings::get('cpe_' . $serviceId, '') ?? ''), true);
        if (is_array($cached) && !empty($cached['groups']) && (int) ($cached['v'] ?? 0) === 2) {
            return $cached;
        }

        $health = json_decode((string) (Settings::get('health_' . $serviceId, '') ?? ''), true);
        if (is_array($health) && !empty($health['report']) && is_array($health['report'])) {
            $groups = self::extractCpe($health['report']);
            if ($groups !== []) {
                $state = ['v' => 2, 'groups' => $groups, 'at' => (int) ($health['at'] ?? time())];
                Settings::set('cpe_' . $serviceId, (string) json_encode($state));

                return $state;
            }
        }

        return null;
    }

    public static function serviceType(string $subType): string
    {
        return match (strtoupper(trim($subType))) {
            'FTTP' => 'NFAS',
            'HFC' => 'NHAS',
            'FW', 'FIXED WIRELESS' => 'NWAS',
            default => 'NCAS', // FTTN / FTTB / FTTC copper family
        };
    }

    /**
     * Customer-facing result: an overall verdict banner plus the parsed
     * indicators as tidy rows — network internals (VLANs, revision
     * timestamps) filtered out, and never the raw JSON payload.
     */
    public static function renderCustomer(array $test): string
    {
        $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES);
        $status = (string) ($test['status'] ?? '');
        $typeLabel = ucwords(strtolower(str_replace('_', ' ', (string) ($test['type'] ?? 'Check'))));
        $typeLabel = str_replace(['Ntd', 'Wntd', 'Dpu', 'Ncd', 'Uni D', 'Uni V', 'Nni'],
            ['NTD', 'WNTD', 'DPU', 'NCD', 'UNI-D', 'UNI-V', 'NNI'], $typeLabel);

        // Overall verdict from the first result entry that reports one.
        $result = '';
        foreach ((array) ($test['results'] ?? []) as $entry) {
            if (is_array($entry) && (string) ($entry['result'] ?? '') !== '') {
                $result = (string) $entry['result'];
                break;
            }
        }

        if ($status === 'TestCompleted' && strcasecmp($result, 'Passed') === 0) {
            $bg = '#12301f'; $line = '#1d5c38'; $ink = '#7fdcaa'; $icon = '&#10003;';
            $head = 'All good &mdash; this check passed';
            $sub = 'The NBN network reports your equipment is healthy.';
        } elseif (in_array($status, ['TestCancelled', 'TestRejected'], true)
            || ($result !== '' && strcasecmp($result, 'Passed') !== 0)) {
            $bg = '#3a1512'; $line = '#722a24'; $ink = '#ffa198'; $icon = '&#33;';
            $head = $status === 'TestCompleted'
                ? 'This check found a problem'
                : 'The check couldn&rsquo;t run';
            $sub = 'Give us a call on 03 4130 5013 and we&rsquo;ll take it from here.';
        } else {
            $bg = '#372a10'; $line = '#6b531f'; $ink = '#ecc575'; $icon = '&#8987;';
            $head = 'Still running&hellip;';
            $sub = 'Checks usually finish within a couple of minutes.';
        }

        $html = '<div style="text-align:left">'
            . '<div style="background:' . $bg . ';border:1px solid ' . $line . ';color:' . $ink . ';'
            . 'border-radius:10px;padding:13px 16px;display:flex;gap:11px;align-items:flex-start">'
            . '<span style="font-weight:900;font-size:16px;line-height:1.3">' . $icon . '</span>'
            . '<span><strong style="font-size:15px">' . $head . '</strong>'
            . '<span style="display:block;font-size:12.5px;margin-top:2px">' . $sub . '</span></span>'
            . '</div>';

        $indicators = [];
        self::collectIndicators((array) ($test['results'] ?? []), $indicators);
        $labels = [
            'Operational State' => 'Connection box',
            'Reporting State' => 'Line reporting',
            'Serial Number' => 'Serial number',
            'NTD ID' => 'NBN box ID',
            'Battery Fail Status' => 'Backup battery',
            'Battery Missing Status' => 'Battery installed',
        ];
        $rows = '';
        $shown = 0;
        foreach ($indicators as $ind) {
            if (preg_match('/vlan|enni|revision|timestamp/i', $ind['id']) || $ind['value'] === '') {
                continue; // network internals mean nothing to customers
            }
            $value = $ind['value'];
            $good = (bool) preg_match('/^(up|ok|pass(ed)?|no defect|in sync|normal|good|connected)$/i', trim($value));
            $bad = !$good && (bool) preg_match('/fail|down|defect|error|missing/i', $value)
                && strcasecmp(trim($value), 'N/A') !== 0;
            $muted = strcasecmp(trim($value), 'N/A') === 0;
            $vColor = $good ? '#7fdcaa' : ($bad ? '#ff9088' : ($muted ? '#5b6b8f' : '#e6e9f2'));
            $rows .= '<div style="display:flex;justify-content:space-between;gap:14px;'
                . 'padding:9px 2px;border-bottom:1px solid #2a3347;font-size:13px">'
                . '<span style="color:#98a2b8">' . $e($labels[$ind['id']] ?? $ind['id']) . '</span>'
                . '<strong style="color:' . $vColor . ';text-align:right">' . $e($value) . '</strong>'
                . '</div>';
            if (++$shown >= 12) {
                break;
            }
        }
        if ($rows !== '') {
            $html .= '<div style="margin-top:12px">' . $rows . '</div>';
        }

        $html .= '<div style="margin-top:10px;color:#5b6b8f;font-size:11px">'
            . $e($typeLabel)
            . (isset($test['at']) ? ' &middot; ' . date('j M Y, g:ia', (int) $test['at']) : '')
            . '</div></div>';

        return $html;
    }

    /** Depth-first sweep for {"@type":"Indicator","id","value"} nodes. */
    private static function collectIndicators($node, array &$out): void
    {
        if (!is_array($node)) {
            return;
        }
        if ((string) ($node['@type'] ?? '') === 'Indicator' && isset($node['id'])) {
            $out[] = ['id' => (string) $node['id'], 'value' => (string) ($node['value'] ?? '')];

            return;
        }
        foreach ($node as $child) {
            self::collectIndicators($child, $out);
        }
    }

    /** Renders one history entry (status banner + result summary). */
    public static function renderOne(array $test): string
    {
        $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES);
        $status = (string) ($test['status'] ?? '?');
        $col = match ($status) {
            'TestCompleted' => '#27ae60',
            'TestCancelled', 'TestRejected' => '#c0392b',
            default => '#e67e22',
        };

        $html = '<div style="border-left:4px solid ' . $col . ';background:#f6f8fb;'
            . 'padding:6px 12px;margin-bottom:8px;border-radius:0 4px 4px 0;font-size:12.5px;'
            . 'text-align:left;color:#222">'
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
            $html .= '<details style="margin-top:4px"><summary style="cursor:pointer;'
                . 'font-size:11.5px;color:#667">Full result</summary>'
                . '<pre style="max-height:200px;overflow:auto;font-size:11px;background:#fff;'
                . 'border:1px solid #dde3ee;padding:6px;border-radius:4px;text-align:left">'
                . $e(substr($pretty, 0, 8000)) . '</pre></details>';
        }

        return $html . '</div>';
    }
}
