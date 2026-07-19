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
<style>
  :root { --brand:#1a5fd0; --ok:#1d9e55; --warn:#c77c11; --bad:#c0392b; --ink:#222; --muted:#667; }
  * { box-sizing:border-box; }
  body { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
         color:var(--ink); margin:0; background:#f5f7fb; }
  .wrap { max-width:640px; margin:0 auto; padding:28px 16px 60px; }
  h1 { font-size:26px; margin:0 0 6px; }
  p.lead { color:var(--muted); margin:0 0 22px; }
  .searchbox { display:flex; gap:8px; }
  .searchbox input { flex:1; font-size:16px; padding:12px 14px; border:1px solid #c8cfdb;
                     border-radius:8px; outline:none; }
  .searchbox input:focus { border-color:var(--brand); }
  .btn { font-size:16px; padding:12px 20px; border:0; border-radius:8px; background:var(--brand);
         color:#fff; cursor:pointer; }
  .btn:disabled { opacity:.6; cursor:wait; }
  .matches { margin:18px 0 0; padding:0; list-style:none; }
  .matches li { background:#fff; border:1px solid #dde3ee; border-radius:8px; margin-bottom:8px; }
  .matches button { width:100%; text-align:left; background:none; border:0; padding:12px 14px;
                    font-size:15px; cursor:pointer; }
  .matches button:hover { background:#eef3fd; }
  .card { background:#fff; border:1px solid #dde3ee; border-radius:12px; padding:22px; margin-top:22px; }
  .status { display:inline-block; padding:5px 12px; border-radius:999px; color:#fff;
            font-size:13px; font-weight:600; letter-spacing:.3px; }
  .status.connect_now { background:var(--ok); }
  .status.device_shipped { background:var(--ok); }
  .status.appointment { background:var(--warn); }
  .status.nbn_work { background:var(--warn); }
  .status.not_available { background:var(--bad); }
  .tech { font-size:18px; font-weight:600; margin:12px 0 2px; }
  .desc { color:var(--muted); margin:10px 0 0; line-height:1.5; }
  .tiers { display:flex; flex-wrap:wrap; gap:8px; margin-top:16px; }
  .tier { background:#eef3fd; color:var(--brand); border:1px solid #c9d8f6; border-radius:8px;
          padding:8px 14px; font-weight:600; font-size:15px; }
  .note { margin-top:14px; padding:10px 14px; background:#fff7e8; border:1px solid #f0d9a8;
          border-radius:8px; font-size:14px; }
  .error { margin-top:16px; padding:12px 14px; background:#fdecea; border:1px solid #f3b6b0;
           border-radius:8px; color:var(--bad); }
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

  function qualify(locId, label) {
    show('<p class="spin">Checking what’s available at ' + esc(label) + '&hellip;</p>');
    post({action: 'qualify', locId: locId}).then(function (res) {
      if (!res.ok) { return fail(res.body.error || 'Check failed.'); }
      var q = res.body;
      var html = '<div class="card">'
        + '<span class="status ' + esc(q.readiness.code) + '">' + esc(q.readiness.label) + '</span>'
        + '<div class="tech">' + esc(q.technology) + '</div>'
        + '<div style="color:#667;font-size:13px">' + esc(label) + '</div>'
        + '<p class="desc">' + esc(q.readiness.description) + '</p>';

      if (q.tiers && q.tiers.length) {
        html += '<div class="tiers">';
        q.tiers.forEach(function (t) { html += '<span class="tier">' + esc(t.label) + '</span>'; });
        html += '</div>';
      }
      if (q.freePorts !== null && q.freePorts !== undefined && q.readiness.code === 'connect_now') {
        html += '<p class="desc">' + q.freePorts + ' spare port' + (q.freePorts === 1 ? '' : 's')
          + ' on the NBN equipment already installed at this address.</p>';
      }
      if (q.newDevelopmentCharge) {
        html += '<div class="note">This address is in a new development area — NBN’s one-off New Development Charge may apply.</div>';
      }
      html += '<button type="button" class="again" onclick="location.reload()">Check a different address</button></div>';
      show(html);
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
