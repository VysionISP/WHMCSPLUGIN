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

        return match (true) {
            // FTTP: NTD on the wall with a free port = plug and play.
            $type === 'nfas' && $class === 3 && $freePorts > 0 => self::connectNow(),
            $type === 'nfas' && $class === 3 => self::appointment('All ports on the existing NBN box are in use — a technician visit is needed to add capacity.'),
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
