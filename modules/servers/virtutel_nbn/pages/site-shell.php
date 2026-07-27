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

/**
 * Home-phone plans: visible products in any product group whose name
 * mentions "phone", with price and up to four description lines.
 *
 * @return array[] [name, price, features[], orderUrl]
 */
function kx_site_phone_plans(): array
{
    try {
        $gids = [];
        foreach (Capsule::table('tblproductgroups')->where('hidden', 0)->get(['id', 'name']) as $g) {
            if (stripos((string) $g->name, 'phone') !== false) {
                $gids[] = (int) $g->id;
            }
        }
        if ($gids === []) {
            return [];
        }

        $rows = Capsule::table('tblproducts')
            ->join('tblpricing', function ($join) {
                $join->on('tblpricing.relid', '=', 'tblproducts.id')
                    ->where('tblpricing.type', '=', 'product')
                    ->where('tblpricing.currency', '=', 1);
            })
            ->whereIn('tblproducts.gid', $gids)
            ->where('tblproducts.hidden', 0)
            ->where('tblproducts.retired', 0)
            ->where('tblpricing.monthly', '>', 0)
            ->orderBy('tblpricing.monthly')
            ->get(['tblproducts.id', 'tblproducts.name', 'tblproducts.description', 'tblpricing.monthly']);

        $plans = [];
        foreach ($rows as $row) {
            $features = array_values(array_filter(array_map(
                fn ($l) => trim(strip_tags((string) $l)),
                preg_split('/\r?\n|<br\s*\/?>/i', (string) $row->description) ?: []
            )));
            $plans[] = [
                (string) $row->name,
                number_format((float) $row->monthly, 2),
                array_slice($features, 0, 4),
                '/cart.php?a=add&pid=' . (int) $row->id,
            ];
        }

        return $plans;
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

function kx_site_render(string $section, string $arg = ''): void
{
    $e = 'kx_site_e';

    // Legal documents render through the same chrome; resolve the doc up
    // front so the <title> is right.
    $legalDocs = [];
    $legalSlug = '';
    if ($section === 'legal') {
        require_once __DIR__ . '/legal.php';
        $legalDocs = kx_legal_docs();
        $legalSlug = isset($legalDocs[$arg]) ? $arg : 'terms';
    }
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
    // Same logo image the portal navbar shows. The uploaded-logo file on
    // disk is preferred (guaranteed servable from this host); LogoURL is
    // the fallback, normalised to an absolute path so it can't break on
    // subdirectory pages like /terms/. Text wordmark when neither exists,
    // or (via onerror) when the image fails to load anyway.
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
        } catch (\Throwable $ex) {
            $logoUrl = '';
        }
        if ($logoUrl !== '' && !preg_match('#^(https?:)?//#i', $logoUrl) && $logoUrl[0] !== '/') {
            $logoUrl = '/' . $logoUrl;
        }
    }

    // Korvix main number.
    $phone = '03 4130 5013';
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
            ['Contact', '/contact/'],
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
            ['Contact', '/contact/'],
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
        'contact' => 'Contact Us — Korvix',
        'notfound' => 'Page Not Found — Korvix',
    ];
    $title = $titles[$section] ?? 'Korvix';
    if ($section === 'legal') {
        $title = $legalDocs[$legalSlug][0] . ' — Korvix';
    }

    // SEO: description + canonical per section (legal uses its tagline).
    $descs = [
        'residential' => 'Fast, local NBN for Gippsland and beyond — unlimited data, no lock-in, real local support. Check your address and see exactly what your place supports.',
        'nbn' => 'NBN plans matched to your address — live NBN lookup, unlimited data, month-to-month, and local Gippsland support.',
        'signup' => 'Get connected with Korvix NBN — check your address, pick your plan, and order in minutes.',
        'mobile' => 'Korvix Mobile is coming — SIM-only 5G plans with unlimited national calls and the same local support as our NBN. Register your interest.',
        'homephone' => 'Keep your home phone number without the line rental — VoIP home phone over your NBN from $9.95/month.',
        'business' => 'Business internet, email and voice with local support — connectivity your business can bank on.',
        'contact' => 'Talk to Korvix — call ' . $phone . ', open a ticket, or start a remote support session.',
        'notfound' => 'That page doesn\'t exist — but the internet does. Head back to Korvix.',
    ];
    $canonPaths = [
        'residential' => '/personal/', 'nbn' => '/personal/nbn/', 'signup' => '/personal/nbn/signup/',
        'mobile' => '/personal/mobile/', 'homephone' => '/personal/home-phone/',
        'business' => '/business/', 'contact' => '/contact/',
    ];
    $metaDesc = $descs[$section] ?? $descs['residential'];
    $canonPath = $canonPaths[$section] ?? '';
    if ($section === 'legal') {
        $metaDesc = $legalDocs[$legalSlug][1];
        $canonPath = '/' . $legalSlug . '/';
    }
    $host = preg_replace('/[^a-zA-Z0-9.\-:]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'korvix.co'));
    $canonical = $canonPath !== '' ? 'https://' . $host . $canonPath : '';

    header('Content-Type: text/html; charset=utf-8');
    if ($section === 'signup' || $section === 'notfound') {
        header('Cache-Control: no-store, max-age=0');
    } else {
        // Launch-ready: short public caching keeps pages snappy; deploys
        // land within five minutes.
        header('Cache-Control: public, max-age=300');
    }
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $e($title); ?></title>
<meta name="description" content="<?php echo $e($metaDesc); ?>">
<?php if ($section === 'notfound') { ?>
<meta name="robots" content="noindex">
<?php } elseif ($canonical !== '') { ?>
<link rel="canonical" href="<?php echo $e($canonical); ?>">
<?php } ?>
<meta property="og:site_name" content="Korvix">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo $e($title); ?>">
<meta property="og:description" content="<?php echo $e($metaDesc); ?>">
<?php if ($canonical !== '') { ?><meta property="og:url" content="<?php echo $e($canonical); ?>">
<?php } ?>
<?php if ($logoUrl !== '') {
    $ogImage = str_starts_with($logoUrl, 'http') ? $logoUrl : 'https://' . $host . $logoUrl; ?>
<meta property="og:image" content="<?php echo $e($ogImage); ?>">
<?php } ?>
<meta name="twitter:card" content="summary">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='14'%20fill='%230b0f1a'/%3E%3Ctext%20x='32'%20y='45'%20font-family='Arial'%20font-size='38'%20font-weight='800'%20text-anchor='middle'%20fill='%234d8dff'%3EK%3C/text%3E%3C/svg%3E">
<script type="application/ld+json">
<?php echo json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'LocalBusiness',
    'name' => 'Korvix',
    'url' => 'https://' . $host . '/',
    'telephone' => '+61 3 4130 5013',
    'description' => 'Local internet service provider — NBN, home phone and business connectivity for Gippsland and beyond.',
    'areaServed' => ['Gippsland VIC', 'Victoria', 'Australia'],
], JSON_UNESCAPED_SLASHES); ?>
</script>
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
  /* same image, size and filter as the portal navbar logo (portal-dark.css)
     so the brand doesn't change between logged-out and logged-in chrome */
  .kx-nav .logo img { height:36px; display:block;
                      filter:invert(1) hue-rotate(180deg) brightness(1.05); }
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
  .hero .glow, .hero2 .glow { position:absolute; border-radius:50%; filter:blur(100px);
                              opacity:.32; pointer-events:none; }
  .hero .g1, .hero2 .g1 { width:440px; height:440px; background:#2b5cff; top:-160px; left:-100px; }
  .hero .g2, .hero2 .g2 { width:400px; height:400px; background:#7a5cff; top:-120px; right:-80px; }
  .hero .inner { position:relative; }
  .checker { max-width:760px; margin:30px auto 0; background:var(--surface); border:1px solid var(--line);
             border-radius:16px; padding:22px 22px 12px; box-shadow:0 18px 50px rgba(0,0,0,.35); text-align:left; }
  .checker .t { font-weight:700; margin:0 0 2px; font-size:17px; }
  .checker .s { color:var(--muted); font-size:13.5px; margin:0 0 10px; }
  .checker iframe { width:100%; border:0; display:block; min-height:84px; background:transparent; }
  /* split hero: copy left, checker right */
  .hero2 { position:relative; overflow:hidden; padding:70px 20px 46px; }
  /* faint engineering-grid texture, faded at the edges — every hero */
  .hero:before, .hero2:before { content:''; position:absolute; inset:0; pointer-events:none;
                  background-image:radial-gradient(rgba(122,146,200,.16) 1px, transparent 1.4px);
                  background-size:26px 26px;
                  -webkit-mask-image:radial-gradient(70% 70% at 50% 30%, #000 30%, transparent 100%);
                  mask-image:radial-gradient(70% 70% at 50% 30%, #000 30%, transparent 100%); }
  .hero .glow, .hero2 .glow { animation:kxDrift 16s ease-in-out infinite; }
  .hero .g2, .hero2 .g2 { animation-delay:-8s; }
  .hero2 .g3 { width:340px; height:340px; background:#b16bff; bottom:-160px; left:36%;
               animation-delay:-4s; }
  @keyframes kxDrift { 0%,100% { transform:translate(0,0); } 50% { transform:translate(26px,18px); } }
  .grad { background-size:200% auto; animation:kxGrad 5s ease-in-out infinite alternate; }
  @keyframes kxGrad { to { background-position:100% center; } }
  .hero2 .wrap { max-width:1120px; margin:0 auto; display:grid; position:relative;
                 grid-template-columns:1.05fr .95fr; gap:48px; align-items:center; }
  @media(max-width:960px){ .hero2 .wrap { grid-template-columns:1fr; gap:30px; } }
  .hero2 h1 { text-align:left; }
  .hero2 .sub { margin:0 0 22px; max-width:none; }
  .ticks { list-style:none; margin:0 0 24px; padding:0; }
  .ticks li { display:flex; align-items:center; gap:12px; margin-bottom:12px;
              color:#c7cede; font-size:15px; }
  .ticks li:before { content:'\2713'; flex:0 0 22px; width:22px; height:22px; border-radius:50%;
                     background:linear-gradient(135deg,#2fbf71,#1d9e55); color:#fff;
                     font-size:12px; font-weight:800; line-height:22px; text-align:center;
                     box-shadow:0 0 14px rgba(47,191,113,.4); }
  .techs { display:flex; gap:8px; flex-wrap:wrap; }
  .techs span { font-size:12px; font-weight:700; letter-spacing:.05em; color:var(--muted);
                border:1px solid var(--line); border-radius:999px; padding:5px 12px;
                background:rgba(20,27,43,.6);
                transition:transform .2s ease, border-color .2s ease, color .2s ease,
                           background .2s ease; }
  .techs span:hover { transform:translateY(-2px); border-color:var(--brand); color:#fff;
                      background:rgba(77,141,255,.14); }
  .checkwrap { padding:1px; border-radius:20px; position:relative;
               background:linear-gradient(135deg,rgba(77,141,255,.65),rgba(122,92,255,.35) 45%,rgba(42,51,71,.6));
               box-shadow:0 34px 80px rgba(31,66,150,.35);
               animation:kxBob 7s ease-in-out infinite; }
  @keyframes kxBob { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-7px); } }
  /* tall embeds (signup) keep the gradient frame but sit still */
  .checkwrap.still { animation:none; }
  .checkwrap .checker { margin:0; max-width:none; border:0; border-radius:19px; background:#10182a;
                        padding:26px 26px 16px; }
  .checkwrap .badge { display:inline-block; font-size:11px; font-weight:800; letter-spacing:.1em;
                      text-transform:uppercase; color:#7fdcaa; background:rgba(47,191,113,.12);
                      border:1px solid rgba(47,191,113,.35); border-radius:999px; padding:4px 11px;
                      margin-bottom:12px; animation:kxPulse 2.6s ease-out infinite; }
  @keyframes kxPulse { 0% { box-shadow:0 0 0 0 rgba(47,191,113,.35); }
                       70%,100% { box-shadow:0 0 0 9px rgba(47,191,113,0); } }
  .srow { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:8px; }
  .srow input { flex:1; min-width:220px; font-size:15.5px; padding:12px 14px; background:#0a0e18;
                color:var(--text); border:1px solid var(--line); border-radius:10px; outline:none;
                transition:border-color .2s ease, box-shadow .2s ease; }
  .srow input:focus { border-color:var(--brand); box-shadow:0 0 0 3px rgba(77,141,255,.22); }
  .chints { display:flex; gap:16px; flex-wrap:wrap; margin:12px 2px 4px;
            color:var(--muted); font-size:11.5px; }
  .chints span:before { content:'\2713'; color:var(--ok); font-weight:800; margin-right:6px; }
  @media (prefers-reduced-motion: reduce) {
    .hero2 .glow, .grad, .checkwrap, .checkwrap .badge, .hiw-step { animation:none; }
  }
  /* the whole check flow lives in a fixed-size popup — no page reflow */
  .kxm { position:fixed; inset:0; z-index:1000; display:flex; align-items:center;
         justify-content:center; background:rgba(4,6,12,.74); backdrop-filter:blur(4px); }
  .kxm .panel { width:min(620px,94vw); max-height:88vh; background:#10182a;
         box-shadow:0 40px 100px rgba(0,0,0,.6), 0 0 0 1px rgba(77,141,255,.25);
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
  /* pricing slider: one row, snap-scrolled, arrows on desktop */
  .pslider { position:relative; margin-top:26px; }
  .pgrid { display:flex; gap:16px; overflow-x:auto; scroll-snap-type:x mandatory;
           padding:24px 6px 18px; align-items:stretch;
           scrollbar-width:thin; scrollbar-color:#2a3347 transparent; }
  .pgrid::-webkit-scrollbar { height:8px; }
  .pgrid::-webkit-scrollbar-thumb { background:#2a3347; border-radius:99px; }
  .pgrid::-webkit-scrollbar-track { background:transparent; }
  .pnav { position:absolute; top:50%; transform:translateY(-50%); z-index:5; width:44px; height:44px;
          border-radius:50%; border:1px solid var(--line); background:rgba(16,24,42,.92);
          color:#e6e9f2; font-size:19px; cursor:pointer; display:flex; align-items:center;
          justify-content:center; box-shadow:0 8px 26px rgba(0,0,0,.4); }
  .pnav:hover { border-color:var(--brand); color:#fff; }
  .pnav.prev { left:-16px; }
  .pnav.next { right:-16px; }
  @media(max-width:700px){
    .pnav { width:38px; height:38px; font-size:17px; }
    .pnav.prev { left:2px; }
    .pnav.next { right:2px; }
  }
  .pcard { background:var(--surface); border:1px solid var(--line); border-radius:18px;
           padding:28px 22px 24px; display:flex; flex-direction:column; align-items:center;
           position:relative; transition:transform .15s,border-color .15s;
           flex:0 0 256px; scroll-snap-align:center; }
  @media(max-width:700px){ .pcard { flex-basis:78vw; } }
  .pcard:hover { transform:translateY(-4px); border-color:var(--brand);
                 box-shadow:0 18px 44px rgba(31,66,150,.28); }
  .pcard .nm { font-weight:800; font-size:17px; }
  .pcard .sp { color:var(--muted); font-size:13px; margin:4px 0 12px; }
  .pcard .bar { width:100%; height:6px; border-radius:99px; background:#1e2739; margin-bottom:16px; }
  .pcard .bar i { display:block; height:100%; border-radius:99px;
                  background:linear-gradient(90deg,#4d8dff,#7a5cff);
                  transform-origin:left; animation:kxBar .9s ease .25s both; }
  @keyframes kxBar { from { transform:scaleX(0); } to { transform:scaleX(1); } }
  .pcard .pr { font-size:32px; font-weight:800; letter-spacing:-.02em; }
  .pcard .pr small { font-size:13px; color:var(--muted); font-weight:600; }
  .pcard .nt { color:var(--muted); font-size:11.5px; margin:2px 0 14px; }
  .pcard ul { list-style:none; margin:0 0 18px; padding:0; color:var(--muted); font-size:13px;
              line-height:1.9; align-self:flex-start; }
  .pcard ul li:before { content:'\2713'; color:var(--ok); font-weight:700; margin-right:8px; }
  .pcard .btn, .pcard .btn.ghost { margin-top:auto; width:100%; }
  .pcard.feat { border:1px solid var(--brand);
                background:linear-gradient(180deg,rgba(77,141,255,.10),rgba(20,27,43,0) 55%),var(--surface);
                box-shadow:0 16px 50px rgba(77,141,255,.22); }
  .pcard.feat .pr { background:linear-gradient(92deg,#7fb2ff,#a88cff);
                    -webkit-background-clip:text; background-clip:text; color:transparent; }
  .pcard.feat .pr small { color:var(--muted); -webkit-text-fill-color:var(--muted); }
  @media(min-width:700px){ .pcard.feat { transform:scale(1.045); } .pcard.feat:hover { transform:scale(1.045) translateY(-4px); } }
  /* coming-soon plan cards: present but visibly not orderable yet */
  .pcard.soonp { filter:saturate(.6); }
  .pcard.soonp:hover { filter:saturate(1); }
  .pcard .tag { position:absolute; top:-12px; left:50%; transform:translateX(-50%);
                background:linear-gradient(92deg,#4d8dff,#7a5cff); color:#fff; font-size:10.5px;
                font-weight:800; padding:4px 13px; border-radius:999px; letter-spacing:.08em;
                text-transform:uppercase; white-space:nowrap; }
  .cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:16px; margin-top:30px; }
  .card { background:var(--surface); border:1px solid var(--line); border-radius:16px; padding:26px 22px;
          transition:transform .22s ease, border-color .22s ease, box-shadow .22s ease; }
  .card:hover { transform:translateY(-5px); border-color:var(--brand);
                box-shadow:0 18px 40px rgba(31,66,150,.24); }
  .card h3 { margin:0 0 8px; font-size:18px; }
  .card p { margin:0 0 16px; color:var(--muted); font-size:14px; }
  .card .ico { font-size:26px; margin-bottom:10px; }
  .plan { text-align:center; display:flex; flex-direction:column; align-items:center; }
  .plan .speed { color:var(--muted); font-size:13.5px; margin:4px 0 12px; }
  .plan .price { font-size:32px; font-weight:800; }
  .plan .price small { font-size:14px; color:var(--muted); font-weight:600; }
  .btn { display:inline-block; border:0; cursor:pointer; text-align:center;
         background:linear-gradient(92deg,#4d8dff,#7a5cff); color:#fff !important; font-weight:700;
         font-size:15px; padding:12px 26px; border-radius:999px;
         transition:transform .18s ease, box-shadow .18s ease, filter .18s ease; }
  .btn:hover { filter:brightness(1.08); transform:translateY(-2px);
               box-shadow:0 10px 26px rgba(77,141,255,.4); }
  .btn.ghost { background:transparent; border:1px solid var(--line); color:var(--text) !important; }
  .btn.ghost:hover { border-color:var(--brand); box-shadow:0 10px 26px rgba(77,141,255,.18); }
  .band { position:relative; overflow:hidden; text-align:center; padding:46px 24px;
          border-radius:20px; border:1px solid transparent;
          background:linear-gradient(120deg,rgba(77,141,255,.13),rgba(122,92,255,.13)) padding-box,
                     linear-gradient(135deg,rgba(77,141,255,.55),rgba(122,92,255,.3) 60%,rgba(42,51,71,.6)) border-box;
          box-shadow:0 26px 60px rgba(31,66,150,.22); }
  .band:before { content:''; position:absolute; inset:0; pointer-events:none;
          background-image:radial-gradient(rgba(122,146,200,.13) 1px, transparent 1.4px);
          background-size:26px 26px;
          -webkit-mask-image:radial-gradient(80% 90% at 50% 50%, #000 40%, transparent 100%);
          mask-image:radial-gradient(80% 90% at 50% 50%, #000 40%, transparent 100%); }
  .band > * { position:relative; }
  /* footer */
  .kx-footer { background:#070a12; border-top:0; margin-top:64px; color:#98a2b8; font-size:14px;
               position:relative; }
  .kx-footer:before { content:''; position:absolute; top:0; left:0; right:0; height:1px;
               background:linear-gradient(90deg,transparent,#4d8dff 30%,#7a5cff 70%,transparent);
               opacity:.55; }
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
  /* ---------- how it works: glowing stepper ---------- */
  .hiw-track { display:grid; grid-template-columns:repeat(3,1fr); gap:26px; margin-top:44px;
               position:relative; text-align:left; }
  .hiw-track:before { content:''; position:absolute; top:-16px; left:14%; right:14%; height:2px;
               background:linear-gradient(90deg,#4d8dff,#7a5cff 55%,#b16bff); opacity:.5;
               border-radius:99px; }
  .hiw-step { position:relative; background:var(--surface); border:1px solid var(--line);
              border-radius:18px; padding:26px 24px 22px; overflow:hidden;
              transition:transform .25s ease, border-color .25s ease, box-shadow .25s ease;
              animation:hiwFade .7s ease both; }
  .hiw-step:nth-child(2) { animation-delay:.12s; }
  .hiw-step:nth-child(3) { animation-delay:.24s; }
  @keyframes hiwFade { from { opacity:0; } to { opacity:1; } }
  .hiw-step:hover { transform:translateY(-6px); border-color:var(--brand);
                    box-shadow:0 20px 46px rgba(28,60,140,.3); }
  .hiw-num { width:44px; height:44px; margin:0 0 16px; border-radius:50%;
             background:linear-gradient(135deg,#4d8dff,#7a5cff);
             color:#fff; font-weight:800; font-size:18px; line-height:44px; text-align:center;
             box-shadow:0 0 24px rgba(77,141,255,.55); }
  .hiw-ghost { position:absolute; top:-6px; right:10px; font-size:88px; font-weight:900;
               letter-spacing:-.04em; color:#fff; opacity:.045; pointer-events:none; }
  .hiw-step h3 { margin:0 0 8px; font-size:18px; }
  .hiw-step p { margin:0; color:var(--muted); font-size:14px; line-height:1.65; }
  .hiw-chip { display:inline-block; margin-top:16px; font-size:12px; font-weight:600;
              color:#9ec2ff; background:#0a0e18; border:1px solid #24314c;
              border-radius:999px; padding:5px 13px; }
  @media(max-width:860px){
    .hiw-track { grid-template-columns:1fr; gap:34px; }
    .hiw-track:before { top:6%; bottom:6%; left:50%; right:auto; width:2px; height:auto;
               background:linear-gradient(180deg,#4d8dff,#7a5cff 55%,#b16bff); }
  }
  /* ---------- network map + FAQ (personal home) ---------- */
  .net-grid { display:grid; grid-template-columns:1.1fr .9fr; gap:44px; align-items:center;
              margin-top:38px; text-align:left; }
  @media(max-width:860px){ .net-grid { grid-template-columns:1fr; gap:28px; } }
  .net-map svg { width:100%; height:auto; display:block; overflow:visible; }
  .net-land { fill:#121a2a; stroke:#33405c; stroke-width:.45; stroke-linejoin:round; }
  .net-dotfill { fill:url(#kxDots); stroke:none; }
  .net-land-g { filter:drop-shadow(0 6px 18px rgba(20,40,90,.45)); }
  .net-line { fill:none; stroke:url(#kxNetGrad); stroke-width:.8; stroke-linecap:round;
              stroke-dasharray:2 2.4; animation:kxFlow 1.4s linear infinite; }
  @keyframes kxFlow { to { stroke-dashoffset:-8.8; } }
  .net-node { stroke:#0b0f1a; stroke-width:.6; }
  .net-node.c-b, .net-line { stroke-opacity:.9; }
  .c-b { fill:#4d8dff; } .c-p { fill:#a88cff; } .c-g { fill:#2fbf71; }
  /* coming-soon sites: hollow nodes, faint dashed static links */
  .c-s { fill:#0b0f1a; stroke:#5b6879; stroke-width:.7; stroke-dasharray:1 .8; }
  .net-line.soon { stroke:#5b6879; stroke-opacity:.45; animation:none; stroke-dasharray:1.2 2; }
  .net-label.soon { fill:#5b6879; }
  .net-ring { fill:none; stroke-width:.7; opacity:.6; animation:kxPing 2.4s ease-out infinite; }
  .net-ring.c-b { stroke:#4d8dff; fill:none; }
  .net-ring.c-p { stroke:#a88cff; fill:none; animation-delay:.5s; }
  .net-ring.c-g { stroke:#2fbf71; fill:none; animation-delay:1s; }
  @keyframes kxPing { 0% { opacity:.65; } 100% { opacity:0; transform:scale(2.1); } }
  .net-ring { transform-box:fill-box; transform-origin:center; }
  .net-label { font-size:2.6px; font-weight:700; letter-spacing:.24px; fill:#98a2b8;
               font-family:'Inter',-apple-system,sans-serif; }
  .net-list { list-style:none; margin:0; padding:0; }
  .net-list li { display:flex; gap:14px; margin-bottom:20px; }
  .net-dot { flex:0 0 12px; width:12px; height:12px; border-radius:50%; margin-top:6px; }
  .net-list strong { display:block; margin-bottom:4px; font-size:16px; }
  .net-list p { margin:0; color:var(--muted); font-size:14px; line-height:1.65; }
  .faq { max-width:760px; margin:34px auto 0; text-align:left; }
  .faq details { background:var(--surface); border:1px solid var(--line); border-radius:12px;
                 margin-bottom:10px; padding:0 20px;
                 transition:border-color .2s ease, box-shadow .2s ease; }
  .faq details[open] { border-color:var(--brand); box-shadow:0 10px 30px rgba(31,66,150,.18); }
  .faq summary { cursor:pointer; font-weight:700; font-size:15.5px; padding:16px 30px 16px 0;
                 list-style:none; position:relative; }
  .faq summary::-webkit-details-marker { display:none; }
  .faq summary:after { content:'+'; position:absolute; right:0; top:12px; font-size:22px;
                 font-weight:400; color:var(--brand); transition:transform .2s ease; }
  .faq details[open] summary:after { transform:rotate(45deg); }
  .faq p { margin:0 0 18px; color:var(--muted); font-size:14.5px; line-height:1.7; }
  /* ---------- legal documents ---------- */
  .lg-head { padding:56px 20px 8px; }
  .lg-nav { display:flex; gap:8px; flex-wrap:wrap; margin:18px 0 0; }
  .lg-nav a { border:1px solid var(--line); border-radius:999px; padding:6px 15px; font-size:13px;
              font-weight:600; color:var(--muted); }
  .lg-nav a:hover { color:#fff; border-color:#3a4763; }
  .lg-nav a.on { color:#fff; background:var(--surface); border-color:var(--brand); }
  .lg-prose { max-width:860px; }
  .lg-prose h2 { font-size:20px; margin:34px 0 10px; }
  .lg-prose h3 { font-size:16px; margin:22px 0 8px; }
  .lg-prose p, .lg-prose li { color:#c7cede; font-size:15px; }
  .lg-prose ul { padding-left:22px; }
  .lg-prose li { margin-bottom:8px; }
  .lg-prose table { width:100%; border-collapse:collapse; margin:14px 0 6px; font-size:14.5px; }
  .lg-prose th, .lg-prose td { border:1px solid var(--line); padding:10px 14px; text-align:left;
                               vertical-align:top; color:#c7cede; }
  .lg-prose th { width:200px; color:#e6e9f2; background:var(--surface); font-weight:700;
                 white-space:nowrap; }
  .lg-prose .cis { margin-top:10px; }
  .lg-updated { color:var(--muted); font-size:13px; margin-top:6px; }
  @media(max-width:700px){ .lg-prose th { white-space:normal; width:120px; } }
  /* ---------- mobile pass ---------- */
  @media(max-width:760px){
    section { padding:40px 16px; }
    .hero2 { padding:42px 16px 32px; }
    .hero2 .wrap { gap:26px; }
    .hero2 h1 br { display:none; }   /* let headlines wrap naturally */
    .ticks li { font-size:14px; }
    h2 { font-size:22px; }
    .kx-switch .in, .kx-topbar .in { padding-left:14px; padding-right:14px; }
    /* with the left links hidden, keep phone + login pinned right */
    .kx-topbar .in { justify-content:flex-end; }
    .kx-nav .in { padding:14px 16px; gap:10px; }
    /* nav: one scrollable row instead of wrapped link soup */
    .kx-nav .links { width:100%; margin-left:0; flex-wrap:nowrap; overflow-x:auto;
                     gap:20px; padding-bottom:2px; scrollbar-width:none; }
    .kx-nav .links::-webkit-scrollbar { display:none; }
    .kx-nav .links a { white-space:nowrap; font-size:14px; }
    .srow { gap:8px; }
    .srow .btn { width:100%; }
    .checkwrap .checker { padding:20px 16px 12px; }
    /* popup: top-anchored so the keyboard doesn't shove it off screen */
    .kxm { align-items:flex-start; padding-top:12px; }
    .kxm .panel { width:96vw; max-height:92vh; }
    .kxm iframe { padding:8px 12px 14px; }
    .band { padding:34px 18px; }
    .pgrid { padding:22px 2px 14px; }
    .kx-footer .in { padding:36px 16px 0; }
  }
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
    <a href="https://go.getscreen.me/invite/683032125" target="_blank" rel="noopener">Get Remote Support</a>
    <a href="/clientarea.php?action=invoices">Pay an Invoice</a>
  </div>
  <div class="r" style="display:flex;gap:14px;align-items:center">
    <a class="ph" href="tel:<?php echo $e($tel); ?>">&#9742;&#65038;&nbsp;<?php echo $e($phone); ?></a>
    <?php if ($loggedIn) { ?>
      <a class="cp" href="/logout.php">Logout</a>
    <?php } else { ?>
      <a class="cp" href="/clientarea.php">Portal Login</a>
    <?php } ?>
  </div>
</div></div>

<div class="kx-nav"><div class="in">
  <a class="logo" href="/"><?php if ($logoUrl !== '') { ?><img src="<?php
      echo $e($logoUrl); ?>" alt="Korvix"
      onerror="this.closest('a').textContent='KORVIX'"><?php } else { ?>KORVIX<?php } ?></a>
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
  <div class="glow g1"></div><div class="glow g2"></div><div class="glow g3"></div>
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
        <div class="chints">
          <span>No credit card</span><span>No obligation</span><span>Instant result</span>
        </div>
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
    <div class="pslider">
    <button class="pnav prev" type="button" aria-label="Previous plans">&larr;</button>
    <button class="pnav next" type="button" aria-label="More plans">&rarr;</button>
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
        <a class="btn<?php echo $i === $featured ? '' : ' ghost'; ?>" href="#check"
           onclick="kxOpenCheck('',{n:<?php echo htmlspecialchars(json_encode((string) $name), ENT_QUOTES); ?>,
           d:<?php echo (int) $down; ?>});return false">Check availability</a>
      </div>
      <?php } ?>
    </div>
    </div>
  </div>
</section>
<?php } ?>

<section style="text-align:center">
  <div class="inner">
    <div class="kicker">How it works</div>
    <h2>Address to online in <span class="grad">three moves</span></h2>
    <p class="sub">No paperwork, no phone queues, no "please hold" &mdash; the whole thing happens right here.</p>
    <div class="hiw-track">
      <div class="hiw-step">
        <div class="hiw-ghost">01</div>
        <div class="hiw-num">1</div>
        <h3>Punch in your address</h3>
        <p>We query the NBN database live and show exactly what your place can do &mdash;
           connection type, ports, and the fastest speed your line supports.</p>
        <div class="hiw-chip">&#9201;&#65038; takes about 30 seconds</div>
      </div>
      <div class="hiw-step">
        <div class="hiw-ghost">02</div>
        <div class="hiw-num">2</div>
        <h3>Pick your speed</h3>
        <p>Only plans your address can actually deliver are shown &mdash; no bait, no guesswork.
           Already connected? Switching to us happens remotely, usually within a day.</p>
        <div class="hiw-chip">&#128275;&#65038; no lock-in, ever</div>
      </div>
      <div class="hiw-step">
        <div class="hiw-ghost">03</div>
        <div class="hiw-num">3</div>
        <h3>We handle the rest</h3>
        <p>Your order is lodged the second payment clears. Track every step in your portal,
           book the technician yourself if one's needed, and we'll email the moment you're live.</p>
        <div class="hiw-chip">&#9889;&#65038; transfers often connect same-day</div>
      </div>
    </div>
  </div>
</section>

<?php if (!$isNbn) { ?>
<section style="text-align:center;padding-top:26px">
  <div class="inner" style="max-width:1120px">
    <div class="kicker">Our network</div>
    <h2>Local roots, <span class="grad">east-coast backbone</span></h2>
    <p class="sub">We're not a badge on someone else's network. Korvix runs its own equipment in
      Melbourne and Sydney, with Gippsland as home base &mdash; your traffic rides our gear from
      the moment it leaves the NBN.</p>
    <div class="net-grid">
      <div class="net-map">
        <svg viewBox="0 0 100 92.1" role="img" aria-label="Map of Australia showing the Korvix network">
          <defs>
            <linearGradient id="kxNetGrad" x1="0" y1="0" x2="1" y2="0">
              <stop offset="0" stop-color="#4d8dff"/><stop offset="1" stop-color="#a88cff"/>
            </linearGradient>
            <pattern id="kxDots" width="1.7" height="1.7" patternUnits="userSpaceOnUse">
              <circle cx=".85" cy=".85" r=".33" fill="rgba(122,146,200,.42)"/>
            </pattern>
          </defs>
          <g class="net-land-g">
            <path class="net-land" d="M75.1,8.6L76.0,10.8L77.6,9.8L78.4,11.0L79.6,12.1L79.4,13.3L79.9,15.7L80.3,17.1L80.9,17.4L81.6,19.8L81.3,21.3L82.1,23.2L84.8,24.6L86.6,25.9L88.3,27.2L87.9,27.8L89.4,29.6L90.3,32.6L91.3,32.0L92.3,33.2L92.9,32.8L93.4,35.7L95.1,37.5L96.3,38.5L98.2,40.8L98.9,43.0L99.0,44.6L98.8,46.4L100.0,48.7L99.9,51.2L99.4,52.5L98.8,55.0L98.8,56.6L98.3,58.6L97.2,61.1L95.4,62.5L94.5,64.7L93.6,66.0L92.9,68.5L91.9,69.8L91.3,71.9L91.0,73.9L91.1,74.7L89.7,75.7L86.9,75.8L84.6,77.0L83.5,78.0L82.0,79.2L79.9,78.0L78.4,77.5L78.8,76.1L77.4,76.6L75.2,78.6L73.1,77.9L71.7,77.4L70.3,77.2L67.9,76.4L66.3,74.7L65.8,72.6L65.2,71.2L64.0,70.0L61.6,69.7L62.4,68.3L61.8,66.3L60.6,68.2L58.4,68.7L59.7,67.2L60.1,65.5L61.0,64.2L60.8,62.1L58.8,64.5L57.3,65.4L56.3,67.7L54.4,66.5L54.4,65.0L52.9,63.0L51.6,62.0L52.0,61.3L48.8,59.6L47.1,59.5L44.7,58.2L40.3,58.4L37.0,59.4L34.2,60.4L31.8,60.2L29.2,61.6L27.1,62.3L26.6,63.7L25.7,64.9L23.5,64.9L22.0,65.2L19.8,64.7L18.0,65.0L16.3,65.1L14.8,66.6L14.1,66.5L12.8,67.3L11.6,68.2L9.8,68.0L8.2,68.0L5.5,66.3L4.2,65.7L4.2,64.1L5.5,63.7L5.9,63.1L5.8,62.1L6.1,60.2L5.8,58.5L4.5,55.7L4.1,54.1L4.2,52.5L3.2,50.7L3.2,49.9L2.1,48.7L1.8,46.6L0.3,44.3L0.0,43.2L1.1,44.4L0.3,41.8L1.5,42.6L2.2,43.7L2.2,42.2L1.0,40.0L0.7,39.2L0.1,38.3L0.4,36.7L0.9,36.0L1.3,34.6L1.0,33.0L2.0,31.0L2.2,33.1L3.3,31.2L5.3,30.2L6.5,29.1L8.4,28.0L9.5,27.8L10.2,28.2L12.2,27.1L13.7,26.8L14.0,26.2L14.7,25.9L16.1,26.0L18.7,25.2L20.0,23.9L20.7,22.5L22.1,21.0L22.2,19.9L22.3,18.4L24.0,16.0L25.1,18.4L26.2,17.9L25.3,16.6L26.0,15.2L27.1,15.8L27.4,13.7L28.8,12.3L29.4,11.2L30.7,10.7L30.7,10.0L31.8,10.3L31.8,9.6L32.9,9.2L34.1,8.8L36.0,10.1L37.3,11.7L38.9,11.8L40.5,12.0L39.9,10.5L41.1,8.2L42.3,7.5L41.9,6.8L42.9,5.2L44.5,4.2L45.7,4.6L47.8,4.0L47.8,2.6L45.9,1.7L47.3,1.3L48.9,2.0L50.2,3.1L52.3,3.8L53.0,3.6L54.6,4.4L56.0,3.6L57.0,3.9L57.6,3.3L58.7,4.7L58.0,6.2L57.1,7.3L56.2,7.4L56.5,8.5L55.8,9.9L54.9,11.3L55.1,12.1L57.1,13.6L59.0,14.5L60.3,15.5L62.1,17.2L62.8,17.1L64.1,17.9L64.4,18.7L66.8,19.7L68.4,18.7L68.9,17.2L69.4,16.0L69.7,14.4L70.5,12.2L70.2,10.9L70.3,10.1L70.0,8.5L70.4,6.4L70.9,5.8L70.5,4.9L71.1,3.4L71.5,1.8L71.6,1.0L72.5,0.0L73.2,1.4L73.4,3.1L74.0,3.5L74.1,4.6L75.0,6.1L75.2,7.6L75.1,8.6Z"/>
            <path class="net-land" d="M79.7,84.2L82.1,85.1L83.4,84.7L85.4,84.2L86.9,84.4L87.1,87.7L86.2,88.7L85.9,90.9L85.1,90.1L83.3,92.1L82.8,91.9L81.3,91.9L79.8,89.5L79.4,87.6L78.0,85.2L78.1,83.9L79.7,84.2Z"/>
            <path class="net-dotfill" d="M75.1,8.6L76.0,10.8L77.6,9.8L78.4,11.0L79.6,12.1L79.4,13.3L79.9,15.7L80.3,17.1L80.9,17.4L81.6,19.8L81.3,21.3L82.1,23.2L84.8,24.6L86.6,25.9L88.3,27.2L87.9,27.8L89.4,29.6L90.3,32.6L91.3,32.0L92.3,33.2L92.9,32.8L93.4,35.7L95.1,37.5L96.3,38.5L98.2,40.8L98.9,43.0L99.0,44.6L98.8,46.4L100.0,48.7L99.9,51.2L99.4,52.5L98.8,55.0L98.8,56.6L98.3,58.6L97.2,61.1L95.4,62.5L94.5,64.7L93.6,66.0L92.9,68.5L91.9,69.8L91.3,71.9L91.0,73.9L91.1,74.7L89.7,75.7L86.9,75.8L84.6,77.0L83.5,78.0L82.0,79.2L79.9,78.0L78.4,77.5L78.8,76.1L77.4,76.6L75.2,78.6L73.1,77.9L71.7,77.4L70.3,77.2L67.9,76.4L66.3,74.7L65.8,72.6L65.2,71.2L64.0,70.0L61.6,69.7L62.4,68.3L61.8,66.3L60.6,68.2L58.4,68.7L59.7,67.2L60.1,65.5L61.0,64.2L60.8,62.1L58.8,64.5L57.3,65.4L56.3,67.7L54.4,66.5L54.4,65.0L52.9,63.0L51.6,62.0L52.0,61.3L48.8,59.6L47.1,59.5L44.7,58.2L40.3,58.4L37.0,59.4L34.2,60.4L31.8,60.2L29.2,61.6L27.1,62.3L26.6,63.7L25.7,64.9L23.5,64.9L22.0,65.2L19.8,64.7L18.0,65.0L16.3,65.1L14.8,66.6L14.1,66.5L12.8,67.3L11.6,68.2L9.8,68.0L8.2,68.0L5.5,66.3L4.2,65.7L4.2,64.1L5.5,63.7L5.9,63.1L5.8,62.1L6.1,60.2L5.8,58.5L4.5,55.7L4.1,54.1L4.2,52.5L3.2,50.7L3.2,49.9L2.1,48.7L1.8,46.6L0.3,44.3L0.0,43.2L1.1,44.4L0.3,41.8L1.5,42.6L2.2,43.7L2.2,42.2L1.0,40.0L0.7,39.2L0.1,38.3L0.4,36.7L0.9,36.0L1.3,34.6L1.0,33.0L2.0,31.0L2.2,33.1L3.3,31.2L5.3,30.2L6.5,29.1L8.4,28.0L9.5,27.8L10.2,28.2L12.2,27.1L13.7,26.8L14.0,26.2L14.7,25.9L16.1,26.0L18.7,25.2L20.0,23.9L20.7,22.5L22.1,21.0L22.2,19.9L22.3,18.4L24.0,16.0L25.1,18.4L26.2,17.9L25.3,16.6L26.0,15.2L27.1,15.8L27.4,13.7L28.8,12.3L29.4,11.2L30.7,10.7L30.7,10.0L31.8,10.3L31.8,9.6L32.9,9.2L34.1,8.8L36.0,10.1L37.3,11.7L38.9,11.8L40.5,12.0L39.9,10.5L41.1,8.2L42.3,7.5L41.9,6.8L42.9,5.2L44.5,4.2L45.7,4.6L47.8,4.0L47.8,2.6L45.9,1.7L47.3,1.3L48.9,2.0L50.2,3.1L52.3,3.8L53.0,3.6L54.6,4.4L56.0,3.6L57.0,3.9L57.6,3.3L58.7,4.7L58.0,6.2L57.1,7.3L56.2,7.4L56.5,8.5L55.8,9.9L54.9,11.3L55.1,12.1L57.1,13.6L59.0,14.5L60.3,15.5L62.1,17.2L62.8,17.1L64.1,17.9L64.4,18.7L66.8,19.7L68.4,18.7L68.9,17.2L69.4,16.0L69.7,14.4L70.5,12.2L70.2,10.9L70.3,10.1L70.0,8.5L70.4,6.4L70.9,5.8L70.5,4.9L71.1,3.4L71.5,1.8L71.6,1.0L72.5,0.0L73.2,1.4L73.4,3.1L74.0,3.5L74.1,4.6L75.0,6.1L75.2,7.6L75.1,8.6Z"/>
            <path class="net-dotfill" d="M79.7,84.2L82.1,85.1L83.4,84.7L85.4,84.2L86.9,84.4L87.1,87.7L86.2,88.7L85.9,90.9L85.1,90.1L83.3,92.1L82.8,91.9L81.3,91.9L79.8,89.5L79.4,87.6L78.0,85.2L78.1,83.9L79.7,84.2Z"/>
          </g>
          <path class="net-line" d="M78.6,75.8 Q87,68 94.1,64.8"/>
          <path class="net-line" d="M78.6,75.8 Q81.2,77.2 83.8,76.7"/>
          <path class="net-line soon" d="M94.1,64.8 Q98.5,56 98.6,46.9"/>
          <path class="net-line soon" d="M78.6,75.8 Q40,72 6.3,59.5"/>
          <circle class="net-node c-s" cx="98.6" cy="46.9" r="1.8"/>
          <circle class="net-node c-s" cx="6.3" cy="59.5" r="1.8"/>
          <text class="net-label soon" x="96.4" y="45" text-anchor="end">BRISBANE &middot; SOON</text>
          <text class="net-label soon" x="8.4" y="57.6">PERTH &middot; SOON</text>
          <circle class="net-ring c-b" cx="78.6" cy="75.8" r="4"/>
          <circle class="net-node c-b" cx="78.6" cy="75.8" r="2.2"/>
          <circle class="net-ring c-p" cx="94.1" cy="64.8" r="4"/>
          <circle class="net-node c-p" cx="94.1" cy="64.8" r="2"/>
          <circle class="net-ring c-g" cx="83.8" cy="76.7" r="3.4"/>
          <circle class="net-node c-g" cx="83.8" cy="76.7" r="1.7"/>
          <text class="net-label" x="75.6" y="74" text-anchor="end">MELBOURNE</text>
          <text class="net-label" x="92" y="62.4" text-anchor="end">SYDNEY</text>
          <text class="net-label" x="85.6" y="80.6">GIPPSLAND</text>
        </svg>
      </div>
      <ul class="net-list">
        <li><span class="net-dot" style="background:#4d8dff;box-shadow:0 0 12px rgba(77,141,255,.7)"></span>
          <div><strong>Melbourne &mdash; network core</strong>
          <p>Where Korvix meets the NBN. Our core routing and provisioning gear lives here,
             a short hop from most Victorian traffic.</p></div></li>
        <li><span class="net-dot" style="background:#a88cff;box-shadow:0 0 12px rgba(168,140,255,.7)"></span>
          <div><strong>Sydney &mdash; point of presence</strong>
          <p>Our second site keeps east-coast routes short: transit, peering and redundancy,
             so one bad day in one city doesn't take you offline.</p></div></li>
        <li><span class="net-dot" style="background:#2fbf71;box-shadow:0 0 12px rgba(47,191,113,.7)"></span>
          <div><strong>Gippsland &mdash; home base</strong>
          <p>Where we live and where support answers from. When you call, you're talking to
             someone on the same network &mdash; probably in the same postcode.</p></div></li>
        <li><span class="net-dot" style="background:transparent;border:2px dashed #5b6879"></span>
          <div><strong style="color:#98a2b8">Perth &amp; Brisbane &mdash; coming soon</strong>
          <p>The next two sites on the roadmap, extending the network national.</p></div></li>
      </ul>
    </div>
  </div>
</section>

<section style="text-align:center">
  <div class="inner" style="max-width:1120px">
    <div class="kicker">Why Korvix</div>
    <h2>Built the way an ISP <span class="grad">should be</span></h2>
    <div class="cards" style="text-align:left">
      <div class="card"><div class="ico">&#128222;&#65038;</div><h3>Local humans</h3>
        <p>Support answers in Gippsland, not a script farm. The person fixing your fault can
           see your actual line, live.</p></div>
      <div class="card"><div class="ico">&#128736;&#65038;</div><h3>Self-serve everything</h3>
        <p>Run real NBN line checks from your portal, book technicians yourself, and watch your
           connection order progress step by step.</p></div>
      <div class="card"><div class="ico">&#128200;</div><h3>Honest speeds</h3>
        <p>We only show plans your address can actually deliver &mdash; checked against the NBN
           database before you pay a cent.</p></div>
      <div class="card"><div class="ico">&#128274;</div><h3>No traps</h3>
        <p>Month-to-month, no lock-in, no exit fees, and your AVC ID is right there in your
           portal if you ever want to leave. We'd rather earn the next month.</p></div>
    </div>
  </div>
</section>

<section style="text-align:center;padding-top:20px">
  <div class="inner">
    <div class="kicker">Good to know</div>
    <h2>Questions people <span class="grad">actually ask</span></h2>
    <div class="faq">
      <details>
        <summary>How long until I'm online?</summary>
        <p>Switching from another provider is done remotely and often connects the same day.
           A brand-new connection depends on your NBN technology &mdash; if a technician visit is
           needed you'll pick the appointment time yourself, usually within a week or two.</p>
      </details>
      <details>
        <summary>Do I need a technician visit?</summary>
        <p>Usually not. If your address has had NBN before, switching is remote. The address
           checker tells you upfront if your place needs an install &mdash; no surprises after
           you've paid.</p>
      </details>
      <details>
        <summary>What router do I need?</summary>
        <p>Almost any modern router works. Korvix uses automatic (IPoE/DHCP) connections &mdash;
           no username or password to type in. Plug it into the NBN box, set the WAN to
           Automatic, done. Not sure about yours? Call us before you buy anything.</p>
      </details>
      <details>
        <summary>Am I locked into a contract?</summary>
        <p>No. Every plan is month-to-month. Cancel from your portal any time and the service
           runs to the end of the period you've paid for &mdash; no exit fees, no "retention team".</p>
      </details>
      <details>
        <summary>What actually happens on switch day?</summary>
        <p>Your connection moves to us remotely &mdash; expect a brief dropout, usually minutes.
           The moment it completes we email you, your portal flips to active, and billing starts
           from that day, not from when you ordered.</p>
      </details>
      <details>
        <summary>Is unlimited really unlimited?</summary>
        <p>Yes &mdash; no data caps, no shaping schedules. Like every ISP we have an acceptable
           use policy for genuinely abnormal use (running a data centre off a home plan), but
           ordinary households never hit it. Stream, game, work &mdash; it's your connection.</p>
      </details>
    </div>
  </div>
</section>
<?php } ?>

<section style="padding-top:10px">
  <div class="inner"><div class="band">
    <h2>Not sure what you need?</h2>
    <p class="sub">Call us on <?php echo $e($phone); ?> and talk to a local &mdash; we'll sort it in one call.</p>
    <a class="btn" href="tel:<?php echo $e($tel); ?>">Call <?php echo $e($phone); ?></a>
  </div></div>
</section>

<script>
function kxOpenCheck(a,plan){
  a=(a||'').trim();
  // plan = {n:name, d:down} when opened from a plan card: the verdict
  // inside the popup answers for THAT plan specifically.
  var planQ='';
  if(plan&&plan.n){planQ='&vt_plan='+encodeURIComponent(plan.n)+'&vt_down='+(plan.d||0);}
  var m=document.createElement('div');m.className='kxm';
  m.innerHTML='<div class="panel"><div class="head"><span>Check your address</span>'
    +'<button type="button" aria-label="Close">&times;</button></div>'
    +'<iframe src="/modules/servers/virtutel_nbn/pages/qualify.php?embed=1&theme=dark&compact=1'
    +planQ+(a?'&q='+encodeURIComponent(a):'')+'" title="NBN address check"></iframe></div>';
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
// Plan slider: arrow paging, and open centred on the featured card.
(function(){
  var g=document.querySelector('.pgrid');
  if(!g){return;}
  document.querySelectorAll('.pnav').forEach(function(b){
    b.addEventListener('click',function(){
      g.scrollBy({left:(b.classList.contains('next')?1:-1)*288,behavior:'smooth'});
    });
  });
  var f=g.querySelector('.pcard.feat');
  if(f){g.scrollLeft=Math.max(0,f.offsetLeft-(g.clientWidth-f.offsetWidth)/2);}
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
    <div class="checkwrap still"><div class="checker" style="padding:26px 26px 16px">
      <iframe id="kxQ" src="<?php echo $e($signupSrc); ?>" scrolling="no" title="NBN signup"></iframe>
    </div></div>
  </div>
</section>

<script>
(function(){var f=document.getElementById('kxQ');if(!f){return;}
setInterval(function(){try{var h=f.contentDocument.documentElement.scrollHeight;
if(h>120&&Math.abs(h-f.offsetHeight)>8){f.style.height=h+'px';}}catch(e){}},400);})();
</script>

<?php } elseif ($section === 'mobile') {
    // Launch line-up (coming soon — prices to be confirmed at launch):
    // [name, price, GB, featured]
    $mobilePlans = [
        ['Saver', '25', '20', false],
        ['Value', '35', '45', false],
        ['Essential', '45', '100', true],
        ['Premium', '55', '180', false],
    ];
?>

<section class="hero2">
  <div class="glow g1"></div><div class="glow g2"></div><div class="glow g3"></div>
  <div class="wrap">
    <div>
      <div class="kicker">Mobile</div>
      <h1>Big-data mobile,<br>same <span class="grad">local support</span></h1>
      <p class="sub">Korvix Mobile is nearly here &mdash; SIM-only plans on a major Australian 5G
        network, managed in the same portal and backed by the same Gippsland humans as your NBN.</p>
      <ul class="ticks">
        <li>Unlimited standard national calls &amp; texts on every plan</li>
        <li>5G access, with 4G coverage reaching 98%+ of Australians</li>
        <li>Keep your number &mdash; ports handled for you</li>
        <li>Month-to-month, no lock-in &mdash; like everything we sell</li>
      </ul>
      <div class="techs">
        <span>5G</span><span>SIM ONLY</span><span>KEEP YOUR NUMBER</span><span>NO LOCK-IN</span>
      </div>
    </div>
    <div class="checkwrap" id="notify">
      <div class="checker">
        <span class="badge" style="color:#ecc575;background:rgba(226,163,54,.12);border-color:rgba(226,163,54,.35)">&#9679; Launching soon</span>
        <p class="t">Be first in line</p>
        <p class="s">Register your interest and we'll email you the moment plans go live &mdash;
          early access (and launch pricing) goes to the people who ask.</p>
        <form class="srow" data-kxlead="mobile">
          <input type="text" name="website" value="" style="display:none" tabindex="-1" autocomplete="off">
          <input type="email" name="email" placeholder="your@email.com" required>
          <button class="btn" type="submit">Notify me</button>
        </form>
        <div class="chints">
          <span>No commitment</span><span>No spam &mdash; one email at launch</span><span>Local support</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section style="text-align:center;padding-top:30px">
  <div class="inner" style="max-width:1120px">
    <div class="kicker">The line-up</div>
    <h2>Plans we're <span class="grad">launching with</span></h2>
    <p class="sub">Final pricing confirmed at launch &mdash; this is the shape of it.</p>
    <div class="pgrid" style="justify-content:center;overflow:visible;padding-top:26px">
      <?php foreach ($mobilePlans as [$mName, $mPrice, $mGb, $mFeat]) { ?>
      <div class="pcard soonp<?php echo $mFeat ? ' feat' : ''; ?>" style="flex:0 1 250px">
        <div class="tag" style="background:#3a4356"><?php echo $mFeat ? 'Coming soon · popular' : 'Coming soon'; ?></div>
        <div class="nm">Korvix Mobile <?php echo $e($mName); ?></div>
        <div class="sp"><?php echo $e($mGb); ?>GB data &middot; 5G</div>
        <div class="pr">$<?php echo $e($mPrice); ?><small>/mo</small></div>
        <div class="nt">expected pricing, AUD incl. GST</div>
        <ul>
          <li>Unlimited national calls &amp; text</li>
          <li><?php echo $e($mGb); ?>GB on a major 5G network</li>
          <li>Keep your number</li>
        </ul>
        <a class="btn ghost" href="#notify">Register interest</a>
      </div>
      <?php } ?>
    </div>
  </div>
</section>

<script>
document.querySelectorAll('[data-kxlead]').forEach(function(f){
  f.addEventListener('submit',function(ev){
    ev.preventDefault();
    var btn=f.querySelector('button');btn.disabled=true;btn.textContent='Saving…';
    fetch('/modules/servers/virtutel_nbn/pages/interest.php',{
      method:'POST',credentials:'same-origin',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({product:f.dataset.kxlead,email:f.querySelector('[name=email]').value,
        website:f.querySelector('[name=website]').value})
    }).then(function(r){return r.json();}).then(function(j){
      if(j.ok){f.outerHTML='<p style="color:#7fdcaa;font-weight:700;margin:8px 0">'
        +'&#10003; You\'re on the list — we\'ll email you at launch.</p>';}
      else{btn.disabled=false;btn.textContent='Notify me';alert(j.error||'Try again shortly.');}
    }).catch(function(){btn.disabled=false;btn.textContent='Notify me';});
  });
});
</script>

<section>
  <div class="inner"><div class="band">
    <h2>Want it sooner?</h2>
    <p class="sub">Call <?php echo $e($phone); ?> and tell us what you need &mdash; early access
      goes to the people who ask.</p>
    <a class="btn ghost" href="tel:<?php echo $e($tel); ?>">Call <?php echo $e($phone); ?></a>
  </div></div>
</section>

<?php } elseif ($section === 'homephone') {
    // Live WHMCS products (any product group named *phone*) when they
    // exist; until then, the launch pricing below renders with an
    // enquire CTA. Rates modelled on the market standard (Leaptel-style
    // Basic/Ultimate tiers).
    $phonePlans = kx_site_phone_plans();
    $phoneFallback = $phonePlans === [];
    if ($phoneFallback) {
        $phonePlans = [
            ['Home Phone Basic', '9.95', [
                'Unlimited local &amp; national calls',
                'Calls to Australian mobiles billed per second',
                '13/1300 numbers 40c untimed',
                'Keep your existing number',
            ], '/contact/'],
            ['Home Phone Ultimate', '19.95', [
                'Unlimited local, national &amp; mobile calls',
                '13/1300 numbers 40c untimed',
                'Per-second billing where charges apply',
                'Keep your existing number',
            ], '/contact/'],
        ];
    }
?>

<section class="hero2">
  <div class="glow g1"></div><div class="glow g2"></div><div class="glow g3"></div>
  <div class="wrap">
    <div>
      <div class="kicker">Home Phone</div>
      <h1>Keep your number,<br>ditch the <span class="grad">line rental</span></h1>
      <p class="sub">Your home phone, running over your NBN instead of a copper line you're
        paying rent on. Same number, clearer calls, and it lives on the same bill as your
        internet.</p>
      <ul class="ticks">
        <li>Keep your existing number &mdash; we handle the porting</li>
        <li>Crystal-clear HD voice over your NBN connection</li>
        <li>Works with a VoIP handset or an adapter for your old phone</li>
        <li>Month-to-month &mdash; no lock-in, no line rental</li>
      </ul>
      <div class="techs">
        <span>KEEP YOUR NUMBER</span><span>HD VOICE</span><span>NO LINE RENTAL</span><span>ONE BILL</span>
      </div>
    </div>
    <div class="checkwrap">
      <div class="checker">
        <span class="badge">&#9679; Number porting</span>
        <p class="t">Bring your number with you</p>
        <p class="s">Porting is handled for you &mdash; your old service keeps working until the
          moment your number moves, so you're never without a phone. We'll confirm timing and
          any cost before anything changes.</p>
        <a class="btn" href="<?php echo $phonePlans !== [] ? '#phoneplans' : '/contact/'; ?>"
           style="width:100%;margin-bottom:8px"><?php
           echo $phonePlans !== [] ? 'See the plans' : 'Talk to us about porting'; ?></a>
        <div class="chints">
          <span>No lock-in</span><span>Porting arranged for you</span><span>Local support</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="phoneplans" style="text-align:center;padding-top:30px">
  <div class="inner" style="max-width:1120px">
    <div class="kicker">Plans</div>
    <h2>Simple home phone pricing</h2>
    <p class="sub">Month-to-month, no line rental, and your number comes with you.</p>
    <div class="pgrid" style="justify-content:center;overflow:visible;padding-top:26px">
      <?php foreach ($phonePlans as $pIdx => [$pName, $pPrice, $pFeatures, $pUrl]) { ?>
      <div class="pcard<?php echo $pIdx === count($phonePlans) - 1 ? ' feat' : ''; ?>" style="flex:0 1 300px">
        <?php if ($pIdx === count($phonePlans) - 1) { ?><div class="tag">Most popular</div><?php } ?>
        <div class="nm"><?php echo $e($pName); ?></div>
        <div class="pr" style="margin-top:10px">$<?php echo $e($pPrice); ?><small>/mo</small></div>
        <div class="nt">AUD incl. GST</div>
        <ul>
          <?php foreach ($pFeatures !== [] ? $pFeatures : ['Keep your number', 'HD voice calls', 'No lock-in contract'] as $feat) { ?>
          <li><?php echo $feat; ?></li>
          <?php } ?>
        </ul>
        <a class="btn<?php echo $pIdx === count($phonePlans) - 1 ? '' : ' ghost'; ?>"
           href="<?php echo $e($pUrl); ?>"><?php echo $phoneFallback ? 'Enquire now' : 'Get started'; ?></a>
      </div>
      <?php } ?>
    </div>
    <p style="color:var(--muted);font-size:12.5px;margin-top:18px">Where call charges apply,
      calls bill per second with a 1c minimum. International rates on request.</p>
  </div>
</section>

<section style="text-align:center">
  <div class="inner" style="max-width:1120px">
    <div class="kicker">How it works</div>
    <h2>Old number, <span class="grad">new tricks</span></h2>
    <div class="hiw-track">
      <div class="hiw-step">
        <div class="hiw-ghost">01</div>
        <div class="hiw-num">1</div>
        <h3>Pick a plan</h3>
        <p>Order online or call us. Tell us the number you want to bring across &mdash; or ask
           for a fresh one if you're starting new.</p>
        <div class="hiw-chip">&#9201;&#65038; two minutes online</div>
      </div>
      <div class="hiw-step">
        <div class="hiw-ghost">02</div>
        <div class="hiw-num">2</div>
        <h3>We port your number</h3>
        <p>You sign one authority form, we deal with your old provider. Your current phone keeps
           working the whole time &mdash; the number moves in one clean cutover.</p>
        <div class="hiw-chip">&#128737;&#65038; no gap in service</div>
      </div>
      <div class="hiw-step">
        <div class="hiw-ghost">03</div>
        <div class="hiw-num">3</div>
        <h3>Plug in and talk</h3>
        <p>Use a VoIP handset, or an adapter that makes your existing phone work over the NBN.
           We'll help you set up whichever you choose.</p>
        <div class="hiw-chip">&#128222;&#65038; sounds better than copper ever did</div>
      </div>
    </div>
  </div>
</section>

<section style="text-align:center;padding-top:16px">
  <div class="inner">
    <div class="kicker">Good to know</div>
    <h2>Home phone <span class="grad">questions</span></h2>
    <div class="faq">
      <details>
        <summary>Can I really keep my number?</summary>
        <p>Almost always, yes &mdash; numbers port between providers under Australian porting
           rules. We check yours before anything is ordered and confirm timing with you first.</p>
      </details>
      <details>
        <summary>Do I need Korvix NBN for this?</summary>
        <p>It's built to pair with our NBN plans &mdash; one bill, one support number, and we can
           see both services when something needs fixing. It needs a reliable internet connection
           to work.</p>
      </details>
      <details>
        <summary>What happens in a power or internet outage?</summary>
        <p>Straight answer: like all NBN-era phone services, it won't work if your power or
           internet is down &mdash; and that includes 000 calls. Keep a charged mobile for
           emergencies. This is true of every VoIP provider; we'd rather say it out loud.</p>
      </details>
      <details>
        <summary>What gear do I need?</summary>
        <p>Either a VoIP handset (plugs into your router) or a small adapter (ATA) that lets your
           existing cordless phone keep working. We'll recommend the cheapest option that suits
           your setup.</p>
      </details>
    </div>
  </div>
</section>

<section style="padding-top:10px">
  <div class="inner"><div class="band">
    <h2>Not sure if your number can move?</h2>
    <p class="sub">Call <?php echo $e($phone); ?> with your current phone bill handy &mdash;
      we'll tell you on the spot.</p>
    <a class="btn" href="tel:<?php echo $e($tel); ?>">Call <?php echo $e($phone); ?></a>
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
    <a class="btn" href="/contact/">Talk to us</a>
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
    <a class="btn" href="/contact/">Contact us</a>
    &nbsp;
    <a class="btn ghost" href="tel:<?php echo $e($tel); ?>">Call <?php echo $e($phone); ?></a>
  </div></div>
</section>

<?php } elseif ($section === 'contact') { ?>

<section class="hero" style="padding-bottom:20px">
  <div class="glow g1"></div><div class="glow g2"></div>
  <div class="inner">
    <div class="kicker">Contact</div>
    <h1>Talk to a <span class="grad">local</span></h1>
    <p class="sub">No phone trees, no offshore scripts &mdash; you get a person in Gippsland who
      can see your actual service.</p>
  </div>
</section>

<section style="text-align:center;padding-top:0">
  <div class="inner" style="max-width:1120px">
    <div class="cards" style="text-align:left">
      <div class="card"><div class="ico">&#128222;&#65038;</div><h3>Call us</h3>
        <p>Sales, support, faults &mdash; one number for all of it.</p>
        <a class="btn" href="tel:<?php echo $e($tel); ?>"><?php echo $e($phone); ?></a></div>
      <div class="card"><div class="ico">&#127915;&#65038;</div><h3>Open a ticket</h3>
        <p>Best for anything with details worth keeping &mdash; billing questions, changes,
           non-urgent faults.</p>
        <a class="btn ghost" href="/submitticket.php">Open a ticket</a></div>
      <div class="card"><div class="ico">&#128421;&#65038;</div><h3>Remote support</h3>
        <p>On the phone with us and need to share your screen? Start a secure session here.</p>
        <a class="btn ghost" href="https://go.getscreen.me/invite/683032125" target="_blank" rel="noopener">Get remote support</a></div>
      <div class="card"><div class="ico">&#128337;&#65038;</div><h3>When we answer</h3>
        <p>Business hours for sales and everyday support. Existing customers with a service-down
           fault: call any time &mdash; the message tells you where to go next.</p>
        <a class="btn ghost" href="/serverstatus.php">Check network status</a></div>
    </div>
  </div>
</section>

<?php } elseif ($section === 'notfound') { ?>

<section class="hero" style="padding-bottom:40px">
  <div class="glow g1"></div><div class="glow g2"></div>
  <div class="inner">
    <div class="kicker">404</div>
    <h1>This page doesn&rsquo;t exist<br><span class="grad">but the internet does</span></h1>
    <p class="sub">The link is old, mistyped, or moved. Everything worth finding is one click
      away.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      <a class="btn" href="/">Back to the home page</a>
      <a class="btn ghost" href="/personal/nbn/">See NBN plans</a>
      <a class="btn ghost" href="/contact/">Contact us</a>
    </div>
  </div>
</section>

<?php } elseif ($section === 'legal') {
    [$lgTitle, $lgTag, $lgUpdated, $lgHtml] = $legalDocs[$legalSlug];
    ?>
<section class="lg-head"><div class="inner">
  <div class="kicker">Legal</div>
  <h1><?php echo $e($lgTitle); ?></h1>
  <p class="sub" style="margin-left:0"><?php echo $e($lgTag); ?></p>
  <div class="lg-updated">Last updated <?php echo $e(date('j F Y', strtotime($lgUpdated))); ?></div>
  <nav class="lg-nav">
    <?php foreach ($legalDocs as $slug => [$navTitle]) { ?>
    <a href="/<?php echo $e($slug); ?>/" class="<?php echo $slug === $legalSlug ? 'on' : ''; ?>"><?php
        echo $e($navTitle); ?></a>
    <?php } ?>
  </nav>
</div></section>
<section style="padding-top:10px"><div class="inner">
  <div class="lg-prose"><?php echo $lgHtml; /* trusted module-authored HTML */ ?></div>
</div></section>

<?php } ?>

<footer class="kx-footer"><div class="in">
  <div class="grid">
    <div>
      <?php if ($logoUrl !== '') { ?>
      <img src="<?php echo $e($logoUrl); ?>" alt="Korvix" style="height:34px;display:block;
        filter:brightness(0) invert(1);margin-bottom:14px"
        onerror="this.outerHTML='<div style=&quot;font-size:24px;font-weight:900;color:#fff;margin-bottom:12px&quot;>KORVIX</div>'">
      <?php } else { ?>
      <div style="font-size:24px;font-weight:900;color:#fff;margin-bottom:12px">KORVIX</div>
      <?php } ?>
      <p style="max-width:300px;margin:0 0 14px;line-height:1.7">Fast, local NBN and hosted services
        for Gippsland and beyond &mdash; no lock-ins, no runaround, real local support.</p>
      <a style="color:#e6e9f2;font-weight:700;font-size:16px" href="tel:<?php echo $e($tel); ?>">&#9742;&#65038;&nbsp;<?php echo $e($phone); ?></a>
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
        <li><a href="/contact/">Contact Us</a></li>
        <li><a href="/clientarea.php">Client Area</a></li>
      </ul>
    </div>
  </div>
  <div class="legal">
    <div>&copy; <?php echo date('Y'); ?> Korvix. All rights reserved.</div>
    <div style="display:flex;gap:18px;flex-wrap:wrap">
      <a href="/terms/">Terms of Service</a>
      <a href="/privacy/">Privacy</a>
      <a href="/acceptable-use/">Acceptable Use</a>
      <a href="/critical-information/">Plan Information (CIS)</a>
      <a href="/complaints/">Complaints</a>
      <a href="/financial-hardship/">Financial Hardship</a>
    </div>
  </div>
</div></footer>

</body>
</html>
<?php
}
