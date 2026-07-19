<?php

namespace WHMCS\Module\Server\VirtutelNbn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\VirtutelNbn\Service\OrderPayloadBuilder;
use WHMCS\Module\Server\VirtutelNbn\Service\ProvisioningService;

class OrderPayloadBuilderTest extends TestCase
{
    private function baseOpts(): array
    {
        return [
            'locationId' => 'LOC123456789012',
            'serviceType' => 'nfas',
            'speed' => 'TC450D20U',
            'customerName' => 'John Smith',
            'orderRef' => 'WHMCS-42',
            'contact' => [
                'firstName' => 'John',
                'lastName' => 'Smith',
                'phoneNumber' => '+61 403 123 456',
                'emailAddress' => 'john@example.com',
            ],
        ];
    }

    public function testFttpOrderMatchesDocumentedShape(): void
    {
        $payload = OrderPayloadBuilder::build($this->baseOpts() + [
            'ntdId' => 'NTD999100158242',
            'uniDPortId' => '1-UNI-D2',
        ]);

        $this->assertSame('LOC123456789012', $payload['locationId']);
        $this->assertFalse($payload['custData']['business']);
        $this->assertSame('WHMCS-42', $payload['custData']['orderRef']);
        $this->assertSame('+61403123456', $payload['contactData']['phoneNumber']);
        $this->assertSame('TC450D20U', $payload['service']['nbn']['speed']);
        $this->assertSame('NTD999100158242', $payload['service']['nbn']['nfas']['ntdId']);
        $this->assertSame('1-UNI-D2', $payload['service']['nbn']['nfas']['uniDPortId']);
        $this->assertArrayNotHasKey('churn', $payload['service']['nbn']);
    }

    public function testChurnBlock(): void
    {
        $payload = OrderPayloadBuilder::build($this->baseOpts() + [
            'churnAvc' => 'avc123456789012',
            'authorityDate' => '2026-07-01',
        ]);

        $this->assertSame([
            'type' => 'Service Transfer',
            'customerAuthorityDate' => '2026-07-01',
            'serviceIDToTransfer' => 'AVC123456789012',
        ], $payload['service']['nbn']['churn']);
    }

    public function testCopperOrderUsesNcasBlock(): void
    {
        $payload = OrderPayloadBuilder::build(array_merge($this->baseOpts(), [
            'serviceType' => 'ncas',
            'copperPairId' => 'CPI300012143105',
        ]));

        $this->assertSame('CPI300012143105', $payload['service']['nbn']['ncas']['copperPairId']);
        $this->assertArrayNotHasKey('nfas', $payload['service']['nbn']);
    }

    public function testHfcOrderEncodesEmptyObjectBlock(): void
    {
        $payload = OrderPayloadBuilder::build(array_merge($this->baseOpts(), [
            'serviceType' => 'nhas',
        ]));

        $this->assertSame('{}', json_encode($payload['service']['nbn']['nhas']));
    }

    public function testSanitisesDisallowedCharacters(): void
    {
        $payload = OrderPayloadBuilder::build(array_merge($this->baseOpts(), [
            'customerName' => "John O'Smith & Sons! <Pty>",
            'notes' => "Beware\nof the “dog”",
        ]));

        $this->assertSame('John OSmith Sons Pty', $payload['custData']['customerName']);
        $this->assertSame('Beware of the dog', $payload['notes']);
    }

    public function testRejectsUnorderableServiceType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        OrderPayloadBuilder::build(array_merge($this->baseOpts(), ['serviceType' => 'nsas']));
    }

    public function testDeviceSelectionChurnFollowsMatch(): void
    {
        $qualification = [
            'ntds' => [[
                'id' => 'NTD1',
                'ports' => [
                    ['id' => '1-UNI-D1', 'status' => 'Used', 'free' => false, 'service_id_match' => false],
                    ['id' => '1-UNI-D2', 'status' => 'Used', 'free' => false, 'service_id_match' => true],
                ],
                'speed_tiers_supported' => [],
            ]],
            'copper_pairs' => [],
        ];

        $selection = ProvisioningService::selectDevice($qualification, [], true);
        $this->assertSame(['ntdId' => 'NTD1', 'uniDPortId' => '1-UNI-D2'], $selection);
    }

    public function testDeviceSelectionNewConnectionAutoPicksFreePort(): void
    {
        $qualification = [
            'ntds' => [[
                'id' => 'NTD1',
                'ports' => [
                    ['id' => '1-UNI-D1', 'status' => 'Used', 'free' => false, 'service_id_match' => false],
                    ['id' => '1-UNI-D2', 'status' => 'Free', 'free' => true, 'service_id_match' => false],
                ],
                'speed_tiers_supported' => [],
            ]],
            'copper_pairs' => [],
        ];

        $selection = ProvisioningService::selectDevice($qualification, [], false);
        $this->assertSame(['ntdId' => 'NTD1', 'uniDPortId' => '1-UNI-D2'], $selection);
    }

    public function testDeviceSelectionRespectsExplicitChoice(): void
    {
        $qualification = ['ntds' => [], 'copper_pairs' => []];

        $selection = ProvisioningService::selectDevice(
            $qualification,
            ['ntdId' => 'NTDX', 'uniDPortId' => '1-UNI-D4'],
            false
        );
        $this->assertSame(['ntdId' => 'NTDX', 'uniDPortId' => '1-UNI-D4'], $selection);
    }
}
