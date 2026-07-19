<?php

namespace WHMCS\Module\Server\VirtutelNbn\Webhook\Handlers;

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Service\StatusMapper;
use WHMCS\Module\Server\VirtutelNbn\Webhook\CallbackEnvelope;

/**
 * Applies Appointment* callbacks to mod_virtutel_appointments.
 */
class AppointmentHandler
{
    public function handle(CallbackEnvelope $envelope): bool
    {
        $appointmentId = $envelope->objectId;
        $status = StatusMapper::appointmentStatus($envelope->notificationType);
        if ($appointmentId === '' || $status === null) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $row = Capsule::table('mod_virtutel_appointments')
            ->where('appointment_id', $appointmentId)
            ->first();

        if ($row) {
            Capsule::table('mod_virtutel_appointments')
                ->where('id', $row->id)
                ->update(['status' => $status, 'updated_at' => $now]);

            Capsule::table('mod_virtutel_callback_events')
                ->where('event_uuid', $envelope->eventUuid)
                ->update(['order_id' => $row->order_id]);

            return true;
        }

        // Appointment not seen before (e.g. created outside WHMCS): record it
        // unlinked; step-3 order flows attach appointments to orders on reserve.
        Capsule::table('mod_virtutel_appointments')->insert([
            'order_id' => 0,
            'appointment_id' => substr($appointmentId, 0, 64),
            'status' => $status,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return true;
    }
}
