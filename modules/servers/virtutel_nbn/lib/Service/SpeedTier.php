<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

/**
 * Translates Virtutel speed enumerations (TC4100D20U, TC4FWHF, L3...) into
 * customer-friendly labels. We sell Layer 2 only, so customer-facing lists
 * filter the L3 variants out.
 */
class SpeedTier
{
    private const FIXED_WIRELESS = [
        'TC4FWP' => ['label' => 'Fixed Wireless Plus', 'down' => 75, 'up' => 10],
        'TC4FWHF' => ['label' => 'Fixed Wireless Home Fast (200–250/8–20 Mbps)', 'down' => 250, 'up' => 20],
        'TC4FWSF' => ['label' => 'Fixed Wireless Superfast (400/10–40 Mbps)', 'down' => 400, 'up' => 40],
    ];

    /**
     * @return array{enum: string, label: string, down: int, up: int, layer3: bool}|null
     *         null when the enum isn't a recognised TC4 residential tier
     */
    public static function describe(string $enum): ?array
    {
        $layer3 = str_starts_with($enum, 'L3');
        $base = $layer3 ? substr($enum, 2) : $enum;

        if (isset(self::FIXED_WIRELESS[$base])) {
            $fw = self::FIXED_WIRELESS[$base];

            return [
                'enum' => $enum,
                'label' => $fw['label'],
                'down' => $fw['down'],
                'up' => $fw['up'],
                'layer3' => $layer3,
            ];
        }

        if (preg_match('/^TC4(\d+)D(\d+)U$/', $base, $m)) {
            return [
                'enum' => $enum,
                'label' => $m[1] . '/' . $m[2] . ' Mbps',
                'down' => (int) $m[1],
                'up' => (int) $m[2],
                'layer3' => $layer3,
            ];
        }

        return null;
    }

    /**
     * Customer-facing tier list: Layer 2 TC4 tiers only, deduplicated,
     * sorted by downstream speed.
     *
     * @param string[] $enums virtutelSpeedsAvailable
     * @return array[] [{enum, label, down, up}]
     */
    public static function customerTiers(array $enums): array
    {
        $tiers = [];
        foreach ($enums as $enum) {
            $tier = self::describe((string) $enum);
            if ($tier === null || $tier['layer3']) {
                continue;
            }
            unset($tier['layer3']);
            $tiers[$tier['enum']] = $tier;
        }

        $tiers = array_values($tiers);
        usort($tiers, fn ($a, $b) => [$a['down'], $a['up']] <=> [$b['down'], $b['up']]);

        return $tiers;
    }
}
