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

if (!function_exists('kx_is_auth_page')) {
    /**
     * Auth pages (/login, /password/reset...) run chrome-less: they get a
     * dedicated centred layout instead of the site header/footer.
     */
    function kx_is_auth_page(): bool
    {
        $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        return (bool) preg_match('#^/(login$|dologin|password/reset)#', $path);
    }
}

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

    // Stale-order nudge: any order in flight for 7+ days raises a to-do,
    // repeated weekly until it moves — dashboards only work when watched.
    try {
        $stale = Capsule::table('mod_virtutel_orders as o')
            ->join('mod_virtutel_services as s', 's.id', '=', 'o.service_id')
            ->whereIn('o.whmcs_status', ['pending', 'in_progress', 'action_required'])
            ->where('o.created_at', '<', date('Y-m-d H:i:s', time() - 7 * 86400))
            ->limit(50)
            ->get(['o.id', 'o.vt_order_id', 'o.order_type', 'o.status',
                'o.action_required', 'o.created_at', 's.whmcs_service_id']);

        foreach ($stale as $order) {
            $nudgeKey = 'stalenudge_' . (int) $order->id;
            $lastNudge = (int) (WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get($nudgeKey, '0') ?? '0');
            if ($lastNudge > time() - 7 * 86400) {
                continue;
            }
            WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set($nudgeKey, (string) time());

            $ageDays = (int) floor((time() - strtotime((string) $order->created_at)) / 86400);
            localAPI('AddTodoItem', [
                'date' => date('Y-m-d'),
                'title' => sprintf(
                    'STALE ORDER: %s in flight %d days',
                    (string) ($order->vt_order_id ?: $order->order_type),
                    $ageDays
                ),
                'description' => sprintf(
                    '%s order %s for WHMCS service #%d has been in flight %d days (status %s%s). '
                    . 'Chase Virtutel or resolve/cancel it from the service tab.',
                    (string) $order->order_type,
                    (string) ($order->vt_order_id ?: '(no VT id)'),
                    (int) $order->whmcs_service_id,
                    $ageDays,
                    (string) ($order->status ?: 'NEW'),
                    (string) ($order->action_required ?? '') !== ''
                        ? ', action: ' . (string) $order->action_required : ''
                ),
                'status' => 'Pending',
                'duedate' => date('Y-m-d'),
            ]);
        }
    } catch (\Throwable $e) {
        logActivity('Virtutel NBN: stale-order sweep failed: ' . $e->getMessage());
    }

    // Data hygiene: without pruning, callback events and the dated /
    // per-test settings keys grow forever.
    try {
        $pruned = Capsule::table('mod_virtutel_callback_events')
            ->where('created_at', '<', date('Y-m-d H:i:s', time() - 90 * 86400))
            ->delete();

        $settingsPrune = [
            // prefix => days to keep (by updated_at)
            'ctests_' => 7,        // daily customer-check counters
            'outagechk_' => 7,     // hourly outage cache
            'cancelreq_' => 1,     // two-click confirm flags
            'linkmsg_' => 30,      // one-shot UI messages
            'speedmsg_' => 30,
            'ordermsg_' => 30,
            'svctest_' => 60,      // test-id -> service mappings
            'healthtest_' => 60,
            'stalenudge_' => 120,  // nudge timestamps
        ];
        foreach ($settingsPrune as $prefix => $days) {
            $pruned += Capsule::table('mod_virtutel_settings')
                ->where('name', 'like', $prefix . '%')
                ->where('updated_at', '<', date('Y-m-d H:i:s', time() - $days * 86400))
                ->delete();
        }

        if ($pruned > 0) {
            logActivity('Virtutel NBN: pruned ' . $pruned . ' expired rows (callback events + dated settings)');
        }
    } catch (\Throwable $e) {
        logActivity('Virtutel NBN: data pruning failed: ' . $e->getMessage());
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

    // Everything is driven by whether the cart currently holds one of our
    // products: no product -> no checker, no card, and any lingering
    // qualification is cleared (e.g. the item was removed from the cart).
    try {
        $cartPids = array_values(array_filter(array_map(
            fn ($p) => (int) ($p['pid'] ?? 0),
            (array) ($_SESSION['cart']['products'] ?? [])
        )));
        $ours = $cartPids !== [] && Capsule::table('tblproducts')
            ->whereIn('id', $cartPids)
            ->where('servertype', 'virtutel_nbn')
            ->exists();
    } catch (\Throwable $e) {
        $ours = false;
    }

    $signup = $_SESSION['virtutel_nbn_signup'] ?? null;

    if (!$ours) {
        // Keep the capture only while an add-to-cart is actually in flight
        // (pages/order.php stores it one request before the product lands).
        if (is_array($signup) && ($_GET['a'] ?? '') !== 'add') {
            unset($_SESSION['virtutel_nbn_signup']);
        }

        return '';
    }

    if (!is_array($signup) || empty($signup['Location ID'])) {
        // Product in cart but no qualification (customer came straight
        // through the store) — show the inline address checker.

        $pidsJson = json_encode($cartPids);
        $base = '/modules/servers/virtutel_nbn/pages';

        return <<<HTML
<script>
document.addEventListener('DOMContentLoaded', function () {
  var wrap = document.createElement('div');
  wrap.className = 'vt-nbn-checker';
  wrap.style.cssText = 'background:#fff7e8;border:1px solid #f0d9a8;border-radius:10px;padding:16px 20px;margin:12px auto 18px;max-width:1100px;font-size:14px';
  wrap.innerHTML = '<div style="font-weight:700;font-size:15px;margin-bottom:4px;color:#a3690e">Check availability for your address</div>'
    + '<div style="color:#667;margin-bottom:10px">NBN plans are address-specific &mdash; confirm yours before checkout.</div>'
    + '<div style="display:flex;gap:8px;flex-wrap:wrap"><input type="text" id="vtqAddr" placeholder="Street address e.g. 5 Pruden Ct Stratford VIC 3862" style="flex:1;min-width:240px;padding:9px 12px;border:1px solid #c8cfdb;border-radius:8px;font-size:15px">'
    + '<button type="button" id="vtqBtn" style="padding:9px 18px;border:0;border-radius:8px;background:#1a5fd0;color:#fff;font-size:15px;cursor:pointer">Check</button></div>'
    + '<div id="vtqOut" style="margin-top:10px"></div>';
  var m = document.querySelector('#main-body') || document.querySelector('.main-content') || document.body;
  m.insertBefore(wrap, m.firstChild);

  var cartPids = {$pidsJson};
  var out = document.getElementById('vtqOut');

  function post(data) {
    return fetch('{$base}/qualify-api.php', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data)})
      .then(function (r) { return r.json().then(function (j) { return {ok: r.ok, body: j}; }); });
  }
  function note(text, bad) {
    out.textContent = text;
    out.style.color = bad ? '#c0392b' : '#667';
  }

  function qualify(locId, label) {
    note('Checking ' + label + '…');
    post({action: 'qualify', locId: locId}).then(function (res) {
      if (!res.ok) { return note(res.body.error || 'Check failed.', true); }
      var q = res.body;
      var available = (q.plans || []).some(function (p) { return cartPids.indexOf(p.pid) !== -1; });
      if (q.readiness && q.readiness.code === 'not_available') {
        return note('NBN can\\'t be ordered at that address yet.', true);
      }
      if (!available) {
        return note('The plan in your cart isn\\'t available at that address — use the availability checker to pick a plan that is.', true);
      }
      fetch('{$base}/order.php?ajax=1&vt_locid=' + encodeURIComponent(locId)
          + '&vt_addr=' + encodeURIComponent(label)
          + '&vt_tech=' + encodeURIComponent(q.technology || ''))
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (j.ok) { note('Address confirmed — attaching your connection details…'); location.reload(); }
          else { note('Could not attach the address — please try again.', true); }
        });
    });
  }

  document.getElementById('vtqBtn').addEventListener('click', function () {
    var address = document.getElementById('vtqAddr').value.trim();
    if (address.length < 8) { return note('Enter your full street address including suburb and postcode.', true); }
    note('Searching the NBN address database…');
    post({action: 'search', address: address}).then(function (res) {
      if (!res.ok) { return note(res.body.error || 'Search failed.', true); }
      var m = res.body.matches || [];
      if (!m.length) { return note('No NBN match found — add your suburb and postcode, or contact us.', true); }
      out.textContent = '';
      out.style.color = '';
      m.slice(0, 8).forEach(function (row) {
        var b = document.createElement('button');
        b.type = 'button';
        b.textContent = row.address;
        b.style.cssText = 'display:block;width:100%;text-align:left;background:#fff;border:1px solid #dde3ee;border-radius:8px;padding:9px 12px;margin-bottom:6px;cursor:pointer;font-size:14px';
        b.addEventListener('click', function () { qualify(row.locId, row.address); });
        out.appendChild(b);
      });
    });
  });
});
</script>
HTML;
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
        $portName = $signup['Port Label'] ?? $signup['UNI-D Port'];
        $rows[] = ['NBN box port', $portName
            . (!empty($signup['Port Auto']) ? ' (auto-selected for you)' : ' (your selection)')];
    } elseif (!empty($signup['Copper Pair ID'])) {
        // FTTN/FTTB/FTTC — no NTD port, the service rides a copper pair.
        $rows[] = ['Copper pair', $signup['Copper Pair ID']
            . (!empty($signup['Port Auto']) ? ' (auto-selected for you)' : ' (your selection)')];
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

    $card = '<div class="vt-nbn-card" style="background:#f4f8ff;border:1px solid #c9d8f6;border-radius:10px;'
        . 'padding:16px 20px;margin:12px auto 18px;max-width:1100px;font-size:14px">'
        . '<div style="font-weight:700;font-size:15px;margin-bottom:6px;color:#1a5fd0">'
        . '&#10003; Your NBN connection</div>'
        . '<table style="border-collapse:collapse">' . $rowsHtml . '</table>'
        . '<div style="color:#667;font-size:12px;margin-top:8px">These details are attached to your order '
        . 'automatically &mdash; nothing more to fill in.</div>'
        . '</div>';

    // Compact version that lives inside the cart line item itself.
    $inlineRows = '';
    foreach ($rows as [$label, $value]) {
        $inlineRows .= '<div><span style="color:#8a93a8">' . $e($label) . ':</span> '
            . '<b style="font-weight:600">' . $e($value) . '</b></div>';
    }
    $inline = '<div class="vt-nbn-inline" style="margin-top:8px;font-size:12.5px;line-height:1.6">'
        . $inlineRows . '</div>';

    // Which cart item indexes are ours (the viewcart Edit links carry
    // cart.php?a=confproduct&i=<index>, our anchor into the right row).
    $vtIdx = [];
    try {
        $vtPids = array_map('intval', Capsule::table('tblproducts')
            ->whereIn('id', $cartPids)
            ->where('servertype', 'virtutel_nbn')
            ->pluck('id')->all());
        foreach ((array) ($_SESSION['cart']['products'] ?? []) as $i => $p) {
            if (in_array((int) ($p['pid'] ?? 0), $vtPids, true)) {
                $vtIdx[] = (int) $i;
            }
        }
    } catch (\Throwable $e2) {
        $vtIdx = [];
    }

    $jsonCard = json_encode($card, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $jsonInline = json_encode($inline, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $jsonIdx = json_encode(array_values($vtIdx));

    return "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "var idx={$jsonIdx},placed=false;"
        . "function findRow(n){var as=document.querySelectorAll('a[href*=\"confproduct\"]');"
        . "for(var i=0;i<as.length;i++){var m=as[i].getAttribute('href').match(/[?&]i=(\\d+)/);"
        . "if(m&&parseInt(m[1],10)===n){return as[i].closest('tr')||as[i].closest('.item');}}return null;}"
        . "idx.forEach(function(n){var row=findRow(n);if(!row){return;}"
        . "var cell=row.querySelector('td')||row;"
        . "var d=document.createElement('div');d.innerHTML={$jsonInline};"
        . "cell.appendChild(d.firstChild);placed=true;});"
        . "if(!placed){var d=document.createElement('div');d.innerHTML={$jsonCard};"
        . "var m=document.querySelector('#main-body')||document.querySelector('.main-content')||document.body;"
        . "m.insertBefore(d.firstChild,m.firstChild);}});</script>";
});

/**
 * Server-side gate: a Virtutel product cannot be checked out without a
 * qualification attached — the inline checker or the qualify page must have
 * confirmed the address first.
 */
add_hook('ShoppingCartValidateCheckout', 1, function () {
    try {
        $cartPids = array_values(array_filter(array_map(
            fn ($p) => (int) ($p['pid'] ?? 0),
            (array) ($_SESSION['cart']['products'] ?? [])
        )));
        if ($cartPids === []) {
            return;
        }
        $ours = Capsule::table('tblproducts')
            ->whereIn('id', $cartPids)
            ->where('servertype', 'virtutel_nbn')
            ->exists();
        if (!$ours) {
            return;
        }

        $signup = $_SESSION['virtutel_nbn_signup'] ?? null;
        if (!is_array($signup) || empty($signup['Location ID'])) {
            return ['Please confirm NBN availability for your address before checkout — use the address checker at the top of the cart page.'];
        }
    } catch (\Throwable $e) {
        return; // never block checkout on an internal error
    }
});

/**
 * Dark portal skin: when enabled in the addon settings, overlay the Korvix
 * dark stylesheet on every client area page. The Template stays Twenty-One
 * — this is pure CSS, so it can never white-screen the site.
 */
add_hook('ClientAreaHeadOutput', 5, function () {
    try {
        $enabled = (string) (Capsule::table('tbladdonmodules')
            ->where('module', 'virtutel_nbn_admin')
            ->where('setting', 'portal_dark')
            ->value('value') ?? '');
        if ($enabled !== 'on' && $enabled !== '1' && $enabled !== 'yes') {
            return '';
        }
    } catch (\Throwable $e) {
        return '';
    }

    // Backstop for markup the stylesheet can't reach (inline styles,
    // unknown wrappers, order-form CSS with higher specificity): strip any
    // element that still RENDERS with a (near-)white background. A dark
    // theme has no white surfaces, so this is safe by definition.
    $whitewash = "<script>(function(){function sweep(root){"
        . "root.querySelectorAll('*').forEach(function(el){"
        . "if(el.tagName==='IFRAME'||el.tagName==='IMG'||el.tagName==='VIDEO'){return;}"
        . "var m=getComputedStyle(el).backgroundColor.match(/rgba?\\((\\d+),\\s*(\\d+),\\s*(\\d+)(?:,\\s*([\\d.]+))?/);"
        . "if(m&&(m[4]===undefined||parseFloat(m[4])>0.5)&&+m[1]>232&&+m[2]>232&&+m[3]>232){"
        . "el.style.setProperty('background-color','transparent','important');}});}"
        . "function run(){var mb=document.getElementById('main-body')||document.body;sweep(mb);}"
        . "document.addEventListener('DOMContentLoaded',function(){run();setTimeout(run,600);});"
        . "})();</script>";

    return '<link rel="stylesheet" href="/modules/servers/virtutel_nbn/pages/portal-dark.css?v=29">'
        . '<meta name="color-scheme" content="dark">'
        . $whitewash;
});

/**
 * Address-first store flow, embedded IN the WHMCS page: on a product
 * group containing Virtutel NBN products, the plan grid is replaced with
 * the full qualification experience (autocomplete, port map, transfer
 * flow, address-valid plans) in a same-origin auto-sizing frame — nav,
 * sidebar, and theme stay. Non-NBN groups are untouched.
 */
add_hook('ClientAreaHeadOutput', 6, function ($vars) {
    if (($_GET['a'] ?? '') === 'add') {
        return '';
    }

    // Two entry points: a /store/<group-slug> landing page (any filename —
    // WHMCS routes these outside cart.php) or cart.php?gid=N. Deeper
    // /store/<group>/<product> URLs are product pages and stay untouched.
    $gid = 0;
    $slug = '';
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    if (preg_match('#/store/([^/?\#]+)/?(?:[?\#]|$)#', $uri, $m)) {
        $slug = preg_replace('/[^a-z0-9\-_]/', '', strtolower($m[1]));
    } elseif (($vars['filename'] ?? '') === 'cart') {
        $gid = (int) ($_REQUEST['gid'] ?? 0);
    } else {
        return '';
    }
    if ($gid === 0 && $slug === '') {
        return '';
    }
    if ($gid === 0 && $slug !== '') {
        // Layered lookups: slug column (if present), then slugified name.
        try {
            if (Capsule::schema()->hasColumn('tblproductgroups', 'slug')) {
                $gid = (int) (Capsule::table('tblproductgroups')
                    ->whereRaw('LOWER(slug) = ?', [$slug])->value('id') ?? 0);
            }
        } catch (\Throwable $e) {
            $gid = 0;
        }
        if ($gid === 0) {
            // Slugified-name match, tolerating small typos in the stored
            // slug (e.g. /store/residental-internet vs "Residential
            // Internet") — but only among groups that actually contain
            // visible Virtutel NBN products, so a near-miss can never
            // hijack an unrelated group.
            try {
                $nbnGids = Capsule::table('tblproducts')
                    ->where('servertype', 'virtutel_nbn')
                    ->where('hidden', 0)
                    ->pluck('gid');
                $nbnGids = array_map('intval', is_array($nbnGids) ? $nbnGids : $nbnGids->all());
                foreach (Capsule::table('tblproductgroups')->get(['id', 'name']) as $group) {
                    $nameSlug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $group->name)), '-');
                    if ($nameSlug === $slug) {
                        $gid = (int) $group->id;
                        break;
                    }
                    if (in_array((int) $group->id, $nbnGids, true)
                        && $nameSlug !== ''
                        && levenshtein($nameSlug, $slug) <= 2
                    ) {
                        $gid = (int) $group->id;
                        break;
                    }
                }
            } catch (\Throwable $e) {
                $gid = 0;
            }
        }
    }
    if ($gid === 0) {
        // Marker so view-source shows why no embed happened.
        return '<!-- vtq: no product group matched store slug "' . $slug . '" -->';
    }

    try {
        $isNbnGroup = Capsule::table('tblproducts')
            ->where('gid', $gid)
            ->where('servertype', 'virtutel_nbn')
            ->where('hidden', 0)
            ->exists();
        if (!$isNbnGroup) {
            return '<!-- vtq: group ' . $gid . ' has no visible virtutel_nbn products -->';
        }
        $dark = (string) (Capsule::table('tbladdonmodules')
            ->where('module', 'virtutel_nbn_admin')
            ->where('setting', 'portal_dark')
            ->value('value') ?? '');
    } catch (\Throwable $e) {
        return '';
    }

    $theme = in_array($dark, ['on', '1', 'yes'], true) ? 'dark' : 'light';
    $src = '/modules/servers/virtutel_nbn/pages/qualify.php?embed=1&theme=' . $theme;

    return "<!-- vtq: embed gid={$gid} theme={$theme} -->"
        . "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "var grid=document.querySelector('.products,#products,.product-listing,.products-list');"
        . "if(!grid){var card=document.querySelector('.product,[class*=product-]');"
        . "if(card){grid=card.parentElement;}}"
        . "var f=document.createElement('iframe');"
        . "f.src='{$src}';"
        . "f.style.cssText='width:100%;border:0;display:block;min-height:520px;background:transparent';"
        . "f.setAttribute('scrolling','no');"
        . "if(grid){grid.replaceWith(f);}"
        . "else{var mb=document.getElementById('main-body')||document.querySelector('.main-content,section#main-menu+*');"
        . "if(!mb){return;}mb.insertBefore(f,mb.firstChild);}"
        // body.scrollHeight shrinks with the content; documentElement
        // ratchets at iframe height and leaves a void after long lists.
        . "setInterval(function(){try{var b=f.contentDocument.body;if(!b){return;}"
        . "var h=b.scrollHeight+24;"
        . "if(h>200&&Math.abs(h-f.offsetHeight)>8){f.style.height=h+'px';}}catch(e){}},400);"
        . "});</script>";
});

