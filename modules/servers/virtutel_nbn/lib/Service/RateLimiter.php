<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;

/**
 * Small fixed-window per-IP rate limiter for the public qualification
 * endpoint (backed by mod_virtutel_ratelimit).
 */
class RateLimiter
{
    private const WINDOW_SECONDS = 600;
    private const MAX_HITS = 30;

    /** @return bool true when the request is allowed */
    public static function allow(string $ip): bool
    {
        $ip = substr($ip, 0, 45);
        $bucket = intdiv(time(), self::WINDOW_SECONDS);

        Capsule::connection()->statement(
            'INSERT INTO mod_virtutel_ratelimit (ip, bucket, hits) VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE hits = hits + 1',
            [$ip, $bucket]
        );

        $hits = (int) Capsule::table('mod_virtutel_ratelimit')
            ->where('ip', $ip)->where('bucket', $bucket)->value('hits');

        // Opportunistic cleanup of expired windows (~1% of requests).
        if (random_int(1, 100) === 1) {
            Capsule::table('mod_virtutel_ratelimit')->where('bucket', '<', $bucket - 1)->delete();
        }

        return $hits <= self::MAX_HITS;
    }
}
