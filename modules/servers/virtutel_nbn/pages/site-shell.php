<?php

/**
 * Standalone marketing site shell: renders full pages for the top-level
 * section URLs (/residental, /business, ...) with the same dark chrome as
 * the portal — utility top bar, marketing nav, footer — and live WHMCS
 * data (product groups, plans, login state). Sections are registered in
 * kx_site_render(); thin index.php stubs in the docroot section folders
 * call it, so new subpages are one stub + one content block away.
 */

use WHMCS\Database\Capsule;

require_once __DIR__ . '/../../../../init.php';

function kx_site_e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES);
}

/** @return array[] visible product groups: [name, url] */
function kx_site_groups(): array
{
    try {
        $hasSlug = Capsule::schema()->hasColumn('tblproductgroups', 'slug');
        $out = [];
        foreach (Capsule::table('tblproductgroups')
            ->where('hidden', 0)->orderBy('order')->get(['id', 'name', 'slug']) as $g) {
            if (stripos((string) $g->name, 'addon') !== false) {
                continue;
            }
            $url = ($hasSlug && (string) ($g->slug ?? '') !== '')
                ? '/store/' . $g->slug
                : '/cart.php?gid=' . (int) $g->id;
            $out[] = [(string) $g->name, $url];
        }
        return $out;
    } catch (\Throwable $e) {
        return [];
    }
}

/** @return array[] NBN plans: [name, price, down, up] cheapest first */
function kx_site_plans(): array
{
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
            ->get(['tblproducts.name', 'tblpricing.monthly']);
        $plans = [];
        foreach ($rows as $row) {
            $down = $up = '';
            if (preg_match('/(\d+)\s*\/\s*(\d+)/', (string) $row->name, $m)) {
                [, $down, $up] = $m;
            }
            $plans[] = [(string) $row->name, number_format((float) $row->monthly, 2), $down, $up];
        }
        return $plans;
    } catch (\Throwable $e) {
        return [];
    }
}

