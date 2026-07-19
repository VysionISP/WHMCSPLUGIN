<?php

namespace WHMCS\Module\Server\VirtutelNbn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\VirtutelNbn\Service\StatusMapper;

class StatusMapperTest extends TestCase
{
    public function testTerminalStates(): void
    {
        $this->assertSame(StatusMapper::COMPLETE, StatusMapper::orderState('VTOrderCompleted'));
        $this->assertSame(StatusMapper::CANCELLED, StatusMapper::orderState('VTOrderCancelled'));
        $this->assertTrue(StatusMapper::isTerminalSuccess('VTOrderCompleted'));
        $this->assertTrue(StatusMapper::isTerminalFailure('VTOrderCancelled'));
    }

    public function testPlainOrderCompletedIsNotTerminal(): void
    {
        // OrderCompleted = NBN done but not yet in VT billing; must NOT
        // activate the service.
        $this->assertFalse(StatusMapper::isTerminalSuccess('OrderCompleted'));
        $this->assertSame(StatusMapper::IN_PROGRESS, StatusMapper::orderState('OrderCompleted'));
    }

    public function testActionRequiredStates(): void
    {
        foreach ([
            'AppointmentRequired' => 'appointment',
            'InstallFeeConfirmationRequired' => 'install_fee',
            'DevelopmentChargeConfirmationRequired' => 'development_charge',
            'FibreUpgradeConfirmationRequired' => 'fibre_upgrade_liability',
            'RSPActionRequired' => 'rsp_action',
            'ManualInterventionRequired' => 'manual_intervention',
        ] as $notification => $action) {
            $this->assertSame(StatusMapper::ACTION_REQUIRED, StatusMapper::orderState($notification));
            $this->assertSame($action, StatusMapper::actionRequired($notification));
        }
    }

    public function testMidFlightProgressStates(): void
    {
        foreach (['NBNAcknowledged', 'OrderAccepted', 'ServiceTestCompleted', 'DeliveryInTransit'] as $n) {
            $this->assertSame(StatusMapper::IN_PROGRESS, StatusMapper::orderState($n));
            $this->assertNull(StatusMapper::actionRequired($n));
        }
    }

    public function testAppointmentStatuses(): void
    {
        $this->assertSame('booked', StatusMapper::appointmentStatus('AppointmentBooked'));
        $this->assertSame('completed', StatusMapper::appointmentStatus('AppointmentCompleted'));
        $this->assertSame('cancelled', StatusMapper::appointmentStatus('AppointmentCancelled'));
        $this->assertNull(StatusMapper::appointmentStatus('SomethingUnknown'));
    }
}
