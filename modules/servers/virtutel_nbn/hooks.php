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

    return '<link rel="stylesheet" href="/modules/servers/virtutel_nbn/pages/portal-dark.css?v=18">'
        . '<meta name="color-scheme" content="dark">';
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
        . "setInterval(function(){try{var h=f.contentDocument.documentElement.scrollHeight;"
        . "if(h>200&&Math.abs(h-f.offsetHeight)>8){f.style.height=h+'px';}}catch(e){}},400);"
        . "});</script>";
});