function kx_site_render(string $section): void
{
    $e = 'kx_site_e';
    // TEMPORARY number — revert to 1300 881 437 when it's live.
    $phone = '03 4130 5012';
    $tel = preg_replace('/\D/', '', $phone);
    $loggedIn = !empty($_SESSION['uid']);
    $groups = kx_site_groups();

    // Personal/Business switcher (top strip) works on section FAMILIES;
    // the main nav below is family-specific.
    $families = [
        'personal' => ['Personal', '/'],
        'business' => ['Business', '/business/'],
    ];
    $family = $section === 'business' ? 'business' : 'personal';

    if ($family === 'personal') {
        $nav = [
            ['Home', '/'],
            ['NBN Internet', '/personal/nbn/'],
            ['Mobile', '/personal/mobile/'],
            ['Home Phone', '/personal/home-phone/'],
            ['Contact', '/contact.php'],
        ];
        $active = [
            'residential' => 'Home',
            'nbn' => 'NBN Internet',
            'mobile' => 'Mobile',
            'homephone' => 'Home Phone',
        ][$section] ?? '';
    } else {
        $nav = [
            ['Home', '/'],
            ['Personal', '/personal/'],
            ['Business', '/business/'],
            ['Contact', '/contact.php'],
        ];
        $active = 'Business';
    }

    $titles = [
        'residential' => 'Personal NBN — Korvix',
        'nbn' => 'NBN Internet Plans — Korvix',
        'mobile' => 'Mobile — Korvix',
        'homephone' => 'Home Phone — Korvix',
        'business' => 'Business Internet & Services — Korvix',
    ];
    $title = $titles[$section] ?? 'Korvix';

    header('Content-Type: text/html; charset=utf-8');
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $e($title); ?></title>
<style>
  :root { --bg:#0b0f1a; --surface:#141b2b; --line:#2a3347; --text:#e6e9f2; --muted:#98a2b8;
          --brand:#4d8dff; --brand2:#7a5cff; --ok:#2fbf71; }
  * { box-sizing:border-box; }
  body { margin:0; background:var(--bg); color:var(--text); font-size:16px; line-height:1.6;
         font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; }
  a { color:var(--brand); text-decoration:none; }
  .inner { max-width:1080px; margin:0 auto; padding:0 20px; }
  /* chrome */
  .kx-switch { background:#04060c; font-size:12.5px; }
  .kx-switch .in { max-width:1200px; margin:0 auto; padding:0 20px; display:flex; gap:2px;
                   justify-content:flex-end; }
  .kx-switch a { color:#98a2b8; font-weight:600; padding:8px 16px; display:inline-block; }
  .kx-switch a:hover { color:#e6e9f2; }
  .kx-switch a.on { color:#fff; background:#0b0f1a; border-top:2px solid var(--brand); }
  .kx-topbar { background:#070a12; border-bottom:1px solid #1c2436; font-size:12.5px; }
  .kx-topbar .in { max-width:1200px; margin:0 auto; display:flex; justify-content:space-between;
                   align-items:center; padding:7px 20px; gap:14px; flex-wrap:wrap; }
  .kx-topbar .l { display:flex; gap:18px; flex-wrap:wrap; }
  .kx-topbar a { color:#98a2b8; }
  .kx-topbar a:hover { color:#e6e9f2; }
  .kx-topbar .ph { color:#e6e9f2; font-weight:700; }
  .kx-topbar .cp { background:linear-gradient(92deg,#4d8dff,#7a5cff); color:#fff;
                   padding:4px 14px; border-radius:999px; font-weight:600; }
  @media(max-width:640px){ .kx-topbar .l { display:none; } }
  .kx-nav { background:#0b0f1a; border-bottom:1px solid #1c2436; }
  .kx-nav .in { max-width:1200px; margin:0 auto; padding:18px 20px; display:flex;
                align-items:center; gap:30px; flex-wrap:wrap; }
  .kx-nav .logo { font-size:24px; font-weight:900; color:#fff; letter-spacing:.02em; }
  .kx-nav .logo img { height:32px; display:block; filter:brightness(0) invert(1); }
  .kx-nav .links { display:flex; gap:22px; flex-wrap:wrap; margin-left:auto; }
  .kx-nav .links a { color:#c7cede; font-weight:600; font-size:15px; }
  .kx-nav .links a:hover, .kx-nav .links a.on { color:#fff; }
  .kx-nav .links a.on { border-bottom:2px solid var(--brand); padding-bottom:3px; }
  /* shared sections */
  section { padding:56px 20px; }
  h1 { font-size:clamp(30px,5vw,44px); line-height:1.15; margin:0 0 14px; font-weight:800;
       letter-spacing:-.02em; }
  h2 { font-size:clamp(22px,3.4vw,30px); margin:0 0 8px; font-weight:800; }
  .kicker { color:var(--brand); font-weight:700; font-size:13px; letter-spacing:.14em;
            text-transform:uppercase; margin-bottom:10px; }
  .sub { color:var(--muted); max-width:640px; margin:0 auto 26px; }
  .grad { background:linear-gradient(92deg,#4d8dff,#7a5cff 55%,#b16bff);
          -webkit-background-clip:text; background-clip:text; color:transparent; }
  .hero { text-align:center; padding-top:64px; position:relative; overflow:hidden; }
  .hero .glow { position:absolute; border-radius:50%; filter:blur(100px); opacity:.32; pointer-events:none; }
  .hero .g1 { width:440px; height:440px; background:#2b5cff; top:-160px; left:-100px; }
  .hero .g2 { width:400px; height:400px; background:#7a5cff; top:-120px; right:-80px; }
  .hero .inner { position:relative; }
  .checker { max-width:760px; margin:30px auto 0; background:var(--surface); border:1px solid var(--line);
             border-radius:16px; padding:22px 22px 8px; box-shadow:0 18px 50px rgba(0,0,0,.35); text-align:left; }
  .checker .t { font-weight:700; margin:0 0 2px; font-size:17px; }
  .checker .s { color:var(--muted); font-size:13.5px; margin:0 0 10px; }
  .checker iframe { width:100%; border:0; display:block; min-height:150px; background:transparent; }
  .cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:16px; margin-top:30px; }
  .card { background:var(--surface); border:1px solid var(--line); border-radius:16px; padding:26px 22px;
          transition:transform .15s,border-color .15s; }
  .card:hover { transform:translateY(-4px); border-color:var(--brand); }
  .card h3 { margin:0 0 8px; font-size:18px; }
  .card p { margin:0 0 16px; color:var(--muted); font-size:14px; }
  .card .ico { font-size:26px; margin-bottom:10px; }
  .plan { text-align:center; display:flex; flex-direction:column; align-items:center; }
  .plan .speed { color:var(--muted); font-size:13.5px; margin:4px 0 12px; }
  .plan .price { font-size:32px; font-weight:800; }
  .plan .price small { font-size:14px; color:var(--muted); font-weight:600; }
  .btn { display:inline-block; border:0; cursor:pointer; text-align:center;
         background:linear-gradient(92deg,#4d8dff,#7a5cff); color:#fff !important; font-weight:700;
         font-size:15px; padding:12px 26px; border-radius:999px; }
  .btn:hover { filter:brightness(1.1); }
  .btn.ghost { background:transparent; border:1px solid var(--line); color:var(--text) !important; }
  .btn.ghost:hover { border-color:var(--brand); }
  .band { background:linear-gradient(120deg,rgba(77,141,255,.14),rgba(122,92,255,.14));
          border:1px solid var(--line); border-radius:20px; padding:44px 24px; text-align:center; }
  /* footer */
  .kx-footer { background:#070a12; border-top:1px solid #1c2436; margin-top:64px; color:#98a2b8; font-size:14px; }
  .kx-footer .in { max-width:1200px; margin:0 auto; padding:48px 20px 0; }
  .kx-footer .grid { display:grid; grid-template-columns:2fr 1fr 1fr; gap:32px; }
  @media(max-width:700px){ .kx-footer .grid { grid-template-columns:1fr; } }
  .kx-footer h4 { color:#e6e9f2; font-size:13px; letter-spacing:.1em; text-transform:uppercase;
                  margin:4px 0 14px; }
  .kx-footer ul { list-style:none; margin:0; padding:0; }
  .kx-footer li { margin-bottom:9px; }
  .kx-footer a { color:#98a2b8; }
  .kx-footer a:hover { color:#fff; }
  .kx-footer .legal { border-top:1px solid #1c2436; margin-top:40px; padding:18px 0; display:flex;
                      justify-content:space-between; gap:14px; flex-wrap:wrap; font-size:12.5px; }
</style>
</head>
<body>

<div class="kx-switch"><div class="in">
  <?php foreach ($families as $key => [$label, $url]) { ?>
    <a href="<?php echo $e($url); ?>"<?php echo $key === $family ? ' class="on"' : ''; ?>><?php echo $e($label); ?></a>
  <?php } ?>
</div></div>

<div class="kx-topbar"><div class="in">
  <div class="l">
    <a href="/serverstatus.php">Service Status</a>
    <a href="/submitticket.php">Get Remote Support</a>
    <a href="/clientarea.php?action=invoices">Pay an Invoice</a>
  </div>
  <div class="r" style="display:flex;gap:14px;align-items:center">
    <a class="ph" href="tel:<?php echo $e($tel); ?>">&#9742;&nbsp;<?php echo $e($phone); ?></a>
    <?php if ($loggedIn) { ?>
      <a class="cp" href="/logout.php">Logout</a>
    <?php } else { ?>
      <a class="cp" href="/clientarea.php">Portal Login</a>
    <?php } ?>
  </div>
</div></div>

<div class="kx-nav"><div class="in">
  <a class="logo" href="/">KORVIX</a>
  <div class="links">
    <?php foreach ($nav as [$label, $url]) { ?>
      <a href="<?php echo $e($url); ?>"<?php echo $label === $active ? ' class="on"' : ''; ?>><?php echo $e($label); ?></a>
    <?php } ?>
  </div>
</div></div>

<?php if ($section === 'residential') { $plans = kx_site_plans(); ?>

<section class="hero">
  <div class="glow g1"></div><div class="glow g2"></div>
  <div class="inner">
    <div class="kicker">Residential</div>
    <h1>NBN for your home,<br>done <span class="grad">properly</span></h1>
    <p class="sub">Every speed tier your address supports, month-to-month, with local support.
      Switching providers happens remotely &mdash; no technician, no drama.</p>
    <div class="checker" id="check">
      <p class="t">Check your address</p>
      <p class="s">We'll show your connection type and every plan available at your place.</p>
      <iframe id="kxQ" src="/modules/servers/virtutel_nbn/pages/qualify.php?embed=1&theme=dark"
              scrolling="no" title="NBN address checker"></iframe>
    </div>
  </div>
</section>

<?php if ($plans !== []) { ?>
<section style="text-align:center">
  <div class="inner">
    <div class="kicker">Plans</div>
    <h2>Simple monthly pricing</h2>
    <div class="cards">
      <?php foreach ($plans as [$name, $price, $down, $up]) { ?>
      <div class="card plan">
        <h3><?php echo $e($name); ?></h3>
        <div class="speed"><?php echo $down !== ''
            ? $e($down) . ' Mbps down / ' . $e($up) . ' Mbps up' : 'Speed tier at your address'; ?></div>
        <div class="price">$<?php echo $e($price); ?><small>/mo</small></div>
        <a class="btn ghost" style="margin-top:14px" href="#check">Check availability</a>
      </div>
      <?php } ?>
    </div>
  </div>
</section>
<?php } ?>

<section>
  <div class="inner"><div class="band">
    <h2>Not sure what you need?</h2>
    <p class="sub">Call us on <?php echo $e($phone); ?> and talk to a local &mdash; we'll sort it in one call.</p>
    <a class="btn" href="tel:<?php echo $e($tel); ?>">Call now</a>
  </div></div>
</section>

<script>
(function(){var f=document.getElementById('kxQ');if(!f){return;}
setInterval(function(){try{var h=f.contentDocument.documentElement.scrollHeight;
if(h>120&&Math.abs(h-f.offsetHeight)>8){f.style.height=h+'px';}}catch(e){}},400);})();
</script>

<?php } elseif ($section === 'nbn') { $plans = kx_site_plans(); ?>

<section class="hero">
  <div class="glow g1"></div><div class="glow g2"></div>
  <div class="inner">
    <div class="kicker">NBN Internet</div>
    <h1>Find the right plan<br>for <span class="grad">your address</span></h1>
    <p class="sub">NBN plans are address-specific &mdash; enter yours and we'll show your connection
      type and exactly which plans and speeds you can get, then order in a couple of clicks.</p>
    <div class="checker" id="check">
      <p class="t">Check your address</p>
      <p class="s">Straight from the NBN database &mdash; takes about ten seconds.</p>
      <iframe id="kxQ" src="/modules/servers/virtutel_nbn/pages/qualify.php?embed=1&theme=dark"
              scrolling="no" title="NBN address checker"></iframe>
    </div>
  </div>
</section>

<?php if ($plans !== []) { ?>
<section style="text-align:center">
  <div class="inner">
    <div class="kicker">Plans</div>
    <h2>Every tier, one simple price</h2>
    <p class="sub">Month-to-month, unlimited data. Your address decides which tiers are available
      &mdash; check it above and order the one that fits.</p>
    <div class="cards">
      <?php foreach ($plans as [$name, $price, $down, $up]) { ?>
      <div class="card plan">
        <h3><?php echo $e($name); ?></h3>
        <div class="speed"><?php echo $down !== ''
            ? $e($down) . ' Mbps down / ' . $e($up) . ' Mbps up' : 'Speed tier at your address'; ?></div>
        <div class="price">$<?php echo $e($price); ?><small>/mo</small></div>
        <a class="btn ghost" style="margin-top:14px" href="#check">Check availability</a>
      </div>
      <?php } ?>
    </div>
  </div>
</section>
<?php } ?>

<section style="text-align:center">
  <div class="inner">
    <div class="kicker">How it works</div>
    <h2>Three steps to connected</h2>
    <div class="cards">
      <div class="card"><div class="ico">&#128205;</div><h3>1. Check your address</h3>
        <p>We look up the NBN database and show your connection type, ports, and available speeds.</p></div>
      <div class="card"><div class="ico">&#128179;</div><h3>2. Pick a plan &amp; order</h3>
        <p>Only plans your address supports are shown. Switching provider? Your AVC ID transfers
           the service remotely.</p></div>
      <div class="card"><div class="ico">&#128640;</div><h3>3. Get online</h3>
        <p>We lodge the order the moment payment clears and keep you posted &mdash; including
           self-serve appointment booking if a technician is needed.</p></div>
    </div>
  </div>
</section>

<script>
(function(){var f=document.getElementById('kxQ');if(!f){return;}
setInterval(function(){try{var h=f.contentDocument.documentElement.scrollHeight;
if(h>120&&Math.abs(h-f.offsetHeight)>8){f.style.height=h+'px';}}catch(e){}},400);})();
</script>

<?php } elseif ($section === 'mobile' || $section === 'homephone') {
    $isMobile = $section === 'mobile';
?>

<section class="hero">
  <div class="glow g1"></div><div class="glow g2"></div>
  <div class="inner">
    <div class="kicker"><?php echo $isMobile ? 'Mobile' : 'Home Phone'; ?></div>
    <h1><?php echo $isMobile
        ? 'Mobile plans are<br><span class="grad">on the way</span>'
        : 'Home phone,<br><span class="grad">simplified</span>'; ?></h1>
    <p class="sub"><?php echo $isMobile
        ? 'We\'re putting the finishing touches on Korvix Mobile — plans on a major Australian network with the same local support as our NBN.'
        : 'Keep your home number without the copper line — VoIP home phone that rides your NBN connection. Launching soon.'; ?></p>
    <a class="btn" href="/contact.php">Register your interest</a>
  </div>
</section>

<section>
  <div class="inner"><div class="band">
    <h2>Want it sooner?</h2>
    <p class="sub">Call <?php echo $e($phone); ?> and tell us what you need &mdash; early access
      goes to the people who ask.</p>
    <a class="btn ghost" href="tel:<?php echo $e($tel); ?>">Call <?php echo $e($phone); ?></a>
  </div></div>
</section>

<?php } elseif ($section === 'business') { ?>

<section class="hero">
  <div class="glow g1"></div><div class="glow g2"></div>
  <div class="inner">
    <div class="kicker">Business</div>
    <h1>Connectivity your business<br>can <span class="grad">bank on</span></h1>
    <p class="sub">High-availability internet, business email, hosted voice, and backup &mdash;
      all supported locally, all on one bill.</p>
    <a class="btn" href="/contact.php">Talk to us</a>
  </div>
</section>

<section>
  <div class="inner">
    <div class="kicker" style="text-align:center">What we do</div>
    <h2 style="text-align:center">Services built for business</h2>
    <div class="cards">
      <?php
      $blurbs = [
          'business' => ['&#128225;', 'Business-grade connections with high-availability options, static IPs, and priority support.'],
          'email' => ['&#9993;', 'Business email and Microsoft 365 &mdash; mailboxes, Office apps, and migration handled for you.'],
          'voip' => ['&#128222;', 'Hosted VoIP phone systems: keep your numbers, drop the PBX hardware, scale by the handset.'],
          'backup' => ['&#128190;', 'Managed backup for servers and workstations with local support when you need a restore.'],
      ];
      foreach ($groups as [$name, $url]) {
          $lower = strtolower($name);
          $key = null;
          if (strpos($lower, 'business') !== false) { $key = 'business'; }
          elseif (strpos($lower, 'email') !== false) { $key = 'email'; }
          elseif (strpos($lower, 'voip') !== false) { $key = 'voip'; }
          elseif (strpos($lower, 'backup') !== false) { $key = 'backup'; }
          if ($key === null) { continue; }
          [$ico, $blurb] = $blurbs[$key];
      ?>
      <div class="card">
        <div class="ico"><?php echo $ico; ?></div>
        <h3><?php echo $e($name); ?></h3>
        <p><?php echo $blurb; ?></p>
        <a class="btn ghost" href="<?php echo $e($url); ?>">View plans</a>
      </div>
      <?php } ?>
    </div>
  </div>
</section>

<section>
  <div class="inner"><div class="band">
    <h2>Let's talk about your setup</h2>
    <p class="sub">Call <?php echo $e($phone); ?> or drop us a line &mdash; we'll design the right mix
      and give you one clear monthly price.</p>
    <a class="btn" href="/contact.php">Contact us</a>
    &nbsp;
    <a class="btn ghost" href="tel:<?php echo $e($tel); ?>">Call <?php echo $e($phone); ?></a>
  </div></div>
</section>

<?php } ?>

<footer class="kx-footer"><div class="in">
  <div class="grid">
    <div>
      <div style="font-size:24px;font-weight:900;color:#fff;margin-bottom:12px">KORVIX</div>
      <p style="max-width:300px;margin:0 0 14px;line-height:1.7">Fast, local NBN and hosted services
        for Gippsland and beyond &mdash; no lock-ins, no runaround, real local support.</p>
      <a style="color:#e6e9f2;font-weight:700;font-size:16px" href="tel:<?php echo $e($tel); ?>">&#9742;&nbsp;<?php echo $e($phone); ?></a>
    </div>
    <div>
      <h4>Services</h4>
      <ul>
        <?php foreach ($groups as [$name, $url]) { ?>
        <li><a href="<?php echo $e($url); ?>"><?php echo $e($name); ?></a></li>
        <?php } ?>
      </ul>
    </div>
    <div>
      <h4>Support</h4>
      <ul>
        <li><a href="/knowledgebase.php">Knowledgebase</a></li>
        <li><a href="/serverstatus.php">Network Status</a></li>
        <li><a href="/submitticket.php">Open a Ticket</a></li>
        <li><a href="/contact.php">Contact Us</a></li>
        <li><a href="/clientarea.php">Client Area</a></li>
      </ul>
    </div>
  </div>
  <div class="legal">
    <div>&copy; <?php echo date('Y'); ?> Korvix. All rights reserved.</div>
    <div style="display:flex;gap:20px">
      <a href="/terms">Terms of Service</a>
      <a href="/privacy">Privacy Policy</a>
    </div>
  </div>
</div></footer>

</body>
</html>
<?php
}
