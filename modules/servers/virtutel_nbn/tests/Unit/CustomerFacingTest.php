<?php

namespace WHMCS\Module\Server\VirtutelNbn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\VirtutelNbn\Service\ConnectReadiness;
use WHMCS\Module\Server\VirtutelNbn\Service\SpeedTier;

class CustomerFacingTest extends TestCase
{
    public function testSpeedTierParsing(): void
    {
        $this->assertSame('50/20 Mbps', SpeedTier::describe('TC450D20U')['label']);
        $this->assertSame('1000/400 Mbps', SpeedTier::describe('TC41000D400U')['label']);
        $this->assertSame('25/5 Mbps', SpeedTier::describe('TC425D5U')['label']);
        $this->assertTrue(SpeedTier::describe('L3TC4100D20U')['layer3']);
        $this->assertStringContainsString('Home Fast', SpeedTier::describe('TC4FWHF')['label']);
        $this->assertNull(SpeedTier::describe('SOMETHING_ELSE'));
    }

    public function testCustomerTiersFilterAndSort(): void
    {
        $tiers = SpeedTier::customerTiers([
            'TC4100D20U', 'L3TC4100D20U', 'TC425D5U', 'TC41000D100U', 'TC450D20U', 'bogus',
        ]);

        $this->assertSame(
            ['TC425D5U', 'TC450D20U', 'TC4100D20U', 'TC41000D100U'],
            array_column($tiers, 'enum')
        );
        $this->assertSame('25/5 Mbps', $tiers[0]['label']);
    }

    private function qualification(array $overrides = []): array
    {
        return array_merge([
            'location_id' => 'LOC1',
            'service_type' => 'nfas',
            'technology' => 'Fibre',
            'service_class' => 3,
            'serviceability_status' => 'Serviceable',
            'orderable' => true,
            'speeds' => ['TC450D20U'],
            'new_development_charge' => false,
            'fibre_upgrade_available' => false,
            'ntds' => [],
            'copper_pairs' => [],
            'notes' => [],
            'poi_id' => '',
        ], $overrides);
    }

    public function testFttpWithFreePortIsConnectNow(): void
    {
        $q = $this->qualification([
            'ntds' => [[
                'id' => 'NTD1',
                'ports' => [
                    ['id' => '1', 'status' => 'Used', 'free' => false, 'service_id_match' => false],
                    ['id' => '2', 'status' => 'Free', 'free' => true, 'service_id_match' => false],
                ],
                'speed_tiers_supported' => [],
            ]],
        ]);

        $this->assertSame('connect_now', ConnectReadiness::assess($q)['code']);
    }

    public function testFttpNoNtdNeedsAppointment(): void
    {
        $q = $this->qualification(['service_class' => 2]);
        $this->assertSame('appointment', ConnectReadiness::assess($q)['code']);
    }

    public function testHfcSc23ShipsDevice(): void
    {
        $q = $this->qualification(['service_type' => 'nhas', 'service_class' => 23]);
        $this->assertSame('device_shipped', ConnectReadiness::assess($q)['code']);
    }

    public function testCopperSc13ConnectNowAndSc12NbnWork(): void
    {
        $ready = $this->qualification(['service_type' => 'ncas', 'service_class' => 13]);
        $work = $this->qualification(['service_type' => 'ncas', 'service_class' => 12]);

        $this->assertSame('connect_now', ConnectReadiness::assess($ready)['code']);
        $this->assertSame('nbn_work', ConnectReadiness::assess($work)['code']);
    }

    public function testNotOrderable(): void
    {
        $q = $this->qualification(['orderable' => false, 'speeds' => []]);
        $this->assertSame('not_available', ConnectReadiness::assess($q)['code']);
    }
}
