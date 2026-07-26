<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

/**
 * Customer-facing "how soon can this connect" assessment, derived from the
 * parsed SQ (service class semantics per docs/api + NBN service class
 * table). Codes:
 *  - connect_now:      infrastructure in place, no technician visit expected
 *  - appointment:      an installation appointment will be required
 *  - device_shipped:   NBN will ship equipment (self-install)
 *  - nbn_work:         NBN-side work (e.g. jumpering/cut-in) may be needed
 *  - not_available:    not orderable at this address
 */
class ConnectReadiness
{
    /** @return array{code: string, label: string, description: string} */
    public static function assess(array $q): array
    {
        if (!$q['orderable']) {
            return [
                'code' => 'not_available',
                'label' => 'Not available yet',
                'description' => 'NBN cannot be ordered at this address right now. Leave your details and we\'ll let you know when that changes.',
            ];
        }

        $class = (int) ($q['service_class'] ?? -1);
        $type = (string) $q['service_type'];

        $freePorts = 0;
        foreach ($q['ntds'] as $ntd) {
            foreach ($ntd['ports'] as $port) {
                $freePorts += $port['free'] ? 1 : 0;
            }
        }

        // NBN equipment present but every port occupied: an active service
        // already exists here, so the customer is almost certainly switching
        // providers — a remote transfer, not a technician visit. Never lead
        // with install messaging in this state.
        if ($q['ntds'] !== [] && $freePorts === 0) {
            return [
                'code' => 'existing_service',
                'label' => 'Active service at this address',
                'description' => 'The NBN equipment here already has a service connected. Switching from another '
                    . 'provider? Your connection transfers to us remotely — no technician visit needed. '
                    . 'Enter your AVC ID below to get started. (Need an extra, separate connection instead? '
                    . 'Contact us and we\'ll arrange it.)',
            ];
        }

        // Copper (FTTN/FTTB/FTTC) equivalent: every pair already carries an
        // active NBN service (NBNServiceStatus "Line In Use") — same story,
        // this is a provider switch, not a new install.
        $pairs = (array) ($q['copper_pairs'] ?? []);
        if ($q['ntds'] === [] && $pairs !== []) {
            $freePairs = 0;
            foreach ($pairs as $pair) {
                if (!preg_match('/in ?use/i', (string) ($pair['status'] ?? ''))) {
                    $freePairs++;
                }
            }
            if ($freePairs === 0) {
                return [
                    'code' => 'existing_service',
                    'label' => 'Active service at this address',
                    'description' => 'The line at this address already has an active NBN service. Switching from '
                        . 'another provider? Your connection transfers to us remotely — no technician visit '
                        . 'needed. Enter your AVC ID below to get started. (Need an extra, separate line '
                        . 'instead? Contact us and we\'ll arrange it.)',
                ];
            }
        }

        return match (true) {
            // FTTP: NTD on the wall with a free port = plug and play.
            $type === 'nfas' && $class === 3 && $freePorts > 0 => self::connectNow(),
            $type === 'nfas' => self::appointment('A technician will install the NBN fibre box at your premises.'),

            // Fixed Wireless.
            $type === 'nwas' && $class === 6 => self::connectNow(),
            $type === 'nwas' => self::appointment('A technician will install the NBN antenna and connection box.'),

            // HFC.
            $type === 'nhas' && $class === 24 => self::connectNow(),
            $type === 'nhas' && $class === 23 => [
                'code' => 'device_shipped',
                'label' => 'Self-install kit',
                'description' => 'NBN will ship a connection device to your address — plug it in and you\'re away. No technician visit expected.',
            ],
            $type === 'nhas' => self::appointment('A technician visit is needed to complete the NBN cable connection at your premises.'),

            // Copper (FTTN/FTTB/FTTC): 13/34 ready; the rest may need NBN work.
            $type === 'ncas' && in_array($class, [13, 34], true) => self::connectNow(),
            $type === 'ncas' => [
                'code' => 'nbn_work',
                'label' => 'Connection work may be required',
                'description' => 'Your address is serviceable, but NBN may need to complete line work first. We\'ll confirm once your order is lodged — an appointment may be required.',
            ],

            default => self::appointment('An installation appointment may be required — we\'ll confirm as soon as your order is lodged.'),
        };
    }

    private static function connectNow(): array
    {
        return [
            'code' => 'connect_now',
            'label' => 'Ready to connect',
            'description' => 'The NBN equipment is already in place — most connections like this are activated remotely, often within hours.',
        ];
    }

    private static function appointment(string $why): array
    {
        return [
            'code' => 'appointment',
            'label' => 'Installation appointment required',
            'description' => $why . ' You\'ll be able to choose a time that suits you after ordering.',
        ];
    }
}
