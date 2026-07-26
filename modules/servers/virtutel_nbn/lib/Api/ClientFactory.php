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

        try {
            return VirtutelClient::fromModuleParams([
                'serverhostname' => (string) $server->hostname,
                'serverip' => (string) ($server->ipaddress ?? ''),
                'serverport' => (int) ($server->port ?: 0),
                'serverusername' => (string) $server->username,
                'serverpassword' => $password,
            ]);
        } catch (ApiException $e) {
            // Name the offending server record — "credentials missing" is
            // undiagnosable without knowing WHICH row was picked.
            throw new ApiException(
                sprintf('Server "%s" (#%d, %s): %s', (string) $server->name, (int) $server->id, (string) $server->type, $e->getMessage()),
                $e->getShortError(),
                $e->getHttpStatus(),
                $e
            );
        }
    }

    /**
     * Client for the server a WHMCS service is assigned to, falling back to
     * the first enabled Virtutel server WITH credentials (NBN preferred).
     * A row without a username can never authenticate, so it is skipped
     * even when it's the assigned one — e.g. a phone server record saved
     * without credentials must not break services that fall back to it.
     */
    public static function forWhmcsService(int $whmcsServiceId): VirtutelClient
    {
        $serverId = (int) (Capsule::table('tblhosting')
            ->where('id', $whmcsServiceId)->value('server') ?? 0);

        $server = null;
        if ($serverId > 0) {
            $assigned = Capsule::table('tblservers')->where('id', $serverId)->first();
            if ($assigned && trim((string) ($assigned->username ?? '')) !== '') {
                $server = $assigned;
            }
        }

        if (!$server) {
            $server = Capsule::table('tblservers')
                ->whereIn('type', ['virtutel_nbn', 'virtutel_phone'])
                ->where('disabled', 0)
                ->where('username', '!=', '')
                ->orderByRaw("type = 'virtutel_nbn' desc")
                ->orderBy('id')
                ->first();
        }

        if (!$server) {
            throw new ApiException(
                'No enabled Virtutel server with API credentials is configured in WHMCS '
                . '— check the username (client_id) and password (client_secret) on the server record'
            );
        }

        return self::forServerRow($server);
    }
}
