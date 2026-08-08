<?php

namespace Vysion\VirtutelNbn\Provider;

use Vysion\VirtutelNbn\Auth\TokenManager;
use Vysion\VirtutelNbn\Provider\Virtutel\VirtutelClient;
use Vysion\VirtutelNbn\Provider\Virtutel\VirtutelProvider;
use Vysion\VirtutelNbn\Repository\ApiLogRepository;
use Vysion\VirtutelNbn\Repository\TokenRepository;
use WHMCS\Database\Capsule;

/**
 * Builds a provider / token manager from the module's WHMCS server record
 * for contexts that run outside a module function (webhook processing on
 * cron, token keep-alive), where no $params array exists. WHMCS stores
 * tblservers.password (the Client Secret) encrypted; it is decrypted via
 * the local DecryptPassword API.
 */
final class VirtutelResolver
{
    public static function tokenManagerFromServerRecord(): ?TokenManager
    {
        $record = self::serverRecord();
        if ($record === null) {
            return null;
        }
        [$baseUrl, $clientId, $clientSecret] = $record;

        return new TokenManager($baseUrl, $clientId, $clientSecret, new TokenRepository());
    }

    public static function providerFromServerRecord(): ?ProviderInterface
    {
        $record = self::serverRecord();
        if ($record === null) {
            return null;
        }
        [$baseUrl, $clientId, $clientSecret] = $record;

        return new VirtutelProvider(new VirtutelClient(
            $baseUrl,
            new TokenManager($baseUrl, $clientId, $clientSecret, new TokenRepository()),
            new ApiLogRepository(),
        ));
    }

    /** @return array{0: string, 1: string, 2: string}|null [baseUrl, clientId, clientSecret] */
    private static function serverRecord(): ?array
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

        $clientId = trim((string) $server->username);

        $clientSecret = (string) $server->password;
        if ($clientSecret !== '' && function_exists('localAPI')) {
            $result = localAPI('DecryptPassword', ['password2' => $clientSecret]);
            if (($result['result'] ?? '') === 'success' && !empty($result['password'])) {
                $clientSecret = (string) $result['password'];
            }
        }

        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        return [rtrim($host, '/'), $clientId, $clientSecret];
    }
}
