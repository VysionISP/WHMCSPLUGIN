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

    /**
     * The real client IP for rate limiting: when the request arrives from
     * a private/loopback address (our own reverse proxy / docker bridge),
     * trust X-Forwarded-For and take the LAST hop in the chain — the one
     * OUR proxy appended; earlier entries are client-forgeable. A public
     * REMOTE_ADDR is used as-is and XFF is ignored (spoof-proof).
     */
    public static function clientIp(): string
    {
        $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $isPrivate = filter_var(
            $remote,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;

        if ($isPrivate) {
            $chain = array_map('trim', explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')));
            for ($i = count($chain) - 1; $i >= 0; $i--) {
                if (filter_var($chain[$i], FILTER_VALIDATE_IP) !== false) {
                    return $chain[$i];
                }
            }
        }

        return $remote;
    }

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
