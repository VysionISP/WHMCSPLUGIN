<?php

namespace Vysion\VirtutelNbn\Service;

use Vysion\VirtutelNbn\Config;
use Vysion\VirtutelNbn\Exception\ModuleException;
use Vysion\VirtutelNbn\Installer;
use Vysion\VirtutelNbn\Provider\ProviderInterface;
use Vysion\VirtutelNbn\Provider\Virtutel\VirtutelClient;
use Vysion\VirtutelNbn\Provider\Virtutel\VirtutelProvider;
use Vysion\VirtutelNbn\Repository\ApiLogRepository;
use Vysion\VirtutelNbn\Repository\OrderRepository;
use Vysion\VirtutelNbn\Repository\ServiceRepository;

/**
 * Orchestrates WHMCS module actions against the upstream provider and the
 * module's own persistence. Every upstream mutation is recorded as an
 * order row for auditability.
 */
final class ProvisioningService
{
    public function __construct(
        private readonly ProviderInterface $provider,
        private readonly ServiceRepository $services,
        private readonly OrderRepository $orders,
        private readonly QualificationService $qualification,
    ) {
    }

    public static function fromParams(array $params): self
    {
        Installer::ensureInstalled();
        $config = new Config($params);
        $client = new VirtutelClient($config->apiBaseUrl(), $config->apiKey(), new ApiLogRepository());
        $provider = new VirtutelProvider($client);

        return new self($provider, new ServiceRepository(), new OrderRepository(), new QualificationService($provider));
    }

    public function provider(): ProviderInterface
    {
        return $this->provider;
    }

    /** CreateAccount: qualify, place the connect order, persist state. */
    public function connect(Config $config): void
    {
        $whmcsServiceId = $config->whmcsServiceId();
        if ($whmcsServiceId <= 0) {
            throw new ModuleException('Missing WHMCS service id.');
        }
        if ($config->planCode() === '') {
            throw new ModuleException('No plan code configured on the product (Module Settings).');
        }

        $existing = $this->services->findByWhmcsServiceId($whmcsServiceId);
        if ($existing && in_array($existing->status, [StatusMapper::INTERNAL_ACTIVE, StatusMapper::INTERNAL_IN_PROGRESS, StatusMapper::INTERNAL_PENDING], true)) {
            throw new ModuleException('A VirtuTel order already exists for this service (status: ' . $existing->status . ').');
        }

        ['subject' => $subject] = $this->qualification->qualify($config);

        $order = $this->provider->connect($config->planCode(), $subject);
        $internalStatus = StatusMapper::fromProvider($order['status']);

        $serviceAttributes = [
            'provider' => $this->provider->name(),
            'provider_service_id' => $order['raw']['service_id'] ?? null,
            'nbn_loc_id' => $subject['loc_id'] ?? null,
            'plan_code' => $config->planCode(),
            'status' => $internalStatus,
        ];

        $serviceRowId = $existing
            ? $existing->id
            : $this->services->create(['whmcs_service_id' => $whmcsServiceId] + $serviceAttributes);
        if ($existing) {
            $this->services->update($serviceRowId, $serviceAttributes);
        }

        $this->recordOrder($serviceRowId, 'connect', $order, [
            'plan_code' => $config->planCode(),
            'subject' => $subject,
        ]);
    }

    public function changePackage(Config $config): void
    {
        $service = $this->requireServiceRow($config);
        if ($config->planCode() === '') {
            throw new ModuleException('No plan code configured on the new product (Module Settings).');
        }

        $order = $this->provider->modify($this->requireProviderServiceId($service), $config->planCode());
        $this->services->update($service->id, ['plan_code' => $config->planCode()]);
        $this->recordOrder($service->id, 'modify', $order, ['plan_code' => $config->planCode()]);
    }

    public function suspend(Config $config): void
    {
        $service = $this->requireServiceRow($config);
        $this->provider->suspend($this->requireProviderServiceId($service));
        $this->services->update($service->id, ['status' => StatusMapper::INTERNAL_SUSPENDED]);
    }

    public function unsuspend(Config $config): void
    {
        $service = $this->requireServiceRow($config);
        $this->provider->unsuspend($this->requireProviderServiceId($service));
        $this->services->update($service->id, ['status' => StatusMapper::INTERNAL_ACTIVE]);
    }

    public function terminate(Config $config): void
    {
        $service = $this->requireServiceRow($config);
        $order = $this->provider->disconnect($this->requireProviderServiceId($service));
        $this->services->update($service->id, ['status' => StatusMapper::INTERNAL_TERMINATED]);
        $this->recordOrder($service->id, 'disconnect', $order, []);
    }

    private function recordOrder(int $serviceRowId, string $type, array $order, array $requestContext): void
    {
        $this->orders->create([
            'service_id' => $serviceRowId,
            'provider_order_id' => $order['order_id'],
            'type' => $type,
            'status' => StatusMapper::fromProvider($order['status']),
            'request_payload' => json_encode(VirtutelClient::redact($requestContext)),
            'response_payload' => json_encode(VirtutelClient::redact($order['raw'])),
        ]);
    }

    private function requireServiceRow(Config $config): object
    {
        $service = $this->services->findByWhmcsServiceId($config->whmcsServiceId());
        if (!$service) {
            throw new ModuleException('No VirtuTel service record exists for this WHMCS service.');
        }

        return $service;
    }

    private function requireProviderServiceId(object $service): string
    {
        $id = (string) ($service->provider_service_id ?? '');
        if ($id === '') {
            throw new ModuleException(
                'The VirtuTel service id is not known yet (order may still be provisioning).'
            );
        }

        return $id;
    }
}
