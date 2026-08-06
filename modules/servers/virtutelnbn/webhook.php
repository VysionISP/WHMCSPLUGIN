<?php
/**
 * Public inbound webhook endpoint for VirtuTel.
 *
 * Register with VirtuTel as:
 *   https://<your-whmcs>/modules/servers/virtutelnbn/webhook.php
 *
 * Requests must be signed (HMAC-SHA256, see VirtutelWebhookVerifier).
 * The endpoint verifies, deduplicates and stores the event, then returns
 * immediately; processing happens on the WHMCS cron (hooks.php).
 */

use Vysion\VirtutelNbn\Autoloader;
use Vysion\VirtutelNbn\Repository\WebhookEventRepository;
use Vysion\VirtutelNbn\Webhook\WebhookController;

// Bootstrap WHMCS (three levels up from modules/servers/virtutelnbn/).
require __DIR__ . '/../../../init.php';
require_once __DIR__ . '/lib/Autoloader.php';
Autoloader::register();

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method not allowed']);
    exit;
}

$rawBody = file_get_contents('php://input') ?: '';
if (strlen($rawBody) > 1048576) { // 1 MiB cap on inbound payloads
    http_response_code(413);
    echo json_encode(['error' => 'payload too large']);
    exit;
}

$headers = [];
foreach ($_SERVER as $key => $value) {
    if (str_starts_with($key, 'HTTP_')) {
        $headers[strtolower(str_replace('_', '-', substr($key, 5)))] = (string) $value;
    }
}

try {
    $controller = new WebhookController(new WebhookEventRepository());
    [$status, $body] = $controller->handle($rawBody, $headers);
} catch (\Throwable $e) {
    // Never leak internals to the caller; details go to the module log.
    if (function_exists('logModuleCall')) {
        logModuleCall('virtutelnbn', 'Webhook', ['length' => strlen($rawBody)], $e->getMessage());
    }
    $status = 500;
    $body = ['error' => 'internal error'];
}

http_response_code($status);
echo json_encode($body);
