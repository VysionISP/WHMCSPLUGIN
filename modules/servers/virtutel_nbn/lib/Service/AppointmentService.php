<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Module\Server\VirtutelNbn\Api\ApiException;
use WHMCS\Module\Server\VirtutelNbn\Api\VirtutelClient;

/**
 * Appointment timeslots, reservation, and rescheduling
 * (docs/api/08_appointments.md).
 *
 * Timeslot searches run per slot type (AM/PM) and merge; searches look up
 * to 90 days ahead and the API caps each response at 25 slots. Reserving
 * requires the order to be in [NBN_]APPOINTMENT_REQUIRED; rescheduling
 * PATCHes the appointment with only the new slot (never combined with a
 * contact update).
 */
class AppointmentService
{
    public const SLOT_TYPES = ['AM', 'PM', 'DAY', 'AHA', 'CA'];

    public function __construct(private readonly VirtutelClient $client)
    {
    }

    /**
     * @param array $opts {
     *   locationId?: string      (new bookings)
     *   appointmentId?: string   (reschedules)
     *   demandType: string
     *   copperPairId?: string    (FTTB/FTTN activations)
     *   fibreUpgrade?: bool
     * }
     * @return array[] merged slots [{type, start, end, label}]
     */
    public function searchSlots(array $opts): array
    {
        $base = array_filter([
            'locationId' => $opts['locationId'] ?? null,
            'appointmentId' => $opts['appointmentId'] ?? null,
            'copperPairId' => $opts['copperPairId'] ?? null,
            'demandType' => $opts['demandType'] ?? 'Standard Install',
            'startDateTime' => date('Y-m-d\T00:00:00', strtotime('+1 day')),
            'endDateTime' => date('Y-m-d\T00:00:00', strtotime('+90 days')),
            'fibreUpgrade' => !empty($opts['fibreUpgrade']) ? 'true' : null,
        ]);

        $slots = [];
        foreach (['AM', 'PM'] as $slotType) {
            try {
                $response = $this->client->request('GET', VirtutelClient::PATH_APPOINTMENT_TIMESLOTS, [
                    'action' => 'GetTimeslots',
                    'query' => $base + ['appointmentSlotType' => $slotType],
                ]);
            } catch (ApiException $e) {
                continue; // one empty window must not sink the other
            }

            foreach (self::parseSlots($response->data) as $slot) {
                $slots[$slot['type'] . '|' . $slot['start']] = $slot;
            }
        }

        $slots = array_values($slots);
        usort($slots, fn ($a, $b) => strcmp($a['start'], $b['start']));

        return array_slice($slots, 0, 25);
    }

    /** @return array[] [{type, start, end, label}] */
    public static function parseSlots(array $response): array
    {
        $slots = [];
        foreach ((array) ($response['availableTimeSlot'] ?? []) as $slot) {
            $start = (string) ($slot['validFor']['startDateTime'] ?? '');
            $end = (string) ($slot['validFor']['endDateTime'] ?? '');
            $type = (string) ($slot['appointmentSlotType'] ?? '');
            if ($start === '' || $end === '' || !in_array($type, self::SLOT_TYPES, true)) {
                continue;
            }
            $slots[] = [
                'type' => $type,
                'start' => $start,
                'end' => $end,
                'label' => self::slotLabel($start, $end),
            ];
        }

        return $slots;
    }

    public static function slotLabel(string $start, string $end): string
    {
        try {
            // Render in the slot's own timezone (NBN sends the premises-local
            // offset) — never convert to the server timezone.
            $startDt = new \DateTimeImmutable($start);
            $endDt = new \DateTimeImmutable($end);
        } catch (\Exception $e) {
            return $start;
        }

        return $startDt->format('D j M Y, g:ia') . ' – ' . $endDt->format('g:ia');
    }

    /** Validate a customer-submitted slot payload. */
    public static function validSlot(array $slot): bool
    {
        $iso = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}([+-]\d{2}:?\d{2}|Z)?$/';

        return isset($slot['type'], $slot['start'], $slot['end'])
            && in_array($slot['type'], self::SLOT_TYPES, true)
            && preg_match($iso, (string) $slot['start']) === 1
            && preg_match($iso, (string) $slot['end']) === 1;
    }

    /**
     * Reserve an appointment against an order in [NBN_]APPOINTMENT_REQUIRED.
     *
     * @return array{id: string, status: string}
     */
    public function reserve(
        string $vtOrderId,
        string $demandType,
        array $slot,
        array $endUserContact,
        array $accessSeekerContact,
    ): array {
        $response = $this->client->request('POST', VirtutelClient::PATH_APPOINTMENTS, [
            'action' => 'ReserveAppointment',
            'json' => [
                'vtOrderId' => $vtOrderId,
                'externalId' => OrderPayloadBuilder::sanitise('WHMCS-' . $vtOrderId),
                'demandType' => $demandType,
                'accessSeekerContact' => [
                    'contactName' => OrderPayloadBuilder::sanitise((string) ($accessSeekerContact['contactName'] ?? '')),
                    'phoneNumber' => preg_replace('/[^0-9+ ]/', '', (string) ($accessSeekerContact['phoneNumber'] ?? '')),
                    'emailAddress' => trim((string) ($accessSeekerContact['emailAddress'] ?? '')),
                ],
                'endUserContact' => [[
                    'contactType' => 'Primary Contact',
                    'contactName' => OrderPayloadBuilder::sanitise((string) ($endUserContact['contactName'] ?? '')),
                    'phoneNumber' => preg_replace('/[^0-9+ ]/', '', (string) ($endUserContact['phoneNumber'] ?? '')),
                ]],
                'appointmentSlot' => self::slotBody($slot),
            ],
        ]);

        return [
            'id' => (string) $response->get('id', ''),
            'status' => (string) $response->get('status', ''),
        ];
    }

    /**
     * Reschedule an existing appointment (slot only — the API forbids
     * combining this with a contact update).
     *
     * @return array{id: string, status: string}
     */
    public function reschedule(string $appointmentId, array $slot): array
    {
        $response = $this->client->request(
            'PATCH',
            VirtutelClient::PATH_APPOINTMENTS . '/' . rawurlencode($appointmentId),
            [
                'action' => 'RescheduleAppointment',
                'json' => ['appointmentSlot' => self::slotBody($slot)],
            ]
        );

        return [
            'id' => (string) $response->get('id', $appointmentId),
            'status' => (string) $response->get('status', ''),
        ];
    }

    /**
     * Demand type per docs: Standard Install for most orders; FTTN/FTTB
     * service class 12 jumpering uses 'Non EU Jumper Only'.
     */
    public static function demandTypeFor(?string $serviceType, ?int $serviceClass): string
    {
        if ($serviceType === 'ncas' && $serviceClass === 12) {
            return 'Non EU Jumper Only';
        }

        return 'Standard Install';
    }

    private static function slotBody(array $slot): array
    {
        return [
            'appointmentSlotType' => (string) $slot['type'],
            'validFor' => [
                'startDateTime' => (string) $slot['start'],
                'endDateTime' => (string) $slot['end'],
            ],
        ];
    }
}
