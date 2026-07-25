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

$systemUrl = rtrim((string) (Capsule::table('tblconfiguration')
    ->where('setting', 'SystemURL')->value('value') ?? ''), '/');

$target = $systemUrl . '/cart.php' . ($pid > 0 ? '?a=add&pid=' . $pid : '');

header('Location: ' . $target, true, 302);
exit;
