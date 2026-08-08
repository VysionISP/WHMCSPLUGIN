<?php

/**
 * Public callback endpoint for Virtutel.
 *
 * Registered with Virtutel as:
 *   https://<whmcs-domain>/modules/servers/virtutel_nbn/callback/webhook.php?token=<secret>
 *
 * Security model (Virtutel does not sign callbacks):
 *  - constant-time shared-secret token check BEFORE the payload is parsed
 *  - eventUuid idempotency (duplicate deliveries are 200-OK no-ops)
 *  - strict envelope validation; unknown events stored but not acted on
 * Responses: 2xx = accepted (Virtutel stops retrying), 403 = bad/missing
 * token (Virtutel retries then drops), 400 = malformed payload.
 */

use WHMCS\Module\Server\VirtutelNbn\Migrations;
use WHMCS\Module\Server\VirtutelNbn\Webhook\CallbackAuthenticator;
use WHMCS\Module\Server\VirtutelNbn\Webhook\CallbackDispatcher;
use WHMCS\Module\Server\VirtutelNbn\Webhook\CallbackEnvelope;

require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../lib/Autoloader.php';

header('Content-Type: application/json');

$respond = function (int $status, string $message): never {
    http_response_code($status);
    echo json_encode(['message' => $message]);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $respond(405, 'method not allowed');
}

if (!CallbackAuthenticator::verify($_GET['token'] ?? null)) {
    if (function_exists('logActivity')) {
        logActivity('Virtutel NBN: callback rejected — invalid or missing token');
    }
    $respond(403, 'forbidden');
}

$body = file_get_contents('php://input');
if ($body === false || strlen($body) > 1048576) {
    $respond(400, 'bad request');
}

try {
    Migrations::ensure();
    $envelope = CallbackEnvelope::fromJson($body);
} catch (\InvalidArgumentException $e) {
    $respond(400, 'bad request');
} catch (\Throwable $e) {
    // Internal fault: non-2xx so Virtutel retries later.
    if (function_exists('logActivity')) {
        logActivity('Virtutel NBN: callback endpoint error: ' . $e->getMessage());
    }
    $respond(500, 'error');
}

$outcome = (new CallbackDispatcher())->dispatch($envelope);

// 'failed' still returns 200: the event is stored for replay/repair, and a
// Virtutel retry would be a duplicate no-op anyway.
$respond(200, $outcome);
