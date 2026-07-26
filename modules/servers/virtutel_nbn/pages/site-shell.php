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
    // Google Places key (same addon setting the qualifier uses); empty key
    // degrades to plain text search.
    $placesKey = '';
    try {
        $placesKey = (string) (Capsule::table('tbladdonmodules')
            ->where('module', 'virtutel_nbn_admin')
            ->where('setting', 'google_places_key')
            ->value('value') ?? '');
    } catch (\Throwable $ex) {
        $placesKey = '';
    }
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
            'signup' => 'NBN Internet',
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
        'signup' => 'Get Connected — Korvix',
        'mobile' => 'Mobile — Korvix',
        'homephone' => 'Home Phone — Korvix',
        'business' => 'Business Internet & Services — Korvix',
    ];
    $title = $titles[$section] ?? 'Korvix';

    header('Content-Type: text/html; charset=utf-8');
    // These pages change with every plugin release — never let the
    // browser serve a stale copy.
    header('Cache-Control: no-store, max-age=0');
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $e($title); ?></title>
<style>
  :root { --bg:#0b0f1a; --surface:#141b2b; --line:#2a3347; --text:#e6e9f2; --muted:#98a2b8;
          --brand:#4d8dff; --brand2:#7a5cff; --ok:#2fbf71;
          /* must match the embedded checker's scheme or Chrome backs the
             transparent iframe with an opaque slab */
          color-scheme: dark; }
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
             border-radius:16px; padding:22px 22px 12px; box-shadow:0 18px 50px rgba(0,0,0,.35); text-align:left; }
  .checker .t { font-weight:700; margin:0 0 2px; font-size:17px; }
  .checker .s { color:var(--muted); font-size:13.5px; margin:0 0 10px; }
  .checker iframe { width:100%; border:0; display:block; min-height:84px; background:transparent; }
  /* split hero: copy left, checker right */
  .hero2 { position:relative; overflow:hidden; padding:70px 20px 46px; }
  .hero2 .wrap { max-width:1120px; margin:0 auto; display:grid; position:relative;
                 grid-template-columns:1.05fr .95fr; gap:48px; align-items:center; }
  @media(max-width:960px){ .hero2 .wrap { grid-template-columns:1fr; gap:30px; } }
  .hero2 h1 { text-align:left; }
  .hero2 .sub { margin:0 0 22px; max-width:none; }
  .ticks { list-style:none; margin:0 0 24px; padding:0; }
  .ticks li { margin-bottom:10px; color:#c7cede; font-size:15px; }
  .ticks li:before { content:'\2713'; color:var(--ok); font-weight:800; margin-right:10px; }
  .techs { display:flex; gap:8px; flex-wrap:wrap; }
  .techs span { font-size:12px; font-weight:700; letter-spacing:.05em; color:var(--muted);
                border:1px solid var(--line); border-radius:999px; padding:5px 12px;
                background:rgba(20,27,43,.6); }
  .checkwrap { padding:1px; border-radius:20px;
               background:linear-gradient(135deg,rgba(77,141,255,.65),rgba(122,92,255,.35) 45%,rgba(42,51,71,.6)); }
  .checkwrap .checker { margin:0; max-width:none; border:0; border-radius:19px; background:#10182a;
                        padding:26px 26px 16px; }
  .checkwrap .badge { display:inline-block; font-size:11px; font-weight:800; letter-spacing:.1em;
                      text-transform:uppercase; color:#7fdcaa; background:rgba(47,191,113,.12);
                      border:1px solid rgba(47,191,113,.35); border-radius:999px; padding:4px 11px;
                      margin-bottom:12px; }
  .srow { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:8px; }
  .srow input { flex:1; min-width:220px; font-size:15.5px; padding:12px 14px; background:#0a0e18;
                color:var(--text); border:1px solid var(--line); border-radius:10px; outline:none; }
  .srow input:focus { border-color:var(--brand); }
  /* the whole check flow lives in a fixed-size popup — no page reflow */
  .kxm { position:fixed; inset:0; z-index:1000; display:flex; align-items:center;
         justify-content:center; background:rgba(4,6,12,.74); backdrop-filter:blur(4px); }
  .kxm .panel { width:min(620px,94vw); max-height:88vh; background:#10182a;
                border:1px solid var(--line); border-radius:20px; overflow:hidden;
                display:flex; flex-direction:column; box-shadow:0 30px 80px rgba(0,0,0,.55); }
  .kxm .head { display:flex; justify-content:space-between; align-items:center;
               padding:14px 20px; border-bottom:1px solid var(--line); font-weight:700; }
  .kxm .head button { background:none; border:0; color:var(--muted); font-size:24px;
                      cursor:pointer; line-height:1; padding:0 2px; }
  .kxm .head button:hover { color:#fff; }
  .kxm iframe { width:100%; border:0; display:block; height:200px; transition:height .18s ease;
                padding:10px 20px 20px; }
  /* Google Places dropdown, dark */
  .pac-container { background:#141b2b; border:1px solid #2a3347; border-radius:12px;
                   box-shadow:0 18px 50px rgba(0,0,0,.5); font-family:inherit; margin-top:6px; }
  .pac-item { border-top:1px solid #1c2436; padding:9px 13px; color:#98a2b8; cursor:pointer;
              font-size:13.5px; }
  .pac-item:first-child { border-top:0; }
  .pac-item:hover, .pac-item-selected { background:#1a2338; }
  .pac-item-query { color:#e6e9f2; font-size:14.5px; }
  .pac-matched { color:#4d8dff; }
  .pac-icon { filter:invert(.6); }
  .pac-logo:after { filter:grayscale(1) invert(.8); }
  /* pricing row */
  .pgrid { display:grid; grid-template-columns:repeat(auto-fit,minmax(215px,1fr)); gap:16px;
           margin-top:34px; align-items:stretch; }
  .pcard { background:var(--surface); border:1px solid var(--line); border-radius:18px;
           padding:28px 22px 24px; display:flex; flex-direction:column; align-items:center;
           position:relative; transition:transform .15s,border-color .15s; }
  .pcard:hover { transform:translateY(-4px); border-color:var(--brand); }
  .pcard .nm { font-weight:800; font-size:17px; }
  .pcard .sp { color:var(--muted); font-size:13px; margin:4px 0 12px; }
  .pcard .bar { width:100%; height:6px; border-radius:99px; background:#1e2739; margin-bottom:16px; }
  .pcard .bar i { display:block; height:100%; border-radius:99px;
                  background:linear-gradient(90deg,#4d8dff,#7a5cff); }
  .pcard .pr { font-size:32px; font-weight:800; letter-spacing:-.02em; }
  .pcard .pr small { font-size:13px; color:var(--muted); font-weight:600; }
  .pcard .nt { color:var(--muted); font-size:11.5px; margin:2px 0 14px; }
  .pcard ul { list-style:none; margin:0 0 18px; padding:0; color:var(--muted); font-size:13px;
              line-height:1.9; align-self:flex-start; }
  .pcard ul li:before { content:'\2713'; color:var(--ok); font-weight:700; margin-right:8px; }
  .pcard .btn, .pcard .btn.ghost { margin-top:auto; width:100%; }
  .pcard.feat { border:1px solid var(--brand);
                background:linear-gradient(180deg,rgba(77,141,255,.10),rgba(20,27,43,0) 55%),var(--surface);
                box-shadow:0 16px 44px rgba(77,141,255,.16); }
  @media(min-width:700px){ .pcard.feat { transform:scale(1.045); } .pcard.feat:hover { transform:scale(1.045) translateY(-4px); } }
  .pcard .tag { position:absolute; top:-12px; left:50%; transform:translateX(-50%);
                background:linear-gradient(92deg,#4d8dff,#7a5cff); color:#fff; font-size:10.5px;
                font-weight:800; padding:4px 13px; border-radius:999px; letter-spacing:.08em;
                text-transform:uppercase; white-space:nowrap; }
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

<?php if ($section === 'residential' || $section === 'nbn') {
    $plans = kx_site_plans();
    $isNbn = $section === 'nbn';
    // Featured plan: the 100 Mbps tier, else the middle card.
    $featured = intdiv(max(count($plans) - 1, 0), 2);
    foreach ($plans as $i => $p) {
        if ($p[2] === '100') { $featured = $i; break; }
    }
    $maxDown = 1;
    foreach ($plans as $p) { $maxDown = max($maxDown, (int) $p[2]); }
?>

<section class="hero2">
  <div class="glow g1"></div><div class="glow g2"></div>
  <div class="wrap">
    <div>
      <div class="kicker"><?php echo $isNbn ? 'NBN Internet' : 'Personal'; ?></div>
      <h1><?php echo $isNbn
          ? 'The right NBN plan<br>for <span class="grad">your address</span>'
          : 'Fast, local NBN<br>without the <span class="grad">runaround</span>'; ?></h1>
      <p class="sub">Enter your address and we'll show your connection type and exactly which
        plans and speeds your place supports &mdash; then order in a couple of clicks.</p>
      <ul class="ticks">
        <li>Unlimited data on every plan</li>
        <li>Switch providers remotely &mdash; usually no technician visit</li>
        <li>Local Gippsland support, no offshore scripts</li>
        <li>Month-to-month &mdash; no lock-in, cancel anytime</li>
      </ul>
      <div class="techs">
        <span>FTTP</span><span>HFC</span><span>FTTC</span><span>FTTN/B</span><span>FIXED WIRELESS</span>
      </div>
    </div>
    <div class="checkwrap">
      <div class="checker" id="check">
        <span class="badge">&#9679; Live NBN lookup</span>
        <p class="t">Check your address</p>
        <p class="s">Straight from the NBN database &mdash; takes about ten seconds.</p>
        <form id="kxForm" class="srow">
          <input id="kxAddr" type="text" placeholder="Start typing your address&hellip;"
                 required minlength="8" autocomplete="off" spellcheck="false"
                 autocorrect="off" autocapitalize="off">
          <button class="btn" type="submit">Check address</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php if ($plans !== []) { ?>
<section style="text-align:center;padding-top:30px">
  <div class="inner" style="max-width:1120px">
    <div class="kicker">Plans</div>
    <h2>Simple monthly pricing</h2>
    <p class="sub">Month-to-month, unlimited data. Your address decides which tiers are available
      &mdash; check it above and order the one that fits.</p>
    <div class="pgrid">
      <?php foreach ($plans as $i => [$name, $price, $down, $up]) {
          $pct = $down !== '' ? max(14, (int) round(sqrt((int) $down) / sqrt($maxDown) * 100)) : 50;
      ?>
      <div class="pcard<?php echo $i === $featured ? ' feat' : ''; ?>">
        <?php if ($i === $featured) { ?><div class="tag">Most popular</div><?php } ?>
        <div class="nm"><?php echo $e($name); ?></div>
        <div class="sp"><?php echo $down !== ''
            ? $e($down) . ' Mbps down &middot; ' . $e($up) . ' Mbps up'
            : 'Speed tier at your address'; ?></div>
        <div class="bar"><i style="width:<?php echo $pct; ?>%"></i></div>
        <div class="pr">$<?php echo $e($price); ?><small>/mo</small></div>
        <div class="nt">AUD incl. GST</div>
        <ul>
          <li>Unlimited data</li>
          <li>No lock-in contract</li>
          <li>BYO router (IPoE)</li>
        </ul>
        <a class="btn<?php echo $i === $featured ? '' : ' ghost'; ?>" href="#check">Check availability</a>
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

<section style="padding-top:10px">
  <div class="inner"><div class="band">
    <h2>Not sure what you need?</h2>
    <p class="sub">Call us on <?php echo $e($phone); ?> and talk to a local &mdash; we'll sort it in one call.</p>
    <a class="btn" href="tel:<?php echo $e($tel); ?>">Call <?php echo $e($phone); ?></a>
  </div></div>
</section>

<script>
function kxOpenCheck(a){
  var m=document.createElement('div');m.className='kxm';
  m.innerHTML='<div class="panel"><div class="head"><span>Check your address</span>'
    +'<button type="button" aria-label="Close">&times;</button></div>'
    +'<iframe src="/modules/servers/virtutel_nbn/pages/qualify.php?embed=1&theme=dark&compact=1&q='
    +encodeURIComponent(a)+'" title="NBN address check"></iframe></div>';
  document.body.appendChild(m);
  document.body.style.overflow='hidden';
  // Popup hugs its content: track the embed's body height (shrinks too,
  // unlike documentElement which ratchets), capped to the viewport.
  var f=m.querySelector('iframe');
  var iv=setInterval(function(){try{
    var b=f.contentDocument&&f.contentDocument.body;
    if(!b){return;}
    var h=b.scrollHeight+40; /* + the iframe's own padding */
    var max=Math.floor(window.innerHeight*0.88)-58;
    if(h>110){f.style.height=Math.min(h,max)+'px';
      f.style.overflow=h>max?'auto':'hidden';}
  }catch(e){}},300);
  function close(){clearInterval(iv);m.remove();document.body.style.overflow='';}
  m.addEventListener('click',function(e){if(e.target===m){close();}});
  m.querySelector('.head button').addEventListener('click',close);
  document.addEventListener('keydown',function esc(e){
    if(e.key==='Escape'){close();document.removeEventListener('keydown',esc);}});
}
(function(){
  var form=document.getElementById('kxForm');
  if(!form){return;}
  form.addEventListener('submit',function(ev){
    ev.preventDefault();
    var a=document.getElementById('kxAddr').value.trim();
    if(a.length<8){return;}
    kxOpenCheck(a);
  });
})();
// Google Places on the hero input: picking a suggestion opens the popup
// immediately with the full formatted address.
function kxInitPlaces(){
  var input=document.getElementById('kxAddr');
  if(!input||!window.google||!google.maps||!google.maps.places){return;}
  var ac=new google.maps.places.Autocomplete(input,{
    componentRestrictions:{country:'au'},
    fields:['formatted_address'],
    types:['address']
  });
  ac.addListener('place_changed',function(){
    var p=ac.getPlace();
    var a=(p&&p.formatted_address)||input.value.trim();
    if(a.length<8){return;}
    input.value=a;
    kxOpenCheck(a);
  });
}
</script>
<?php if ($placesKey !== '') { ?>
<script async
  src="https://maps.googleapis.com/maps/api/js?key=<?php echo $e(rawurlencode($placesKey)); ?>&libraries=places&region=AU&callback=kxInitPlaces"></script>
<?php } ?>

<?php } elseif ($section === 'signup') {
    // Onboarding start: the landing page's compact checker links here with
    // the validated LOC ID + address; the full experience (plans, port
    // selection, provider transfer) runs against it immediately.
    $bootLoc = strtoupper(trim((string) ($_GET['vt_locid'] ?? '')));
    $bootAddr = trim((string) ($_GET['vt_addr'] ?? ''));
    $signupSrc = '/modules/servers/virtutel_nbn/pages/qualify.php?embed=1&theme=dark';
    if (preg_match('/^LOC\d{9,15}$/', $bootLoc)) {
        $signupSrc .= '&vt_locid=' . rawurlencode($bootLoc)
            . '&vt_addr=' . rawurlencode(mb_substr($bootAddr, 0, 120));
    }
?>

<section style="padding:48px 20px 40px">
  <div class="inner" style="max-width:880px">
    <div class="kicker">Get connected</div>
    <h1 style="font-size:clamp(26px,4vw,36px)">Let&rsquo;s get you <span class="grad">online</span></h1>
    <p class="sub" style="margin:0 0 24px">Pick the plan that suits &mdash; port selection and
      switching from your current provider are all handled right here, and your address details
      carry straight through to checkout.</p>
    <div class="checkwrap"><div class="checker" style="padding:26px 26px 16px">
      <iframe id="kxQ" src="<?php echo $e($signupSrc); ?>" scrolling="no" title="NBN signup"></iframe>
    </div></div>
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
