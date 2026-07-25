<?php

/**
 * Virtutel NBN module hooks.
 *
 * DailyCronJob proactively refreshes API access tokens (30-day lifetime,
 * refreshed at 80% elapsed) for every enabled virtutel_nbn server so
 * customer-facing requests never pay the generation cost or race expiry.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory;
use WHMCS\Module\Server\VirtutelNbn\Api\VirtutelClient;
use WHMCS\Module\Server\VirtutelNbn\Migrations;
use WHMCS\Module\Server\VirtutelNbn\Service\CallbackRegistrar;
use WHMCS\Module\Server\VirtutelNbn\Service\CustomFields;
use WHMCS\Module\Server\VirtutelNbn\Service\OrderCompletion;
use WHMCS\Module\Server\VirtutelNbn\Service\StatusMapper;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Guard against double registration (module autoload + includes/hooks loader).
if (defined('VIRTUTEL_NBN_HOOKS_LOADED')) {
    return;
}
define('VIRTUTEL_NBN_HOOKS_LOADED', true);

require_once __DIR__ . '/lib/Autoloader.php';

add_hook('DailyCronJob', 1, function () {
    try {
        Migrations::ensure();

        $servers = Capsule::table('tblservers')
            ->where('type', 'virtutel_nbn')
            ->where('disabled', 0)
            ->get();

        foreach ($servers as $server) {
            try {
                $password = localAPI('DecryptPassword', ['password2' => $server->password]);

                $client = VirtutelClient::fromModuleParams([
                    'serverhostname' => $server->hostname,
                    'serverip' => $server->ipaddress,
                    'serverport' => $server->port,
                    'serverusername' => $server->username,
                    'serverpassword' => (string) ($password['password'] ?? ''),
                ]);

                if ($client->tokenNeedsRefresh()) {
                    $client->ensureAccessToken();
                    logActivity(sprintf(
                        'Virtutel NBN: refreshed %s API access token for server "%s"',
                        $client->getEnvironment(),
                        $server->name
                    ));
                }

                // Keep the callback URL registration in place (idempotent).
                // The Access Hash field on the server record carries the
                // callback base URL (e.g. https://backend.korvix.co).
                try {
                    (new CallbackRegistrar($client, (string) $server->accesshash))->ensureRegistered();
                } catch (\Throwable $e) {
                    logActivity(sprintf(
                        'Virtutel NBN: callback registration check failed for server "%s": %s',
                        $server->name,
                        $e->getMessage()
                    ));
                }
            } catch (\Throwable $e) {
                logActivity(sprintf(
                    'Virtutel NBN: token refresh FAILED for server "%s": %s',
                    $server->name,
                    $e->getMessage()
                ));
            }
        }
    } catch (\Throwable $e) {
        logActivity('Virtutel NBN: daily token maintenance failed: ' . $e->getMessage());
    }
});

/**
 * Poll backstop for missed callbacks: Virtutel permanently drops a callback
 * after several failed deliveries, so in-flight orders are re-checked
 * against GET /product-orders/{id} on every WHMCS cron tick (throttled to
 * one sweep per hour).
 */
