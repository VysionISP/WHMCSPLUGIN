<?php

/**
 * Order hand-off: the qualify page's "Order now" links land here first.
 *
 * Stores the validated qualification data (LOC ID, transfer AVC, port
 * choice, display labels) in the WHMCS session ITSELF — no reliance on cart
 * hook timing — then forwards to the normal cart add-to-cart URL. The
 * AfterShoppingCartCheckout hook later writes the stored values onto the
 * ordered service's admin-only custom fields.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Service\SignupCapture;

require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../lib/Autoloader.php';

$pid = (int) ($_GET['pid'] ?? 0);

$signup = SignupCapture::fromRequest($_GET);
if ($signup !== []) {
    $_SESSION['virtutel_nbn_signup'] = $signup;
    if (function_exists('logActivity')) {
        logActivity(sprintf(
            'Virtutel NBN: qualification captured for cart (pid %d, %s%s)',
            $pid,
            $signup['Location ID'],
            isset($signup['Churn AVC']) ? ', transfer ' . $signup['Churn AVC'] : ''
        ));
    }
}

// AJAX mode (inline cart qualifier): report the capture result as JSON
// instead of redirecting.
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => $signup !== []]);
    exit;
}

// Signup wizard "yes, I need a modem": drop the chosen router product
// straight into the session cart alongside the plan being added. The pid
// is only honoured when it's in the admin-configured router list.
if (($_GET['vt_router'] ?? '') === '1' || (int) ($_GET['vt_router_pid'] ?? 0) > 0) {
    $parseIds = static fn (string $csv): array => array_values(array_filter(array_map(
        'intval',
        preg_split('/[\s,]+/', $csv, -1, PREG_SPLIT_NO_EMPTY) ?: []
    ), static fn ($id) => $id > 0));
    $configured = [];
    foreach (['router_pids', 'router_pid'] as $settingName) {
        $configured = $parseIds((string) (Capsule::table('tbladdonmodules')
            ->where('module', 'virtutel_nbn_admin')
            ->where('setting', $settingName)->value('value') ?? ''));
        if ($configured !== []) {
            break;
        }
    }
    $requested = (int) ($_GET['vt_router_pid'] ?? 0);
    $routerPid = in_array($requested, $configured, true)
        ? $requested
        : ($requested === 0 ? (int) ($configured[0] ?? 0) : 0);
    if ($routerPid > 0) {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (!isset($_SESSION['cart']['products']) || !is_array($_SESSION['cart']['products'])) {
            $_SESSION['cart']['products'] = [];
        }
        $inCart = false;
        foreach ($_SESSION['cart']['products'] as $cartProduct) {
            if ((int) ($cartProduct['pid'] ?? 0) === $routerPid) {
                $inCart = true;
                break;
            }
        }
        if (!$inCart) {
            $_SESSION['cart']['products'][] = [
                'pid' => $routerPid,
                'domain' => '',
                'billingcycle' => '',
                'configoptions' => [],
                'customfields' => [],
                'addons' => [],
            ];
        }
    }
}

$systemUrl = rtrim((string) (Capsule::table('tblconfiguration')
    ->where('setting', 'SystemURL')->value('value') ?? ''), '/');

$target = $systemUrl . '/cart.php' . ($pid > 0 ? '?a=add&pid=' . $pid : '');

header('Location: ' . $target, true, 302);
exit;
