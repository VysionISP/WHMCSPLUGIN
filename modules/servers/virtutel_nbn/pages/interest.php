<?php

/**
 * Launch-interest capture for the coming-soon pages (JSON POST).
 * Public endpoint: same-origin + honeypot + per-IP rate limit; stores the
 * lead (deduped per product) and emails the admins.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Migrations;
use WHMCS\Module\Server\VirtutelNbn\Service\RateLimiter;

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

$input = $_POST;
if (str_contains((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
    $decoded = json_decode((string) file_get_contents('php://input'), true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

// Honeypot: real users never fill this.
if (trim((string) ($input['website'] ?? '')) !== '') {
    $respond(200, ['ok' => true]);
}

try {
    Migrations::ensure();

    if (!RateLimiter::allow(RateLimiter::clientIp())) {
        $respond(429, ['ok' => false, 'error' => 'Too many requests — please try again shortly.']);
    }

    $product = strtolower(trim((string) ($input['product'] ?? '')));
    if (!in_array($product, ['mobile', 'homephone'], true)) {
        $respond(422, ['ok' => false, 'error' => 'Unknown product.']);
    }
    $email = strtolower(trim((string) ($input['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $respond(422, ['ok' => false, 'error' => 'That email doesn\'t look right.']);
    }

    $now = date('Y-m-d H:i:s');
    $fresh = Capsule::table('mod_virtutel_leads')->insertOrIgnore([
        'product' => $product,
        'email' => $email,
        'ip' => substr(RateLimiter::clientIp(), 0, 45),
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    if ($fresh > 0 && function_exists('localAPI')) {
        $count = (int) Capsule::table('mod_virtutel_leads')->where('product', $product)->count();
        localAPI('SendAdminEmail', [
            'customsubject' => 'Korvix launch interest: ' . $product . ' (#' . $count . ')',
            'custommessage' => '<p><strong>' . htmlspecialchars($email) . '</strong> registered interest in <strong>'
                . htmlspecialchars($product) . '</strong>. Total on the list: ' . $count . '.</p>',
            'type' => 'system',
        ]);
    }

    $respond(200, ['ok' => true]);
} catch (\Throwable $e) {
    if (function_exists('logActivity')) {
        logActivity('Virtutel NBN: interest capture error: ' . $e->getMessage());
    }
    $respond(500, ['ok' => false, 'error' => 'Something went wrong — call us instead!']);
}
