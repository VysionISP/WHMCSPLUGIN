<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;

/**
 * Customer email notifications, sent via WHMCS's own template system so
 * admins can edit the wording under Configuration > Email Templates.
 */
class EmailNotifier
{
    public const TEMPLATE_APPOINTMENT = 'Virtutel NBN Appointment Required';

    /**
     * "Your NBN install needs an appointment — pick a time" with a link to
     * the client-area booking page. Used for both first bookings and
     * NBN-initiated reschedules (wording adapts via appointment_reason).
     */
    public static function appointmentRequired(int $whmcsServiceId, bool $reschedule = false): void
    {
        if (!function_exists('localAPI')) {
            return;
        }

        $reason = $reschedule
            ? 'Your NBN installation appointment needs to be rescheduled — the technician couldn\'t complete the original booking.'
            : 'Your NBN installation needs an appointment before the connection can go ahead.';

        localAPI('SendEmail', [
            'messagename' => self::TEMPLATE_APPOINTMENT,
            'id' => $whmcsServiceId,
            'customvars' => base64_encode(serialize([
                'appointment_reason' => $reason,
                'appointment_link' => self::bookingUrl($whmcsServiceId),
            ])),
        ]);

        logActivity(sprintf(
            'Virtutel NBN: appointment %s email sent for service #%d',
            $reschedule ? 'reschedule' : 'booking',
            $whmcsServiceId
        ));
    }

    public static function bookingUrl(int $whmcsServiceId): string
    {
        $systemUrl = rtrim((string) (Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value') ?? ''), '/');

        return $systemUrl . '/clientarea.php?action=productdetails&id=' . $whmcsServiceId
            . '&modop=custom&a=bookappointment';
    }
}
