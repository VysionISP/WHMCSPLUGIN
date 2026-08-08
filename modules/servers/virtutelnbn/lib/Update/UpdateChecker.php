<?php

namespace Vysion\VirtutelNbn\Update;

use Vysion\VirtutelNbn\Repository\SettingRepository;
use Vysion\VirtutelNbn\Version;

/**
 * Once a day (driven by the WHMCS cron) asks the GitHub releases API for
 * the latest tagged release and remembers it; the admin area shows an
 * update banner when it is newer than the installed Version::VERSION.
 *
 * Failures are always silent — update checking must never affect
 * provisioning. Note: the releases API only works while the repository
 * (or at least its releases) is publicly visible.
 */
final class UpdateChecker
{
    private const CHECK_INTERVAL_SECONDS = 86400;

    public static function cronCheck(): void
    {
        $settings = new SettingRepository();

        $lastCheck = (int) ($settings->get('update_last_check') ?? 0);
        if (time() - $lastCheck < self::CHECK_INTERVAL_SECONDS) {
            return;
        }
        $settings->put('update_last_check', (string) time());

        $latest = self::fetchLatestVersion();
        if ($latest !== null) {
            $settings->put('update_latest_version', $latest);
        }
    }

    /** Returns the newer version string, or null when up to date / unknown. */
    public static function updateAvailable(): ?string
    {
        $latest = (new SettingRepository())->get('update_latest_version');
        if ($latest !== null && version_compare($latest, Version::VERSION, '>')) {
            return $latest;
        }

        return null;
    }

    private static function fetchLatestVersion(): ?string
    {
        $ch = curl_init('https://api.github.com/repos/' . Version::GITHUB_REPO . '/releases/latest');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/vnd.github+json',
                'User-Agent: whmcs-virtutelnbn-update-check',
            ],
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($body === false || $status !== 200) {
            return null;
        }

        $data = json_decode((string) $body, true);
        $tag = is_array($data) ? (string) ($data['tag_name'] ?? '') : '';
        if ($tag === '') {
            return null;
        }

        $version = ltrim($tag, 'vV');

        return preg_match('/^\d+(\.\d+)*([.-][0-9A-Za-z.-]+)?$/', $version) ? $version : null;
    }
}
