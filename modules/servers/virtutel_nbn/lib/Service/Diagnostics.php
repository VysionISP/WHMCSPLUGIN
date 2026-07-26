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

                return ['done' => $done, 'html' => self::renderOne($test)];
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
