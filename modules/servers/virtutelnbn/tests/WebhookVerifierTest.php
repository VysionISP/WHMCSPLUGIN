<?php

namespace Vysion\VirtutelNbn\Tests;

use PHPUnit\Framework\TestCase;
use Vysion\VirtutelNbn\Provider\Virtutel\VirtutelWebhookVerifier;

final class WebhookVerifierTest extends TestCase
{
    private const SECRET = 'test-secret';

    private function sign(string $body, string $timestamp, string $secret = self::SECRET): string
    {
        return hash_hmac('sha256', $timestamp . '.' . $body, $secret);
    }

    public function testValidSignaturePasses(): void
    {
        $verifier = new VirtutelWebhookVerifier(self::SECRET);
        $body = '{"event_id":"evt_1","status":"active"}';
        $ts = '1700000000';

        $this->assertTrue($verifier->verify($body, $this->sign($body, $ts), $ts, 1700000000));
    }

    public function testWrongSecretFails(): void
    {
        $verifier = new VirtutelWebhookVerifier(self::SECRET);
        $body = '{"event_id":"evt_1"}';
        $ts = '1700000000';

        $this->assertFalse($verifier->verify($body, $this->sign($body, $ts, 'other-secret'), $ts, 1700000000));
    }

    public function testTamperedBodyFails(): void
    {
        $verifier = new VirtutelWebhookVerifier(self::SECRET);
        $ts = '1700000000';
        $signature = $this->sign('{"status":"pending"}', $ts);

        $this->assertFalse($verifier->verify('{"status":"active"}', $signature, $ts, 1700000000));
    }

    public function testExpiredTimestampFails(): void
    {
        $verifier = new VirtutelWebhookVerifier(self::SECRET);
        $body = '{"event_id":"evt_1"}';
        $ts = '1700000000';

        // 10 minutes later — outside the 5 minute tolerance.
        $this->assertFalse($verifier->verify($body, $this->sign($body, $ts), $ts, 1700000600));
    }

    public function testFutureTimestampWithinToleranceAccepted(): void
    {
        $verifier = new VirtutelWebhookVerifier(self::SECRET);
        $body = '{"event_id":"evt_1"}';
        $ts = '1700000100';

        $this->assertTrue($verifier->verify($body, $this->sign($body, $ts), $ts, 1700000000));
    }

    public function testNonNumericTimestampFails(): void
    {
        $verifier = new VirtutelWebhookVerifier(self::SECRET);
        $body = '{}';

        $this->assertFalse($verifier->verify($body, $this->sign($body, 'abc'), 'abc', 1700000000));
    }

    public function testEmptySecretNeverVerifies(): void
    {
        $verifier = new VirtutelWebhookVerifier('');
        $body = '{}';
        $ts = '1700000000';

        $this->assertFalse($verifier->verify($body, $this->sign($body, $ts, ''), $ts, 1700000000));
    }
}
