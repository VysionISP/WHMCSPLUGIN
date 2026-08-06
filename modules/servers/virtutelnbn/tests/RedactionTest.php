<?php

namespace Vysion\VirtutelNbn\Tests;

use PHPUnit\Framework\TestCase;
use Vysion\VirtutelNbn\Provider\Virtutel\VirtutelClient;

final class RedactionTest extends TestCase
{
    public function testSensitiveKeysAreRedactedRecursively(): void
    {
        $redacted = VirtutelClient::redact([
            'plan_code' => 'NBN-100-20',
            'api_key' => 'super-secret',
            'nested' => [
                'password' => 'hunter2',
                'Token' => 'abc',
                'loc_id' => 'LOC000012345678',
            ],
        ]);

        $this->assertSame('NBN-100-20', $redacted['plan_code']);
        $this->assertSame('[REDACTED]', $redacted['api_key']);
        $this->assertSame('[REDACTED]', $redacted['nested']['password']);
        $this->assertSame('[REDACTED]', $redacted['nested']['Token']);
        $this->assertSame('LOC000012345678', $redacted['nested']['loc_id']);
    }
}
