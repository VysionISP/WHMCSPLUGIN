<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Api\VirtutelClient;

/**
 * Client-area appointment booking controller: renders the timeslot picker
 * and processes reservations/reschedules. WHMCS guarantees the logged-in
 * client owns the service before our custom function runs.
 */
class BookingPage
{
    public function __construct(
        private readonly VirtutelClient $client,
        private readonly array $params,
    ) {
    }

    /** @return array template vars for templates/book.tpl */
    public function handle(array $post): array
    {
        $whmcsServiceId = (int) $this->params['serviceid'];

        $service = Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $whmcsServiceId)->first();
        $order = $service ? Capsule::table('mod_virtutel_orders')
            ->where('service_id', $service->id)->orderByDesc('id')->first() : null;
        $appointment = $order ? Capsule::table('mod_virtutel_appointments')
            ->where('order_id', $order->id)->orderByDesc('id')->first() : null;

        $base = ['vt_serviceid' => $whmcsServiceId];

        [$needsBooking, $isReschedule] = ClientAreaState::bookingNeeded($order, $appointment);
        if (!$needsBooking) {
            return $base + ['vt_not_needed' => true] + $this->confirmedVars($appointment);
        }

        $demandType = (string) (($appointment->demand_type ?? '')
            ?: AppointmentService::demandTypeFor(
                $service->technology_type ?? null,
                isset($service->service_class) ? (int) $service->service_class : null
            ));

        $appointments = new AppointmentService($this->client);

        // Reservation / reschedule submission.
        if (!empty($post['vt_slot'])) {
            $slot = json_decode((string) base64_decode((string) $post['vt_slot'], true), true);
            if (!is_array($slot) || !AppointmentService::validSlot($slot)) {
                return $base + ['vt_error' => 'That timeslot could not be validated — please pick again.'];
            }

            try {
                if ($isReschedule && !empty($appointment->appointment_id)) {
                    $result = $appointments->reschedule((string) $appointment->appointment_id, $slot);
                } else {
                    $client = $this->params['clientsdetails'] ?? [];
                    $result = $appointments->reserve(
                        (string) $order->vt_order_id,
                        $demandType,
                        $slot,
                        [
                            'contactName' => trim(($client['firstname'] ?? '') . ' ' . ($client['lastname'] ?? '')),
                            'phoneNumber' => (string) ($client['phonenumberformatted'] ?? ($client['phonenumber'] ?? '')),
                        ],
                        $this->accessSeekerContact()
                    );
                }
            } catch (\Throwable $e) {
                return $base + [
                    'vt_error' => 'The appointment could not be reserved: ' . $e->getMessage()
                        . ' Please pick a different slot.',
                ];
            }

            $now = date('Y-m-d H:i:s');
            Capsule::table('mod_virtutel_appointments')->updateOrInsert(
                ['order_id' => $order->id, 'appointment_id' => $result['id']],
                [
                    'status' => $isReschedule ? 'rescheduled' : 'reserved',
                    'slot_start' => date('Y-m-d H:i:s', strtotime($slot['start'])),
                    'slot_end' => date('Y-m-d H:i:s', strtotime($slot['end'])),
                    'demand_type' => $demandType,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
            Capsule::table('mod_virtutel_orders')->where('id', $order->id)->update([
                'action_required' => null,
                'updated_at' => $now,
            ]);
            if (function_exists('logActivity')) {
                logActivity(sprintf(
                    'Virtutel NBN: customer %s appointment %s for order %s (%s)',
                    $isReschedule ? 'rescheduled' : 'reserved',
                    $result['id'],
                    $order->vt_order_id,
                    AppointmentService::slotLabel($slot['start'], $slot['end'])
                ));
            }

            return $base + [
                'vt_confirmed' => true,
                'vt_slot_label' => AppointmentService::slotLabel($slot['start'], $slot['end']),
                'vt_appointment_id' => $result['id'],
            ];
        }

        // Render the picker.
        try {
            $slots = $appointments->searchSlots(array_filter([
                'locationId' => $isReschedule ? null : ($service->nbn_location_id ?? null),
                'appointmentId' => $isReschedule ? ($appointment->appointment_id ?? null) : null,
                'demandType' => $demandType,
                'copperPairId' => $this->copperPairId($order),
            ]));
        } catch (\Throwable $e) {
            return $base + ['vt_error' => 'Available times could not be loaded right now — please try again shortly.'];
        }

        if ($slots === []) {
            return $base + [
                'vt_error' => 'No appointment slots are available in the next 90 days. '
                    . 'Please contact us and we\'ll arrange it with NBN directly.',
            ];
        }

        return $base + [
            'vt_mode' => $isReschedule ? 'reschedule' : 'new',
            'vt_slots' => array_map(fn ($slot) => [
                'label' => $slot['label'],
                'value' => base64_encode(json_encode([
                    'type' => $slot['type'],
                    'start' => $slot['start'],
                    'end' => $slot['end'],
                ])),
            ], $slots),
        ];
    }

    private function confirmedVars(?object $appointment): array
    {
        if (!$appointment || !$appointment->slot_start) {
            return [];
        }

        return [
            'vt_slot_label' => AppointmentService::slotLabel(
                (string) $appointment->slot_start,
                (string) $appointment->slot_end
            ),
            'vt_appointment_status' => (string) $appointment->status,
        ];
    }

    private function accessSeekerContact(): array
    {
        $get = fn (string $setting) => (string) (Capsule::table('tblconfiguration')
            ->where('setting', $setting)->value('value') ?? '');

        return [
            'contactName' => $get('CompanyName'),
            'emailAddress' => $get('Email'),
            'phoneNumber' => '',
        ];
    }

    private function copperPairId(?object $order): ?string
    {
        if (!$order || empty($order->request_payload)) {
            return null;
        }
        $payload = json_decode((string) $order->request_payload, true);

        $pair = $payload['service']['nbn']['ncas']['copperPairId'] ?? null;

        return is_string($pair) && $pair !== '' ? $pair : null;
    }
}
