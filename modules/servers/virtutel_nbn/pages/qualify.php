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
  html.vt-embed .wrap { max-width: 100%; padding: 4px 2px 30px; }
  html.vt-embed h1, html.vt-embed p.lead { display: none; }
  * { box-sizing:border-box; }
  body { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
         color:var(--ink); margin:0; background:var(--page); }
  .wrap { max-width:640px; margin:0 auto; padding:28px 16px 60px; }
  h1 { font-size:26px; margin:0 0 6px; }
  p.lead { color:var(--muted); margin:0 0 22px; }
  .searchbox { display:flex; gap:8px; }
  .searchbox input { flex:1; font-size:16px; padding:12px 14px; background:var(--input); color:var(--ink); border:1px solid var(--line);
                     border-radius:8px; outline:none; }
  .searchbox input:focus { border-color:var(--brand); }
  .btn { font-size:16px; padding:12px 20px; border:0; border-radius:8px; background:var(--brand);
         color:#fff; cursor:pointer; }
  .btn:disabled { opacity:.6; cursor:wait; }
  .matches { margin:18px 0 0; padding:0; list-style:none; }
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
    if (m.length === 1) { return qualify(m[0].locId, m[0].address || fallbackLabel); }
    var html = '<p class="spin">Select your exact address / unit:</p><ul class="matches">';
    m.forEach(function (row) {
      html += '<li><button type="button" data-loc="' + esc(row.locId) + '">' + esc(row.address) + '</button></li>';
    });
    html += '</ul>';
    show(html);
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
      var html = '<div class="card">'
        + '<span class="status ' + esc(q.readiness.code) + '">' + esc(q.readiness.label) + '</span>'
        + '<div class="tech">' + esc(q.technology) + '</div>'
        + '<div style="color:#667;font-size:13px">' + esc(label) + '</div>'
        + '<p class="desc">' + esc(q.readiness.description) + '</p>';

      var hasPortMap = q.portMap && q.portMap.length > 0;
      if (hasPortMap) {
        var totalPorts = 0;
        q.portMap.forEach(function (b) { totalPorts += b.ports.length; });
        var anyFree = (q.freePorts || 0) > 0;

        html += '<div style="margin-top:16px">'
          + '<p class="desc" style="margin:0">' + (q.freePorts || 0) + ' of ' + totalPorts
          + ' ports available on the NBN equipment at this address.</p>';

        if (anyFree) {
          html += '<div class="portmode">'
            + '<button type="button" id="modeAuto" class="modebtn active">Auto-select port (recommended)</button>'
            + '<button type="button" id="modeManual" class="modebtn">Choose port manually</button>'
            + '</div>';
        }

        html += '<div id="portDiagram" style="' + (anyFree ? 'display:none;' : '') + 'margin-top:4px">';
        q.portMap.forEach(function (box) {
          html += '<div class="portbox"><div class="portboxlabel">' + esc(box.label) + '</div><div class="portrow">';
          box.ports.forEach(function (p) {
            html += '<button type="button" class="portbtn ' + (p.free ? 'free' : 'used') + '"'
              + ' data-free="' + (p.free ? '1' : '0') + '"'
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
          var orderUrl = p.orderUrl + '&vt_addr=' + encodeURIComponent(label)
            + '&vt_tech=' + encodeURIComponent(q.technology);
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

      html += '<button type="button" class="again" onclick="location.reload()">Check a different address</button></div>';
      show(html);

      // Port map interactions: green ports select for the order; orange
      // ports steer into the transfer flow; the mode toggle returns to
      // auto-pick. The chosen port rides along on the order links — in
      // auto mode too, so the cart can name the port we picked.
      function updatePortParams(ntd, port, label, auto) {
        out.querySelectorAll('.plan a.btn').forEach(function (a) {
          var url = a.getAttribute('href')
            .replace(/&vt_ntd=[^&]*/g, '').replace(/&vt_port=[^&]*/g, '')
            .replace(/&vt_portlabel=[^&]*/g, '').replace(/&vt_auto=[^&]*/g, '');
          if (ntd && port) {
            url += '&vt_ntd=' + encodeURIComponent(ntd) + '&vt_port=' + encodeURIComponent(port)
              + (label ? '&vt_portlabel=' + encodeURIComponent(label) : '')
              + (auto ? '&vt_auto=1' : '');
          }
          a.setAttribute('href', url);
        });
      }

      // The port auto-select picks the first free port; ride it on the
      // order links up front so checkout shows which port it will be.
      var autoPort = null;
      (q.portMap || []).some(function (box) {
        return (box.ports || []).some(function (p) {
          if (p.free) { autoPort = {ntd: box.ntdId || p.ntdId, port: p.portId, label: p.label}; return true; }
          return false;
        });
      });
      function applyAutoPort() {
        if (autoPort) { updatePortParams(autoPort.ntd, autoPort.port, autoPort.label, true); }
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
          updatePortParams(null, null);
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
            applyAutoPort();
          });
        }

        diagram.querySelectorAll('.portbtn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            if (btn.getAttribute('data-free') === '1') {
              clearSelection();
              btn.classList.add('selected');
              updatePortParams(btn.getAttribute('data-ntd'), btn.getAttribute('data-port'),
                (btn.firstChild.textContent || btn.textContent || '').trim(), false);
              if (hint) {
                hint.innerHTML = 'Your new connection will use <strong>'
                  + esc(btn.firstChild.textContent || btn.textContent) + '</strong>.';
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
})();
</script>
<?php if ($placesKey !== ''): ?>
<script async
  src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars(rawurlencode($placesKey), ENT_QUOTES); ?>&libraries=places&region=AU&callback=vtInitPlaces">
</script>
<?php endif; ?>
</body>
</html>
