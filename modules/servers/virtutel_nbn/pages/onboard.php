<?php

/**
 * Full-page signup walkthrough (the "big ISP" onboarding journey).
 *
 * The qualify page's Order buttons land here with the validated order
 * query string (?oq=pid=..&vt_locid=..&vt_ntd=..&...). This page walks the
 * customer through Details -> Contact -> Modem -> SMS Verify -> Review,
 * creates their WHMCS account via signup-api.php, then forwards to the
 * UNCHANGED order.php URL — so qualification/port/transfer capture works
 * exactly as before.
 *
 * The order URL is rebuilt server-side from an allowlisted query string,
 * never taken verbatim from the request — no open-redirect surface.
 */

use WHMCS\Database\Capsule;

require_once __DIR__ . '/../../../../init.php';

if (function_exists('session_write_close')) {
    @session_write_close();
}

$systemUrl = '';
try {
    $systemUrl = rtrim((string) (Capsule::table('tblconfiguration')
        ->where('setting', 'SystemURL')->value('value') ?? ''), '/');
} catch (\Throwable $e) {
    // pre-activation — page still renders, order link falls back relative
}

// Same logo the marketing nav and portal navbar show: uploaded file on
// disk first, LogoURL fallback normalised to an absolute path, text
// wordmark when neither exists (or the image 404s, via onerror).
$logoUrl = '';
foreach (['assets/img/logo.png', 'assets/img/logo.jpg'] as $cand) {
    if (is_file(__DIR__ . '/../../../../' . $cand)) {
        $logoUrl = '/' . $cand;
        break;
    }
}
if ($logoUrl === '') {
    try {
        $logoUrl = trim((string) (Capsule::table('tblconfiguration')
            ->where('setting', 'LogoURL')->value('value') ?? ''));
    } catch (\Throwable $e) {
        $logoUrl = '';
    }
    if ($logoUrl !== '' && !preg_match('#^(https?:)?//#i', $logoUrl) && $logoUrl[0] !== '/') {
        $logoUrl = '/' . $logoUrl;
    }
}

// Rebuild the order URL from an allowlist of SignupCapture keys.
$allowed = ['pid', 'vt_locid', 'vt_avc', 'vt_addr', 'vt_tech', 'vt_cpi',
    'vt_ntd', 'vt_port', 'vt_portlabel', 'vt_auto'];
parse_str((string) ($_GET['oq'] ?? ''), $rawQuery);
$clean = [];
foreach ($allowed as $key) {
    if (isset($rawQuery[$key]) && is_string($rawQuery[$key]) && $rawQuery[$key] !== '') {
        $clean[$key] = mb_substr($rawQuery[$key], 0, 200);
    }
}
$pid = (int) ($clean['pid'] ?? 0);
$orderUrl = $pid > 0
    ? ($systemUrl !== '' ? $systemUrl : '') . '/modules/servers/virtutel_nbn/pages/order.php?'
        . http_build_query($clean)
    : '';

$plan = mb_substr(trim((string) ($_GET['plan'] ?? '')), 0, 80);
$price = mb_substr(trim((string) ($_GET['price'] ?? '')), 0, 40);
$addr = mb_substr(trim((string) ($_GET['addr'] ?? ($clean['vt_addr'] ?? ''))), 0, 160);

