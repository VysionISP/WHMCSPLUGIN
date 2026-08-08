<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

/**
 * Builds Connect Order request bodies (docs/api/07_productorders.md).
 *
 * Pure and unit-tested: technology-specific keys (nfas/ncas/nhas/nwas),
 * device/port selection, churn block, string sanitisation.
 */
class OrderPayloadBuilder
{
    /**
     * Virtutel/NBN appointment systems only accept a limited character set;
     * apply it to all free-text strings we send.
     */
    public static function sanitise(string $value): string
    {
        $spaced = preg_replace('/\s+/', ' ', $value) ?? '';
        $clean = preg_replace('/[^A-Za-z0-9 ,.\/\-@+_()]/', '', $spaced) ?? '';

        return trim(preg_replace('/ {2,}/', ' ', $clean) ?? '');
    }

    /**
     * @param array $opts {
     *   locationId: string (required)
     *   serviceType: nfas|ncas|nhas|nwas (required)
     *   speed: string (required)
     *   customerName: string, orderRef: string, business?: bool
     *   contact: {firstName, lastName, phoneNumber, emailAddress}
     *   ntdId?: string, uniDPortId?: string   (nfas / nwas)
     *   copperPairId?: string                  (ncas)
     *   fibreUpgrade?: bool                    (nfas upgrades)
     *   churnAvc?: string, authorityDate?: string (YYYY-MM-DD)
     *   notes?: string
     * }
     */
    public static function build(array $opts): array
    {
        foreach (['locationId', 'serviceType', 'speed'] as $required) {
            if (empty($opts[$required])) {
                throw new \InvalidArgumentException("Order build missing required '{$required}'");
            }
        }

        $serviceType = strtolower((string) $opts['serviceType']);
        if (!in_array($serviceType, ['nfas', 'ncas', 'nhas', 'nwas'], true)) {
            throw new \InvalidArgumentException("Unsupported service type '{$serviceType}' for connect orders");
        }

        $contact = (array) ($opts['contact'] ?? []);

        $nbn = [
            $serviceType => self::technologyBlock($serviceType, $opts),
            'speed' => (string) $opts['speed'],
        ];

        if (!empty($opts['churnAvc'])) {
            $nbn['churn'] = [
                'type' => 'Service Transfer',
                'customerAuthorityDate' => (string) ($opts['authorityDate'] ?? date('Y-m-d')),
                'serviceIDToTransfer' => strtoupper(trim((string) $opts['churnAvc'])),
            ];
        }

        return [
            'locationId' => (string) $opts['locationId'],
            'custData' => [
                'business' => (bool) ($opts['business'] ?? false),
                'customerName' => self::sanitise((string) ($opts['customerName'] ?? '')),
                'orderRef' => self::sanitise((string) ($opts['orderRef'] ?? '')),
            ],
            'contactData' => [
                'firstName' => self::sanitise((string) ($contact['firstName'] ?? '')),
                'lastName' => self::sanitise((string) ($contact['lastName'] ?? '')),
                'phoneNumber' => preg_replace('/[^0-9+]/', '', (string) ($contact['phoneNumber'] ?? '')),
                'emailAddress' => trim((string) ($contact['emailAddress'] ?? '')),
            ],
            'service' => ['nbn' => $nbn],
            'notes' => self::sanitise((string) ($opts['notes'] ?? '')),
        ];
    }

    /**
     * Technology-specific keys. An empty block is returned as \stdClass so
     * it JSON-encodes as {} rather than [] (e.g. plain nhas orders).
     *
     * @return array<string,mixed>|\stdClass
     */
    private static function technologyBlock(string $serviceType, array $opts): array|\stdClass
    {
        $block = [];

        if (in_array($serviceType, ['nfas', 'nwas'], true)) {
            if (!empty($opts['ntdId'])) {
                $block['ntdId'] = (string) $opts['ntdId'];
            }
            if (!empty($opts['uniDPortId'])) {
                $block['uniDPortId'] = (string) $opts['uniDPortId'];
            }
            if ($serviceType === 'nfas' && !empty($opts['fibreUpgrade'])) {
                $block['fibreUpgrade'] = true;
            }
        }

        if ($serviceType === 'ncas' && !empty($opts['copperPairId'])) {
            $block['copperPairId'] = (string) $opts['copperPairId'];
        }

        // nhas (HFC) carries no device keys at order time; device details
        // (e.g. MAC) arrive later via DeviceDetailsRequired.
        return $block === [] ? new \stdClass() : $block;
    }
}
