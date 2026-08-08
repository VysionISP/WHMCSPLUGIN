<?php
/**
 * WHMCS hooks for the VirtuTel NBN module.
 *
 * AfterCronJob fires on every WHMCS cron invocation (typically every
 * 5 minutes), which drives the asynchronous webhook event queue.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/Autoloader.php';

use Vysion\VirtutelNbn\Autoloader;
use Vysion\VirtutelNbn\Installer;
use Vysion\VirtutelNbn\Provider\VirtutelResolver;
use Vysion\VirtutelNbn\Webhook\EventProcessor;

Autoloader::register();

add_hook('AfterCronJob', 1, function () {
    try {
        EventProcessor::run();
    } catch (\Throwable $e) {
        logModuleCall('virtutelnbn', 'EventProcessorCron', [], $e->getMessage());
    }

    // Token keep-alive: TokenManager renews the ~28-day access token two
    // days before expiry; calling token() here means renewal happens on
    // schedule even if no orders are placed for weeks.
    try {
        Installer::ensureInstalled();
        VirtutelResolver::tokenManagerFromServerRecord()?->token();
    } catch (\Throwable $e) {
        logModuleCall('virtutelnbn', 'TokenKeepAliveCron', [], $e->getMessage());
    }
});