/**
 * Client dashboard overhaul (clientarea.php with no action): a warm
 * greeting hero with one-tap quick actions replaces the generic
 * "Dashboard" heading, the stat tiles and panels take the site's card
 * language, and the stuff we don't sell (domains, affiliates) is
 * hidden by label so no dead tiles clutter the page.
 */
add_hook('ClientAreaHeadOutput', 11, function ($vars) {
    // Path-gated to the literal dashboard URL: the filename check alone
    // also matches / and /index.php, and the takeover CSS must never
    // touch the marketing homepage.
    $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    if ($path !== '/clientarea.php'
        || ($vars['filename'] ?? '') !== 'clientarea'
        || !empty($_GET['action']) || !empty($_GET['rp'])
        || empty($_SESSION['uid'])) {
        return '';
    }

    $uid = (int) $_SESSION['uid'];
    $e2 = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES);
    $first = '';
    $invCount = $tixCount = null;
    $services = [];
    $tickets = [];
    try {
        $first = trim((string) (Capsule::table('tblclients')
            ->where('id', $uid)->value('firstname') ?? ''));
        $invCount = (int) Capsule::table('tblinvoices')->where('userid', $uid)
            ->where('status', 'Unpaid')->count();
        $tixCount = (int) Capsule::table('tbltickets')->where('userid', $uid)
            ->whereIn('status', ['Open', 'Answered', 'Customer-Reply', 'In Progress'])->count();

        $rows = Capsule::table('tblhosting as h')
            ->join('tblproducts as p', 'p.id', '=', 'h.packageid')
            ->leftJoin('mod_virtutel_services as v', 'v.whmcs_service_id', '=', 'h.id')
            ->where('h.userid', $uid)
            ->whereIn('h.domainstatus', ['Active', 'Pending', 'Suspended'])
            ->whereIn('p.servertype', ['virtutel_nbn', 'virtutel_phone'])
            ->orderBy('h.id')
            ->limit(4)
            ->get(['h.id', 'h.domainstatus', 'h.domain', 'p.name',
                'v.carrier_status as vstatus', 'v.avc_id', 'v.service_address']);
        foreach ($rows as $row) {
            $services[] = $row;
        }

        $trows = Capsule::table('tbltickets')->where('userid', $uid)
            ->orderByDesc('lastreply')->limit(3)
            ->get(['id', 'tid', 'c', 'title', 'status', 'lastreply']);
        foreach ($trows as $trow) {
            $tickets[] = $trow;
        }
    } catch (\Throwable $e) {
        // greeting still renders without data
    }

    $hi = 'G&rsquo;day' . ($first !== ''
        ? ', <span class="kx-grad">' . $e2($first) . '</span>' : '') . '!';

    $connCards = '';
    foreach ($services as $svc) {
        $vstatus = strtolower((string) ($svc->vstatus ?? ''));
        if ((string) $svc->domainstatus === 'Suspended') {
            $cls = 'r'; $label = 'Suspended &mdash; call us on 03 4130 5013';
        } elseif (str_contains($vstatus, 'disconnect')) {
            $cls = 'a'; $label = 'Disconnection in progress';
        } elseif (str_contains($vstatus, 'connect') && $vstatus !== 'connected') {
            $cls = 'a'; $label = 'Getting you connected';
        } elseif ((string) $svc->domainstatus === 'Pending') {
            $cls = 'a'; $label = 'Order in progress';
        } else {
            $cls = 'g'; $label = 'Connected';
        }
        $addr = trim((string) ($svc->service_address ?? ''));
        $avc = trim((string) ($svc->avc_id ?? $svc->domain ?? ''));
        $connCards .= '<div class="kx-conn">'
            . '<div class="kx-conn-top"><span class="dot ' . $cls . '"></span>'
            . '<b>' . $label . '</b>'
            . '<span class="plan">' . $e2($svc->name) . '</span></div>'
            . ($addr !== '' ? '<div class="kx-conn-addr">' . $e2($addr) . '</div>' : '')
            . ($avc !== '' ? '<div class="kx-conn-meta">' . $e2($avc) . '</div>' : '')
            . '<div class="kx-conn-actions">'
            . '<a class="b1" href="/clientarea.php?action=productdetails&id=' . (int) $svc->id . '">'
            . 'Manage &amp; test my line</a>'
            . '<a class="b2" href="/submitticket.php?step=2&deptid=1">Report a fault</a>'
            . '</div></div>';
    }
    if ($connCards === '') {
        $connCards = '<div class="kx-conn"><div class="kx-conn-top">'
            . '<span class="dot n"></span><b>No service yet</b></div>'
            . '<div class="kx-conn-addr">Let&rsquo;s fix that &mdash; check what your address supports.</div>'
            . '<div class="kx-conn-actions">'
            . '<a class="b1" href="/personal/nbn/signup/">Check my address</a></div></div>';
    }

    $invSub = $invCount === null ? 'View &amp; pay'
        : ($invCount > 0
            ? '<span class="w">' . $invCount . ' unpaid</span>'
            : '<span class="g">All paid &#10003;</span>');
    $tixSub = $tixCount === null ? 'We\'re here to help'
        : ($tixCount > 0
            ? '<span class="w">' . $tixCount . ' open</span>'
            : 'No open tickets');

    $ticketHtml = '';
    if ($tickets !== []) {
        $ticketHtml = '<div class="kx-tix"><div class="kx-tix-head">Recent tickets'
            . '<a href="/supporttickets.php">View all &rarr;</a></div>';
        foreach ($tickets as $ticket) {
            $open = in_array((string) $ticket->status,
                ['Open', 'Answered', 'Customer-Reply', 'In Progress'], true);
            $ticketHtml .= '<a class="kx-tix-row" href="/viewticket.php?tid='
                . $e2($ticket->tid) . '&c=' . $e2($ticket->c) . '">'
                . '<span class="t">' . $e2($ticket->title) . '</span>'
                . '<span class="s' . ($open ? ' o' : '') . '">' . $e2($ticket->status) . '</span>'
                . '</a>';
        }
        $ticketHtml .= '</div>';
    }

    $hero = '<div class="kx-dash">'
        . '<div class="kx-dash-hi">' . $hi . '</div>'
        . '<div class="kx-dash-sub">Here&rsquo;s how your connection is looking right now.</div>'
        . $connCards
        . '<div class="kx-dash-row">'
        . '<a href="/clientarea.php?action=invoices"><b>Invoices</b><span>' . $invSub . '</span></a>'
        . '<a href="/supporttickets.php"><b>Support</b><span>' . $tixSub . '</span></a>'
        . '<a href="https://go.getscreen.me/invite/683032125" target="_blank" rel="noopener">'
        . '<b>Remote Support</b><span>Start a session</span></a>'
        . '</div>'
        . $ticketHtml
        . '</div>';
    $json = json_encode($hero, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    return '<style>'
        // the dashboard is 100% ours: everything WHMCS renders inside the
        // content area is hidden outright — no restyled leftovers
        . '#main-body>*:not(.kx-dash),.main-content>*:not(.kx-dash)'
        . '{display:none !important}'
        . '.kx-dash{display:block !important;margin:34px auto 60px;position:relative;width:100%;'
        . 'padding-left:15px;padding-right:15px;'
        . 'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif}'
        . '@media(min-width:576px){.kx-dash{max-width:540px}}'
        . '@media(min-width:768px){.kx-dash{max-width:720px}}'
        . '@media(min-width:992px){.kx-dash{max-width:960px}}'
        . '@media(min-width:1200px){.kx-dash{max-width:1140px}}'
        . '.kx-grad{background:linear-gradient(92deg,#4d8dff,#7a5cff 55%,#b16bff);'
        . '-webkit-background-clip:text;background-clip:text;color:transparent}'
        . '.kx-dash-hi{font-size:32px;font-weight:800;letter-spacing:-.02em;color:#e6e9f2}'
        . '.kx-dash-sub{color:#98a2b8;font-size:14.5px;margin:6px 0 22px}'
        . '.kx-conn{background:#141b2c;border:1px solid #2a3347;border-radius:16px;'
        . 'padding:26px 30px;position:relative;overflow:hidden;margin-bottom:16px;'
        . 'box-shadow:0 18px 50px rgba(0,0,0,.3)}'
        . '.kx-conn::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;'
        . 'background:linear-gradient(92deg,#4d8dff,#7a5cff 55%,#b16bff)}'
        . '.kx-conn-top{display:flex;align-items:center;gap:11px;flex-wrap:wrap}'
        . '.kx-conn-top b{font-size:19px;font-weight:800;color:#e6e9f2}'
        . '.kx-conn-top .plan{margin-left:auto;background:#1e2739;border:1px solid #2a3347;'
        . 'border-radius:999px;padding:6px 15px;font-size:13px;font-weight:700;color:#c7cede}'
        . '.kx-conn .dot{width:11px;height:11px;border-radius:50%;flex:0 0 11px}'
        . '.kx-conn .dot.g{background:#2fbf71;box-shadow:0 0 0 0 rgba(47,191,113,.4);'
        . 'animation:kxDot 2.2s ease-out infinite}'
        . '.kx-conn .dot.a{background:#e2a336}'
        . '.kx-conn .dot.r{background:#e2564a}'
        . '.kx-conn .dot.n{background:#5b6b8f}'
        . '@keyframes kxDot{0%{box-shadow:0 0 0 0 rgba(47,191,113,.4)}'
        . '70%{box-shadow:0 0 0 10px rgba(47,191,113,0)}100%{box-shadow:0 0 0 0 rgba(47,191,113,0)}}'
        . '@media (prefers-reduced-motion:reduce){.kx-conn .dot.g{animation:none}}'
        . '.kx-conn-addr{color:#c7cede;font-size:15px;font-weight:600;margin:14px 0 3px}'
        . '.kx-conn-meta{color:#5b6b8f;font-size:12.5px;font-family:ui-monospace,Menlo,monospace}'
        . '.kx-conn-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}'
        . '.kx-conn-actions a{text-decoration:none !important;border-radius:9px;'
        . 'padding:11px 20px;font-weight:800;font-size:14px;'
        . 'transition:transform .12s ease,box-shadow .12s ease}'
        . '.kx-conn-actions .b1{background:linear-gradient(135deg,#4d8dff,#7a5cff);color:#fff !important}'
        . '.kx-conn-actions .b1:hover{transform:translateY(-1px);'
        . 'box-shadow:0 8px 26px rgba(77,141,255,.35)}'
        . '.kx-conn-actions .b2{border:1px solid #2a3347;color:#c7cede !important;background:transparent}'
        . '.kx-conn-actions .b2:hover{border-color:#4d8dff;color:#fff !important}'
        . '.kx-dash-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px}'
        . '.kx-dash-row a{display:flex;justify-content:space-between;align-items:center;gap:10px;'
        . 'background:#11182a;border:1px solid #232d44;border-radius:12px;padding:14px 17px;'
        . 'text-decoration:none !important;transition:border-color .12s ease}'
        . '.kx-dash-row a:hover{border-color:#4d8dff}'
        . '.kx-dash-row b{color:#e6e9f2;font-weight:700;font-size:13.5px}'
        . '.kx-dash-row span{color:#98a2b8;font-size:12.5px;white-space:nowrap}'
        . '.kx-dash-row span .w{color:#ecc575;font-weight:700}'
        . '.kx-dash-row span .g{color:#7fdcaa;font-weight:700}'
        . '.kx-tix{background:#11182a;border:1px solid #232d44;border-radius:12px;'
        . 'padding:6px 17px 8px;margin-top:12px}'
        . '.kx-tix-head{display:flex;justify-content:space-between;align-items:center;'
        . 'color:#98a2b8;font-size:12px;font-weight:800;text-transform:uppercase;'
        . 'letter-spacing:.07em;padding:10px 0 6px}'
        . '.kx-tix-head a{color:#7aa5ff;text-decoration:none;font-weight:700;'
        . 'text-transform:none;letter-spacing:0;font-size:12.5px}'
        . '.kx-tix-row{display:flex;justify-content:space-between;align-items:center;gap:14px;'
        . 'padding:11px 0;border-top:1px solid #1d2740;text-decoration:none !important}'
        . '.kx-tix-row .t{color:#e6e9f2;font-size:13.5px;font-weight:600;overflow:hidden;'
        . 'text-overflow:ellipsis;white-space:nowrap}'
        . '.kx-tix-row .s{color:#5b6b8f;font-size:12px;font-weight:700;white-space:nowrap}'
        . '.kx-tix-row .s.o{color:#ecc575}'
        . '.kx-tix-row:hover .t{color:#fff}'
        . '.header-lined,.page-header{display:none !important}'
        . '</style>'
        . "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "var main=document.getElementById('main-body')||document.querySelector('.main-content,main');"
        . "if(main&&!document.querySelector('.kx-dash')){"
        . "var d=document.createElement('div');d.innerHTML={$json};"
        . "main.insertBefore(d.firstChild,main.firstChild);}"
        . "});</script>";
});

