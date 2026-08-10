<?php
/**
 * Create the Korvix NBN product lineup in WHMCS, idempotently.
 *
 * Usage (on the WHMCS server):
 *   sudo cp make-plans.php /var/www/whmcs/
 *   cd /var/www/whmcs && sudo -u www-data php8.2 -q make-plans.php
 *   sudo rm /var/www/whmcs/make-plans.php   # one-shot tool, remove after
 *
 * Safe to re-run: plans that already exist (matched on module + speed
 * enum) are skipped. Prices are starting values — adjust in the UI.
 */

use WHMCS\Database\Capsule;

foreach ([__DIR__ . '/init.php', '/var/www/whmcs/init.php'] as $init) {
    if (is_file($init)) {
        require $init;
        break;
    }
}
if (!class_exists(Capsule::class)) {
    fwrite(STDERR, "Could not bootstrap WHMCS (init.php not found).\n");
    exit(1);
}

$plans = [
    // [name, speed enum, monthly price]
    ['NBN 25/10',                  'TC425D10U',   69.00],
    ['NBN 50/20',                  'TC450D20U',   95.00],
    ['NBN 500/50 HomeFast',        'TC4500D50U',  99.00],
    ['NBN 750/50 SuperFast',       'TC4750D50U', 115.00],
    ['NBN 1000/100 UltraFast',     'TC41000D100U', 135.00],
    // "FW" in the name is REQUIRED — the signup portal uses it to show
    // these only on Fixed Wireless addresses.
    ['NBN FW Plus',                'TC4FWP',      95.00],
    ['NBN FW HomeFast',            'TC4FWHF',     99.00],
    ['NBN FW SuperFast',           'TC4FWSF',    115.00],
];

// --- product group -----------------------------------------------------
$gid = Capsule::table('tblproductgroups')->where('name', 'NBN Plans')->value('id');
if (!$gid) {
    $groupRow = [
        'name' => 'NBN Plans',
        'headline' => 'NBN Internet',
        'tagline' => 'Fast, local NBN — no lock-ins',
        'orderfrmtpl' => '',
        'disabledordertypes' => '',
        'hidden' => 0,
        'order' => 1,
    ];
    try {
        $gid = Capsule::table('tblproductgroups')->insertGetId($groupRow);
    } catch (\Throwable $e) {
        // Some schema versions require a slug column.
        $groupRow['slug'] = 'nbn-plans';
        $gid = Capsule::table('tblproductgroups')->insertGetId($groupRow);
    }
    echo "Created product group 'NBN Plans' (gid {$gid})\n";
} else {
    echo "Product group 'NBN Plans' exists (gid {$gid})\n";
}

// --- server group ------------------------------------------------------
$serverGroupId = (int) (Capsule::table('tblservergroups')->orderBy('id')->value('id') ?? 0);
if ($serverGroupId === 0) {
    fwrite(STDERR, "No server group found — create one under System Settings > Servers first.\n");
    exit(1);
}

$currencyId = (int) (Capsule::table('tblcurrencies')->orderByDesc('default')->orderBy('id')->value('id') ?? 1);

// --- products ----------------------------------------------------------
foreach ($plans as [$name, $enum, $price]) {
    $exists = Capsule::table('tblproducts')
        ->where('servertype', 'virtutel_nbn')
        ->where('configoption1', $enum)
        ->value('id');
    if ($exists) {
        echo "Skip  {$name} ({$enum}) — already product #{$exists}\n";
        continue;
    }

    $result = localAPI('AddProduct', [
        'name' => $name,
        'gid' => $gid,
        'type' => 'other',
        'paytype' => 'recurring',
        'description' => '',
        'hidden' => false,
        'welcomeemail' => 0,
    ]);
    if (($result['result'] ?? '') !== 'success' || empty($result['pid'])) {
        fwrite(STDERR, "FAILED {$name}: " . ($result['message'] ?? 'unknown error') . "\n");
        continue;
    }
    $pid = (int) $result['pid'];

    // Module wiring + auto-setup on payment (set directly — bulletproof
    // across API parameter differences between WHMCS versions).
    Capsule::table('tblproducts')->where('id', $pid)->update([
        'servertype' => 'virtutel_nbn',
        'servergroup' => $serverGroupId,
        'configoption1' => $enum,
        'autosetup' => 'payment',
    ]);

    // Monthly-only recurring pricing; other cycles disabled (-1).
    Capsule::table('tblpricing')->updateOrInsert(
        ['type' => 'product', 'currency' => $currencyId, 'relid' => $pid],
        [
            'msetupfee' => 0, 'qsetupfee' => 0, 'ssetupfee' => 0,
            'asetupfee' => 0, 'bsetupfee' => 0, 'tsetupfee' => 0,
            'monthly' => $price, 'quarterly' => -1, 'semiannually' => -1,
            'annually' => -1, 'biennially' => -1, 'triennially' => -1,
        ]
    );

    echo sprintf("Added product #%d  %-26s %-13s \$%.2f/mo\n", $pid, $name, $enum, $price);
}

echo "Done. Review prices/descriptions under System Settings > Products/Services.\n";
