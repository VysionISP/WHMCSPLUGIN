<?php

namespace WHMCS\Module\Server\VirtutelNbn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\VirtutelNbn\Api\ApiResponse;

class ApiResponseTest extends TestCase
{
    public function testVtEnvelopeSuccess(): void
    {
        $response = new ApiResponse(200, [
            'vt_success' => true,
            'vt_short_error' => '',
            'vt_error_desc' => '',
            'responseData' => ['locId' => 'LOC000000000001'],
        ]);

        $this->assertTrue($response->vtSuccess());
        $this->assertSame('', $response->vtShortError());
        $this->assertSame('LOC000000000001', $response->get('responseData.locId'));
    }

    public function testVtEnvelopeFailure(): void
    {
        $response = new ApiResponse(401, [
            'vt_success' => false,
            'vt_short_error' => 'auth_error',
            'vt_error_desc' => 'You must supply a valid access token with your request',
        ]);

        $this->assertFalse($response->vtSuccess());
        $this->assertSame('auth_error', $response->vtShortError());
        $this->assertSame(
            'You must supply a valid access token with your request',
            $response->vtErrorDesc()
        );
    }

    public function testDotPathReturnsDefaultForMissingKeys(): void
    {
        $response = new ApiResponse(200, ['vt_success' => true]);

        $this->assertNull($response->get('responseData.nothing.here'));
        $this->assertSame('fallback', $response->get('missing', 'fallback'));
    }
}
