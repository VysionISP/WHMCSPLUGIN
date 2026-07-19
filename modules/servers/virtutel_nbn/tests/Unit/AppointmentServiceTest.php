<?php

namespace WHMCS\Module\Server\VirtutelNbn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\VirtutelNbn\Service\AppointmentService;
use WHMCS\Module\Server\VirtutelNbn\Service\ClientAreaState;

class AppointmentServiceTest extends TestCase
{
    public function testParsesDocumentedTimeslotResponse(): void
    {
        $slots = AppointmentService::parseSlots([
            'availableTimeSlot' => [
                [
                    'appointmentSlotType' => 'AM',
                    'validFor' => [
                        'startDateTime' => '2019-08-23T08:00:00+10:00',
                        'endDateTime' => '2019-08-23T12:00:00+10:00',
                    ],
                    'withinTimeWindow' => false,
                ],
                ['appointmentSlotType' => 'XX', 'validFor' => ['startDateTime' => 'x', 'endDateTime' => 'y']],
            ],
        ]);

        $this->assertCount(1, $slots);
        $this->assertSame('AM', $slots[0]['type']);
        $this->assertStringContainsString('23 Aug 2019', $slots[0]['label']);
        $this->assertStringContainsString('–', $slots[0]['label']);
    }

    public function testSlotValidation(): void
    {
        $good = ['type' => 'PM', 'start' => '2026-08-23T13:00:00+10:00', 'end' => '2026-08-23T17:00:00+10:00'];
        $this->assertTrue(AppointmentService::validSlot($good));

        $this->assertFalse(AppointmentService::validSlot(['type' => 'PM', 'start' => 'DROP TABLE', 'end' => 'x']));
        $this->assertFalse(AppointmentService::validSlot(['type' => 'ZZ'] + $good));
        $this->assertFalse(AppointmentService::validSlot([]));
    }

    public function testDemandTypeSelection(): void
    {
        $this->assertSame('Standard Install', AppointmentService::demandTypeFor('nfas', 2));
        $this->assertSame('Non EU Jumper Only', AppointmentService::demandTypeFor('ncas', 12));
        $this->assertSame('Standard Install', AppointmentService::demandTypeFor('ncas', 13));
        $this->assertSame('Standard Install', AppointmentService::demandTypeFor(null, null));
    }

    public function testBookingNeededStates(): void
    {
        $order = fn (string $state, ?string $action) => (object) [
            'whmcs_status' => $state,
            'action_required' => $action,
        ];
        $appointment = fn (string $status) => (object) ['status' => $status];

        // Appointment required, nothing booked yet -> book.
        $this->assertSame([true, false], ClientAreaState::bookingNeeded($order('action_required', 'appointment'), null));
        // Already reserved -> nothing to do.
        $this->assertSame([false, false], ClientAreaState::bookingNeeded($order('in_progress', 'appointment'), $appointment('reserved')));
        // NBN asked for a reschedule -> rebook.
        $this->assertSame([true, true], ClientAreaState::bookingNeeded($order('action_required', 'appointment_reschedule'), $appointment('booked')));
        $this->assertSame([true, true], ClientAreaState::bookingNeeded($order('in_progress', null), $appointment('reschedule_required')));
        // Terminal orders never prompt.
        $this->assertSame([false, false], ClientAreaState::bookingNeeded($order('complete', 'appointment'), null));
        $this->assertSame([false, false], ClientAreaState::bookingNeeded(null, null));
    }
}
