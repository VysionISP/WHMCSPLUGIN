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
use Vysion\VirtutelNbn\Update\UpdateChecker;
use Vysion\VirtutelNbn\Version;
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

    // Daily check of GitHub releases for a newer module version
    // (self-throttled to once per 24h).
    try {
        UpdateChecker::cronCheck();
    } catch (\Throwable $e) {
        logModuleCall('virtutelnbn', 'UpdateCheckCron', [], $e->getMessage());
    }
});

add_hook('AdminAreaHeaderOutput', 1, function () {
    try {
        $latest = UpdateChecker::updateAvailable();
        if ($latest === null) {
            return '';
        }

        return '<div class="alert alert-info" style="margin: 10px 20px;">'
            . 'VirtuTel NBN module update available: v' . htmlspecialchars($latest)
            . ' (installed: v' . htmlspecialchars(Version::VERSION) . '). '
            . '<a href="' . htmlspecialchars(Version::RELEASES_URL) . '" target="_blank" rel="noopener">'
            . 'Download from GitHub Releases</a> and upload over the existing files.'
            . '</div>';
    } catch (\Throwable $e) {
        return '';
    }
});