/**
 * Dedicated auth layout: /login and /password/reset drop ALL site
 * chrome (switcher, topbar, nav, footer) and become a centred branded
 * card — logo above, dot-grid and glows behind, back-to-site link
 * below. The WHMCS form itself is untouched, so login, captcha and
 * reset flows keep working.
 */
add_hook('ClientAreaHeadOutput', 5, function () {
    if (!kx_is_auth_page()) {
        return '';
    }
    $logo = '';
    foreach (['assets/img/logo.png', 'assets/img/logo.jpg'] as $cand) {
        $root = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
        if (is_file($root . '/' . $cand)) {
            $logo = '/' . $cand;
            break;
        }
    }
    if ($logo === '') {
        try {
            $logo = trim((string) (Capsule::table('tblconfiguration')
                ->where('setting', 'LogoURL')->value('value') ?? ''));
        } catch (\Throwable $e) {
            $logo = '';
        }
        if ($logo !== '' && !preg_match('#^(https?:)?//#i', $logo) && $logo[0] !== '/') {
            $logo = '/' . $logo;
        }
    }
    $logoHtml = $logo !== ''
        ? '<img src="' . htmlspecialchars($logo, ENT_QUOTES) . '" alt="Korvix" '
            . 'onerror="this.outerHTML=\'<div style=&quot;font-weight:900;font-size:26px;'
            . 'letter-spacing:.1em;color:#fff&quot;>KORVIX</div>\'">'
        : '<div style="font-weight:900;font-size:26px;letter-spacing:.1em;color:#fff">KORVIX</div>';

    $head = '<div class="kx-authlogo"><a href="/">' . $logoHtml . '</a>'
        . '<div class="t">Customer portal</div></div>';
    $foot = '<div class="kx-authback">&larr; <a href="/">Back to the Korvix site</a>'
        . ' &nbsp;&middot;&nbsp; Need a hand? <a href="tel:0341305013">03 4130 5013</a></div>';
    $headJson = json_encode($head, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $footJson = json_encode($foot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    return '<style>'
        . '.kx-chrome,.kx-pnav,#header,.app-header,#main-menu,.app-main-menu,'
        . '#footer,footer,.footer,.breadcrumb,div.topbar{display:none !important}'
        . 'html,body{min-height:100vh}'
        . 'body{background:#0f1420 !important;position:relative;overflow-x:hidden}'
        . 'body::before{content:"";position:fixed;inset:0;pointer-events:none;z-index:0;'
        . 'background-image:radial-gradient(rgba(122,146,200,.14) 1px,transparent 1.4px);'
        . 'background-size:26px 26px;'
        . '-webkit-mask-image:radial-gradient(ellipse 90% 70% at 50% 0%,#000 30%,transparent 75%);'
        . 'mask-image:radial-gradient(ellipse 90% 70% at 50% 0%,#000 30%,transparent 75%)}'
        . '.kx-authglow{position:fixed;border-radius:50%;filter:blur(100px);opacity:.26;'
        . 'pointer-events:none;z-index:0}'
        . '.kx-authglow.g1{width:440px;height:440px;background:#2b5cff;top:-160px;left:-110px}'
        . '.kx-authglow.g2{width:400px;height:400px;background:#7a5cff;top:-120px;right:-90px}'
        . '.kx-authlogo{display:flex;flex-direction:column;align-items:center;gap:8px;'
        . 'margin:7vh auto 26px;position:relative;z-index:1}'
        . '.kx-authlogo img{height:42px;display:block;'
        . 'filter:invert(1) hue-rotate(180deg) brightness(1.05)}'
        . '.kx-authlogo .t{color:#98a2b8;font-size:13.5px;letter-spacing:.06em;'
        . 'text-transform:uppercase;font-weight:600}'
        . '.kx-authback{text-align:center;margin:26px auto 8vh;color:#98a2b8;font-size:13.5px;'
        . 'position:relative;z-index:1}'
        . '.kx-authback a{color:#c7cede;font-weight:600;text-decoration:none}'
        . '.kx-authback a:hover{color:#fff}'
        . '#main-body,.main-content,.app-main{position:relative;z-index:1;padding-top:0 !important}'
        // The card itself, restyled to the site language: gradient
        // hairline, 18px radius, deep shadow, form-width like the signup
        // walkthrough card.
        . 'div.kx-authcard{background:#141b2c !important;border:1px solid #2a3347 !important;'
        . 'border-radius:18px !important;box-shadow:0 24px 70px rgba(0,0,0,.45) !important;'
        . 'max-width:420px !important;width:100% !important;margin:0 auto !important;'
        . 'padding:32px 32px 24px !important;position:relative;overflow:hidden}'
        . '.kx-authcard::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;'
        . 'background:linear-gradient(92deg,#4d8dff,#7a5cff 55%,#b16bff)}'
        // the template lays fields out on a grid — force one full-width column
        . '.kx-authcard .row{margin:0 !important;display:block !important}'
        . '.kx-authcard [class*="col-"],.kx-authcard [class*="grid"],.kx-authcard form>div'
        . '{width:100% !important;max-width:100% !important;flex:0 0 100% !important;'
        . 'padding-left:0 !important;padding-right:0 !important}'
        . '.kx-authcard h1,.kx-authcard h2,.kx-authcard h3,.kx-authcard .card-title'
        . '{font-size:24px !important;font-weight:800 !important;letter-spacing:-.02em;'
        . 'margin:0 0 4px !important}'
        . '.kx-authcard p{color:#98a2b8 !important;font-size:14px;margin:0 0 18px !important}'
        . '.kx-authcard label{font-weight:700 !important;font-size:13px !important;'
        . 'color:#e6e9f2 !important;margin:0 0 6px !important}'
        // inputs by ELEMENT, not class — whatever the template calls them
        . '.kx-authcard input[type=text],.kx-authcard input[type=email],'
        . '.kx-authcard input[type=password]'
        . '{display:block;width:100% !important;background:#0a0e18 !important;'
        . 'border:1px solid #2a3347 !important;border-radius:9px !important;'
        . 'padding:12px 14px !important;font-size:15px !important;color:#e6e9f2 !important;'
        . 'box-shadow:none !important;margin:0 0 16px !important;height:auto !important}'
        . '.kx-authcard input:focus{border-color:#4d8dff !important;outline:none !important}'
        . '.kx-authcard input[type=password]{padding-right:48px !important}'
        // icon prefixes die; the password eye floats INSIDE its field
        . '.kx-authcard .input-group-prepend,'
        . '.kx-authcard .input-group-text:first-child{display:none !important}'
        // the wrapper itself must never paint chrome — the theme gives
        // .input-group its own border/padding on :focus-within, which drew
        // a second ghost box around the focused field
        . '.kx-authcard .input-group,.kx-authcard .input-group:focus-within'
        . '{position:relative !important;display:block !important;'
        . 'background:transparent !important;border:0 !important;box-shadow:none !important;'
        . 'outline:none !important;padding:0 !important;margin:0 0 16px !important;'
        . 'min-height:0 !important;height:auto !important;border-radius:0 !important;'
        . 'width:100% !important;overflow:visible !important}'
        . '.kx-authcard .input-group input{margin:0 !important}'
        . '.kx-authcard .input-group-append,.kx-authcard .input-group>span:last-child,'
        . '.kx-authcard .input-group>div:last-child:not(:first-child)'
        . '{position:absolute !important;right:4px;top:22px;transform:translateY(-50%);'
        . 'background:transparent !important;border:0 !important;margin:0 !important;'
        . 'height:auto !important;z-index:3}'
        . '.kx-authcard .input-group-append *,.kx-authcard .input-group button'
        . '{background:transparent !important;border:0 !important;color:#98a2b8 !important;'
        . 'box-shadow:none !important}'
        // submit: full-width gradient, Remember Me centred under it
        . '.kx-authcard button[type=submit],.kx-authcard input[type=submit],'
        . '.kx-authcard .btn-primary'
        . '{display:block !important;width:100% !important;max-width:100% !important;'
        . 'background:linear-gradient(135deg,#4d8dff,#7a5cff) !important;'
        . 'border:0 !important;border-radius:9px !important;padding:13px 22px !important;'
        . 'font-weight:800 !important;font-size:15.5px !important;color:#fff !important;'
        . 'margin:4px 0 0 !important;'
        . 'transition:transform .12s ease,box-shadow .12s ease}'
        . '.kx-authcard button[type=submit]:hover,.kx-authcard .btn-primary:hover'
        . '{transform:translateY(-1px);box-shadow:0 8px 26px rgba(77,141,255,.35)}'
        . '.kx-authactions{display:block !important;width:100% !important}'
        . '.kx-authactions label,.kx-authactions .custom-control,.kx-authcard .kx-remember'
        . '{display:flex !important;justify-content:center;align-items:center;gap:8px;'
        . 'color:#98a2b8 !important;font-weight:400 !important;font-size:13.5px !important;'
        . 'margin:14px 0 0 !important}'
        // Remember Me: the theme uses a hidden input + pseudo-element
        // checkbox positioned absolutely, which lands on top of the label
        // text once the row is re-centred — force a plain native checkbox
        . '.kx-remember input[type=checkbox],.kx-authcard input[type=checkbox]'
        . '{position:static !important;opacity:1 !important;appearance:auto !important;'
        . '-webkit-appearance:checkbox !important;width:16px !important;height:16px !important;'
        . 'margin:0 !important;display:inline-block !important;accent-color:#4d8dff;'
        . 'left:auto !important;top:auto !important}'
        . '.kx-remember::before,.kx-remember::after,'
        . '.kx-remember span::before,.kx-remember span::after,'
        . '.kx-remember label::before,.kx-remember label::after'
        . '{display:none !important;content:none !important}'
        . '.kx-authcard a{color:#7aa5ff !important}'
        . '.kx-authcard a:hover{color:#fff !important}'
        . '.kx-authcard .card-footer,.kx-authcard hr'
        . '{border-color:#2a3347 !important;background:transparent !important;'
        . 'text-align:center;margin-top:18px !important}'
        . '</style>'
        . "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "document.body.insertAdjacentHTML('afterbegin','<div class=\"kx-authglow g1\"></div><div class=\"kx-authglow g2\"></div>');"
        . "var pw=document.querySelector('input[type=password],input[name=email],#inputEmail');"
        . "var card=pw?(pw.closest('.card,.panel,.login-card,.w-full')||pw.closest('form')):null;"
        . "if(card){"
        . "card.classList.add('kx-authcard');"
        . "var d1=document.createElement('div');d1.innerHTML={$headJson};"
        . "card.parentNode.insertBefore(d1.firstChild,card);"
        . "var d2=document.createElement('div');d2.innerHTML={$footJson};"
        . "if(card.nextSibling){card.parentNode.insertBefore(d2.firstChild,card.nextSibling);}"
        . "else{card.parentNode.appendChild(d2.firstChild);}"
        // stack the submit row: full-width gradient button with the
        // Remember Me checkbox centred beneath it
        . "var sub=card.querySelector('button[type=submit],input[type=submit],.btn-primary');"
        . "if(sub){var host=sub.parentElement;host.classList.add('kx-authactions');"
        . "var rem=null;card.querySelectorAll('label').forEach(function(l){"
        . "if(/remember/i.test(l.textContent||'')){rem=l.closest('.custom-control')||l;}});"
        . "if(rem){rem.classList.add('kx-remember');host.appendChild(rem);}}"
        . "}});</script>";
});

/**
 * Perfect header parity: the theme's two-tier header (logo row + menu
 * bar) is hidden entirely and replaced with a .kx-pnav bar that uses
 * the SAME markup and CSS as the marketing site's .kx-nav — identical
 * by construction, not by imitation. Logged-in clients get portal
 * links (Home / My Services / Invoices / Support + Account / Logout);
 * guests get the marketing family (Personal / Business / Contact +
 * Log in). Renders after the kx-chrome topbar, like the marketing
 * pages. Styles: portal-dark.css (.kx-pnav block).
 */
add_hook('ClientAreaHeadOutput', 6, function () {
    if (kx_is_auth_page()) {
        return '';
    }
    // Same logo resolution as the marketing nav.
    $logo = '';
    foreach (['assets/img/logo.png', 'assets/img/logo.jpg'] as $cand) {
        $root = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
        if (is_file($root . '/' . $cand)) {
            $logo = '/' . $cand;
            break;
        }
    }
    if ($logo === '') {
        try {
            $logo = trim((string) (Capsule::table('tblconfiguration')
                ->where('setting', 'LogoURL')->value('value') ?? ''));
        } catch (\Throwable $e) {
            $logo = '';
        }
        if ($logo !== '' && !preg_match('#^(https?:)?//#i', $logo) && $logo[0] !== '/') {
            $logo = '/' . $logo;
        }
    }
    $logoHtml = $logo !== ''
        ? '<img src="' . htmlspecialchars($logo, ENT_QUOTES) . '" alt="Korvix" '
            . 'onerror="this.parentNode.textContent=\'KORVIX\'">'
        : 'KORVIX';

    $uid = !empty($_SESSION['uid']);
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    // Guests see the marketing family nav verbatim; clients get the
    // portal set — in both cases ONE right-aligned cluster, exactly like
    // the marketing bar (no spacers, no split groups).
    $links = $uid
        ? [
            ['Home', '/clientarea.php', '#^/clientarea\.php$#'],
            ['My Services', '/clientarea.php?action=services', '#action=(services|productdetails)#'],
            ['Invoices', '/clientarea.php?action=invoices', '#action=invoices|viewinvoice\.php#'],
            ['Support', '/supporttickets.php', '#supporttickets|viewticket|submitticket|knowledgebase#'],
            ['Account', '/clientarea.php?action=details', '#action=details#'],
            ['Logout', '/logout.php', '#^\x00$#'],
        ]
        : [
            ['Home', '/', '#^/$|^/index\.php$#'],
            ['NBN Internet', '/personal/nbn/', '#^/personal/nbn#'],
            ['Mobile', '/personal/mobile/', '#^/personal/mobile#'],
            ['Home Phone', '/personal/home-phone/', '#^/personal/home-phone#'],
            ['Contact', '/contact/', '#^/contact#'],
        ];
    $items = '';
    foreach ($links as [$label, $url, $re]) {
        $items .= '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '"'
            . (preg_match($re, $uri) ? ' class="on"' : '') . '>'
            . htmlspecialchars($label, ENT_QUOTES) . '</a>';
    }

    $nav = '<div class="kx-pnav"><div class="in">'
        . '<a class="logo" href="/">' . $logoHtml . '</a>'
        . '<div class="links">' . $items . '</div>'
        . '</div></div>';
    $json = json_encode($nav, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    return '<style>#header,.app-header,header#header,#main-menu,.app-main-menu'
        . '{display:none !important}</style>'
        . "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "if(document.querySelector('.kx-pnav')){return;}"
        . "var d=document.createElement('div');d.innerHTML={$json};"
        . "var chrome=document.querySelector('.kx-chrome');"
        . "if(chrome){chrome.insertAdjacentElement('afterend',d.firstChild);}"
        . "else{document.body.insertBefore(d.firstChild,document.body.firstChild);}"
        . "});</script>";
});

/**
 * Submit-ticket polish: an urgent-call banner (nobody with a dead
 * connection should be typing a ticket), a what-to-include tips card
 * that gets faults solved in one round-trip, and useful placeholders.
 * Styles live in portal-dark.css (kx-ticket-help block).
 */
add_hook('ClientAreaHeadOutput', 8, function ($vars) {
    if (($vars['filename'] ?? '') !== 'submitticket') {
        return '';
    }

    return "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "var form=document.getElementById('frmTicketSubmit')"
        . "||document.querySelector('form[action*=\"submitticket\"]');"
        . "if(form&&!document.querySelector('.kx-ticket-help')){"
        . "var help=document.createElement('div');help.className='kx-ticket-help';"
        . "help.innerHTML='"
        . '<div class="kx-urgent">&#128222; <strong>No internet right now?</strong> '
        . 'Don\\\'t wait on a ticket &mdash; call <a href="tel:0341305013">03 4130 5013</a> '
        . 'and a Gippsland human picks up.</div>'
        . '<div class="kx-tips"><strong>Help us fix it in one go &mdash; include:</strong>'
        . '<ul><li>Your service address or AVC ID (starts with AVC&hellip;)</li>'
        . '<li>What the lights on the NBN box and router are doing</li>'
        . '<li>When it started, and anything you\\\'ve already tried</li></ul></div>'
        . "';"
        . "form.parentNode.insertBefore(help,form);}"
        . "var subj=document.getElementById('inputSubject');"
        . "if(subj&&!subj.placeholder){subj.placeholder='e.g. NBN dropping out every evening at 12 Example St';}"
        . "var msg=document.getElementById('inputMessage');"
        . "if(msg&&!msg.placeholder){msg.placeholder='What\\'s happening, since when, and what you\\'ve tried so far\\u2026';}"
        . "});</script>";
});

/**
 * The order-form sidebar's "Actions / View Cart" panel is dead weight in
 * the address-first flow — hide it on cart and store pages. Matched by
 * header text since standard_cart hardcodes the panel markup.
 */
add_hook('ClientAreaHeadOutput', 8, function ($vars) {
    $isCart = (($vars['filename'] ?? '') === 'cart');
    $isStore = (bool) preg_match('#/store/#', (string) ($_SERVER['REQUEST_URI'] ?? ''));
    if (!$isCart && !$isStore) {
        return '';
    }

    return '<style>[menuitemname="Actions"]{display:none !important}</style>'
        . "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "var els=document.querySelectorAll('.card-header,.panel-heading,h2,h3,h4,div,span');"
        . "for(var i=0;i<els.length;i++){var el=els[i];"
        . "if(el.childElementCount>3){continue;}"
        . "var t=(el.textContent||'').replace(/\\s+/g,' ').trim().toLowerCase();"
        . "if(t==='actions'||t==='+ actions'){"
        . "var box=el.closest('.card,.panel,.sidebar-collapsible,.section')||el.parentElement;"
        . "if(box){box.style.display='none';break;}}}"
        . "});</script>";
});

/**
 * The WHMCS front page (/) was a lesser duplicate of the /personal/
 * marketing page — one homepage is enough, so / redirects there for
 * everyone. rp/action/module requests (login, password reset, ...)
 * and non-GETs pass through untouched.
 */
add_hook('ClientAreaPage', 1, function ($vars) {
    $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    if (($path === '/' || $path === '/index.php')
        && empty($_GET['rp']) && empty($_GET['action']) && empty($_GET['m'])
        && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET')) {
        header('Location: /personal/', true, 302);
        exit;
    }
});

/**
 * Landing homepage: the portal front page (/) becomes a full marketing
 * landing — hero with the embedded address checker, features, live plan
 * cards, steps, FAQ — while keeping the theme's nav and footer. Content
 * comes from pages/home-landing.php (fetched same-origin and injected;
 * scripts can't ride innerHTML, so behaviour lives here).
 * (Normally unreachable now that / redirects to /personal/ — kept as
 * the fallback if the redirect hook is ever disabled.)
 */
add_hook('ClientAreaHeadOutput', 9, function ($vars) {
    $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    if ($path !== '/' && $path !== '/index.php') {
        return '';
    }
    if (($_GET['rp'] ?? '') !== '' || ($_GET['m'] ?? '') !== '') {
        return '';
    }

    return '<style>#main-body{opacity:0;transition:opacity .2s}'
        . 'html{scroll-behavior:smooth}#kx-check{scroll-margin-top:90px}'
        // The knowledgebase search has no business on a marketing homepage.
        . 'form:has(input[placeholder*="knowledgebase" i]){display:none !important}'
        . '</style>'
        . "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "var kb=document.querySelector('input[placeholder*=\"knowledgebase\" i]');"
        . "if(kb){(kb.closest('form')||kb.parentElement).style.display='none';}"
        . "var mb=document.getElementById('main-body')||document.querySelector('.main-content');"
        . "if(!mb){return;}"
        . "fetch('/modules/servers/virtutel_nbn/pages/home-landing.php')"
        . ".then(function(r){if(!r.ok){throw new Error('http '+r.status);}return r.text();})"
        . ".then(function(html){"
        . "mb.innerHTML=html;mb.style.opacity='1';"
        . "var f=document.getElementById('kxQualifyFrame');"
        . "if(f){setInterval(function(){try{"
        . "var b=f.contentDocument.body;if(!b){return;}"
        . "var h=b.scrollHeight+24;"
        . "if(h>120&&Math.abs(h-f.offsetHeight)>8){f.style.height=h+'px';}}catch(e){}},400);}"
        . "}).catch(function(){mb.style.opacity='1';});"
        . "});</script>";
});

/**
 * Site-wide utility bar above the theme nav: status / remote support /
 * pay invoice on the left, phone + Customer Portals on the right.
 * Edit the $items / $right arrays to change destinations.
 */
add_hook('ClientAreaHeadOutput', 4, function () {
    if (kx_is_auth_page()) {
        return '';
    }
    $items = [
        ['Service Status', '/serverstatus.php'],
        ['Get Remote Support', 'https://go.getscreen.me/invite/683032125'],
        ['Pay an Invoice', '/clientarea.php?action=invoices'],
    ];
    // Korvix main number.
    $phone = '03 4130 5013';
    $portal = !empty($_SESSION['uid'])
        ? ['Logout', '/logout.php']
        : ['Portal Login', '/clientarea.php'];

    $left = '';
    foreach ($items as [$label, $href]) {
        $external = str_starts_with($href, 'http');
        $left .= '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '"'
            . ($external ? ' target="_blank" rel="noopener"' : '') . '>'
            . htmlspecialchars($label, ENT_QUOTES) . '</a>';
    }
    $tel = preg_replace('/\D/', '', $phone);

    // One wrapper element: the injector inserts d.firstChild, so sibling
    // top-level divs would lose everything after the first.
    $bar = '<div class="kx-chrome">'
        . '<div class="kx-switch"><div class="in">'
        . '<a href="/">Personal</a><a href="/business/">Business</a>'
        . '</div></div>'
        . '<div class="kx-topbar"><div class="in">'
        . '<div class="l">' . $left . '</div>'
        . '<div class="r">'
        . '<a class="ph" href="tel:' . $tel . '">&#9742;&#65038;&nbsp;' . htmlspecialchars($phone, ENT_QUOTES) . '</a>'
        . '<a class="cp" href="' . htmlspecialchars($portal[1], ENT_QUOTES) . '">'
        . htmlspecialchars($portal[0], ENT_QUOTES) . '</a>'
        . '</div></div></div></div>';

    $json = json_encode($bar, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    return '<style>'
        . '.kx-switch{background:#04060c;font-size:12.5px;'
        . 'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif}'
        // Bar containers track Bootstrap's .container widths so the chrome
        // lines up with the WHMCS navbar/page content at every breakpoint.
        . '.kx-switch .in,.kx-topbar .in{width:100%;margin:0 auto;padding-left:15px;padding-right:15px}'
        . '@media(min-width:576px){.kx-switch .in,.kx-topbar .in{max-width:540px}}'
        . '@media(min-width:768px){.kx-switch .in,.kx-topbar .in{max-width:720px}}'
        . '@media(min-width:992px){.kx-switch .in,.kx-topbar .in{max-width:960px}}'
        . '@media(min-width:1200px){.kx-switch .in,.kx-topbar .in{max-width:1140px}}'
        . '.kx-switch .in{display:flex;gap:2px;justify-content:flex-end}'
        . '.kx-switch a{color:#98a2b8;font-weight:600;padding:7px 16px;display:inline-block;'
        . 'text-decoration:none;transition:color .15s}'
        . '.kx-switch a:hover{color:#e6e9f2;text-decoration:none}'
        . '.kx-topbar{background:#070a12;border-bottom:1px solid #1c2436;font-size:12.5px;'
        . 'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif}'
        . '.kx-topbar .in{display:flex;justify-content:space-between;'
        . 'align-items:center;padding-top:7px;padding-bottom:7px;gap:14px;flex-wrap:wrap}'
        . '.kx-topbar .l{display:flex;gap:18px;flex-wrap:wrap}'
        . '.kx-topbar .r{display:flex;gap:14px;align-items:center}'
        . '.kx-topbar a{color:#98a2b8;text-decoration:none;transition:color .15s}'
        . '.kx-topbar a:hover{color:#e6e9f2;text-decoration:none}'
        . '.kx-topbar .ph{color:#e6e9f2;font-weight:700;letter-spacing:.02em}'
        . '.kx-topbar .cp{background:linear-gradient(92deg,#4d8dff,#7a5cff);color:#fff;'
        . 'padding:4px 14px;border-radius:999px;font-weight:600}'
        . '.kx-topbar .cp:hover{color:#fff;filter:brightness(1.12)}'
        . '@media(max-width:640px){.kx-topbar .l{display:none}}'
        . '.kx-bell{display:inline-flex !important;align-items:center;justify-content:center;'
        . 'width:42px;height:42px;border-radius:50%;border:1px solid #2a3347;color:#e6e9f2 !important;'
        . 'position:relative;background:transparent;text-decoration:none !important}'
        . '.kx-bell:hover{border-color:#4d8dff;color:#fff !important}'
        . '.kx-bell svg{pointer-events:none}'
        . '.kx-bellbadge{position:absolute;top:-5px;right:-5px;'
        . 'background:linear-gradient(92deg,#4d8dff,#7a5cff);color:#fff;font-size:10.5px;'
        . 'font-weight:700;line-height:1;padding:3px 6px;border-radius:999px}'
        . '.kx-bellwrap{display:inline-flex !important;align-items:center;vertical-align:middle;'
        . 'margin-right:10px;position:relative}'
        // The theme strip the notifications button came from (not our
        // .kx-topbar). Its Logged-in-as block dies with it.
        . 'div.topbar{display:none !important}'
        // Breadcrumbs add nothing here — the nav covers it.
        . '.breadcrumb,.breadcrumbs,ol.breadcrumb,nav[aria-label*="breadcrumb" i]'
        . '{display:none !important}'
        . '</style>'
        . "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "var d=document.createElement('div');d.innerHTML={$json};"
        . "document.body.insertBefore(d.firstChild,document.body.firstChild);"
        // Hide WHMCS chrome strips: the admin 'Logged in as' masquerade bar
        // and the notifications strip (the Return-to-admin side tab stays).
        . "function hideBar(rx){var all=document.querySelectorAll('body div,body section,body header,body span,body a');"
        . "for(var i=0;i<all.length;i++){var el=all[i];"
        . "if(el.closest('.kx-topbar')){continue;}"
        . "if(el.childElementCount>6){continue;}"
        . "var t=(el.textContent||'').replace(/\\s+/g,' ').trim();"
        . "if(!rx.test(t)||t.length>80){continue;}"
        . "var box=el;"
        . "while(box.parentElement&&box.parentElement!==document.body){"
        . "var pt=(box.parentElement.textContent||'').replace(/\\s+/g,' ').trim();"
        . "if(pt.length>t.length+12){break;}box=box.parentElement;}"
        . "box.style.display='none';return;}}"
        . "hideBar(/^Logged in as:/i);"
        // Relocate the theme's notifications button (#accountNotifications,
        // inside div.topbar — exact markup confirmed from the live site)
        // into a bell beside the header cart icon. The popover content div
        // moves along with it so the notifications list keeps working;
        // div.topbar is then hidden by the CSS above.
        . "var kxBellTries=0;"
        . "(function kxBell(){"
        . "if(document.querySelector('.kx-bell')){return;}"
        . "var btn=document.getElementById('accountNotifications');"
        . "var strip=document.querySelector('div.topbar');"
        . "if(!btn){if(++kxBellTries<40){setTimeout(kxBell,400);}return;}"
        . "var count=(btn.textContent.match(/\\d+/)||[''])[0];"
        // Cart icon: prefer the link containing a cart glyph, then any
        // short-text cart link; final fallback is our own utility bar —
        // the strip stays hidden regardless.
        . "var ic=document.querySelector('a[href*=\"cart\"] i[class*=\"shopping\"],"
        . "a[href*=\"cart\"] i[class*=\"cart\"],a[href*=\"cart\"] svg');"
        . "var cart=ic?ic.closest('a'):null;"
        . "if(!cart){var cs=document.querySelectorAll('a[href*=\"cart.php\"]');"
        . "for(var j=0;j<cs.length;j++){var ct=(cs[j].textContent||'').replace(/\\s+/g,' ').trim();"
        . "if(ct.length<=4&&!cs[j].closest('.kx-topbar')){cart=cs[j];break;}}}"
        . "var wrap=document.createElement('span');wrap.className='kx-bellwrap';"
        . "if(cart){cart.parentElement.insertBefore(wrap,cart);"
        // Lay bell and cart out side by side, whatever the container was.
        . "var p=cart.parentElement;p.style.display='flex';p.style.alignItems='center';"
        . "p.style.justifyContent='flex-end';p.style.gap='10px';}"
        . "else{var tbr=document.querySelector('.kx-topbar .r');"
        . "if(tbr){tbr.insertBefore(wrap,tbr.firstChild);}else{return;}}"
        . "btn.className='kx-bell';"
        . "btn.innerHTML='<svg width=\"17\" height=\"17\" viewBox=\"0 0 24 24\" fill=\"none\" "
        . "stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\">"
        . "<path d=\"M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9\"/>"
        . "<path d=\"M13.7 21a2 2 0 0 1-3.4 0\"/></svg>'"
        . "+(count&&count!=='0'?'<span class=\"kx-bellbadge\">'+count+'</span>':'');"
        . "wrap.appendChild(btn);"
        . "var cont=document.getElementById('accountNotificationsContent');"
        . "if(cont){wrap.appendChild(cont);}"
        . "})();"
        . "});</script>";
});

/**
 * Full site footer: logo + blurb + phone, live product categories from
 * the store, support and account links, legal bar with Terms of Service
 * (pulled from the WHMCS setting when present). The theme's own footer
 * is hidden; ours renders via ClientAreaFooterOutput on every page.
 */
add_hook('ClientAreaFooterOutput', 5, function ($vars) {
    $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES);

    // Product categories, friendly /store/ links when slugs exist.
    $cats = [];
    try {
        $hasSlug = Capsule::schema()->hasColumn('tblproductgroups', 'slug');
        $groups = Capsule::table('tblproductgroups')
            ->where('hidden', 0)->orderBy('order')->limit(6)
            ->get(['id', 'name', 'slug']);
        foreach ($groups as $g) {
            $url = ($hasSlug && (string) ($g->slug ?? '') !== '')
                ? '/store/' . $g->slug
                : '/cart.php?gid=' . (int) $g->id;
            $cats[] = [(string) $g->name, $url];
        }
    } catch (\Throwable $ex) {
        $cats = [];
    }

    $tos = '';
    try {
        $tos = (string) (Capsule::table('tblconfiguration')
            ->whereIn('setting', ['TermsOfServiceURL', 'TermsofServiceURL'])
            ->value('value') ?? '');
    } catch (\Throwable $ex) {
        $tos = '';
    }

    $support = [
        ['Knowledgebase', '/knowledgebase.php'],
        ['Network Status', '/serverstatus.php'],
        ['Announcements', '/announcements.php'],
        ['Open a Ticket', '/submitticket.php'],
        ['Contact Us', '/contact/'],
    ];
    $account = [
        ['Client Area', '/clientarea.php'],
        ['Pay an Invoice', '/clientarea.php?action=invoices'],
        ['Order New Services', '/cart.php'],
        ['Check NBN Availability', '/index.php#kx-check'],
    ];

    $col = function (string $title, array $links) use ($e) {
        $out = '<div class="col"><h4>' . $e($title) . '</h4><ul>';
        foreach ($links as [$label, $href]) {
            $out .= '<li><a href="' . $e($href) . '">' . $e($label) . '</a></li>';
        }
        return $out . '</ul></div>';
    };

    $legal = '<a href="' . $e($tos !== '' ? $tos : '/terms/') . '">Terms of Service</a>'
        . '<a href="/privacy/">Privacy</a>'
        . '<a href="/acceptable-use/">Acceptable Use</a>'
        . '<a href="/critical-information/">Plan Information (CIS)</a>'
        . '<a href="/complaints/">Complaints</a>'
        . '<a href="/financial-hardship/">Financial Hardship</a>';

    return '<style>'
        . 'footer:not(.kx-footer),.footer:not(.kx-footer),#footer:not(.kx-footer)'
        . '{display:none !important}'
        . '.kx-footer{background:#070a12;border-top:1px solid #1c2436;margin-top:64px;'
        . 'color:#98a2b8;font-size:14px;'
        . "font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif}"
        . '.kx-footer .in{width:100%;margin:0 auto;padding:48px 15px 0}'
        . '@media(min-width:576px){.kx-footer .in{max-width:540px}}'
        . '@media(min-width:768px){.kx-footer .in{max-width:720px}}'
        . '@media(min-width:992px){.kx-footer .in{max-width:960px}}'
        . '@media(min-width:1200px){.kx-footer .in{max-width:1140px}}'
        . '.kx-footer .grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:32px}'
        . '@media(max-width:860px){.kx-footer .grid{grid-template-columns:1fr 1fr}}'
        . '@media(max-width:520px){.kx-footer .grid{grid-template-columns:1fr}}'
        . '.kx-footer .brand img{height:34px;filter:brightness(0) invert(1);margin-bottom:14px}'
        . '.kx-footer .brand .txtlogo{font-size:26px;font-weight:900;letter-spacing:.02em;'
        . 'color:#fff;margin-bottom:14px}'
        . '.kx-footer .brand p{margin:0 0 14px;line-height:1.7;max-width:300px}'
        . '.kx-footer .brand .ph{color:#e6e9f2;font-weight:700;text-decoration:none;font-size:16px}'
        . '.kx-footer h4{color:#e6e9f2;font-size:13px;letter-spacing:.1em;text-transform:uppercase;'
        . 'margin:4px 0 14px;font-weight:700}'
        . '.kx-footer ul{list-style:none;margin:0;padding:0}'
        . '.kx-footer li{margin-bottom:9px}'
        . '.kx-footer a{color:#98a2b8;text-decoration:none;transition:color .15s}'
        . '.kx-footer a:hover{color:#fff;text-decoration:none}'
        . '.kx-footer .legal{border-top:1px solid #1c2436;margin-top:40px;padding:18px 0;'
        . 'display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;'
        . 'font-size:12.5px}'
        . '.kx-footer .legal .links{display:flex;gap:18px;flex-wrap:wrap}'
        . '.powered-by,.footer-powered-by,#poweredBy{display:none !important}'
        . '</style>'
        . '<footer class="kx-footer"><div class="in"><div class="grid">'
        . '<div class="col brand"><div id="kxFooterLogo" class="txtlogo">KORVIX</div>'
        . '<p>Fast, local NBN and hosted services for Gippsland and beyond &mdash; '
        . 'no lock-ins, no runaround, real local support.</p>'
        // Korvix main number.
        . '<a class="ph" href="tel:0341305013">&#9742;&#65038;&nbsp;03 4130 5013</a></div>'
        . ($cats !== [] ? $col('Services', $cats) : '')
        . $col('Support', $support)
        . $col('Account', $account)
        . '</div><div class="legal">'
        . '<div>&copy; ' . date('Y') . ' Korvix. All rights reserved.</div>'
        . '<div class="links">' . $legal . '</div>'
        . '</div></div></footer>'
        . "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "var h=document.querySelector('header img,nav img,.navbar-brand img,a[href=\"/\"] img');"
        . "if(h){var slot=document.getElementById('kxFooterLogo');"
        . "var img=h.cloneNode(true);img.removeAttribute('style');img.removeAttribute('width');"
        . "img.removeAttribute('height');slot.replaceWith(img);}"
        // WHMCS "Powered by WHMCompleteSolution" line: remove wherever the
        // template put it (small leaf elements only, so no real content
        // container can ever match).
        . "document.querySelectorAll('p,div,span,small,a').forEach(function(el){"
        . "var t=(el.textContent||'').trim();"
        . "if(t.length<60&&/WHMCompleteSolution/i.test(t)){el.remove();}"
        . "});"
        // Knowledgebase search: removed on every portal page (CSS in
        // portal-dark.css is the first line of defence; this catches
        // markup the selectors miss).
        . "document.querySelectorAll('input[placeholder*=\"knowledgebase\" i]').forEach(function(inp){"
        . "var box=inp.closest('form,.search-container,section,div');"
        . "if(box){box.remove();}else{inp.remove();}"
        . "});"
        // (The old one-row header relocation lived here; the theme header
        // is now replaced wholesale by the injected .kx-pnav bar — see the
        // dedicated hook below.)
        . "});</script>";
});

/**
 * Guests browsing sales/marketing pages don't need checkout furniture:
 * the header cart widget only renders for visitors on actual cart/store
 * pages (and always for logged-in clients, whose cart can hold items).
 */
add_hook('ClientAreaHeadOutput', 7, function () {
    if (!empty($_SESSION['uid'])) {
        return '';
    }
    $path = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
    if (str_contains($path, 'cart.php') || str_contains($path, '/store/')) {
        return '';
    }

    return '<style>'
        . '#header a[href*="cart.php"],.app-header a[href*="cart.php"],'
        . 'nav a[href*="cart.php"],.kx-onerow a[href*="cart.php"]{display:none !important}'
        . '</style>'
        . "<script>document.addEventListener('DOMContentLoaded',function(){"
        . "document.querySelectorAll('a[href*=\"cart.php\"]').forEach(function(a){"
        . "if(a.closest('#header,.app-header,nav,.navbar')){a.style.display='none';}"
        . "});});</script>";
});

/**
 * Split main nav: logged-in clients keep the stock portal nav (Services,
 * Billing, Support, Open Ticket); visitors get a marketing nav built live
 * from the visible product groups (store slug links when present) plus
 * Contact. Groups whose name contains "addon" are skipped.
 */
add_hook('ClientAreaPrimaryNavbar', 1, function ($primaryNavbar) {
    if (!empty($_SESSION['uid'])) {
        return;
    }

    try {
        foreach (array_keys((array) $primaryNavbar->getChildren()) as $name) {
            if (strcasecmp((string) $name, 'Home') !== 0) {
                $primaryNavbar->removeChild($name);
            }
        }

        // Mirror the section-shell nav exactly so guests see the same
        // menu on WHMCS pages as on /personal and /business.
        $primaryNavbar->addChild('kxPersonal', [
            'label' => 'Personal',
            'uri' => '/',
            'order' => 10,
        ]);
        $primaryNavbar->addChild('kxBusiness', [
            'label' => 'Business',
            'uri' => '/business/',
            'order' => 20,
        ]);
        $primaryNavbar->addChild('kxContact', [
            'label' => 'Contact',
            'uri' => '/contact/',
            'order' => 30,
        ]);
    } catch (\Throwable $e) {
        // Any failure leaves the stock nav in place.
    }
});

/**
 * Personal is the site's front page: for visitors the portal homepage
 * renders the Personal section directly — same look and behaviour as
 * /personal/ but the URL stays clean at /. Logged-in clients keep the
 * portal homepage.
 */
add_hook('ClientAreaPageHome', 1, function () {
    if (!empty($_SESSION['uid'])) {
        return;
    }
    require_once __DIR__ . '/pages/site-shell.php';
    kx_site_render('residential');
    exit;
});

/**
 * NBN identifiers on invoice lines: for every Hosting line that belongs to
 * a linked Virtutel service, append the AVC ID and the service address to
 * the item description. WHMCS already prints the domain (= AVC) inline,
 * but the description is what appears bold on the PDF/email — the AVC and
 * address make the line self-identifying for customers with multiple
 * services and for churn paperwork.
 */
add_hook('InvoiceCreation', 1, function ($vars) {
    try {
        $invoiceId = (int) ($vars['invoiceid'] ?? 0);
        if ($invoiceId === 0) {
            return;
        }

        $items = Capsule::table('tblinvoiceitems')
            ->where('invoiceid', $invoiceId)
            ->where('type', 'Hosting')
            ->where('relid', '>', 0)
            ->get(['id', 'relid', 'description']);
        if (count($items) === 0) {
            return;
        }

        $links = Capsule::table('mod_virtutel_services')
            ->whereIn('whmcs_service_id', $items->pluck('relid')->map(fn ($v) => (int) $v)->all())
            ->whereNotNull('avc_id')->where('avc_id', '!=', '')
            ->get()
            ->keyBy('whmcs_service_id');

        foreach ($items as $item) {
            $link = $links[(int) $item->relid] ?? null;
            if ($link === null) {
                continue;
            }

            $extra = [];
            $avc = (string) $link->avc_id;
            $address = trim((string) ($link->service_address ?? ''));
            if ($avc !== '' && stripos((string) $item->description, $avc) === false) {
                $extra[] = 'AVC: ' . $avc;
            }
            if ($address !== '' && stripos((string) $item->description, $address) === false) {
                $extra[] = $address;
            }
            if ($extra === []) {
                continue;
            }

            Capsule::table('tblinvoiceitems')->where('id', $item->id)->update([
                'description' => rtrim((string) $item->description) . "\n" . implode(' — ', $extra),
            ]);
        }
    } catch (\Throwable $e) {
        // Invoice generation must never fail because of decoration.
    }
});