add_hook('AfterCronJob', 1, function () {
    try {
        Migrations::ensure();

        $lastRun = (int) (WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get('order_poll_last_run', '0'));
        if (time() - $lastRun < 3600) {
            return;
        }
        WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set('order_poll_last_run', (string) time());

        $inFlight = Capsule::table('mod_virtutel_orders')
            ->whereIn('whmcs_status', ['pending', 'in_progress', 'action_required'])
            ->whereNotNull('vt_order_id')
            ->orderBy('id')
            ->limit(100)
            ->get();

        foreach ($inFlight as $order) {
            try {
                $service = Capsule::table('mod_virtutel_services')->where('id', $order->service_id)->first();
                if (!$service) {
                    continue;
                }
                $client = ClientFactory::forWhmcsService((int) $service->whmcs_service_id);
                $details = $client->request(
                    'GET',
                    VirtutelClient::PATH_PRODUCT_ORDERS . '/' . rawurlencode((string) $order->vt_order_id),
                    ['action' => 'PollOrder']
                );

                $rawStatus = (string) $details->get('status', '');
                if ($rawStatus === '' || $rawStatus === $order->status) {
                    continue;
                }

                Capsule::table('mod_virtutel_orders')->where('id', $order->id)->update([
                    'status' => substr($rawStatus, 0, 64),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $completion = new OrderCompletion();
                if ($rawStatus === 'VT_ORDER_COMPLETED') {
                    Capsule::table('mod_virtutel_orders')->where('id', $order->id)->update([
                        'whmcs_status' => StatusMapper::COMPLETE,
                        'completed_at' => date('Y-m-d H:i:s'),
                    ]);
                    $completion->complete($order);
                    logActivity(sprintf(
                        'Virtutel NBN: poll backstop detected completed order %s (callback missed?)',
                        $order->vt_order_id
                    ));
                } elseif ($rawStatus === 'VT_ORDER_CANCELLED') {
                    Capsule::table('mod_virtutel_orders')->where('id', $order->id)->update([
                        'whmcs_status' => StatusMapper::CANCELLED,
                        'completed_at' => date('Y-m-d H:i:s'),
                    ]);
                    $completion->cancelled($order, 'detected via poll backstop');
                }
            } catch (\Throwable $e) {
                logActivity(sprintf(
                    'Virtutel NBN: order poll failed for %s: %s',
                    $order->vt_order_id,
                    $e->getMessage()
                ));
            }
        }
    } catch (\Throwable $e) {
        logActivity('Virtutel NBN: order poll sweep failed: ' . $e->getMessage());
    }
});

/**
 * Checkout hand-off from the qualification page: the "Order now" links carry
 * vt_locid (and vt_avc for validated transfers) into the cart. Capture them
 * in the session on any cart page load...
 */
add_hook('ClientAreaPageCart', 1, function () {
    // Fallback only — the primary capture is pages/order.php, which stores
    // the session BEFORE redirecting into the cart.
    $signup = WHMCS\Module\Server\VirtutelNbn\Service\SignupCapture::fromRequest($_GET);
    if ($signup !== []) {
        $_SESSION['virtutel_nbn_signup'] = $signup;
    }
});

/**
 * ...then write them onto the ordered service's (admin-only) custom fields
 * at checkout, where CreateAccount picks them up to lodge the NBN order.
 */
add_hook('AfterShoppingCartCheckout', 1, function ($vars) {
    $signup = $_SESSION['virtutel_nbn_signup'] ?? null;
    if (!is_array($signup) || $signup === []) {
        return;
    }

    try {
        $serviceIds = array_filter(array_map('intval', (array) ($vars['ServiceIDs'] ?? [])));
        foreach ($serviceIds as $serviceId) {
            $productId = (int) (Capsule::table('tblhosting')
                ->where('id', $serviceId)->value('packageid') ?? 0);
            $isOurs = $productId > 0 && Capsule::table('tblproducts')
                ->where('id', $productId)
                ->where('servertype', 'virtutel_nbn')
                ->exists();
            if (!$isOurs) {
                continue;
            }

            CustomFields::writeServiceValues($serviceId, $signup);
            logActivity(sprintf(
                'Virtutel NBN: checkout captured qualification for service #%d (%s%s)',
                $serviceId,
                $signup['Location ID'],
                isset($signup['Churn AVC']) ? ', transfer ' . $signup['Churn AVC'] : ''
            ));
        }
    } catch (\Throwable $e) {
        logActivity('Virtutel NBN: checkout hand-off failed: ' . $e->getMessage());
    } finally {
        unset($_SESSION['virtutel_nbn_signup']);
    }
});

/**
 * Reassure the customer on cart pages that their qualification carried
 * over: a banner naming the address (and transfer/port choice) attached to
 * the order.
 */
add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    if (($vars['filename'] ?? '') !== 'cart') {
        return '';
    }
    $signup = $_SESSION['virtutel_nbn_signup'] ?? null;
    if (!is_array($signup) || empty($signup['Location ID'])) {
        return '';
    }

    $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES);

    $rows = [];
    $rows[] = ['Address', ($signup['Address'] ?? '') !== ''
        ? $signup['Address']
        : 'NBN location ' . $signup['Location ID']];
    if (!empty($signup['Technology'])) {
        $rows[] = ['Connection type', $signup['Technology']];
    }
    if (!empty($signup['Churn AVC'])) {
        $rows[] = ['Connection method', 'Transfer of your existing service (' . $signup['Churn AVC']
            . ') — done remotely, no technician visit'];
    } elseif (!empty($signup['UNI-D Port'])) {
        $rows[] = ['NBN box port', $signup['UNI-D Port'] . ' (your selection)'];
    } else {
        $rows[] = ['NBN box port', 'Auto-selected for you'];
    }
    $rows[] = ['NBN Location ID', $signup['Location ID']];

    $rowsHtml = '';
    foreach ($rows as [$label, $value]) {
        $rowsHtml .= '<tr>'
            . '<td style="padding:6px 14px 6px 0;color:#667;white-space:nowrap;vertical-align:top">' . $e($label) . '</td>'
            . '<td style="padding:6px 0;font-weight:600">' . $e($value) . '</td>'
            . '</tr>';
    }

    $card = '<div style="background:#f4f8ff;border:1px solid #c9d8f6;border-radius:10px;'
        . 'padding:16px 20px;margin:12px auto 18px;max-width:1100px;font-size:14px">'
        . '<div style="font-weight:700;font-size:15px;margin-bottom:6px;color:#1a5fd0">'
        . '&#10003; Your NBN connection</div>'
        . '<table style="border-collapse:collapse">' . $rowsHtml . '</table>'
        . '<div style="color:#667;font-size:12px;margin-top:8px">These details are attached to your order '
        . 'automatically &mdash; nothing more to fill in.</div>'
        . '</div>';

    $json = json_encode($card, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    return "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "var d=document.createElement('div');"
        . "d.innerHTML={$json};"
        . "var m=document.querySelector('#main-body')||document.querySelector('.main-content')||document.body;"
        . "m.insertBefore(d.firstChild,m.firstChild);});</script>";
});