$jsVar = static fn ($v) => json_encode($v, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Sign up — Korvix</title>
<script>(function(){var q=new URLSearchParams(location.search);var t=q.get("theme");if(t==="dark")document.documentElement.classList.add("vt-dark");if(t==="light")document.documentElement.classList.add("vt-light");})();</script>
<style>
  :root {
    --brand:#1a5fd0; --ok:#1d9e55; --warn:#c77c11; --bad:#c0392b; --ink:#222; --muted:#667;
    --page:#f5f7fb; --card:#fff; --line:#dde3ee; --input:#fff; --chip:#eef3fd; --chipline:#c9d8f6;
  }
  html.vt-dark {
    color-scheme: dark;
    --brand:#4d8dff; --ok:#2fbf71; --warn:#e2a336; --bad:#e2564a; --ink:#e6e9f2; --muted:#98a2b8;
    --page:#0f1420; --card:#171e2e; --line:#2a3347; --input:#0a0e18; --chip:#1e2739; --chipline:#2a3347;
  }
  @media (prefers-color-scheme: dark) {
    html:not(.vt-light) {
      color-scheme: dark;
      --brand:#4d8dff; --ok:#2fbf71; --warn:#e2a336; --bad:#e2564a; --ink:#e6e9f2; --muted:#98a2b8;
      --page:#0f1420; --card:#171e2e; --line:#2a3347; --input:#0a0e18; --chip:#1e2739; --chipline:#2a3347;
    }
  }
  :root { --logofilter:none; }
  html.vt-dark { --logofilter:invert(1) hue-rotate(180deg) brightness(1.05); }
  @media (prefers-color-scheme: dark) {
    html:not(.vt-light) { --logofilter:invert(1) hue-rotate(180deg) brightness(1.05); }
  }
  * { box-sizing:border-box; }
  html { -webkit-text-size-adjust:100%; text-size-adjust:100%; }
  body { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
         color:var(--ink); margin:0; background:var(--page); min-height:100vh;
         position:relative; overflow-x:hidden; }
  a { color:var(--brand); }

  /* Marketing-site texture: faint engineering dot-grid faded at the
     edges, plus two drifting corner glows behind everything. */
  body::before { content:''; position:fixed; inset:0; pointer-events:none; z-index:0;
    background-image:radial-gradient(rgba(122,146,200,.14) 1px, transparent 1.4px);
    background-size:26px 26px;
    -webkit-mask-image:radial-gradient(ellipse 90% 70% at 50% 0%, #000 30%, transparent 75%);
    mask-image:radial-gradient(ellipse 90% 70% at 50% 0%, #000 30%, transparent 75%); }
  .glow { position:fixed; border-radius:50%; filter:blur(100px); opacity:.26;
          pointer-events:none; z-index:0; }
  .g1 { width:440px; height:440px; background:#2b5cff; top:-180px; left:-120px;
        animation:kxDrift 16s ease-in-out infinite alternate; }
  .g2 { width:400px; height:400px; background:#7a5cff; top:-140px; right:-100px;
        animation:kxDrift 19s ease-in-out infinite alternate-reverse; }
  @keyframes kxDrift { from { transform:translate(0,0); } to { transform:translate(40px,26px); } }
  @media (prefers-reduced-motion: reduce) { .g1, .g2 { animation:none; } }
  html.vt-light body::before, html.vt-light .glow { opacity:.12; }

  .bar { display:flex; align-items:center; justify-content:space-between; gap:14px;
         max-width:960px; margin:0 auto; padding:18px 16px; position:relative; z-index:1; }
  .brand { font-weight:900; font-size:22px; letter-spacing:.02em; text-decoration:none;
           color:var(--ink); display:flex; align-items:center; }
  .brand img { height:36px; display:block; filter:var(--logofilter); }
  .barr { display:flex; align-items:center; gap:14px; font-size:13px; color:var(--muted); }
  .barr .lock { border:1px solid var(--line); border-radius:999px; padding:5px 13px;
                font-weight:600; background:var(--card); }
  .barr .lock::before { content:'\1F512\FE0E'; margin-right:6px; opacity:.8; }
  .barr a { text-decoration:none; font-weight:700; color:var(--ink); }
  @media (max-width:560px) { .barr .lock { display:none; } }

  .wrap { max-width:960px; margin:0 auto; padding:6px 16px 70px; position:relative; z-index:1; }
  .pagehead { margin:8px 2px 18px; }
  .kicker { color:var(--brand); font-weight:700; font-size:12.5px; letter-spacing:.14em;
            text-transform:uppercase; margin:0 0 6px; }
  .pagehead h1 { margin:0; font-size:clamp(24px,4.5vw,32px); font-weight:800; letter-spacing:-.02em; }
  .pagehead h1 .grad { background:linear-gradient(92deg,#4d8dff,#7a5cff 55%,#b16bff);
                       -webkit-background-clip:text; background-clip:text; color:transparent; }
  .grid { display:grid; grid-template-columns:minmax(0,1fr) 300px; grid-template-areas:'main side';
          gap:18px; align-items:start; }
  main  { grid-area:main; }
  aside { grid-area:side; position:sticky; top:14px; }
  @media (max-width:820px) {
    .grid { grid-template-columns:minmax(0,1fr); grid-template-areas:'side' 'main'; }
    aside { position:static; }
  }

  .card { background:var(--card); border:1px solid var(--line); border-radius:16px; padding:22px;
          box-shadow:0 18px 50px rgba(0,0,0,.28); }
  html.vt-light .card { box-shadow:0 14px 40px rgba(30,50,100,.10); }
  main.card { padding:26px 28px 28px; position:relative; overflow:hidden; }
  main.card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(92deg,#4d8dff,#7a5cff 55%,#b16bff); }
  h2 { margin:16px 0 12px; font-size:22px; }
  .desc { color:var(--muted); margin:10px 0 0; line-height:1.55; font-size:14.5px; }
  .spin { color:var(--muted); }

  .wzsteps { display:flex; align-items:center; gap:6px; }
  .wzstep { display:flex; flex-direction:column; align-items:center; gap:5px; }
  .wzdot { width:34px; height:34px; border-radius:50%; background:var(--chip); border:1px solid var(--chipline);
           color:var(--muted); font-weight:800; font-size:14px; line-height:32px; text-align:center; }
  .wzstep.on .wzdot { background:linear-gradient(135deg,#4d8dff,#7a5cff); border:0; color:#fff;
                      line-height:34px; box-shadow:0 0 16px rgba(77,141,255,.5); }
  .wzstep.done .wzdot { background:var(--ok); border:0; color:#fff; line-height:34px; }
  .wzlbl { font-size:11.5px; color:var(--muted); white-space:nowrap; }
  .wzstep.on .wzlbl { color:var(--ink); font-weight:700; }
  .wzbar { flex:1; height:2px; background:var(--line); border-radius:2px; margin-bottom:18px; min-width:12px; }
  .wzbar.done { background:var(--ok); }
  @media (max-width:480px) { .wzlbl { display:none; } .wzbar { margin-bottom:0; } }

  input, select { width:100%; margin-bottom:10px; font-size:15px; padding:12px 14px;
    background:var(--input); color:var(--ink); border:1px solid var(--line); border-radius:9px; outline:none; }
  input:focus { border-color:var(--brand); }
  label.small { display:block; color:var(--muted); font-size:12.5px; margin:2px 0 4px; }
  .samebox { display:flex; gap:11px; align-items:flex-start; background:var(--chip);
             border:1px solid var(--chipline); border-radius:10px; padding:13px 14px;
             margin-bottom:12px; cursor:pointer; font-size:14.5px; font-weight:600; }
  .samebox input { width:auto; margin:3px 0 0; }
  .samebox .sub { display:block; color:var(--muted); font-size:12.5px; font-weight:400; margin-top:2px; }

  .btn { display:inline-block; width:100%; text-align:center; border:0; cursor:pointer;
         background:linear-gradient(135deg,#4d8dff,#7a5cff); color:#fff; font-weight:800;
         font-size:15.5px; padding:13px 22px; border-radius:9px; margin-top:6px;
         transition:transform .12s ease, box-shadow .12s ease; text-decoration:none; }
  .btn:hover { transform:translateY(-1px); box-shadow:0 8px 26px rgba(77,141,255,.35); }
  .btn:disabled { opacity:.6; cursor:default; transform:none; box-shadow:none; }
  .btn.ghost { background:transparent; border:1px solid var(--line); color:var(--ink); width:auto; }
  .again { background:none; border:0; color:var(--muted); cursor:pointer; font-size:13.5px;
           padding:0; margin-top:12px; text-decoration:underline; }
  .err { display:none; color:var(--bad); font-size:13.5px; margin:10px 0 0; }

  aside h4 { margin:0 0 6px; font-size:12px; font-weight:800; text-transform:uppercase;
             letter-spacing:.09em; color:var(--muted); }
  .srow { display:flex; justify-content:space-between; gap:12px; padding:9px 0;
          border-bottom:1px solid var(--line); font-size:14px; }
  .srow:last-of-type { border-bottom:0; }
  .srow .k { color:var(--muted); white-space:nowrap; }
  .srow .v { text-align:right; font-weight:700; overflow-wrap:anywhere; }
  .srow .v .price { display:block; font-size:17px; font-weight:800;
    background:linear-gradient(92deg,#4d8dff,#7a5cff 55%,#b16bff);
    -webkit-background-clip:text; background-clip:text; color:transparent; }
  .sumnote { margin-top:10px; font-size:12.5px; color:var(--muted); line-height:1.5; }

  .rgrid { display:grid; grid-template-columns:repeat(auto-fit,minmax(168px,1fr)); gap:12px; margin-top:6px; }
  .rcard { border:1px solid var(--line); border-radius:13px; padding:15px 14px 14px; background:var(--input);
           display:flex; flex-direction:column; gap:8px; text-align:left;
           transition:border-color .12s ease, transform .12s ease; }
  .rcard:hover { border-color:var(--brand); transform:translateY(-2px); }
  .rimg { height:76px; display:flex; align-items:center; justify-content:center; }
  .rimg img { max-height:76px; max-width:100%; object-fit:contain; }
  .rimg.ph { color:var(--muted); opacity:.7; }
  .rimg.ph svg { width:62px; height:44px; }
  .rname { font-weight:800; font-size:15px; letter-spacing:.01em; }
  .rprice .amt { font-size:21px; font-weight:800;
    background:linear-gradient(92deg,#4d8dff,#7a5cff 55%,#b16bff);
    -webkit-background-clip:text; background-clip:text; color:transparent; }
  .rprice .suf { font-size:12px; color:var(--muted); margin-left:5px; }
  .rfeat { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:5px;
           font-size:12.5px; color:var(--muted); line-height:1.4; flex:1; }
  .rfeat li::before { content:'\2713'; color:var(--ok); font-weight:800; margin-right:6px; }
  .rcard .btn { margin-top:auto; padding:10px 14px; font-size:14px; }
  .rinfo { background:none; border:0; padding:0; color:var(--brand); font-size:12.5px;
           cursor:pointer; text-decoration:underline; text-align:left; }
  .rmodal { position:fixed; inset:0; z-index:60; background:rgba(6,9,16,.82);
            display:flex; align-items:flex-start; justify-content:center;
            padding:30px 14px; overflow:auto; }
  .rmodal .rbox { background:var(--card); border:1px solid var(--line); border-radius:16px;
                  max-width:500px; width:100%; padding:26px 28px 24px; position:relative;
                  box-shadow:0 24px 70px rgba(0,0,0,.5); margin-top:2vh; }
  .rmodal .rbox::before { content:''; position:absolute; top:0; left:0; right:0; height:3px;
    border-radius:16px 16px 0 0; background:linear-gradient(92deg,#4d8dff,#7a5cff 55%,#b16bff); }
  .rmodal .rclose { position:absolute; top:10px; right:14px; background:none; border:0;
                    color:var(--muted); font-size:24px; line-height:1; cursor:pointer; }
  .rmodal .rclose:hover { color:var(--ink); }
  .rmodal .rimg { height:190px; margin:4px 0 12px; }
  .rmodal .rimg img { max-height:190px; }
  .rmodal .rimg.ph svg { width:110px; height:78px; }
  .rmodal h3 { margin:0 0 2px; font-size:20px; }
  .rmodal .ruse { margin:12px 0 0; padding:12px 14px; background:var(--chip);
                  border:1px solid var(--chipline); border-radius:10px; font-size:13.5px;
                  color:var(--muted); line-height:1.55; }
  .chints { display:flex; flex-wrap:wrap; gap:8px 18px; margin:14px 4px 0;
            font-size:13px; color:var(--muted); }
  .chints span::before { content:'\2713'; color:var(--ok); font-weight:800; margin-right:6px; }

  .skip { display:none; margin-top:16px; font-size:13px; color:var(--muted); }
  .foot { max-width:960px; margin:0 auto; padding:0 16px 30px; color:var(--muted); font-size:13px;
          position:relative; z-index:1; }
  .foot a { font-weight:700; }
</style>
</head>
<body>
<div class="glow g1"></div><div class="glow g2"></div>
<div class="bar">
  <a class="brand" href="/personal/"><?php if ($logoUrl !== '') { ?><img
    src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES); ?>" alt="Korvix"
    onerror="this.parentNode.textContent='KORVIX'"><?php } else { echo 'KORVIX'; } ?></a>
  <div class="barr">
    <span class="lock">Secure signup</span>
    <a href="tel:0341305013">03 4130 5013</a>
  </div>
</div>

<div class="wrap">
<?php if ($orderUrl === ''): ?>
  <div class="card" style="max-width:520px;margin:30px auto;text-align:center">
    <h2 style="margin-top:4px">Let&rsquo;s start with your address</h2>
    <p class="desc">Signup starts from a plan for a checked address — takes about 30 seconds.</p>
    <a class="btn" style="max-width:320px;margin-top:18px" href="/personal/nbn/signup/">Check my address &rarr;</a>
  </div>
<?php else: ?>
  <div class="pagehead">
    <p class="kicker">Almost there</p>
    <h1>Let&rsquo;s get you <span class="grad">connected</span></h1>
  </div>
  <div class="grid">
    <main class="card">
      <div id="wzSteps"></div>
      <div id="wzErr" class="err"></div>
      <div id="wzBody"><p class="spin">Getting things ready&hellip;</p></div>
      <div id="wzSkip" class="skip">Having trouble?
        <a href="<?php echo htmlspecialchars($orderUrl, ENT_QUOTES); ?>">Continue straight to checkout &rarr;</a>
        or call <a href="tel:0341305013">03 4130 5013</a>.</div>
      <div class="chints"><span>No lock-in contracts</span><span>Local Aussie support</span><span>Fast activation</span></div>
    </main>
    <aside class="card">
      <h4>Your order</h4>
      <?php if ($plan !== ''): ?>
      <div class="srow"><span class="k">Plan</span>
        <span class="v"><?php echo htmlspecialchars($plan, ENT_QUOTES);
            echo $price !== '' ? '<span class="price">'
                . htmlspecialchars($price, ENT_QUOTES) . '</span>' : ''; ?></span></div>
      <?php endif; ?>
      <?php if ($addr !== ''): ?>
      <div class="srow"><span class="k">Address</span>
        <span class="v" style="font-weight:500"><?php echo htmlspecialchars($addr, ENT_QUOTES); ?></span></div>
      <?php endif; ?>
      <div class="srow" id="sumModem" style="display:none"><span class="k">Modem</span>
        <span class="v" id="sumModemV"></span></div>
      <div class="sumnote">Nothing is charged yet &mdash; you&rsquo;ll review everything, pick a billing
        cycle and pay securely at checkout.</div>
    </aside>
  </div>
<?php endif; ?>
</div>

<div class="foot">Questions before you commit? Call <a href="tel:0341305013">03 4130 5013</a> &mdash;
  a human in Gippsland picks up.</div>

<?php if ($orderUrl !== ''): ?>
<script>
(function () {
  var api = 'signup-api.php';
  var ORDER_URL = <?php echo $jsVar($orderUrl); ?>;
  var state = {
    plan: <?php echo $jsVar($plan); ?>,
    price: <?php echo $jsVar($price); ?>,
    addr: <?php echo $jsVar($addr); ?>,
    tech: <?php echo $jsVar((string) ($clean['vt_tech'] ?? '')); ?>
  };
  var meta = {routers: [], sms: false};
  var FLOW = [];
  var LABELS = {details: 'Your details', contact: 'Contact', address: 'Billing',
    modem: 'Modem', verify: 'Verify', review: 'Review'};

  var elSteps = document.getElementById('wzSteps');
  var elBody = document.getElementById('wzBody');
  var elErr = document.getElementById('wzErr');

  function esc(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
  }
  function post(data) {
    return fetch(api, {method: 'POST', credentials: 'same-origin',
      headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data)})
      .then(function (r) { return r.json(); });
  }
  function err(msg) { elErr.textContent = msg; elErr.style.display = msg ? 'block' : 'none'; }
  function escapeHatch() { var s = document.getElementById('wzSkip'); if (s) { s.style.display = 'block'; } }
  function busy(on, label) {
    var b = document.getElementById('vtWizGo');
    if (b) { b.disabled = on; if (label) { b.textContent = on ? 'One moment…' : label; } }
  }
  function inputRow(name, ph, type, extra) {
    return '<input name="' + name + '" type="' + (type || 'text') + '" placeholder="' + ph + '" '
      + (extra || '') + ' />';
  }
  function nav(nextLabel, backLabel) {
    return '<button type="button" class="btn" id="vtWizGo">' + nextLabel + '</button>'
      + '<button type="button" class="again" id="vtWizBack">' + (backLabel || 'Back') + '</button>';
  }
  function keep(keys) {
    keys.forEach(function (k) {
      var el = elBody.querySelector('[name=' + k + ']');
      if (el && state[k]) { el.value = state[k]; }
    });
  }
  function grab(keys) {
    keys.forEach(function (k) {
      var el = elBody.querySelector('[name=' + k + ']');
      if (el) { state[k] = el.value.trim(); }
    });
  }
  function buildFlow() {
    // Ethernet-WAN-only routers (no VDSL modem, e.g. MikroTik) can't
    // terminate an FTTN/FTTB line — drop them for those addresses.
    state.routerList = (meta.routers || []).filter(function (r) {
      return !(r.ethernetOnly && /FTTN|FTTB|to the Node|to the Building/i.test(state.tech || ''));
    });
    FLOW = ['details', 'contact', 'address'];
    if (state.routerList.length) { FLOW.push('modem'); }
    if (meta.sms) { FLOW.push('verify'); }
    FLOW.push('review');
  }
  function stepper(currentIdx) {
    var html = '';
    FLOW.forEach(function (key, i) {
      var cls = i < currentIdx ? 'done' : (i === currentIdx ? 'on' : '');
      html += '<div class="wzstep ' + cls + '"><span class="wzdot">'
        + (i < currentIdx ? '&#10003;' : (i + 1)) + '</span>'
        + '<span class="wzlbl">' + LABELS[key] + '</span></div>'
        + (i < FLOW.length - 1 ? '<span class="wzbar ' + (i < currentIdx ? 'done' : '') + '"></span>' : '');
    });
    elSteps.innerHTML = '<div class="wzsteps">' + html + '</div>';
  }
  function render(idx, title, body) {
    stepper(idx);
    err('');
    elBody.innerHTML = '<h2>' + title + '</h2>' + body;
    window.scrollTo({top: 0, behavior: 'smooth'});
  }
  function setSumModem() {
    var row = document.getElementById('sumModem');
    var val = document.getElementById('sumModemV');
    if (!row || !val) { return; }
    if (state.router === undefined) { row.style.display = 'none'; return; }
    row.style.display = 'flex';
    val.innerHTML = state.router
      ? esc(state.router.name) + (state.router.price
          ? '<span class="price">' + esc(state.router.price)
            + ' <span style="font-size:11px">' + esc(state.router.priceSuffix) + '</span></span>' : '')
      : 'Bringing my own';
  }

  function go(idx) {
    if (idx < 0) {
      if (history.length > 1) { history.back(); }
      else { location.href = '/personal/nbn/signup/'; }
      return;
    }
    var key = FLOW[idx];
    if (key === 'details') { return stepDetails(idx); }
    if (key === 'contact') { return stepContact(idx); }
    if (key === 'address') { return stepAddress(idx); }
    if (key === 'modem') { return stepModem(idx); }
    if (key === 'verify') { return stepVerify(idx); }
    if (key === 'review') { return stepReview(idx); }
  }

  function stepDetails(idx) {
    render(idx, 'Who’s this connection for?',
      '<p class="desc" style="margin:0 0 14px">Just the basics — this becomes your Korvix account.</p>'
      + inputRow('first', 'First name')
      + inputRow('last', 'Last name')
      + inputRow('email', 'Email address', 'email')
      + nav('Next →', 'Back to plans'));
    keep(['first', 'last', 'email']);
    document.getElementById('vtWizBack').onclick = function () { go(idx - 1); };
    document.getElementById('vtWizGo').onclick = function () {
      grab(['first', 'last', 'email']);
      if (!state.first || !state.last) { return err('Please enter your name.'); }
      if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(state.email)) { return err('That email doesn’t look right.'); }
      go(idx + 1);
    };
  }

  function stepContact(idx) {
    render(idx, 'How do we reach you?',
      '<p class="desc" style="margin:0 0 14px">Your mobile gets connection updates; date of birth '
      + 'is for identity checks — never marketing.</p>'
      + inputRow('phone', 'Mobile number (04xx xxx xxx)', 'tel')
      + '<label class="small">Date of birth</label>'
      + inputRow('dob', '', 'date', 'max="' + new Date().toISOString().slice(0, 10) + '"')
      + nav('Next →'));
    keep(['phone', 'dob']);
    document.getElementById('vtWizBack').onclick = function () { go(idx - 1); };
    document.getElementById('vtWizGo').onclick = function () {
      grab(['phone', 'dob']);
      if (!/^(\+?61|0)[\s()-]*4[\d\s()-]{8,}$/.test(state.phone)) { return err('Please enter a valid Australian mobile.'); }
      var dobTs = Date.parse(state.dob);
      if (!state.dob || isNaN(dobTs)) { return err('Please enter your date of birth.'); }
      var age = (Date.now() - dobTs) / (365.25 * 86400000);
      if (age < 18) { return err('You must be 18 or over to sign up.'); }
      if (age > 110) { return err('That date of birth doesn’t look right — please check it.'); }
      go(idx + 1);
    };
  }

  function stepAddress(idx) {
    var same = state.sameAddr !== false;
    render(idx, 'Where do we send the bills?',
      '<p class="desc" style="margin:0 0 14px">Invoices go to your email — this address goes on them.</p>'
      + '<label class="samebox"><input type="checkbox" id="vtSame"' + (same ? ' checked' : '') + '>'
      + '<span>Same as my connection address'
      + (state.addr ? '<span class="sub">' + esc(state.addr) + '</span>' : '') + '</span></label>'
      + '<div id="vtBFields" style="' + (same ? 'display:none' : '') + '">'
      + inputRow('baddr1', 'Street address')
      + inputRow('bcity', 'Suburb / town')
      + '<div style="display:flex;gap:10px">'
      + '<select name="bstate" style="flex:1"><option value="">State</option>'
      + ['VIC', 'NSW', 'QLD', 'SA', 'WA', 'TAS', 'NT', 'ACT'].map(function (s) {
          return '<option' + (state.bstate === s ? ' selected' : '') + '>' + s + '</option>';
        }).join('') + '</select>'
      + '<input name="bpostcode" type="tel" placeholder="Postcode" maxlength="4" '
      + 'inputmode="numeric" style="flex:1" />'
      + '</div></div>'
      + nav('Next →'));
    keep(['baddr1', 'bcity', 'bpostcode']);
    var box = document.getElementById('vtSame');
    box.onchange = function () {
      document.getElementById('vtBFields').style.display = box.checked ? 'none' : '';
    };
    document.getElementById('vtWizBack').onclick = function () { go(idx - 1); };
    document.getElementById('vtWizGo').onclick = function () {
      if (box.checked) {
        state.sameAddr = true;
        state.baddr1 = state.bcity = state.bstate = state.bpostcode = '';
        return go(idx + 1);
      }
      state.sameAddr = false;
      grab(['baddr1', 'bcity', 'bstate', 'bpostcode']);
      if (!state.baddr1 || state.baddr1.length < 5) { return err('Please enter your billing street address.'); }
      if (!state.bcity) { return err('Please enter your billing suburb or town.'); }
      if (!state.bstate) { return err('Please pick your billing state.'); }
      if (!/^\d{4}$/.test(state.bpostcode)) { return err('Please enter a valid 4-digit postcode.'); }
      go(idx + 1);
    };
  }

  var ROUTER_SVG = '<svg viewBox="0 0 64 44" fill="none" stroke="currentColor" stroke-width="2.5"'
    + ' stroke-linecap="round"><path d="M16 20 V6 M48 20 V6"/>'
    + '<rect x="6" y="20" width="52" height="17" rx="5"/>'
    + '<circle cx="16" cy="28.5" r="1.6" fill="currentColor" stroke="none"/>'
    + '<circle cx="24" cy="28.5" r="1.6" fill="currentColor" stroke="none"/>'
    + '<circle cx="32" cy="28.5" r="1.6" fill="currentColor" stroke="none"/></svg>';

  function routerUsage(r) {
    return r.ethernetOnly
      ? 'Connects to your NBN connection box with an ethernet cable — great for FTTP, HFC and '
        + 'Fixed Wireless. It has no phone-line (VDSL) modem, so it doesn’t suit FTTN/FTTB.'
      : 'Works on every NBN connection type — including FTTN/FTTB, thanks to its built-in VDSL '
        + 'modem. It ships pre-configured for your Korvix service: plug it in, wait for the '
        + 'lights, and you’re online. No usernames or passwords to enter.';
  }

  function routerModal(idx, i) {
    var r = state.routerList[i];
    var m = document.createElement('div');
    m.className = 'rmodal';
    m.innerHTML = '<div class="rbox">'
      + '<button type="button" class="rclose" aria-label="Close">&times;</button>'
      + (r.img ? '<div class="rimg"><img src="' + esc(r.img) + '" alt=""></div>'
               : '<div class="rimg ph">' + ROUTER_SVG + '</div>')
      + '<h3>' + esc(r.name) + '</h3>'
      + (r.price ? '<div class="rprice"><span class="amt">' + esc(r.price) + '</span>'
          + '<span class="suf">' + esc(r.priceSuffix) + '</span></div>' : '')
      + '<div class="ruse">' + routerUsage(r) + '</div>'
      + (r.features.length
          ? '<ul class="rfeat" style="margin-top:14px">' + r.features.map(function (f) {
              return '<li>' + esc(f) + '</li>';
            }).join('') + '</ul>'
          : '')
      + '<button type="button" class="btn rpick" style="width:100%;margin-top:16px">'
      + 'Add ' + esc(r.name) + ' to my order</button>'
      + '</div>';
    var close = function () { m.remove(); document.removeEventListener('keydown', onKey); };
    var onKey = function (ev) { if (ev.key === 'Escape') { close(); } };
    m.addEventListener('click', function (ev) { if (ev.target === m) { close(); } });
    m.querySelector('.rclose').onclick = close;
    m.querySelector('.rpick').onclick = function () {
      close();
      state.router = r; setSumModem(); advance(idx);
    };
    document.addEventListener('keydown', onKey);
    document.body.appendChild(m);
  }

  function stepModem(idx) {
    var cards = state.routerList.map(function (r, i) {
      return '<div class="rcard">'
        + (r.img ? '<div class="rimg"><img src="' + esc(r.img) + '" alt=""></div>'
                 : '<div class="rimg ph">' + ROUTER_SVG + '</div>')
        + '<div class="rname">' + esc(r.name) + '</div>'
        + (r.price ? '<div class="rprice"><span class="amt">' + esc(r.price) + '</span>'
            + '<span class="suf">' + esc(r.priceSuffix) + '</span></div>' : '')
        + (r.features.length
            ? '<ul class="rfeat">' + r.features.slice(0, 5).map(function (f) {
                return '<li>' + esc(f) + '</li>';
              }).join('') + '</ul>'
            : '')
        + '<button type="button" class="rinfo" data-i="' + i + '">More info</button>'
        + '<button type="button" class="btn rsel" data-i="' + i + '">Select +</button>'
        + '</div>';
    }).join('');
    render(idx, 'Would you like a modem?',
      '<p class="desc" style="margin:0 0 14px">Pre-configured for Korvix — plug in and you’re online. '
      + 'Or bring your own router: no username or password needed.</p>'
      + '<div class="rgrid">' + cards + '</div>'
      + '<button type="button" class="btn ghost" id="vtWizNo" style="width:100%;margin-top:14px">'
      + 'No thanks — I’ll bring my own</button>'
      + '<button type="button" class="again" id="vtWizBack">Back</button>');
    document.getElementById('vtWizBack').onclick = function () { go(idx - 1); };
    document.getElementById('vtWizNo').onclick = function () {
      state.router = null; setSumModem(); advance(idx);
    };
    Array.prototype.forEach.call(elBody.querySelectorAll('.rsel'), function (b) {
      b.onclick = function () {
        state.router = state.routerList[parseInt(b.getAttribute('data-i'), 10)];
        setSumModem(); advance(idx);
      };
    });
    Array.prototype.forEach.call(elBody.querySelectorAll('.rinfo'), function (b) {
      b.onclick = function () { routerModal(idx, parseInt(b.getAttribute('data-i'), 10)); };
    });
  }

  // Leaving the last input step: create the account (or send the code).
  function advance(idx) {
    var nextKey = FLOW[idx + 1];
    if (nextKey === 'verify' || nextKey === 'review') {
      if (state.accountDone) { return go(idx + 1); }
      busy(true);
      post({action: 'start', first: state.first, last: state.last, email: state.email,
        phone: state.phone, dob: state.dob, addr: state.addr,
        baddr1: state.sameAddr === false ? state.baddr1 : '',
        bcity: state.sameAddr === false ? state.bcity : '',
        bstate: state.sameAddr === false ? state.bstate : '',
        bpostcode: state.sameAddr === false ? state.bpostcode : ''})
        .then(function (j) {
          busy(false);
          if (!j.ok) {
            // A rejected field belongs to an earlier step — jump back to
            // it so the message sits next to the input it's about.
            var msg = j.error || 'Please check your details.';
            var backTo = /billing|postcode|suburb|state/i.test(msg) ? FLOW.indexOf('address')
              : (/birth|18|mobile/i.test(msg) ? FLOW.indexOf('contact')
              : (/name|email/i.test(msg) ? 0 : -1));
            if (backTo >= 0 && backTo < idx) { go(backTo); }
            err(msg); escapeHatch(); return;
          }
          if (j.verify) { state.hint = j.hint; return go(idx + 1); } // -> verify
          state.accountDone = true;
          state.existing = !!j.existing;
          go(FLOW.indexOf('review'));
        })
        .catch(function () { busy(false); err('Connection hiccup — try again.'); escapeHatch(); });
    } else {
      go(idx + 1);
    }
  }

  function stepVerify(idx) {
    render(idx, 'Confirm your mobile',
      '<p class="desc" style="margin:0 0 14px">We’ve texted a 6-digit code to <strong>'
      + esc(state.hint || 'your mobile') + '</strong>.</p>'
      + inputRow('code', '123456', 'tel', 'maxlength="6" inputmode="numeric" autocomplete="one-time-code"')
      + nav('Verify →'));
    var codeEl = elBody.querySelector('[name=code]');
    codeEl.focus();
    document.getElementById('vtWizBack').onclick = function () { go(idx - 1); };
    document.getElementById('vtWizGo').onclick = function () {
      busy(true);
      post({action: 'verify', code: codeEl.value})
        .then(function (j) {
          busy(false);
          if (!j.ok) { err(j.error || 'That code isn’t right.'); return; }
          state.accountDone = true;
          state.existing = !!j.existing;
          go(idx + 1);
        })
        .catch(function () { busy(false); err('Connection hiccup — try again.'); escapeHatch(); });
    };
  }

  function stepReview(idx) {
    var row = function (k, v) {
      return '<div class="srow"><span class="k">' + k + '</span>'
        + '<span class="v">' + v + '</span></div>';
    };
    render(idx, 'Ready to go?',
      row('Plan', esc(state.plan || '') + (state.price ? ' · ' + esc(state.price) : ''))
      + (state.addr ? row('Address', esc(state.addr)) : '')
      + row('Billing', state.sameAddr === false && state.baddr1
          ? esc(state.baddr1 + ', ' + state.bcity + ' ' + state.bstate + ' ' + state.bpostcode)
          : 'Same as connection address')
      + (state.routerList && state.routerList.length
          ? row('Modem', state.router ? esc(state.router.name) : 'Bringing my own') : '')
      + row('Account', esc(state.email) + (state.existing
          ? ' <span style="color:var(--warn)">(existing — log in at checkout)</span>'
          : ' <span style="color:var(--ok)">✓ ready</span>'))
      + '<button type="button" class="btn" id="vtWizGo" style="margin-top:16px">'
      + 'Continue to secure checkout →</button>'
      + '<button type="button" class="again" id="vtWizBack">Back</button>');
    document.getElementById('vtWizBack').onclick = function () { go(idx - 1); };
    document.getElementById('vtWizGo').onclick = function () {
      busy(true, 'Continue to secure checkout →');
      location.href = ORDER_URL
        + (state.router ? '&vt_router_pid=' + encodeURIComponent(state.router.pid) : '');
    };
  }

  // Modem/Verify steps only exist when configured; a metadata failure
  // still gives Details -> Contact -> Review, and the escape hatch keeps
  // checkout reachable no matter what.
  post({action: 'meta'})
    .then(function (j) { if (j.ok) { meta = j; } })
    .catch(function () { escapeHatch(); })
    .then(function () { buildFlow(); go(0); });
})();
</script>
<?php endif; ?>
</body>
</html>
