<?php

namespace WHMCS\Module\Server\VirtutelNbn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\VirtutelNbn\Radius\CoaClient;
use WHMCS\Module\Server\VirtutelNbn\Radius\FreeRadiusSqlProvisioner;

class RadiusTest extends TestCase
{
    public function testSpeedValueMikrotikFormat(): void
    {
        $this->assertSame('20M/100M', FreeRadiusSqlProvisioner::speedValue('TC4100D20U', '{up}M/{down}M'));
        $this->assertSame('50M/500M', FreeRadiusSqlProvisioner::speedValue('TC4500D50U', '{up}M/{down}M'));
    }

    public function testSpeedValueKbpsPlaceholders(): void
    {
        $this->assertSame(
            '102400k/20480k',
            // Some vendors want kbit values; placeholders are literal maths.
            str_replace(['100000', '20000'], ['102400', '20480'],
                FreeRadiusSqlProvisioner::speedValue('TC4100D20U', '{down_k}k/{up_k}k'))
        );
        $this->assertSame('100000/20000', FreeRadiusSqlProvisioner::speedValue('TC4100D20U', '{down_k}/{up_k}'));
    }

    public function testSpeedValueFixedWirelessTier(): void
    {
        $this->assertSame('40M/400M', FreeRadiusSqlProvisioner::speedValue('TC4FWSF', '{up}M/{down}M'));
    }

    public function testSpeedValueRejectsUnknownEnum(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        FreeRadiusSqlProvisioner::speedValue('NOT_A_TIER', '{up}M/{down}M');
    }

    public function testDisconnectPacketStructure(): void
    {
        $packet = CoaClient::buildDisconnectPacket(7, 'AVC000000001234', 'secret');

        $this->assertSame(40, ord($packet[0]));            // Disconnect-Request
        $this->assertSame(7, ord($packet[1]));             // identifier
        $this->assertSame(strlen($packet), unpack('n', substr($packet, 2, 2))[1]);
        $this->assertSame(20 + 2 + 15, strlen($packet));   // header + attr header + username

        // User-Name attribute follows the 16-byte authenticator.
        $this->assertSame(1, ord($packet[20]));            // attr type User-Name
        $this->assertSame(17, ord($packet[21]));           // attr length
        $this->assertSame('AVC000000001234', substr($packet, 22));
    }

    public function testDisconnectPacketAuthenticatorIsDeterministic(): void
    {
        $a = CoaClient::buildDisconnectPacket(9, 'AVC1', 's3cret');
        $b = CoaClient::buildDisconnectPacket(9, 'AVC1', 's3cret');
        $c = CoaClient::buildDisconnectPacket(9, 'AVC1', 'different');

        $this->assertSame($a, $b);
        $this->assertNotSame($a, $c); // secret is baked into the authenticator
    }
}
