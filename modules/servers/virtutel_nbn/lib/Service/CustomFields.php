<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;

/**
 * Manages the module's service custom fields (Location ID, Churn AVC, ...):
 * ensures they exist on a product (admin-only, so the order form stays
 * clean) and writes values captured during checkout onto a service.
 */
class CustomFields
{
    public const FIELDS = [
        'Location ID',
        'Churn AVC',
        'Authority Date',
        'NTD ID',
        'UNI-D Port',
        'Copper Pair ID',
    ];

    /** @return array<string,int> fieldName => tblcustomfields.id */
    public static function ensureProductFields(int $productId): array
    {
        $existing = Capsule::table('tblcustomfields')
            ->where('type', 'product')
            ->where('relid', $productId)
            ->pluck('id', 'fieldname');

        $map = [];
        foreach (self::FIELDS as $name) {
            // WHMCS field names may carry a |Display Name suffix.
            foreach ($existing as $fieldName => $id) {
                if (strcasecmp(explode('|', (string) $fieldName)[0], $name) === 0) {
                    $map[$name] = (int) $id;
                    continue 2;
                }
            }

            $map[$name] = (int) Capsule::table('tblcustomfields')->insertGetId([
                'type' => 'product',
                'relid' => $productId,
                'fieldname' => $name,
                'fieldtype' => 'text',
                'adminonly' => 'on',
                'required' => '',
                'showorder' => '',
                'showinvoice' => '',
            ]);
        }

        return $map;
    }

    /** @param array<string,string> $values fieldName => value */
    public static function writeServiceValues(int $whmcsServiceId, array $values): void
    {
        $productId = (int) (Capsule::table('tblhosting')
            ->where('id', $whmcsServiceId)->value('packageid') ?? 0);
        if ($productId === 0) {
            return;
        }

        $map = self::ensureProductFields($productId);
        foreach ($values as $name => $value) {
            if (!isset($map[$name]) || $value === '') {
                continue;
            }
            Capsule::table('tblcustomfieldsvalues')->updateOrInsert(
                ['fieldid' => $map[$name], 'relid' => $whmcsServiceId],
                ['value' => $value]
            );
        }
    }
}
