<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;

/**
 * Assembles the client-area view state for a service: connection details,
 * order progress, and whether an appointment booking is currently needed.
 */
class ClientAreaState
{
    public static function forService(int $whmcsServiceId): array
    {
        $service = Capsule::table('mod_virtutel_services')
            ->where('whmcs_service_id', $whmcsServiceId)->first();

        if (!$service) {
            return ['vt_linked' => false, 'vt_serviceid' => $whmcsServiceId];
        }

        $order = Capsule::table('mod_virtutel_orders')
            ->where('service_id', $service->id)
            ->orderByDesc('id')
            ->first();

        $appointment = $order ? Capsule::table('mod_virtutel_appointments')
            ->where('order_id', $order->id)
            ->orderByDesc('id')
            ->first() : null;

        [$needsBooking, $isReschedule] = self::bookingNeeded($order, $appointment);

        $tier = $service->speed_tier ? SpeedTier::describe((string) $service->speed_tier) : null;

        return [
            'vt_linked' => true,
            'vt_serviceid' => $whmcsServiceId,
            'vt_technology' => strtoupper((string) ($service->technology_type ?? '')),
            'vt_speed' => $tier['label'] ?? (string) ($service->speed_tier ?? ''),
            'vt_avc' => (string) ($service->avc_id ?? ''),
            'vt_carrier_status' => (string) ($service->carrier_status ?? ''),
            'vt_order_id' => $order->vt_order_id ?? null,
            'vt_order_status' => $order->status ?? null,
            'vt_order_state' => $order->whmcs_status ?? null,
            'vt_action_required' => $order->action_required ?? null,
            'vt_appointment_status' => $appointment->status ?? null,
            'vt_appointment_start' => ($appointment && $appointment->slot_start)
                ? date('D j M Y, g:ia', strtotime((string) $appointment->slot_start)) : null,
            'vt_appointment_end' => ($appointment && $appointment->slot_end)
                ? date('g:ia', strtotime((string) $appointment->slot_end)) : null,
            'vt_needs_booking' => $needsBooking,
            'vt_is_reschedule' => $isReschedule,
            'vt_booking_url' => EmailNotifier::bookingUrl($whmcsServiceId),
        ];
    }

    /** @return array{0: bool, 1: bool} [needsBooking, isReschedule] */
    public static function bookingNeeded(?object $order, ?object $appointment): array
    {
        if (!$order || in_array($order->whmcs_status, [StatusMapper::COMPLETE, StatusMapper::CANCELLED], true)) {
            return [false, false];
        }

        if (($appointment->status ?? '') === 'reschedule_required'
            || $order->action_required === 'appointment_reschedule') {
            return [true, true];
        }

        $booked = $appointment
            && in_array($appointment->status, ['reserved', 'booked', 'rescheduled', 'tech_on_site', 'completed'], true);

        return [$order->action_required === 'appointment' && !$booked, false];
    }
}
