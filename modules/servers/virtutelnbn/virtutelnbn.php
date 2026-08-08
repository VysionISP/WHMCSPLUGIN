<?php
/**
 * VirtuTel NBN Provisioning Module for WHMCS
 *
 * Server credential mapping (Setup > Products/Services > Servers):
 *   Hostname    = VirtuTel API base URL
 *   Username    = VirtuTel Client ID
 *   Password    = VirtuTel Client Secret (encrypted by WHMCS); exchanged
 *                 for a ~28-day access token, auto-renewed by TokenManager
 *   Access Hash = Webhook shared secret (HMAC key)
 *
 * Webhook endpoint to register with VirtuTel:
 *   https://<your-whmcs>/modules/servers/virtutelnbn/webhook.php
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/Autoloader.php';

use Vysion\VirtutelNbn\Autoloader;
use Vysion\VirtutelNbn\Config;
use Vysion\VirtutelNbn\Installer;
use Vysion\VirtutelNbn\Repository\OrderRepository;
use Vysion\VirtutelNbn\Repository\ServiceRepository;
use Vysion\VirtutelNbn\Service\ProvisioningService;

Autoloader::register();

function virtutelnbn_MetaData(): array
{
    return [
        'DisplayName' => 'VirtuTel NBN Provisioning',
        'APIVersion' => '1.1',
        'RequiresServer' => true,
    ];
}

function virtutelnbn_ConfigOptions(): array
{
    return [
        'Plan Code' => [
            'Type' => 'text',
            'Size' => '30',
            'Description' => 'VirtuTel plan / speed-tier code for this product',
        ],
        'Test Mode' => [
            'Type' => 'yesno',
            'Description' => 'Tick to log actions without contacting the live API',
        ],
    ];
}

function virtutelnbn_TestConnection(array $params): array
{
    try {
        ProvisioningService::fromParams($params)->provider()->ping();

        return ['success' => true, 'error' => ''];
    } catch (\Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function virtutelnbn_CreateAccount(array $params): string
{
    return virtutelnbn_run($params, 'CreateAccount', function (ProvisioningService $service, Config $config) {
        $service->connect($config);
    });
}

function virtutelnbn_SuspendAccount(array $params): string
{
    return virtutelnbn_run($params, 'SuspendAccount', function (ProvisioningService $service, Config $config) {
        $service->suspend($config);
    });
}

function virtutelnbn_UnsuspendAccount(array $params): string
{
    return virtutelnbn_run($params, 'UnsuspendAccount', function (ProvisioningService $service, Config $config) {
        $service->unsuspend($config);
    });
}

function virtutelnbn_TerminateAccount(array $params): string
{
    return virtutelnbn_run($params, 'TerminateAccount', function (ProvisioningService $service, Config $config) {
        $service->terminate($config);
    });
}

function virtutelnbn_ChangePackage(array $params): string
{
    return virtutelnbn_run($params, 'ChangePackage', function (ProvisioningService $service, Config $config) {
        $service->changePackage($config);
    });
}

/**
 * Shared wrapper: build services, honour test mode, translate exceptions
 * into the error-string convention WHMCS expects from module commands.
 */
function virtutelnbn_run(array $params, string $action, callable $callback): string
{
    try {
        $config = new Config($params);
        if ($config->testMode()) {
            logModuleCall('virtutelnbn', $action, ['serviceid' => $config->whmcsServiceId()], 'TEST MODE: no API call made');

            return 'success';
        }

        $callback(ProvisioningService::fromParams($params), $config);

        return 'success';
    } catch (\Throwable $e) {
        logModuleCall('virtutelnbn', $action, ['serviceid' => $params['serviceid'] ?? null], $e->getMessage());

        return $e->getMessage();
    }
}

function virtutelnbn_AdminServicesTabFields(array $params): array
{
    try {
        Installer::ensureInstalled();
        $service = (new ServiceRepository())->findByWhmcsServiceId((int) ($params['serviceid'] ?? 0));
        if (!$service) {
            return ['VirtuTel' => 'No VirtuTel record for this service yet.'];
        }

        $rows = '';
        foreach ((new OrderRepository())->recentForService($service->id, 5) as $order) {
            $rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                htmlspecialchars((string) $order->provider_order_id),
                htmlspecialchars($order->type),
                htmlspecialchars($order->status),
                htmlspecialchars((string) $order->created_at),
            );
        }
        $ordersTable = $rows === ''
            ? '<em>No orders recorded.</em>'
            : '<table class="table table-condensed"><tr><th>Order</th><th>Type</th><th>Status</th><th>Created</th></tr>' . $rows . '</table>';

        return [
            'VirtuTel Service ID' => htmlspecialchars((string) ($service->provider_service_id ?? '-')),
            'AVC ID' => htmlspecialchars((string) ($service->avc_id ?? '-')),
            'NBN Location ID' => htmlspecialchars((string) ($service->nbn_loc_id ?? '-')),
            'Provider Status' => htmlspecialchars($service->status),
            'Recent Orders' => $ordersTable,
        ];
    } catch (\Throwable $e) {
        return ['VirtuTel' => 'Error: ' . htmlspecialchars($e->getMessage())];
    }
}

function virtutelnbn_ClientArea(array $params): array
{
    try {
        Installer::ensureInstalled();
        $service = (new ServiceRepository())->findByWhmcsServiceId((int) ($params['serviceid'] ?? 0));

        return [
            'templatefile' => 'templates/clientarea',
            'vars' => [
                'nbnStatus' => $service->status ?? 'pending',
                'avcId' => $service->avc_id ?? null,
                'planCode' => $service->plan_code ?? null,
            ],
        ];
    } catch (\Throwable $e) {
        return [
            'templatefile' => 'templates/clientarea',
            'vars' => ['nbnStatus' => 'unknown', 'avcId' => null, 'planCode' => null],
        ];
    }
}
