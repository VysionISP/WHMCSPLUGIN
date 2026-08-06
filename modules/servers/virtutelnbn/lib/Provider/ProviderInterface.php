<?php

namespace Vysion\VirtutelNbn\Provider;

/**
 * Abstraction over an upstream NBN wholesale provider. VirtuTel is the
 * first implementation; additional carriers implement this interface and
 * plug in without touching the WHMCS-facing orchestration code.
 *
 * All methods return decoded response arrays and throw
 * Vysion\VirtutelNbn\Exception\ApiException on failure.
 */
interface ProviderInterface
{
    public function name(): string;

    /**
     * Service qualification: can this address / NBN location be serviced,
     * and with which technology + plans?
     *
     * @param array{loc_id?: string, address?: string} $subject
     */
    public function qualify(array $subject): array;

    /**
     * Place a new connection order.
     *
     * @return array{order_id: string, status: string, raw: array}
     */
    public function connect(string $planCode, array $subject, array $extra = []): array;

    /**
     * Change speed tier / plan on an existing service.
     *
     * @return array{order_id: string, status: string, raw: array}
     */
    public function modify(string $providerServiceId, string $newPlanCode): array;

    public function suspend(string $providerServiceId): array;

    public function unsuspend(string $providerServiceId): array;

    /**
     * Disconnect / cancel the service.
     *
     * @return array{order_id: string, status: string, raw: array}
     */
    public function disconnect(string $providerServiceId): array;

    /** Fetch current order state (used to verify webhook claims). */
    public function getOrder(string $providerOrderId): array;

    /** Fetch current service state. */
    public function getService(string $providerServiceId): array;

    /** Lightweight credential check for WHMCS Test Connection. */
    public function ping(): void;
}
