<?php

namespace WHMCS\Module\Server\VirtutelNbn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\VirtutelNbn\Service\QualificationService;

class QualificationParseTest extends TestCase
{
    /** Trimmed FTTP SC3 fixture from docs/api/04_servicequalifications.md. */
    private function fttpSc3(): array
    {
        return [
            'id' => 'LOC123456789012',
            'siteRestriction' => [
                'serviceabilityStatus' => 'Serviceable',
                'supportingTechnology' => [
                    'primaryAccessTechnology' => 'Fibre',
                    'serviceabilityClass' => '3',
                ],
                'supportingRelatedSiteBoundaries' => ['poiId' => '2LID'],
                'supportingRelatedLocationFeatures' => ['newDevelopmentsChargeApplies' => false],
                'supportingResource' => [
                    [
                        'id' => 'NTD999100158242',
                        'type' => 'NTD',
                        'uniPortD' => [
                            ['id' => '1-UNI-D1', 'status' => 'Used'],
                            ['id' => '1-UNI-D2', 'status' => 'Free'],
                            ['id' => '1-UNI-D3', 'status' => 'Free'],
                            ['id' => '1-UNI-D4', 'status' => 'Free'],
                        ],
                    ],
                ],
            ],
            'virtutelSpeedsAvailable' => ['TC450D20U', 'TC4100D20U', 'L3TC4100D20U'],
            'serviceType' => 'nfas',
        ];
    }

    public function testParsesFttpSc3(): void
    {
        $parsed = QualificationService::parseQualification($this->fttpSc3());

        $this->assertSame('LOC123456789012', $parsed['location_id']);
        $this->assertSame('nfas', $parsed['service_type']);
        $this->assertSame(3, $parsed['service_class']);
        $this->assertTrue($parsed['orderable']);
        $this->assertFalse($parsed['new_development_charge']);
        $this->assertFalse($parsed['fibre_upgrade_available']);
        $this->assertSame('2LID', $parsed['poi_id']);

        $this->assertCount(1, $parsed['ntds']);
        $ports = $parsed['ntds'][0]['ports'];
        $this->assertCount(4, $ports);
        $this->assertFalse($ports[0]['free']); // 1-UNI-D1 Used
        $this->assertTrue($ports[1]['free']);  // 1-UNI-D2 Free — auto-pick target
    }

    public function testNotOrderableServiceClassZero(): void
    {
        $sq = $this->fttpSc3();
        $sq['siteRestriction']['supportingTechnology']['serviceabilityClass'] = '0';
        $sq['virtutelSpeedsAvailable'] = [];

        $parsed = QualificationService::parseQualification($sq);
        $this->assertSame(0, $parsed['service_class']);
        $this->assertFalse($parsed['orderable']);
    }

    public function testOrderableRequiresSpeeds(): void
    {
        $sq = $this->fttpSc3();
        $sq['virtutelSpeedsAvailable'] = [];

        $this->assertFalse(QualificationService::parseQualification($sq)['orderable']);
    }

    public function testFibreUpgradeDetection(): void
    {
        $sq = $this->fttpSc3();
        $sq['siteRestriction']['supportingTechnology']['alternativeTechnology'] = 'Fibre';

        $this->assertTrue(QualificationService::parseQualification($sq)['fibre_upgrade_available']);
    }

    public function testFindChurnMatchOnPort(): void
    {
        $sq = $this->fttpSc3();
        $sq['siteRestriction']['supportingResource'][0]['uniPortD'][0]['serviceIDMatch'] = true;

        $parsed = QualificationService::parseQualification($sq);
        $this->assertSame(
            ['ntdId' => 'NTD999100158242', 'uniDPortId' => '1-UNI-D1'],
            QualificationService::findChurnMatch($parsed)
        );
    }

    public function testFindChurnMatchNoneAndRestrictionError(): void
    {
        $sq = $this->fttpSc3();
        $sq['siteRestriction']['siteRestrictionError'] = [
            'code' => 'RJ002005',
            'message' => 'The provided AVC exists at a different location: 89012',
        ];

        $parsed = QualificationService::parseQualification($sq);
        $this->assertNull(QualificationService::findChurnMatch($parsed));
        $this->assertSame('RJ002005', $parsed['restriction_error']['code']);
    }

    public function testCopperPairChurnMatch(): void
    {
        $sq = $this->fttpSc3();
        $sq['siteRestriction']['supportingResource'] = [
            [
                'id' => 'CPI300012143105',
                'type' => 'CopperLineResource',
                'copperPairStatus' => 'Active',
                'serviceIDMatch' => true,
            ],
        ];

        $parsed = QualificationService::parseQualification($sq);
        $this->assertCount(1, $parsed['copper_pairs']);
        $this->assertTrue($parsed['copper_pairs'][0]['service_id_match']);
        $this->assertSame([], $parsed['ntds']);
    }
}
