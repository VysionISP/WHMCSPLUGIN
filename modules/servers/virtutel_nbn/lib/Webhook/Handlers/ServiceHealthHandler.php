<?php

namespace WHMCS\Module\Server\VirtutelNbn\Webhook\Handlers;

use WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory;
use WHMCS\Module\Server\VirtutelNbn\Repository\Settings;
use WHMCS\Module\Server\VirtutelNbn\Webhook\CallbackEnvelope;

/**
 * ServiceHealthStateChangeNotification: tracks the async health check we
 * requested from the admin service tab. On ServiceHealthCompleted the full
 * report is fetched and stored for display; the test-id -> service mapping
 * is written by the runhealthcheck admin action.
 */
class ServiceHealthHandler
{
    public function handle(CallbackEnvelope $envelope): bool
    {
        $testId = strtoupper(trim($envelope->objectId));
        if ($testId === '') {
            return false;
        }

        $serviceId = (int) (Settings::get('healthtest_' . $testId, '0') ?? '0');
        if ($serviceId === 0) {
            return false; // not one of ours (or mapping lost) — stored raw anyway
        }

        $state = json_decode((string) (Settings::get('health_' . $serviceId, '') ?? ''), true);
        $state = is_array($state) ? $state : [];
        $state['id'] = $testId;
        $state['status'] = $envelope->notificationType;
        $state['at'] = time();

        if ($envelope->notificationType === 'ServiceHealthCompleted') {
            try {
                $client = ClientFactory::forWhmcsService($serviceId);
                $report = $client->request(
                    'GET',
                    '/service-health-checks/' . rawurlencode($testId),
                    ['action' => 'HealthReport']
                );
                $state['report'] = $report->data;
            } catch (\Throwable $e) {
                $state['report_error'] = $e->getMessage();
            }
        }

        Settings::set('health_' . $serviceId, (string) json_encode($state));

        if (function_exists('logActivity')) {
            logActivity(sprintf(
                'Virtutel NBN: health check %s -> %s (service #%d)',
                $testId,
                $envelope->notificationType,
                $serviceId
            ));
        }

        return true;
    }
}
