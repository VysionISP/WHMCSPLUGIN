<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

/**
 * Maps Virtutel order callback notificationTypes onto the module's order
 * lifecycle states. Catalogue: docs/api/07_productorders.md.
 */
class StatusMapper
{
    /** Final, successful — order is in VT billing. */
    public const COMPLETE = 'complete';
    /** Final, unsuccessful. */
    public const CANCELLED = 'cancelled';
    /** Waiting on us or the end user; must be surfaced to staff. */
    public const ACTION_REQUIRED = 'action_required';
    public const IN_PROGRESS = 'in_progress';

    /** notificationType => action code for states requiring RSP-side action. */
    private const ACTIONS = [
        'AppointmentRequired' => 'appointment',
        'AppointmentRescheduleRequired' => 'appointment_reschedule',
        'InstallFeeConfirmationRequired' => 'install_fee',
        'DevelopmentChargeConfirmationRequired' => 'development_charge',
        'FibreUpgradeConfirmationRequired' => 'fibre_upgrade_liability',
        'RSPActionRequired' => 'rsp_action',
        'DispatchDetailsRequired' => 'dispatch_details',
        'DeviceDetailsRequired' => 'device_details',
        'DeviceOnlineRequired' => 'device_online',
        'ManualInterventionRequired' => 'manual_intervention',
    ];

    public static function orderState(string $notificationType): string
    {
        return match ($notificationType) {
            'VTOrderCompleted' => self::COMPLETE,
            'VTOrderCancelled' => self::CANCELLED,
            default => isset(self::ACTIONS[$notificationType])
                ? self::ACTION_REQUIRED
                : self::IN_PROGRESS,
        };
    }

    /** Action code for action-required notifications, else null. */
    public static function actionRequired(string $notificationType): ?string
    {
        return self::ACTIONS[$notificationType] ?? null;
    }

    /**
     * True only for VTOrderCompleted. Note that plain 'OrderCompleted' means
     * NBN finished but VT billing hasn't processed it yet — NOT final.
     */
    public static function isTerminalSuccess(string $notificationType): bool
    {
        return $notificationType === 'VTOrderCompleted';
    }

    public static function isTerminalFailure(string $notificationType): bool
    {
        return $notificationType === 'VTOrderCancelled';
    }

    /** Appointment callback notificationType => appointment status column. */
    public static function appointmentStatus(string $notificationType): ?string
    {
        return match ($notificationType) {
            'AppointmentCreated' => 'created',
            'AppointmentBooked' => 'booked',
            'AppointmentRescheduled' => 'rescheduled',
            'AppointmentRescheduleRequired' => 'reschedule_required',
            'TechOnSite' => 'tech_on_site',
            'CompletionTimeExtended' => 'time_extended',
            'AppointmentCompleted' => 'completed',
            'AppointmentIncomplete' => 'incomplete',
            'AppointmentCancelled' => 'cancelled',
            default => null,
        };
    }
}
