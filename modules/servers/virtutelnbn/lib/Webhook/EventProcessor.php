<?php

namespace Vysion\VirtutelNbn\Webhook;

use Vysion\VirtutelNbn\Installer;
use Vysion\VirtutelNbn\Provider\Virtutel\VirtutelClient;
use Vysion\VirtutelNbn\Provider\VirtutelResolver;
use Vysion\VirtutelNbn\Repository\OrderRepository;
use Vysion\VirtutelNbn\Repository\ServiceRepository;
use Vysion\VirtutelNbn\Repository\WebhookEventRepository;
use Vysion\VirtutelNbn\Service\StatusMapper;
use WHMCS\Database\Capsule;

/**
 * Cron-driven processing of queued webhook events. For safety-critical
 * transitions (active / cancelled / terminated) the order state is
 * re-fetched from the API rather than trusting the webhook payload.
 */
final class EventProcessor
{
    public function __construct(
        private readonly WebhookEventRepository $events,
        private readonly OrderRepository $orders,
        private readonly ServiceRepository $services,
    ) {
    }

    public static function run(int $limit = 25): void
    {
        Installer::ensureInstalled();
        (new self(new WebhookEventRepository(), new OrderRepository(), new ServiceRepository()))
            ->processPending($limit);
    }

    public function processPending(int $limit = 25): void
    {
        foreach ($this->events->pending($limit) as $event) {
            try {
                $this->processOne($event);
            } catch (\Throwable $e) {
                $this->events->markFailed($event->id, $e->getMessage());
            }
        }
    }

    private function processOne(object $event): void
    {
        $payload = json_decode($event->raw_payload, true) ?: [];

        // TODO(virtutel-spec): confirm order reference field name.
        $providerOrderId = (string) ($payload['order_id'] ?? $payload['order']['id'] ?? '');
        if ($providerOrderId === '') {
            $this->events->markSkipped($event->id, 'Event carries no order reference.');

            return;
        }

        $order = $this->orders->findByProviderOrderId($providerOrderId);
        if (!$order) {
            $this->events->markSkipped($event->id, "No local order found for provider order {$providerOrderId}.");

            return;
        }

        $claimedStatus = (string) ($payload['status'] ?? $payload['order']['status'] ?? '');
        $internal = StatusMapper::fromProvider($claimedStatus);

        // Trust but verify: re-fetch from the API before acting on
        // critical transitions. The webhook is a doorbell, not the parcel.
        $verified = $payload;
        if (StatusMapper::requiresVerification($internal)) {
            $provider = VirtutelResolver::providerFromServerRecord();
            if ($provider !== null) {
                $verified = $provider->getOrder($providerOrderId);
                $internal = StatusMapper::fromProvider((string) ($verified['status'] ?? $claimedStatus));
            }
        }

        $this->orders->update($order->id, [
            'status' => $internal,
            'response_payload' => json_encode(VirtutelClient::redact($verified)),
        ]);

        $serviceRow = Capsule::table('mod_virtutel_service')->where('id', $order->service_id)->first();
        if ($serviceRow) {
            $serviceUpdates = ['status' => $internal];
            foreach (['service_id' => 'provider_service_id', 'avc_id' => 'avc_id'] as $from => $to) {
                if (!empty($verified[$from])) {
                    $serviceUpdates[$to] = (string) $verified[$from];
                }
            }
            $this->services->update($serviceRow->id, $serviceUpdates);

            $whmcsStatus = StatusMapper::toWhmcsServiceStatus($internal);
            if ($whmcsStatus !== null) {
                $this->updateWhmcsService((int) $serviceRow->whmcs_service_id, $whmcsStatus);
            }
        }

        $this->events->markProcessed($event->id, $order->id);
    }

    private function updateWhmcsService(int $whmcsServiceId, string $status): void
    {
        if (!function_exists('localAPI')) {
            return;
        }
        localAPI('UpdateClientProduct', [
            'serviceid' => $whmcsServiceId,
            'status' => $status,
        ]);
    }
}
