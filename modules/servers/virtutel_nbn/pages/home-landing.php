<?php

/**
 * Homepage landing fragment: fetched by the homepage hook and injected in
 * place of the stock WHMCS homepage body. Self-contained styling (works
 * with or without the portal dark theme), address checker embedded via the
 * qualify page, live plan cards from the Virtutel product group.
 *
 * Returns an HTML FRAGMENT (no <html>/<head>) — scripts must NOT be used
 * here; innerHTML-injected scripts never execute. Behaviour lives in the
 * injecting hook.
 */

use WHMCS\Database\Capsule;

require_once __DIR__ . '/../../../../init.php';

header('Content-Type: text/html; charset=utf-8');

$e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES);

// Live plans: Virtutel products with monthly pricing, cheapest first.
$plans = [];
try {
    $rows = Capsule::table('tblproducts')
        ->join('tblpricing', function ($join) {
            $join->on('tblpricing.relid', '=', 'tblproducts.id')
                ->where('tblpricing.type', '=', 'product')
                ->where('tblpricing.currency', '=', 1);
        })
        ->where('tblproducts.servertype', 'virtutel_nbn')
        ->where('tblproducts.hidden', 0)
        ->where('tblproducts.retired', 0)
        ->where('tblpricing.monthly', '>', 0)
        ->orderBy('tblpricing.monthly')
        ->get(['tblproducts.id', 'tblproducts.name', 'tblproducts.description', 'tblpricing.monthly']);

    foreach ($rows as $row) {
        $speed = ['down' => '', 'up' => ''];
        if (preg_match('/(\d+)\s*\/\s*(\d+)/', (string) $row->name, $m)) {
            $speed = ['down' => $m[1], 'up' => $m[2]];
        }
        $plans[] = [
            'name' => (string) $row->name,
            'price' => number_format((float) $row->monthly, 2),
            'down' => $speed['down'],
            'up' => $speed['up'],
        ];
    }
} catch (\Throwable $ex) {
    $plans = [];
}

$storeUrl = '/store/residental-internet';
$qualifySrc = '/modules/servers/virtutel_nbn/pages/qualify.php?embed=1&theme=dark';

