<?php

/**
 * Onboarding wizard API (JSON POST, same-origin): powers the Aussie-style
 * step-by-step signup — modem offer metadata, account creation with DOB,
 * and SMS mobile verification — WITHOUT touching the existing
 * qualification/port/transfer flow (the wizard only intercepts the final
 * Order click; the order URL it forwards to is unchanged).
 *
 * Registration happens here so customers never see the WHMCS register
 * form: AddClient (+ Date of Birth custom field), then the session is
 * logged in so checkout skips registration entirely.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Migrations;
use WHMCS\Module\Server\VirtutelNbn\Repository\Settings;
use WHMCS\Module\Server\VirtutelNbn\Service\RateLimiter;
use WHMCS\Module\Server\VirtutelNbn\Service\Sms;

require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../lib/Autoloader.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, max-age=0');

$respond = function (int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $respond(405, ['ok' => false, 'error' => 'POST only']);
}
$ownHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
$srcHost = '';
foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $header) {
    $value = (string) ($_SERVER[$header] ?? '');
    if ($value !== '') {
        $srcHost = strtolower((string) (parse_url($value, PHP_URL_HOST) ?? ''));
        break;
    }
}
if ($ownHost === '' || $srcHost !== $ownHost) {
    $respond(403, ['ok' => false, 'error' => 'Cross-origin request refused.']);
}

$input = [];
$decoded = json_decode((string) file_get_contents('php://input'), true);
if (is_array($decoded)) {
    $input = $decoded;
}
$input += $_POST;

$pendingKey = 'onboard_' . substr(sha1(session_id() ?: (string) (RateLimiter::clientIp())), 0, 32);

$createClient = function (array $payload) use ($respond) {
    $email = strtolower($payload['email']);
    $existing = (int) (Capsule::table('tblclients')->where('email', $email)->value('id') ?? 0);
    if ($existing > 0) {
        // Known email: don't hijack the account from an unauthenticated
        // flow — checkout will ask them to log in.
        $respond(200, ['ok' => true, 'done' => true, 'existing' => true]);
    }

    // Rough AU address split for the client record (skipvalidation keeps
    // this best-effort; the service address is authoritative elsewhere).
    $addr = trim((string) ($payload['addr'] ?? ''));
    $state = '';
    $postcode = '';
    $city = '';
    if (preg_match('/\b(VIC|NSW|QLD|SA|WA|TAS|NT|ACT)\b/i', $addr, $m)) {
        $state = strtoupper($m[1]);
    }
    if (preg_match('/\b(\d{4})\s*$/', $addr, $m)) {
        $postcode = $m[1];
    }
    if ($state !== '' && preg_match('/([A-Za-z\' ]{2,40})\s+' . $state . '\b/i', $addr, $m)) {
        $city = ucwords(strtolower(trim($m[1])));
    }

    $params = [
        'firstname' => $payload['first'],
        'lastname' => $payload['last'],
        'email' => $email,
        'password2' => bin2hex(random_bytes(10)),
        'phonenumber' => $payload['phone'],
        'address1' => $addr !== '' ? mb_substr($addr, 0, 100) : 'To be confirmed',
        'city' => $city !== '' ? $city : 'TBC',
        'state' => $state !== '' ? $state : 'VIC',
        'postcode' => $postcode !== '' ? $postcode : '0000',
        'country' => 'AU',
        'skipvalidation' => true,
    ];

    $dobFieldId = (int) (Capsule::table('tblcustomfields')
        ->where('type', 'client')
        ->where('fieldname', 'like', 'Date of Birth%')
        ->value('id') ?? 0);
    if ($dobFieldId > 0) {
        $params['customfields'] = base64_encode(serialize([$dobFieldId => $payload['dob']]));
    }

    $result = localAPI('AddClient', $params);
    if (($result['result'] ?? '') !== 'success') {
        $respond(500, ['ok' => false,
            'error' => 'We couldn\'t create your account: ' . (string) ($result['message'] ?? 'unknown error')]);
    }

    $clientId = (int) ($result['clientid'] ?? 0);
    // Log this browser session in so checkout skips registration/login.
    $_SESSION['uid'] = $clientId;

    logActivity(sprintf(
        'Virtutel NBN: onboarding created client #%d (%s) — signup wizard',
        $clientId,
        $email
    ));

    $respond(200, ['ok' => true, 'done' => true]);
};

try {
    Migrations::ensure();
    $action = (string) ($input['action'] ?? '');

    if ($action === 'meta') {
        $routerPid = (int) (Capsule::table('tbladdonmodules')
            ->where('module', 'virtutel_nbn_admin')
            ->where('setting', 'router_pid')->value('value') ?? 0);
        $router = null;
        if ($routerPid > 0) {
            $product = Capsule::table('tblproducts')->where('id', $routerPid)
                ->where('hidden', 0)->first(['id', 'name', 'paytype', 'description']);
            if ($product) {
                $pricing = Capsule::table('tblpricing')->where('type', 'product')
                    ->where('currency', 1)->where('relid', $routerPid)->first();
                $amount = null;
                $suffix = '';
                if ($pricing) {
                    if ((string) $product->paytype === 'onetime' && (float) $pricing->monthly >= 0) {
                        $amount = (float) $pricing->monthly;
                        $suffix = ' once';
                    } elseif ((float) $pricing->monthly > 0) {
                        $amount = (float) $pricing->monthly;
                        $suffix = '/mo';
                    }
                }
                $router = [
                    'name' => (string) $product->name,
                    'price' => $amount !== null ? '$' . number_format($amount, 2) . $suffix : '',
                    'blurb' => trim(strip_tags((string) $product->description)),
                ];
            }
        }
        $respond(200, ['ok' => true, 'router' => $router, 'sms' => Sms::enabled()]);
    }

    if (!RateLimiter::allow(RateLimiter::clientIp())) {
        $respond(429, ['ok' => false, 'error' => 'Too many attempts — please try again shortly.']);
    }

    if ($action === 'start') {
        $first = trim((string) ($input['first'] ?? ''));
        $last = trim((string) ($input['last'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $dob = trim((string) ($input['dob'] ?? ''));
        $phoneRaw = preg_replace('/[\s()-]/', '', (string) ($input['phone'] ?? ''));
        $addr = trim((string) ($input['addr'] ?? ''));

        foreach ([['first', $first], ['last', $last]] as [$which, $value]) {
            if (!preg_match("/^[A-Za-z][A-Za-z' -]{1,39}$/", $value)) {
                $respond(422, ['ok' => false, 'error' => 'Please enter your ' . $which . ' name.']);
            }
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $respond(422, ['ok' => false, 'error' => 'That email doesn\'t look right.']);
        }
        $dobTs = strtotime($dob);
        if ($dobTs === false || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            $respond(422, ['ok' => false, 'error' => 'Please enter your date of birth.']);
        }
        $age = (int) floor((time() - $dobTs) / (365.25 * 86400));
        if ($age < 18 || $age > 110) {
            $respond(422, ['ok' => false, 'error' => 'You must be 18 or over to sign up.']);
        }
        if (!preg_match('/^(?:\+?61|0)(4\d{8})$/', $phoneRaw, $m)) {
            $respond(422, ['ok' => false, 'error' => 'Please enter a valid Australian mobile (04xx xxx xxx).']);
        }
        $phone = '+61' . $m[1];

        $payload = [
            'first' => $first, 'last' => $last, 'email' => $email,
            'dob' => $dob, 'phone' => $phone, 'addr' => mb_substr($addr, 0, 140),
        ];

        if (!Sms::enabled()) {
            $createClient($payload);
        }

        $code = (string) random_int(100000, 999999);
        Settings::set($pendingKey, (string) json_encode([
            'payload' => $payload,
            'code' => $code,
            'exp' => time() + 600,
            'tries' => 0,
        ]));
        Sms::send($phone, 'Your Korvix verification code is ' . $code
            . '. It expires in 10 minutes.');

        $respond(200, ['ok' => true, 'verify' => true,
            'hint' => substr($phone, 0, 6) . '****' . substr($phone, -2)]);
    }

    if ($action === 'verify') {
        $pending = json_decode((string) (Settings::get($pendingKey, '') ?? ''), true);
        if (!is_array($pending) || (int) ($pending['exp'] ?? 0) < time()) {
            $respond(410, ['ok' => false, 'error' => 'That code has expired — go back and try again.']);
        }
        if ((int) ($pending['tries'] ?? 0) >= 5) {
            Settings::set($pendingKey, '');
            $respond(429, ['ok' => false, 'error' => 'Too many wrong codes — go back and start again.']);
        }
        $code = preg_replace('/\D/', '', (string) ($input['code'] ?? ''));
        if (!hash_equals((string) $pending['code'], $code)) {
            $pending['tries'] = (int) ($pending['tries'] ?? 0) + 1;
            Settings::set($pendingKey, (string) json_encode($pending));
            $respond(422, ['ok' => false, 'error' => 'That code isn\'t right — check the SMS and try again.']);
        }

        Settings::set($pendingKey, '');
        $createClient((array) $pending['payload']);
    }

    $respond(422, ['ok' => false, 'error' => 'Unknown action.']);
} catch (\Throwable $e) {
    if (function_exists('logActivity')) {
        logActivity('Virtutel NBN: signup wizard error: ' . $e->getMessage());
    }
    $respond(500, ['ok' => false, 'error' => 'Something went wrong — call us on 03 4130 5013 and we\'ll sign you up over the phone.']);
}
