<?php

namespace Vysion\VirtutelNbn\Tests;

use PHPUnit\Framework\TestCase;
use Vysion\VirtutelNbn\Auth\StaticTokenProvider;
use Vysion\VirtutelNbn\Auth\TokenManager;

final class TokenManagerTest extends TestCase
{
    private const NOW = 1700000000;

    public function testTokenWithPlentyOfLifeLeftIsFresh(): void
    {
        // 28 days out — well beyond the 2-day renewal buffer.
        $expiresAt = date('Y-m-d H:i:s', self::NOW + 28 * 86400);
        $this->assertTrue(TokenManager::isFresh($expiresAt, self::NOW));
    }

    public function testTokenInsideRenewalBufferIsStale(): void
    {
        // 1 day left — inside the 2-day buffer, must renew early.
        $expiresAt = date('Y-m-d H:i:s', self::NOW + 86400);
        $this->assertFalse(TokenManager::isFresh($expiresAt, self::NOW));
    }

    public function testExpiredTokenIsStale(): void
    {
        $expiresAt = date('Y-m-d H:i:s', self::NOW - 60);
        $this->assertFalse(TokenManager::isFresh($expiresAt, self::NOW));
    }

    public function testUnparseableExpiryIsStale(): void
    {
        $this->assertFalse(TokenManager::isFresh('not-a-date', self::NOW));
    }

    public function testStaticTokenProviderReturnsFixedToken(): void
    {
        $provider = new StaticTokenProvider('fixed');
        $this->assertSame('fixed', $provider->token());
        $this->assertSame('fixed', $provider->token(true));
    }
}
