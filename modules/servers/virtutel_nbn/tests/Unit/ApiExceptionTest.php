<?php

namespace WHMCS\Module\Server\VirtutelNbn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\VirtutelNbn\Api\ApiException;

class ApiExceptionTest extends TestCase
{
    public function testAuthErrorDetectionByStatus(): void
    {
        $e = new ApiException('denied', '', 401);
        $this->assertTrue($e->isAuthError());
    }

    public function testAuthErrorDetectionByShortError(): void
    {
        $e = new ApiException('denied', 'auth_error', 403);
        $this->assertTrue($e->isAuthError());
    }

    public function testNonAuthError(): void
    {
        $e = new ApiException('boom', 'validation_error', 422);
        $this->assertFalse($e->isAuthError());
        $this->assertSame('validation_error', $e->getShortError());
        $this->assertSame(422, $e->getHttpStatus());
    }
}
