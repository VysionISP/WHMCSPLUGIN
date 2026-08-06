<?php

namespace Vysion\VirtutelNbn\Provider\Virtutel;

use Vysion\VirtutelNbn\Exception\ApiException;
use Vysion\VirtutelNbn\Provider\ProviderInterface;

/**
 * VirtuTel implementation of ProviderInterface.
 *
 * NOTE: endpoint paths and payload field names below are a placeholder
 * REST shape pending the official VirtuTel API specification. Each method
 * is the single place to adjust once the spec is confirmed — nothing
 * outside this class knows VirtuTel's wire format.
 */
final class VirtutelProvider implements ProviderInterface
{
    public function __construct(private readonly VirtutelClient $client)
    {
    }

    public function name(): string
    {
        return 'virtutel';
    }

    public function qualify(array $subject): array
    {
        // TODO(virtutel-spec): confirm SQ endpoint + payload.
        return $this->client->request('POST', '/v1/qualifications', array_filter([
            'loc_id' => $subject['loc_id'] ?? null,
            'address' => $subject['address'] ?? null,
        ]));
    }

    public function connect(string $planCode, array $subject, array $extra = []): array
    {
        // TODO(virtutel-spec): confirm order endpoint + payload.
        $response = $this->client->request('POST', '/v1/orders', array_filter([
            'type' => 'connect',
            'plan_code' => $planCode,
            'loc_id' => $subject['loc_id'] ?? null,
            'address' => $subject['address'] ?? null,
        ]) + $extra);

        return $this->normaliseOrder($response);
    }

    public function modify(string $providerServiceId, string $newPlanCode): array
    {
        $response = $this->client->request('POST', '/v1/orders', [
            'type' => 'modify',
            'service_id' => $providerServiceId,
            'plan_code' => $newPlanCode,
        ]);

        return $this->normaliseOrder($response);
    }

    public function suspend(string $providerServiceId): array
    {
        return $this->client->request('POST', '/v1/services/' . rawurlencode($providerServiceId) . '/suspend');
    }

    public function unsuspend(string $providerServiceId): array
    {
        return $this->client->request('POST', '/v1/services/' . rawurlencode($providerServiceId) . '/unsuspend');
    }

    public function disconnect(string $providerServiceId): array
    {
        $response = $this->client->request('POST', '/v1/orders', [
            'type' => 'disconnect',
            'service_id' => $providerServiceId,
        ]);

        return $this->normaliseOrder($response);
    }

    public function getOrder(string $providerOrderId): array
    {
        return $this->client->request('GET', '/v1/orders/' . rawurlencode($providerOrderId));
    }

    public function getService(string $providerServiceId): array
    {
        return $this->client->request('GET', '/v1/services/' . rawurlencode($providerServiceId));
    }

    public function ping(): void
    {
        // TODO(virtutel-spec): use the cheapest authenticated endpoint available.
        $this->client->request('GET', '/v1/account');
    }

    private function normaliseOrder(array $response): array
    {
        $orderId = (string) ($response['order_id'] ?? $response['id'] ?? '');
        if ($orderId === '') {
            throw new ApiException('VirtuTel order response did not contain an order id.', null, $response);
        }

        return [
            'order_id' => $orderId,
            'status' => (string) ($response['status'] ?? 'submitted'),
            'raw' => $response,
        ];
    }
}
