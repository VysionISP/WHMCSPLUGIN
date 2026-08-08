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
            // Self-heal: an unlinked service whose Domain field carries an
            // AVC/VT/PRI reference gets one linking attempt per hour, so
            // customers stop seeing "being set up" on live services that
            // simply never got linked.
            $service = self::attemptAutoLink($whmcsServiceId);
        }

        if (!$service) {
            $hostingStatus = (string) (Capsule::table('tblhosting')
                ->where('id', $whmcsServiceId)->value('domainstatus') ?? '');

            return [
                'vt_linked' => false,
                'vt_serviceid' => $whmcsServiceId,
                'vt_hosting_status' => $hostingStatus,
            ];
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
            'vt_address' => (string) ($service->service_address ?? ''),
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
            'vt_change_note' => self::changeNote($service, $order),
            'vt_cpe' => Diagnostics::cpe($whmcsServiceId),
            'vt_order_status_label' => isset($order->status)
                ? ucwords(strtolower(str_replace('_', ' ', (string) $order->status))) : null,
            'vt_steps' => self::timeline($order, $appointment, $needsBooking, $isReschedule),
        ];
    }

    /** @return string active | in_progress | attention | unknown */
    /**
     * One throttled attempt to link an unlinked service from the reference
     * in its Domain field (AVC/VT/PRI). Returns the fresh link row, or
     * null when there's nothing to go on / the lookup finds no match.
     */
    private static function attemptAutoLink(int $whmcsServiceId): ?object
    {
        try {
            $throttleKey = 'autolink_' . $whmcsServiceId;
            $lastTry = (int) (\WHMCS\Module\Server\VirtutelNbn\Repository\Settings::get($throttleKey, '0') ?? '0');
            if ($lastTry > time() - 3600) {
                return null;
            }

            $hosting = Capsule::table('tblhosting')
                ->where('id', $whmcsServiceId)->first(['domain', 'domainstatus']);
            $ref = strtoupper(trim((string) ($hosting->domain ?? '')));
            if (!$hosting || !preg_match('/^(AVC\d{12}|VT\d+|PRI[0-9A-Z]+)$/', $ref)) {
                return null;
            }
            \WHMCS\Module\Server\VirtutelNbn\Repository\Settings::set($throttleKey, (string) time());

            $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService($whmcsServiceId);
            $svc = ServiceLinker::lookup($client, $ref);
            if ($svc === null) {
                return null;
            }
            ServiceLinker::link($whmcsServiceId, $svc, $client);
            if (function_exists('logActivity')) {
                logActivity(sprintf(
                    'Virtutel NBN: auto-linked service #%d from client-area view (%s)',
                    $whmcsServiceId,
                    $ref
                ));
            }

            return Capsule::table('mod_virtutel_services')
                ->where('whmcs_service_id', $whmcsServiceId)->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Friendly banner line for a change order riding on an active service
     * (the hero stays Connected; this explains what's in flight).
     */
    private static function changeNote(object $service, ?object $order): string
    {
        if (self::connectionState($service, $order) !== 'active' || !$order) {
            return '';
        }
        $orderState = (string) ($order->whmcs_status ?? '');
        if (!in_array($orderState, ['pending', 'in_progress', 'action_required'], true)) {
            return '';
        }

        $type = strtolower((string) ($order->order_type ?? ''));

        return match (true) {
            $type === 'modify_speed' => 'Speed change in progress — your connection stays up until the switch.',
            str_starts_with($type, 'modify') => 'A change to this service is in progress.',
            $type === 'disconnect' => 'Disconnection in progress.',
            default => '',
        };
    }

    private static function connectionState(object $service, ?object $order): string
    {
        $orderState = (string) ($order->whmcs_status ?? '');
        $orderType = strtolower((string) ($order->order_type ?? ''));
        $inFlight = in_array($orderState, ['pending', 'in_progress', 'action_required'], true);
        $carrier = strtolower((string) ($service->carrier_status ?? ''));
        $carrierActive = $carrier === '' || str_contains($carrier, 'active');

        // Only a CONNECT order in flight means "getting you connected" —
        // a modify/disconnect order on an already-active service must not
        // demote the hero from Connected.
        if ($inFlight && ($orderType === 'connect' || !$carrierActive)) {
            return 'in_progress';
        }
        if ($orderState === StatusMapper::CANCELLED && !$carrierActive) {
            return 'attention';
        }
        if ($carrierActive) {
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
        // The lodged->activation->online journey only describes CONNECT
        // orders; speed changes and disconnects on live services don't get
        // a provisioning timeline.
        if (strtolower((string) ($order->order_type ?? '')) !== 'connect') {
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
