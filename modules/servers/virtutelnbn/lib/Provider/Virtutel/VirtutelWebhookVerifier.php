<?php

namespace Vysion\VirtutelNbn\Provider\Virtutel;

/**
 * Verifies inbound VirtuTel webhooks.
 *
 * Expected headers (names configurable if VirtuTel's spec differs):
 *   X-Virtutel-Signature: hex HMAC-SHA256 of "{timestamp}.{rawBody}"
 *   X-Virtutel-Timestamp: unix epoch seconds when the payload was signed
 *
 * Security properties:
 *   - HMAC over the raw body -> integrity + authenticity.
 *   - Timestamp bound to the signature with a small tolerance -> replay
 *     protection (a captured request expires in minutes).
 *   - hash_equals() -> constant-time comparison, no timing side channel.
 */
final class VirtutelWebhookVerifier
{
    public const SIGNATURE_HEADER = 'X-Virtutel-Signature';
    public const TIMESTAMP_HEADER = 'X-Virtutel-Timestamp';
    private const TOLERANCE_SECONDS = 300;

    public function __construct(private readonly string $secret)
    {
    }

    public function verify(string $rawBody, string $signature, string $timestamp, ?int $now = null): bool
    {
        if ($this->secret === '' || $signature === '' || $timestamp === '') {
            return false;
        }

        if (!ctype_digit($timestamp)) {
            return false;
        }
        $now ??= time();
        if (abs($now - (int) $timestamp) > self::TOLERANCE_SECONDS) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $rawBody, $this->secret);

        return hash_equals($expected, strtolower(trim($signature)));
    }
}
