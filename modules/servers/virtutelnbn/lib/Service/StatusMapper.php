<?php

namespace Vysion\VirtutelNbn\Service;

/**
 * Maps provider-side order/service statuses onto the module's internal
 * status vocabulary and WHMCS service states. Pure functions — unit
 * tested without WHMCS.
 */
final class StatusMapper
{
    public const INTERNAL_PENDING = 'pending';
    public const INTERNAL_IN_PROGRESS = 'in_progress';
    public const INTERNAL_HELD = 'held';
    public const INTERNAL_ACTIVE = 'active';
    public const INTERNAL_SUSPENDED = 'suspended';
    public const INTERNAL_CANCELLED = 'cancelled';
    public const INTERNAL_TERMINATED = 'terminated';

    /** Provider status string -> internal status. */
    public static function fromProvider(string $providerStatus): string
    {
        return match (strtolower(trim($providerStatus))) {
            'pending', 'submitted', 'received', 'created' => self::INTERNAL_PENDING,
            'acknowledged', 'in_progress', 'in progress', 'provisioning', 'scheduled' => self::INTERNAL_IN_PROGRESS,
            'held', 'on_hold', 'pending_information', 'delayed' => self::INTERNAL_HELD,
            'active', 'complete', 'completed', 'connected' => self::INTERNAL_ACTIVE,
            'suspended' => self::INTERNAL_SUSPENDED,
            'cancelled', 'canceled', 'withdrawn', 'rejected' => self::INTERNAL_CANCELLED,
            'disconnected', 'terminated' => self::INTERNAL_TERMINATED,
            default => self::INTERNAL_IN_PROGRESS,
        };
    }

    /**
     * Internal status -> WHMCS tblhosting.domainstatus value, or null when
     * the WHMCS state should not change (e.g. an order merely progressing).
     */
    public static function toWhmcsServiceStatus(string $internalStatus): ?string
    {
        return match ($internalStatus) {
            self::INTERNAL_ACTIVE => 'Active',
            self::INTERNAL_SUSPENDED => 'Suspended',
            self::INTERNAL_CANCELLED => 'Cancelled',
            self::INTERNAL_TERMINATED => 'Terminated',
            default => null,
        };
    }

    /** Statuses that are safety-critical enough to verify via API re-fetch. */
    public static function requiresVerification(string $internalStatus): bool
    {
        return in_array($internalStatus, [
            self::INTERNAL_ACTIVE,
            self::INTERNAL_CANCELLED,
            self::INTERNAL_TERMINATED,
        ], true);
    }
}
