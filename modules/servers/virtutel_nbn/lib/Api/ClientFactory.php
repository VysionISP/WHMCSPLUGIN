<?php

namespace WHMCS\Module\Server\VirtutelNbn\Api;

use WHMCS\Database\Capsule;

/**
 * Builds VirtutelClients outside of module-call contexts (webhook endpoint,
 * cron), where WHMCS does not hand us decrypted $params.
 */
class ClientFactory
{
    /** @param object $server row from tblservers */
    public static function forServerRow(object $server): VirtutelClient
    {
        $password = '';
        if (function_exists('localAPI')) {
            $result = localAPI('DecryptPassword', ['password2' => (string) $server->password]);
            $password = (string) ($result['password'] ?? '');
        }

        return VirtutelClient::fromModuleParams([
            'serverhostname' => (string) $server->hostname,
            'serverip' => (string) ($server->ipaddress ?? ''),
            'serverport' => (int) ($server->port ?: 0),
            'serverusername' => (string) $server->username,
            'serverpassword' => $password,
        ]);
    }

    /**
     * Client for the server a WHMCS service is assigned to, falling back to
     * the first enabled virtutel_nbn server.
     */
    public static function forWhmcsService(int $whmcsServiceId): VirtutelClient
    {
        $serverId = (int) (Capsule::table('tblhosting')
            ->where('id', $whmcsServiceId)->value('server') ?? 0);

        $query = Capsule::table('tblservers')
            ->whereIn('type', ['virtutel_nbn', 'virtutel_phone'])
            ->where('disabled', 0);
        $server = $serverId > 0
            ? (Capsule::table('tblservers')->where('id', $serverId)->first()
                ?: $query->first())
            : $query->first();

        if (!$server) {
            throw new ApiException('No enabled Virtutel NBN server is configured in WHMCS');
        }

        return self::forServerRow($server);
    }
}
