<?php

namespace Vysion\VirtutelNbn\Tests;

use PHPUnit\Framework\TestCase;
use Vysion\VirtutelNbn\Service\StatusMapper;

final class StatusMapperTest extends TestCase
{
    public function testProviderStatusMapping(): void
    {
        $this->assertSame(StatusMapper::INTERNAL_PENDING, StatusMapper::fromProvider('Submitted'));
        $this->assertSame(StatusMapper::INTERNAL_IN_PROGRESS, StatusMapper::fromProvider('in_progress'));
        $this->assertSame(StatusMapper::INTERNAL_IN_PROGRESS, StatusMapper::fromProvider('In Progress'));
        $this->assertSame(StatusMapper::INTERNAL_HELD, StatusMapper::fromProvider('held'));
        $this->assertSame(StatusMapper::INTERNAL_ACTIVE, StatusMapper::fromProvider('COMPLETE'));
        $this->assertSame(StatusMapper::INTERNAL_ACTIVE, StatusMapper::fromProvider('connected'));
        $this->assertSame(StatusMapper::INTERNAL_CANCELLED, StatusMapper::fromProvider('cancelled'));
        $this->assertSame(StatusMapper::INTERNAL_CANCELLED, StatusMapper::fromProvider('canceled'));
        $this->assertSame(StatusMapper::INTERNAL_TERMINATED, StatusMapper::fromProvider('disconnected'));
    }

    public function testUnknownStatusDefaultsToInProgress(): void
    {
        $this->assertSame(StatusMapper::INTERNAL_IN_PROGRESS, StatusMapper::fromProvider('something-new'));
    }

    public function testWhmcsMapping(): void
    {
        $this->assertSame('Active', StatusMapper::toWhmcsServiceStatus(StatusMapper::INTERNAL_ACTIVE));
        $this->assertSame('Suspended', StatusMapper::toWhmcsServiceStatus(StatusMapper::INTERNAL_SUSPENDED));
        $this->assertSame('Cancelled', StatusMapper::toWhmcsServiceStatus(StatusMapper::INTERNAL_CANCELLED));
        $this->assertSame('Terminated', StatusMapper::toWhmcsServiceStatus(StatusMapper::INTERNAL_TERMINATED));
        $this->assertNull(StatusMapper::toWhmcsServiceStatus(StatusMapper::INTERNAL_PENDING));
        $this->assertNull(StatusMapper::toWhmcsServiceStatus(StatusMapper::INTERNAL_IN_PROGRESS));
        $this->assertNull(StatusMapper::toWhmcsServiceStatus(StatusMapper::INTERNAL_HELD));
    }

    public function testVerificationRequiredOnCriticalTransitions(): void
    {
        $this->assertTrue(StatusMapper::requiresVerification(StatusMapper::INTERNAL_ACTIVE));
        $this->assertTrue(StatusMapper::requiresVerification(StatusMapper::INTERNAL_CANCELLED));
        $this->assertTrue(StatusMapper::requiresVerification(StatusMapper::INTERNAL_TERMINATED));
        $this->assertFalse(StatusMapper::requiresVerification(StatusMapper::INTERNAL_PENDING));
        $this->assertFalse(StatusMapper::requiresVerification(StatusMapper::INTERNAL_SUSPENDED));
    }
}