?>
<div class="kx-landing">
<style>
  .kx-landing { --bg:#0b0f1a; --surface:#141b2b; --surface2:#1a2338; --line:#2a3347;
                --text:#e6e9f2; --muted:#98a2b8; --brand:#4d8dff; --brand2:#7a5cff;
                --ok:#2fbf71;
                color:var(--text); font-size:16px; line-height:1.6; }
  .kx-landing * { box-sizing:border-box; }
  .kx-landing section { padding:56px 20px; }
  .kx-landing .inner { max-width:1080px; margin:0 auto; }
  .kx-landing h1 { font-size:clamp(30px,5vw,46px); line-height:1.15; margin:0 0 14px; font-weight:800;
                   letter-spacing:-.02em; }
  .kx-landing h2 { font-size:clamp(22px,3.4vw,30px); margin:0 0 8px; font-weight:800; letter-spacing:-.01em; }
  .kx-landing .kicker { color:var(--brand); font-weight:700; font-size:13px; letter-spacing:.14em;
                        text-transform:uppercase; margin-bottom:10px; }
  .kx-landing .sub { color:var(--muted); max-width:640px; margin:0 auto 26px; }
  .kx-grad { background:linear-gradient(92deg,#4d8dff,#7a5cff 55%,#b16bff);
             -webkit-background-clip:text; background-clip:text; color:transparent; }

  .kx-hero { text-align:center; padding-top:64px; }
  .kx-hero .chips { display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin-top:22px; }
  .kx-hero .chip { font-size:13px; color:var(--muted); border:1px solid var(--line);
                   background:var(--surface); border-radius:999px; padding:6px 14px; }
  .kx-hero .chip b { color:var(--ok); font-weight:700; }
  .kx-checker { max-width:760px; margin:30px auto 0; background:var(--surface);
                border:1px solid var(--line); border-radius:16px; padding:22px 22px 8px;
                box-shadow:0 18px 50px rgba(0,0,0,.35); text-align:left; }
  .kx-checker .kx-checker-title { font-weight:700; margin:0 0 2px; font-size:17px; }
  .kx-checker .kx-checker-sub { color:var(--muted); font-size:13.5px; margin:0 0 10px; }
  .kx-checker iframe { width:100%; border:0; display:block; min-height:150px; background:transparent; }

  .kx-feat .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-top:26px; }
  .kx-feat .tile { background:var(--surface); border:1px solid var(--line); border-radius:14px; padding:22px; }
  .kx-feat .tile .ico { font-size:24px; margin-bottom:10px; }
  .kx-feat .tile h3 { margin:0 0 6px; font-size:16.5px; }
  .kx-feat .tile p { margin:0; color:var(--muted); font-size:14px; }

  .kx-plans { background:linear-gradient(180deg,transparent,rgba(77,141,255,.05) 40%,transparent); text-align:center; }
  .kx-plans .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:16px; margin-top:30px; }
  .kx-plan { background:var(--surface); border:1px solid var(--line); border-radius:16px; padding:26px 22px;
             display:flex; flex-direction:column; align-items:center; transition:transform .15s,border-color .15s; }
  .kx-plan:hover { transform:translateY(-4px); border-color:var(--brand); }
  .kx-plan .pname { font-weight:800; font-size:18px; }
  .kx-plan .pspeed { color:var(--muted); font-size:13.5px; margin:6px 0 14px; }
  .kx-plan .pprice { font-size:34px; font-weight:800; letter-spacing:-.02em; }
  .kx-plan .pprice small { font-size:14px; color:var(--muted); font-weight:600; }
  .kx-plan .pnote { color:var(--muted); font-size:12px; margin:4px 0 16px; }
  .kx-btn { display:inline-block; border:0; cursor:pointer; text-decoration:none; text-align:center;
            background:linear-gradient(92deg,#4d8dff,#7a5cff); color:#fff !important; font-weight:700;
            font-size:15px; padding:12px 26px; border-radius:999px; }
  .kx-btn:hover { filter:brightness(1.1); }
  .kx-btn.ghost { background:transparent; border:1px solid var(--line); color:var(--text) !important; }
  .kx-btn.ghost:hover { border-color:var(--brand); }

  .kx-steps { text-align:center; }
  .kx-steps .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:16px; margin-top:30px; }
  .kx-steps .step { background:var(--surface); border:1px solid var(--line); border-radius:14px; padding:26px 22px; }
  .kx-steps .num { width:38px; height:38px; border-radius:999px; margin:0 auto 12px; display:flex;
                   align-items:center; justify-content:center; font-weight:800;
                   background:linear-gradient(92deg,#4d8dff,#7a5cff); color:#fff; }
  .kx-steps h3 { margin:0 0 6px; font-size:16.5px; }
  .kx-steps p { margin:0; color:var(--muted); font-size:14px; }

  .kx-faq .list { max-width:760px; margin:26px auto 0; }
  .kx-faq details { background:var(--surface); border:1px solid var(--line); border-radius:12px;
                    margin-bottom:10px; padding:0 18px; }
  .kx-faq summary { cursor:pointer; font-weight:700; padding:15px 0; list-style:none; }
  .kx-faq summary::-webkit-details-marker { display:none; }
  .kx-faq summary:after { content:'+'; float:right; color:var(--brand); font-weight:800; }
  .kx-faq details[open] summary:after { content:'\2212'; }
  .kx-faq details p { margin:0 0 15px; color:var(--muted); font-size:14.5px; }

  .kx-cta { text-align:center; }
  .kx-cta .band { background:linear-gradient(120deg,rgba(77,141,255,.14),rgba(122,92,255,.14));
                  border:1px solid var(--line); border-radius:20px; padding:44px 24px; }
</style>

<section class="kx-hero">
  <div class="inner">
    <div class="kicker">NBN Internet &mdash; Gippsland &amp; beyond</div>
    <h1>Fast, local NBN<br>without the <span class="kx-grad">runaround</span></h1>
    <p class="sub">Check your address, pick a plan, and get connected &mdash; switching from another
      provider usually happens remotely with no technician visit and no dropout drama.</p>
    <div class="kx-checker" id="kx-check">
      <p class="kx-checker-title">Is your address ready?</p>
      <p class="kx-checker-sub">Search the NBN database for your exact address &mdash; we'll show your
        connection type and every plan available at your place.</p>
      <iframe id="kxQualifyFrame" src="<?php echo $e($qualifySrc); ?>" scrolling="no" title="NBN address checker"></iframe>
    </div>
    <div class="chips">
      <span class="chip"><b>&#10003;</b> Local Aussie support</span>
      <span class="chip"><b>&#10003;</b> No lock-in contracts</span>
      <span class="chip"><b>&#10003;</b> Easy provider switch</span>
      <span class="chip"><b>&#10003;</b> All NBN technologies</span>
    </div>
  </div>
</section>

<section class="kx-feat">
  <div class="inner">
    <div class="kicker">Why Korvix</div>
    <h2>Internet that just works</h2>
    <div class="grid">
      <div class="tile"><div class="ico">&#9889;</div><h3>Rapid activation</h3>
        <p>Ready addresses connect fast &mdash; transfers from another provider happen remotely,
           often the same day.</p></div>
      <div class="tile"><div class="ico">&#128205;</div><h3>Local support</h3>
        <p>Talk to people who know your area, not an offshore script. Gippsland-based help when
           you need it.</p></div>
      <div class="tile"><div class="ico">&#128274;</div><h3>No lock-in</h3>
        <p>Month-to-month plans. Stay because the internet's good, not because a contract says so.</p></div>
      <div class="tile"><div class="ico">&#128200;</div><h3>Honest speeds</h3>
        <p>Plans across every tier your address supports &mdash; we'll only show you what your
           line can actually do.</p></div>
    </div>
  </div>
</section>

<?php if ($plans !== []) { ?>
<section class="kx-plans">
  <div class="inner">
    <div class="kicker">Plans</div>
    <h2>Simple monthly pricing</h2>
    <p class="sub">Every plan is month-to-month with unlimited data. Availability and top speeds
      depend on your address &mdash; check it above and we'll show you exactly what you can get.</p>
    <div class="grid">
      <?php foreach ($plans as $plan) { ?>
      <div class="kx-plan">
        <div class="pname"><?php echo $e($plan['name']); ?></div>
        <div class="pspeed"><?php echo $plan['down'] !== ''
            ? $e($plan['down']) . ' Mbps down / ' . $e($plan['up']) . ' Mbps up'
            : 'Speed tier at your address'; ?></div>
        <div class="pprice">$<?php echo $e($plan['price']); ?><small>/mo</small></div>
        <div class="pnote">AUD, incl. GST</div>
        <a class="kx-btn ghost" href="#kx-check">Check availability</a>
      </div>
      <?php } ?>
    </div>
  </div>
</section>
<?php } ?>

<section class="kx-steps">
  <div class="inner">
    <div class="kicker">Getting connected</div>
    <h2>Three steps, no paperwork</h2>
    <div class="grid">
      <div class="step"><div class="num">1</div><h3>Check your address</h3>
        <p>We look up the NBN database and show your connection type, ports, and available speeds.</p></div>
      <div class="step"><div class="num">2</div><h3>Pick your plan</h3>
        <p>Only plans your address supports are shown. Switching providers? Have your AVC ID handy
           and we'll transfer the service remotely.</p></div>
      <div class="step"><div class="num">3</div><h3>Get online</h3>
        <p>We lodge the order with NBN the moment payment clears and keep you posted at every step
           &mdash; including appointment booking if a technician is needed.</p></div>
    </div>
  </div>
</section>

<section class="kx-faq">
  <div class="inner">
    <div class="kicker">Questions</div>
    <h2 style="text-align:center">Fair questions, straight answers</h2>
    <div class="list">
      <details>
        <summary>Can I keep my current provider until the switch happens?</summary>
        <p>Yes. A service transfer (churn) moves your existing NBN line to us remotely &mdash; there's
           no technician visit and only a brief dropout while it cuts over. Grab the AVC ID from your
           current provider's portal or invoice and enter it during the address check.</p>
      </details>
      <details>
        <summary>Do I need a new modem or router?</summary>
        <p>Any modern router that supports IPoE (DHCP) on the WAN port works &mdash; no usernames or
           passwords to type in. Plug into the NBN connection box (or VDSL modem for FTTN/FTTB) and
           you're away. We'll send simple setup steps with your order.</p>
      </details>
      <details>
        <summary>What if my address needs a technician?</summary>
        <p>Some new connections (like first-time fibre installs) need an NBN technician. If yours
           does, we'll email you a booking link the moment NBN asks, and you pick the timeslot that
           suits &mdash; no phone tag.</p>
      </details>
      <details>
        <summary>Which NBN technology do I have?</summary>
        <p>The address checker tells you instantly &mdash; FTTP, HFC, FTTC, FTTN/B, or Fixed
           Wireless &mdash; along with every speed tier your line supports and whether a free fibre
           upgrade is available at your address.</p>
      </details>
      <details>
        <summary>Are there setup fees or lock-in contracts?</summary>
        <p>Plans are month-to-month. If NBN applies a one-off charge for your address (like a New
           Development Charge), we'll tell you up front during checkout &mdash; never as a surprise
           on your first invoice.</p>
      </details>
    </div>
  </div>
</section>

<section class="kx-cta">
  <div class="inner">
    <div class="band">
      <h2>Ready when you are</h2>
      <p class="sub">Takes under a minute to see every plan available at your address.</p>
      <a class="kx-btn" href="#kx-check">Check my address</a>
      &nbsp;
      <a class="kx-btn ghost" href="<?php echo $e($storeUrl); ?>">Browse plans</a>
    </div>
  </div>
</section>
</div>
