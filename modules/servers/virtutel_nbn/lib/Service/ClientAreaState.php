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
            'vt_conn_state' => self::connectionState($service, $order),
            'vt_cpe' => Diagnostics::cpe($whmcsServiceId),
            'vt_order_status_label' => isset($order->status)
                ? ucwords(strtolower(str_replace('_', ' ', (string) $order->status))) : null,
            'vt_steps' => self::timeline($order, $appointment, $needsBooking, $isReschedule),
        ];
    }

    /** @return string active | in_progress | attention | unknown */
    private static function connectionState(object $service, ?object $order): string
    {
        $orderState = (string) ($order->whmcs_status ?? '');
        if (in_array($orderState, ['pending', 'in_progress', 'action_required'], true)) {
            return 'in_progress';
        }
        if ($orderState === StatusMapper::CANCELLED) {
            return 'attention';
        }
        $carrier = strtolower((string) ($service->carrier_status ?? ''));
        if ($carrier === '' || str_contains($carrier, 'active')) {
            return 'active';
        }

        return 'attention';
    }

    /**
     * Provisioning timeline for in-flight orders (empty once complete).
     *
     * @return array[] [{label, state: done|current|todo, note}]
     */
    private static function timeline(?object $order, ?object $appointment, bool $needsBooking, bool $isReschedule): array
    {
        $orderState = (string) ($order->whmcs_status ?? '');
        if (!$order || !in_array($orderState, ['pending', 'in_progress', 'action_required'], true)) {
            return [];
        }

        $steps = [[
            'label' => 'Order lodged',
            'state' => 'done',
            'note' => (string) ($order->vt_order_id ?? ''),
        ]];

        $apptPending = false;
        if ($needsBooking) {
            $steps[] = [
                'label' => $isReschedule ? 'Rebook your appointment' : 'Book your appointment',
                'state' => 'current',
                'note' => 'Action needed — pick a time below',
            ];
            $apptPending = true;
        } elseif ($appointment) {
            $done = in_array((string) $appointment->status, ['completed'], true);
            $steps[] = [
                'label' => 'Technician visit',
                'state' => $done ? 'done' : 'current',
                'note' => $appointment->slot_start
                    ? date('D j M, g:ia', strtotime((string) $appointment->slot_start)) : '',
            ];
            $apptPending = !$done;
        }

        $steps[] = [
            'label' => 'Activation',
            'state' => $apptPending ? 'todo' : 'current',
            'note' => $apptPending ? '' : 'Usually completes within hours',
        ];
        $steps[] = ['label' => 'You\'re online', 'state' => 'todo', 'note' => ''];

        return $steps;
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
