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
    public const TEMPLATE_ACTIVATED = 'Virtutel NBN Service Activated';
    public const TEMPLATE_WELCOME = 'Korvix Account Welcome';

    /**
     * Signup-wizard welcome: point the fresh account (random password they
     * never saw) at the password-reset page, and set expectations.
     */
    public static function accountWelcome(int $clientId, string $firstName): void
    {
        if (!function_exists('localAPI')) {
            return;
        }

        $systemUrl = rtrim((string) (Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value') ?? ''), '/');

        localAPI('SendEmail', [
            'messagename' => self::TEMPLATE_WELCOME,
            'id' => $clientId,
            'customvars' => base64_encode(serialize([
                'first_name' => $firstName,
                'reset_url' => $systemUrl . '/index.php?rp=/password/reset',
                'portal_url' => $systemUrl . '/clientarea.php',
            ])),
        ]);

        logActivity(sprintf('Virtutel NBN: account welcome email sent to client #%d', $clientId));
    }

    /**
     * "You're connected" welcome email with IPoE setup steps, sent once
     * when the connect order completes.
     *
     * @param array<string,string> $vars nbn_avc / nbn_speed / nbn_address
     */
    public static function serviceActivated(int $whmcsServiceId, array $vars): void
    {
        if (!function_exists('localAPI')) {
            return;
        }

        localAPI('SendEmail', [
            'messagename' => self::TEMPLATE_ACTIVATED,
            'id' => $whmcsServiceId,
            'customvars' => base64_encode(serialize($vars)),
        ]);

        logActivity(sprintf(
            'Virtutel NBN: activation welcome email sent for service #%d (AVC %s)',
            $whmcsServiceId,
            (string) ($vars['nbn_avc'] ?? '?')
        ));
    }

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
