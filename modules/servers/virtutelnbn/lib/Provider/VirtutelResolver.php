<?php

namespace Vysion\VirtutelNbn\Provider;

use Vysion\VirtutelNbn\Provider\Virtutel\VirtutelClient;
use Vysion\VirtutelNbn\Provider\Virtutel\VirtutelProvider;
use Vysion\VirtutelNbn\Repository\ApiLogRepository;
use WHMCS\Database\Capsule;

/**
 * Builds a provider from the module's WHMCS server record for contexts
 * that run outside a module function (webhook processing on cron), where
 * no $params array exists. WHMCS stores tblservers.password encrypted;
 * it is decrypted via the local DecryptPassword API.
 */
final class VirtutelResolver
{
    public static function providerFromServerRecord(): ?ProviderInterface
    {
        $server = Capsule::table('tblservers')
            ->where('type', 'virtutelnbn')
            ->where('disabled', 0)
            ->orderBy('id')
            ->first();
        if (!$server) {
            return null;
        }

        $host = trim((string) $server->hostname);
        if ($host === '') {
            return null;
        }
        if (!preg_match('#^https?://#i', $host)) {
            $host = 'https://' . $host;
        }

        $apiKey = (string) $server->password;
        if ($apiKey !== '' && function_exists('localAPI')) {
            $result = localAPI('DecryptPassword', ['password2' => $apiKey]);
            if (($result['result'] ?? '') === 'success' && !empty($result['password'])) {
                $apiKey = (string) $result['password'];
            }
        }
        if ($apiKey === '') {
            return null;
        }

        return new VirtutelProvider(
            new VirtutelClient(rtrim($host, '/'), $apiKey, new ApiLogRepository())
        );
    }
}
