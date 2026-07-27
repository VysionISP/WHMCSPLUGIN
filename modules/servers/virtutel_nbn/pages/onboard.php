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
  * { box-sizing:border-box; }
  body { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
         color:var(--ink); margin:0; background:var(--page); min-height:100vh; }
  a { color:var(--brand); }

  .bar { display:flex; align-items:center; justify-content:space-between; gap:14px;
         max-width:960px; margin:0 auto; padding:16px; }
  .brand { font-weight:900; font-size:21px; letter-spacing:.14em; text-decoration:none;
           background:linear-gradient(135deg,#4d8dff,#7a5cff); -webkit-background-clip:text;
           background-clip:text; -webkit-text-fill-color:transparent; }
  .barr { display:flex; align-items:center; gap:14px; font-size:13px; color:var(--muted); }
  .barr .lock::before { content:'\1F512\FE0E'; margin-right:6px; opacity:.8; }
  .barr a { text-decoration:none; font-weight:700; color:var(--ink); }
  @media (max-width:560px) { .barr .lock { display:none; } }

  .wrap { max-width:960px; margin:0 auto; padding:6px 16px 70px; }
  .grid { display:grid; grid-template-columns:minmax(0,1fr) 300px; grid-template-areas:'main side';
          gap:18px; align-items:start; }
  main  { grid-area:main; }
  aside { grid-area:side; position:sticky; top:14px; }
  @media (max-width:820px) {
    .grid { grid-template-columns:minmax(0,1fr); grid-template-areas:'side' 'main'; }
    aside { position:static; }
  }

  .card { background:var(--card); border:1px solid var(--line); border-radius:14px; padding:22px; }
  main.card { padding:24px 26px 26px; }
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
  .sumnote { margin-top:10px; font-size:12.5px; color:var(--muted); line-height:1.5; }

  .skip { display:none; margin-top:16px; font-size:13px; color:var(--muted); }
  .foot { max-width:960px; margin:0 auto; padding:0 16px 30px; color:var(--muted); font-size:13px; }
  .foot a { font-weight:700; }
</style>
</head>
<body>
<div class="bar">
  <a class="brand" href="/personal/">KORVIX</a>
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
  <div class="grid">
    <main class="card">
      <div id="wzSteps"></div>
      <div id="wzErr" class="err"></div>
      <div id="wzBody"><p class="spin">Getting things ready&hellip;</p></div>
      <div id="wzSkip" class="skip">Having trouble?
        <a href="<?php echo htmlspecialchars($orderUrl, ENT_QUOTES); ?>">Continue straight to checkout &rarr;</a>
        or call <a href="tel:0341305013">03 4130 5013</a>.</div>
    </main>
    <aside class="card">
      <h4>Your order</h4>
      <?php if ($plan !== ''): ?>
      <div class="srow"><span class="k">Plan</span>
        <span class="v"><?php echo htmlspecialchars($plan, ENT_QUOTES);
            echo $price !== '' ? '<span style="display:block;color:var(--brand)">'
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
    addr: <?php echo $jsVar($addr); ?>
  };
  var meta = {router: null, sms: false};
  var FLOW = [];
  var LABELS = {details: 'Your details', contact: 'Contact', modem: 'Modem',
    verify: 'Verify', review: 'Review'};

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
    FLOW = ['details', 'contact'];
    if (meta.router) { FLOW.push('modem'); }
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
      ? esc(meta.router.name) + (meta.router.price
          ? '<span style="display:block;color:var(--brand)">' + esc(meta.router.price) + '</span>' : '')
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
    if (key === 'modem') { return stepModem(idx); }
    if (key === 'verify') { return stepVerify(idx); }
    if (key === 'review') { return stepReview(idx); }
  }

  function stepDetails(idx) {
    render(idx, 'Let’s get you set up',
      '<p class="desc" style="margin:0 0 14px">Who’s this connection for?</p>'
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
      if (!state.dob) { return err('Please enter your date of birth.'); }
      go(idx + 1);
    };
  }

  function stepModem(idx) {
    var r = meta.router;
    render(idx, 'Need a modem?',
      '<p class="desc" style="margin:0 0 6px"><strong>' + esc(r.name) + '</strong>'
      + (r.price ? ' — ' + esc(r.price) : '') + '</p>'
      + '<p class="desc" style="margin:0 0 16px">' + esc(r.blurb || 'Pre-configured for Korvix — plug in and you’re online. Or bring your own router: no username or password needed.') + '</p>'
      + '<div style="display:flex;gap:10px;flex-wrap:wrap">'
      + '<button type="button" class="btn" style="width:auto" id="vtWizYes">Yes, add it</button>'
      + '<button type="button" class="btn ghost" id="vtWizNo">I’ll bring my own</button></div>'
      + '<button type="button" class="again" id="vtWizBack">Back</button>');
    document.getElementById('vtWizBack').onclick = function () { go(idx - 1); };
    document.getElementById('vtWizYes').onclick = function () { state.router = true; setSumModem(); advance(idx); };
    document.getElementById('vtWizNo').onclick = function () { state.router = false; setSumModem(); advance(idx); };
  }

  // Leaving the last input step: create the account (or send the code).
  function advance(idx) {
    var nextKey = FLOW[idx + 1];
    if (nextKey === 'verify' || nextKey === 'review') {
      if (state.accountDone) { return go(idx + 1); }
      busy(true);
      post({action: 'start', first: state.first, last: state.last, email: state.email,
        phone: state.phone, dob: state.dob, addr: state.addr})
        .then(function (j) {
          busy(false);
          if (!j.ok) { err(j.error || 'Please check your details.'); escapeHatch(); return; }
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
      + (meta.router ? row('Modem', state.router ? esc(meta.router.name) : 'Bringing my own') : '')
      + row('Account', esc(state.email) + (state.existing
          ? ' <span style="color:var(--warn)">(existing — log in at checkout)</span>'
          : ' <span style="color:var(--ok)">✓ ready</span>'))
      + '<button type="button" class="btn" id="vtWizGo" style="margin-top:16px">'
      + 'Continue to secure checkout →</button>'
      + '<button type="button" class="again" id="vtWizBack">Back</button>');
    document.getElementById('vtWizBack').onclick = function () { go(idx - 1); };
    document.getElementById('vtWizGo').onclick = function () {
      busy(true, 'Continue to secure checkout →');
      location.href = ORDER_URL + (state.router ? '&vt_router=1' : '');
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
