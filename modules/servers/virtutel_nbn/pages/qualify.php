<?php

/**
 * Customer-facing NBN address qualification page.
 *
 * Self-contained (inline CSS/JS, no template dependencies) so it can be
 * linked directly or embedded in an iframe on the marketing site:
 *   https://<domain>/modules/servers/virtutel_nbn/pages/qualify.php
 * All data comes from qualify-api.php in the same directory.
 */

require_once __DIR__ . '/../../../../init.php';

// Static page — release the session lock so it never queues behind other
// requests from the same visitor.
if (function_exists('session_write_close')) {
    @session_write_close();
}

// Google Places key from the addon settings (Addons -> Virtutel NBN Tools
// -> Configure). Empty key = plain text search fallback.
$placesKey = '';
try {
    $placesKey = (string) (WHMCS\Database\Capsule::table('tbladdonmodules')
        ->where('module', 'virtutel_nbn_admin')
        ->where('setting', 'google_places_key')
        ->value('value') ?? '');
} catch (\Throwable $e) {
    // table missing pre-activation — fall back silently
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Check NBN availability at your address</title>
<!-- When embedded in the store page, order links must replace the whole
     page, not load the cart inside the frame. Harmless standalone. -->
<base target="_top">
<script>(function(){var q=new URLSearchParams(location.search);var t=q.get("theme");if(t==="dark")document.documentElement.classList.add("vt-dark");if(t==="light")document.documentElement.classList.add("vt-light");if(q.get("embed"))document.documentElement.classList.add("vt-embed");})();</script>
<style>
  :root {
    --brand:#1a5fd0; --ok:#1d9e55; --warn:#c77c11; --bad:#c0392b; --ink:#222; --muted:#667;
    --page:#f5f7fb; --card:#fff; --line:#dde3ee; --input:#fff; --chip:#eef3fd; --chipline:#c9d8f6;
    --okbg:#e7f6ec; --okline:#b7e3c6; --oktext:#177a43;
    --warnbg:#fdf1e0; --warnline:#f0d9a8; --warntext:#a3690e;
    --badbg:#fdecea; --badline:#f3b6b0;
  }
  /* Dark palette: follows the OS setting, or force with ?theme=dark (for
     embedding in the dark portal) / ?theme=light. */
  html.vt-dark {
    /* Chrome paints an opaque white canvas behind a transparent iframe
       whose color-scheme differs from the embedding page's (the dark
       portal declares color-scheme: dark) — match it or the embed gets
       a white halo. */
    color-scheme: dark;
    --brand:#4d8dff; --ok:#2fbf71; --warn:#e2a336; --bad:#e2564a; --ink:#e6e9f2; --muted:#98a2b8;
    --page:#0f1420; --card:#171e2e; --line:#2a3347; --input:#0a0e18; --chip:#1e2739; --chipline:#2a3347;
    --okbg:#12301f; --okline:#1d5c38; --oktext:#7fdcaa;
    --warnbg:#372a10; --warnline:#6b531f; --warntext:#ecc575;
    --badbg:#3a1512; --badline:#722a24;
  }
  @media (prefers-color-scheme: dark) {
    html:not(.vt-light) {
      color-scheme: dark;
      --brand:#4d8dff; --ok:#2fbf71; --warn:#e2a336; --bad:#e2564a; --ink:#e6e9f2; --muted:#98a2b8;
      --page:#0f1420; --card:#171e2e; --line:#2a3347; --input:#0a0e18; --chip:#1e2739; --chipline:#2a3347;
      --okbg:#12301f; --okline:#1d5c38; --oktext:#7fdcaa;
      --warnbg:#372a10; --warnline:#6b531f; --warntext:#ecc575;
      --badbg:#3a1512; --badline:#722a24;
    }
  }
  /* Embedded in the WHMCS store page: blend in — no own heading,
     transparent background, full width. */
  html.vt-embed, html.vt-embed body { background: transparent !important; }
  html.vt-embed .wrap { max-width: 100%; padding: 4px 2px 10px; }
  html.vt-embed h1, html.vt-embed p.lead { display: none; }
  * { box-sizing:border-box; }
  body { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
         color:var(--ink); margin:0; background:var(--page); }
  .wrap { max-width:640px; margin:0 auto; padding:28px 16px 60px; }
  h1 { font-size:26px; margin:0 0 6px; }
  p.lead { color:var(--muted); margin:0 0 22px; }
  .searchbox { display:flex; gap:8px; }
  /* phones: button drops below the input at full width instead of
     clipping off the right edge */
  @media (max-width:560px) {
    .searchbox { flex-wrap:wrap; }
    .searchbox .btn { width:100%; }
    .portmode { flex-wrap:wrap; }
    .portmode .modebtn { flex:1 1 auto; }
  }
  .searchbox input { flex:1; font-size:16px; padding:12px 14px; background:var(--input); color:var(--ink); border:1px solid var(--line);
                     border-radius:8px; outline:none; }
  .searchbox input:focus { border-color:var(--brand); }
  /* Google Places dropdown restyled to the page theme (vars cover both
     light and dark) instead of Google's stock white panel. */
  .pac-container { background:var(--card); border:1px solid var(--line); border-radius:10px;
                   box-shadow:0 18px 50px rgba(0,0,0,.35); font-family:inherit; margin-top:6px;
                   padding:4px 0; }
  .pac-item { border-top:1px solid var(--line); padding:9px 13px; color:var(--muted);
              cursor:pointer; font-size:13.5px; }
  .pac-item:first-child { border-top:0; }
  .pac-item:hover, .pac-item-selected { background:var(--chip); }
  .pac-item-query { color:var(--ink); font-size:14.5px; }
  .pac-matched { color:var(--brand); }
  html.vt-dark .pac-icon { filter:invert(.6); }
  html.vt-dark .pac-logo:after { filter:grayscale(1) invert(.8); }
  @media (prefers-color-scheme: dark) {
    html:not(.vt-light) .pac-icon { filter:invert(.6); }
    html:not(.vt-light) .pac-logo:after { filter:grayscale(1) invert(.8); }
  }
  .btn { font-size:16px; padding:12px 20px; border:0; border-radius:8px; background:var(--brand);
         color:#fff; cursor:pointer; }
  .btn:disabled { opacity:.6; cursor:wait; }
  .matches { margin:18px 0 0; padding:0; list-style:none; }
  /* Long unit lists (apartment towers): filter box + own scroll area so
     the page doesn't become a kilometre of buttons. */
  .matchfilter { width:100%; margin:14px 0 0; padding:11px 13px; font-size:15px;
                 background:var(--input); color:var(--ink); border:1px solid var(--line);
                 border-radius:8px; outline:none; }
  .matchfilter:focus { border-color:var(--brand); }
  .matches.scrolling { margin-top:10px; max-height:330px; overflow-y:auto;
                       padding-right:4px; overscroll-behavior:contain; }
  .matches li { background:var(--card); border:1px solid var(--line); border-radius:8px; margin-bottom:8px; }
  .matches button { width:100%; text-align:left; background:none; border:0; padding:12px 14px;
                    font-size:15px; cursor:pointer; color:var(--ink); }
  .matches button:hover { background:var(--chip); }
  .card { background:var(--card); border:1px solid var(--line); border-radius:12px; padding:22px; margin-top:22px; }
  .status { display:inline-block; padding:5px 12px; border-radius:999px; color:#fff;
            font-size:13px; font-weight:600; letter-spacing:.3px; }
  .status.connect_now { background:var(--ok); }
  .status.transfer_ready { background:var(--ok); }
  .status.device_shipped { background:var(--ok); }
  .status.appointment { background:var(--warn); }
  .status.nbn_work { background:var(--warn); }
  .status.existing_service { background:var(--brand); }
  .status.not_available { background:var(--bad); }
  .tech { font-size:18px; font-weight:600; margin:12px 0 2px; }
  .desc { color:var(--muted); margin:10px 0 0; line-height:1.5; }
  .tiers { display:flex; flex-wrap:wrap; gap:8px; margin-top:16px; }
  .tier { background:var(--chip); color:var(--brand); border:1px solid var(--chipline); border-radius:8px;
          padding:8px 14px; font-weight:600; font-size:15px; }
  .plans { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; margin-top:18px; }
  .plan { border:1px solid var(--line); border-radius:10px; padding:16px; text-align:center; background:var(--card); }
  .plan .pname { font-weight:700; margin-bottom:2px; }
  .plan .pspeed { color:var(--muted); font-size:13px; margin-bottom:8px; }
  .plan .pprice { font-size:19px; font-weight:700; color:var(--brand); margin-bottom:12px; }
  .plan a.btn { display:inline-block; text-decoration:none; padding:9px 18px; font-size:15px; }
  .note { margin-top:14px; padding:10px 14px; background:var(--warnbg); border:1px solid var(--warnline); color:var(--warntext);
          border-radius:8px; font-size:14px; }
  .error { margin-top:16px; padding:12px 14px; background:var(--badbg); border:1px solid var(--badline);
           border-radius:8px; color:var(--bad); }
  .portmode { display:flex; gap:8px; margin-top:16px; }
  .modebtn { padding:8px 14px; font-size:14px; border-radius:8px; border:1px solid var(--line);
             background:var(--card); color:var(--muted); cursor:pointer; }
  .modebtn.active { background:var(--brand); border-color:var(--brand); color:#fff; }
  .portbox { margin-top:12px; padding:14px; border:1px solid var(--line); border-radius:10px; background:var(--card); }
  .portboxlabel { font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase;
                  letter-spacing:.5px; margin-bottom:10px; }
  .portrow { display:flex; flex-wrap:wrap; gap:10px; }
  .portbtn { min-width:86px; padding:10px 8px; border-radius:8px; cursor:pointer; text-align:center;
             font-weight:700; font-size:14px; border:2px solid transparent; }
  .portbtn .portstate { display:block; font-weight:400; font-size:11px; margin-top:3px; }
  .portbtn.free { background:var(--okbg); color:var(--oktext); border-color:var(--okline); }
  .portbtn.used { background:var(--warnbg); color:var(--warntext); border-color:var(--warnline); }
  .portbtn.selected { border-color:var(--brand); box-shadow:0 0 0 2px rgba(26,95,208,.25); }
  .churn { margin-top:18px; padding:16px; background:var(--chip); border:1px solid var(--chipline); border-radius:10px; }
  .churn.attention { border-color:var(--brand); box-shadow:0 0 0 2px rgba(26,95,208,.2); }
  .churn h3 { margin:0 0 6px; font-size:16px; }
  .churn p { margin:0 0 10px; color:var(--muted); font-size:14px; line-height:1.5; }
  .churn .row { display:flex; gap:8px; }
  .churn input[type=text] { flex:1; font-size:15px; padding:10px 12px; background:var(--input); color:var(--ink); border:1px solid var(--line); border-radius:8px; }
  .churn label.consent { display:flex; gap:8px; align-items:flex-start; font-size:13px; color:var(--muted);
                          margin:10px 0 0; line-height:1.45; }
  .churn .cherr { color:var(--bad); font-size:14px; margin-top:8px; }
  .spin { color:var(--muted); margin-top:16px; }
  .again { margin-top:18px; background:none; border:0; color:var(--brand); cursor:pointer;
           font-size:14px; text-decoration:underline; padding:0; }
</style>
</head>
<body>
<div class="wrap">
  <h1>Can you get NBN with us?</h1>
  <p class="lead">Enter your address to see the connection type, available speeds, and how fast we can get you online.</p>

  <form id="searchForm" class="searchbox" autocomplete="street-address">
    <input id="address" type="text" placeholder="e.g. 546 Flinders St Melbourne VIC 3000" required minlength="8">
    <button id="searchBtn" type="submit" class="btn">Check address</button>
  </form>

  <div id="out"></div>
</div>

<script>
var VT_PLACES_ENABLED = <?php echo $placesKey !== '' ? 'true' : 'false'; ?>;
(function () {
  var api = 'qualify-api.php';
  var out = document.getElementById('out');
  var btn = document.getElementById('searchBtn');
  var PARAMS = new URLSearchParams(location.search);
  var COMPACT = PARAMS.get('compact') === '1';
  // Reset link target: same mode, minus any carried address/LOC params.
  window.vtReset = function () {
    var p = new URLSearchParams(location.search);
    p.delete('q'); p.delete('vt_locid'); p.delete('vt_addr');
    var s = p.toString();
    location.replace('qualify.php' + (s ? '?' + s : ''));
  };

  function esc(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
  }

  function post(data) {
    return fetch(api, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data)
    }).then(function (r) { return r.json().then(function (j) { return {ok: r.ok, body: j}; }); });
  }

  function show(html) { out.innerHTML = html; }
  function fail(msg) { show('<div class="error">' + esc(msg) + '</div>'); }

  function renderMatches(res, fallbackLabel) {
    btn.disabled = false;
    if (!res.ok) { return fail(res.body.error || 'Search failed.'); }
    var m = res.body.matches || [];
    if (!m.length) { return fail('We couldn’t find that address in the NBN database. Try adding your suburb and postcode, or contact us and we’ll check manually.'); }
    // Auto-accept a single match ONLY when its street number agrees with
    // what was typed — the NBN fuzzy search happily returns the nearest
    // neighbour (type 23, get 21) and silently qualifying that would tell
    // the customer the wrong house is serviceable.
    var confirmOnly = false;
    if (m.length === 1) {
      var typedNum = ((fallbackLabel || '').match(/\d+[A-Za-z]?/) || [''])[0];
      var numOk = typedNum === '' || new RegExp('(^|[\\s/])' + typedNum + '([\\s/,]|$)', 'i')
        .test(m[0].address || '');
      if (numOk) { return qualify(m[0].locId, m[0].address || fallbackLabel); }
      confirmOnly = true;
    }
    var html = '<p class="spin">' + (confirmOnly
      ? 'We couldn&rsquo;t find that exact address &mdash; this is the closest match in the NBN database. Please check it&rsquo;s yours:'
      : 'Select your exact address / unit:') + '</p>';
    if (m.length > 8) {
      html += '<input type="text" id="matchFilter" class="matchfilter" '
        + 'placeholder="Type your unit / shop number to narrow it down, e.g. 1506">';
    }
    html += '<ul class="matches' + (m.length > 8 ? ' scrolling' : '') + '" id="matchList">';
    m.forEach(function (row) {
      html += '<li><button type="button" data-loc="' + esc(row.locId) + '">' + esc(row.address) + '</button></li>';
    });
    html += '</ul>';
    if (m.length > 8) { html += '<div id="matchCount" class="spin" style="font-size:13px"></div>'; }
    show(html);

    var filter = document.getElementById('matchFilter');
    if (filter) {
      var items = Array.prototype.slice.call(document.getElementById('matchList').children);
      var count = document.getElementById('matchCount');
      function applyFilter() {
        var terms = filter.value.trim().toUpperCase().split(/\s+/).filter(Boolean);
        var shown = 0;
        items.forEach(function (li) {
          var t = li.textContent.toUpperCase();
          var hit = terms.every(function (w) { return t.indexOf(w) !== -1; });
          li.style.display = hit ? '' : 'none';
          if (hit) { shown++; }
        });
        count.textContent = shown === items.length
          ? items.length + ' addresses found'
          : shown + ' of ' + items.length + ' addresses match';
      }
      filter.addEventListener('input', applyFilter);
      applyFilter();
      filter.focus();
    }
    out.querySelectorAll('button[data-loc]').forEach(function (b) {
      b.addEventListener('click', function () { qualify(b.getAttribute('data-loc'), b.textContent); });
    });
  }

  function searchByText(address) {
    btn.disabled = true;
    show('<p class="spin">Searching the NBN address database&hellip;</p>');
    post({action: 'search', address: address})
      .then(function (res) { renderMatches(res, address); })
      .catch(function () { btn.disabled = false; fail('Something went wrong — please try again.'); });
  }

  function searchByCoords(lat, lng, label) {
    btn.disabled = true;
    show('<p class="spin">Matching your address in the NBN database&hellip;</p>');
    post({action: 'search', lat: lat, lng: lng})
      .then(function (res) { renderMatches(res, label); })
      .catch(function () { btn.disabled = false; fail('Something went wrong — please try again.'); });
  }

  document.getElementById('searchForm').addEventListener('submit', function (ev) {
    ev.preventDefault();
    var address = document.getElementById('address').value.trim();
    if (address.length < 8) { return; }
    searchByText(address);
  });

  // Google Places autocomplete: pick address -> lat/lng -> NBN coordinate
  // search (exact premises incl. units, no address-string guesswork).
  window.vtInitPlaces = function () {
    var input = document.getElementById('address');
    var ac = new google.maps.places.Autocomplete(input, {
      componentRestrictions: {country: 'au'},
      types: ['address'],
      fields: ['geometry', 'formatted_address']
    });
    ac.addListener('place_changed', function () {
      var place = ac.getPlace();
      if (place && place.geometry && place.geometry.location) {
        searchByCoords(
          place.geometry.location.lat(),
          place.geometry.location.lng(),
          place.formatted_address || input.value
        );
      }
    });
    // Stop Enter from submitting the form while the dropdown is open.
    input.addEventListener('keydown', function (ev) {
      if (ev.key === 'Enter' && document.querySelector('.pac-container:not([style*="display: none"])')) {
        ev.preventDefault();
      }
    });
  };

  function qualify(locId, label, avcId) {
    show('<p class="spin">Checking what’s available at ' + esc(label) + '&hellip;</p>');
    var req = {action: 'qualify', locId: locId};
    if (avcId) { req.avcId = avcId; }
    post(req).then(function (res) {
      if (!res.ok) { return fail(res.body.error || 'Check failed.'); }
      var q = res.body;

      // Compact mode (landing-page hero): just the verdict + a View
      // Plans button into the onboarding page — no ports, no plans here.
      if (COMPACT) {
        var ok = q.readiness && q.readiness.code !== 'not_available';
        var go = '/personal/nbn/signup/?vt_locid=' + encodeURIComponent(locId)
          + '&vt_addr=' + encodeURIComponent(label);
        show('<div class="card" style="text-align:center;padding:28px 22px 20px">'
          + '<span class="status ' + esc(q.readiness.code) + '">' + esc(q.readiness.label) + '</span>'
          + (ok
            ? '<h3 style="margin:16px 0 4px;font-size:21px">Good news &mdash; we can service your address!</h3>'
            : '<h3 style="margin:16px 0 4px;font-size:21px">We can&rsquo;t connect this address just yet</h3>')
          + '<div style="color:var(--muted,#667);font-size:13.5px">' + esc(label) + '</div>'
          + (ok
            ? '<p class="desc" style="margin:10px 0 0">' + esc(q.technology)
              + (q.readiness.code === 'existing_service'
                ? ' &mdash; there&rsquo;s already an active service here, and switching to us happens remotely (no technician visit).'
                : ' is available at your place.') + '</p>'
              + '<div style="margin:20px 0 0"><a class="btn" style="display:inline-block;'
              + 'text-decoration:none;padding:13px 34px;font-size:16px" href="' + esc(go)
              + '">View plans for my address &rarr;</a></div>'
            : '<p class="desc" style="margin:10px 0 0">' + esc(q.readiness.description) + '</p>'
              + '<div style="margin:20px 0 0"><a class="btn" style="display:inline-block;'
              + 'text-decoration:none;padding:13px 34px;font-size:16px" href="/contact.php">Contact us</a></div>')
          + '<div style="margin-top:6px"><button type="button" class="again" '
          + 'onclick="vtReset()">Check a different address</button></div>'
          + '</div>');
        return;
      }

      var html = '<div class="card">'
        + '<span class="status ' + esc(q.readiness.code) + '">' + esc(q.readiness.label) + '</span>'
        + '<div class="tech">' + esc(q.technology) + '</div>'
        + '<div style="color:#667;font-size:13px">' + esc(label) + '</div>'
        + '<p class="desc">' + esc(q.readiness.description) + '</p>';

      var hasPortMap = q.portMap && q.portMap.length > 0;
      var isCopper = hasPortMap && q.portMap.every(function (b) { return b.copper; });
      var portNoun = isCopper ? 'copper pair' : 'port';
      if (hasPortMap) {
        var totalPorts = 0;
        q.portMap.forEach(function (b) { totalPorts += b.ports.length; });
        var anyFree = (q.freePorts || 0) > 0;

        html += '<div style="margin-top:16px">'
          + '<p class="desc" style="margin:0">' + (q.freePorts || 0) + ' of ' + totalPorts
          + (isCopper ? ' copper pairs available at this address.'
            : ' ports available on the NBN equipment at this address.') + '</p>';

        if (anyFree) {
          html += '<div class="portmode">'
            + '<button type="button" id="modeAuto" class="modebtn active">Auto-select ' + portNoun + ' (recommended)</button>'
            + '<button type="button" id="modeManual" class="modebtn">Choose ' + portNoun + ' manually</button>'
            + '</div>';
        }

        html += '<div id="portDiagram" style="' + (anyFree ? 'display:none;' : '') + 'margin-top:4px">';
        q.portMap.forEach(function (box) {
          html += '<div class="portbox"><div class="portboxlabel">' + esc(box.label) + '</div><div class="portrow">';
          box.ports.forEach(function (p) {
            html += '<button type="button" class="portbtn ' + (p.free ? 'free' : 'used') + '"'
              + ' data-free="' + (p.free ? '1' : '0') + '"'
              + (p.copper ? ' data-cpi="1" title="' + esc(p.id || p.portId) + '"' : '')
              + ' data-ntd="' + esc(p.ntdId) + '" data-port="' + esc(p.portId) + '">'
              + esc(p.label)
              + '<span class="portstate">' + (p.free ? 'Available' : 'In use') + '</span>'
              + '</button>';
          });
          html += '</div></div>';
        });
        html += '<div id="portHint" class="desc" style="font-size:13px"></div></div></div>';
      }

      if (q.plans && q.plans.length && q.readiness.code !== 'not_available') {
        html += '<div class="plans">';
        q.plans.forEach(function (p) {
          var singleCopper = q.copperPair
            && !(q.portMap || []).some(function (b) { return b.copper; });
          var orderUrl = p.orderUrl + '&vt_addr=' + encodeURIComponent(label)
            + '&vt_tech=' + encodeURIComponent(q.technology)
            + (singleCopper ? '&vt_cpi=' + encodeURIComponent(q.copperPair.id) + '&vt_auto=1' : '');
          html += '<div class="plan">'
            + '<div class="pname">' + esc(p.name) + '</div>'
            + '<div class="pspeed">' + esc(p.speedLabel) + '</div>'
            + '<div class="pprice">' + esc(p.price) + '</div>'
            + '<a class="btn" href="' + esc(orderUrl) + '">Order now</a>'
            + '</div>';
        });
        html += '</div>';
      } else if (q.tiers && q.tiers.length) {
        html += '<div class="tiers">';
        q.tiers.forEach(function (t) { html += '<span class="tier">' + esc(t.label) + '</span>'; });
        html += '</div>';
      }
      if (q.newDevelopmentCharge) {
        html += '<div class="note">This address is in a new development area — NBN’s one-off New Development Charge may apply.</div>';
      }

      // Transfer (churn) path: offer whenever the site is orderable and the
      // transfer hasn't already been validated. Collapsed to a one-line link
      // when a clean new connection is the primary path; expanded when the
      // equipment is full or a transfer attempt failed.
      var churnFailed = q.churn && !q.churn.matched;
      var churnExpanded = churnFailed || q.readiness.code === 'existing_service';
      if (q.readiness.code !== 'not_available' && q.readiness.code !== 'transfer_ready') {
        if (!churnExpanded) {
          html += '<p style="margin:16px 0 0"><button type="button" id="churnToggle" class="again" style="margin:0">'
            + 'Switching from another provider? Transfer your existing service instead &rarr;</button></p>';
        }
        html += '<div class="churn"' + (churnExpanded ? '' : ' style="display:none"') + '><h3>Already have NBN at this address?</h3>'
          + '<p>Switching from another provider? Transfers are done remotely — '
          + (q.hasExistingService ? 'and since the NBN equipment here is already in use, this is usually the fastest way to connect. ' : '')
          + 'Grab the <strong>AVC ID</strong> from your current provider (it looks like AVC123456789012 — '
          + 'check their portal, app, or a recent invoice; the last 5 digits are enough).</p>'
          + '<div class="row"><input type="text" id="avcInput" maxlength="15" placeholder="AVC123456789012 or last 5 digits"'
          + (churnFailed ? ' value="' + esc(q.churn.attempted || '') + '"' : '') + '>'
          + '<button type="button" class="btn" id="avcBtn">Check transfer</button></div>'
          + '<label class="consent"><input type="checkbox" id="avcConsent"> I’m the account holder (or authorised by them) '
          + 'and I authorise this provider to transfer the service at this address from my current provider.</label>'
          + (churnFailed ? '<div class="cherr">' + esc(q.churn.error) + '</div>' : '')
          + '</div>';
      }

      html += '<button type="button" class="again" onclick="vtReset()">Check a different address</button></div>';
      show(html);

      // Port map interactions: green ports/pairs select for the order;
      // orange ones steer into the transfer flow; the mode toggle returns
      // to auto-pick. The choice rides along on the order links — in auto
      // mode too, so the cart can name what was picked. sel is null (clear),
      // {cpi, label, auto} for copper, or {ntd, port, label, auto}.
      function updatePortParams(sel) {
        out.querySelectorAll('.plan a.btn').forEach(function (a) {
          var url = a.getAttribute('href')
            .replace(/&vt_ntd=[^&]*/g, '').replace(/&vt_port=[^&]*/g, '')
            .replace(/&vt_portlabel=[^&]*/g, '').replace(/&vt_auto=[^&]*/g, '')
            .replace(/&vt_cpi=[^&]*/g, '');
          if (sel && sel.cpi) {
            url += '&vt_cpi=' + encodeURIComponent(sel.cpi) + (sel.auto ? '&vt_auto=1' : '');
          } else if (sel && sel.ntd && sel.port) {
            url += '&vt_ntd=' + encodeURIComponent(sel.ntd) + '&vt_port=' + encodeURIComponent(sel.port)
              + (sel.label ? '&vt_portlabel=' + encodeURIComponent(sel.label) : '')
              + (sel.auto ? '&vt_auto=1' : '');
          }
          a.setAttribute('href', url);
        });
      }

      // Auto-select picks the first free port/pair; ride it on the order
      // links up front so checkout shows which one it will be.
      var autoPort = null;
      (q.portMap || []).some(function (box) {
        return (box.ports || []).some(function (p) {
          if (p.free) {
            autoPort = p.copper
              ? {cpi: p.portId, label: p.label, auto: true}
              : {ntd: box.ntdId || p.ntdId, port: p.portId, label: p.label, auto: true};
            return true;
          }
          return false;
        });
      });
      function applyAutoPort() {
        if (autoPort) { updatePortParams(autoPort); }
      }
      applyAutoPort();

      // Collapsed transfer box expands on demand.
      var churnToggle = document.getElementById('churnToggle');
      function expandChurn() {
        var box = out.querySelector('.churn');
        if (box) { box.style.display = 'block'; }
        if (churnToggle) { churnToggle.parentNode.style.display = 'none'; }
        return box;
      }
      // Choosing a fresh connection path hides the transfer box (and brings
      // back its collapsed link when one exists).
      function collapseChurn() {
        var box = out.querySelector('.churn');
        if (box) { box.style.display = 'none'; box.classList.remove('attention'); }
        if (churnToggle) { churnToggle.parentNode.style.display = ''; }
      }
      if (churnToggle) {
        churnToggle.addEventListener('click', function () {
          var box = expandChurn();
          if (box) { box.scrollIntoView({behavior: 'smooth', block: 'center'}); }
        });
      }

      var diagram = document.getElementById('portDiagram');
      if (diagram) {
        var hint = document.getElementById('portHint');
        var modeAuto = document.getElementById('modeAuto');
        var modeManual = document.getElementById('modeManual');
        var churnBox = out.querySelector('.churn');

        function clearSelection() {
          diagram.querySelectorAll('.portbtn.selected').forEach(function (b) { b.classList.remove('selected'); });
          updatePortParams(null);
          if (hint) { hint.textContent = ''; }
          if (churnBox) { churnBox.classList.remove('attention'); }
        }

        if (modeAuto && modeManual) {
          modeManual.addEventListener('click', function () {
            modeManual.classList.add('active'); modeAuto.classList.remove('active');
            diagram.style.display = 'block';
          });
          modeAuto.addEventListener('click', function () {
            modeAuto.classList.add('active'); modeManual.classList.remove('active');
            diagram.style.display = 'none';
            clearSelection();
            collapseChurn();
            applyAutoPort();
          });
        }

        diagram.querySelectorAll('.portbtn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            if (btn.getAttribute('data-free') === '1') {
              clearSelection();
              collapseChurn();
              btn.classList.add('selected');
              var tileLabel = (btn.firstChild.textContent || btn.textContent || '').trim();
              updatePortParams(btn.getAttribute('data-cpi') === '1'
                ? {cpi: btn.getAttribute('data-port'), label: tileLabel, auto: false}
                : {ntd: btn.getAttribute('data-ntd'), port: btn.getAttribute('data-port'),
                   label: tileLabel, auto: false});
              if (hint) {
                hint.innerHTML = 'Your new connection will use <strong>' + esc(tileLabel)
                  + (btn.getAttribute('data-cpi') === '1'
                    ? ' (' + esc(btn.getAttribute('data-port')) + ')' : '')
                  + '</strong>.';
              }
            } else {
              clearSelection();
              btn.classList.add('selected');
              if (hint) {
                hint.innerHTML = 'That port is carrying an active service. To take it over, '
                  + '<strong>transfer that service</strong> — enter its AVC ID below and we\'ll '
                  + 'match it to this port automatically.';
              }
              if (churnBox) {
                expandChurn();
                churnBox.classList.add('attention');
                churnBox.scrollIntoView({behavior: 'smooth', block: 'center'});
                var avcInput = document.getElementById('avcInput');
                if (avcInput) { avcInput.focus(); }
              }
            }
          });
        });
      }

      var avcBtn = document.getElementById('avcBtn');
      if (avcBtn) {
        avcBtn.addEventListener('click', function () {
          var avc = document.getElementById('avcInput').value.trim().toUpperCase();
          var consent = document.getElementById('avcConsent').checked;
          if (!/^(AVC\d{12}|\d{5})$/.test(avc)) {
            document.getElementById('avcInput').style.borderColor = '#c0392b';
            return;
          }
          if (!consent) {
            document.getElementById('avcConsent').parentNode.style.color = '#c0392b';
            return;
          }
          qualify(locId, label, avc);
        });
      }
    }).catch(function () { fail('Something went wrong — please try again.'); });
  }

  // Onboarding hand-off: arriving with ?vt_locid= (and vt_addr=) skips the
  // search and qualifies immediately — the landing page's compact checker
  // links here with the address it already validated.
  var bootLoc = (PARAMS.get('vt_locid') || '').toUpperCase();
  var bootAddr = PARAMS.get('vt_addr') || '';
  var bootQ = (PARAMS.get('q') || '').trim();
  if (/^LOC\d{9,15}$/.test(bootLoc)) {
    var addrInput = document.getElementById('address');
    if (addrInput && bootAddr) { addrInput.value = bootAddr; }
    qualify(bootLoc, bootAddr || bootLoc);
  } else if (bootQ.length >= 8) {
    // Popup mode: the landing page hands the typed address over.
    var qInput = document.getElementById('address');
    if (qInput) { qInput.value = bootQ; }
    searchByText(bootQ);
  } else if (COMPACT) {
    // Popup opened without an address (plan card CTA): start typing.
    var fInput = document.getElementById('address');
    if (fInput) { fInput.focus(); }
  }
})();
</script>
<?php if ($placesKey !== ''): ?>
<script async
  src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars(rawurlencode($placesKey), ENT_QUOTES); ?>&libraries=places&region=AU&callback=vtInitPlaces">
</script>
<?php endif; ?>
</body>
</html>
