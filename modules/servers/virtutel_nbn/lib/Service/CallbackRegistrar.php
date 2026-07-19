<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Api\VirtutelClient;
use WHMCS\Module\Server\VirtutelNbn\Repository\Settings;
use WHMCS\Module\Server\VirtutelNbn\Webhook\CallbackAuthenticator;

/**
 * Self-manages the Virtutel callback URL registration.
 *
 * The module derives its public callback URL from the WHMCS SystemURL,
 * appends the shared-secret token, and registers it via POST
 * /callbacks/urls if not already present (idempotent — compares against
 * GET /callbacks/urls). Registration IDs (CRI...) are stored for
 * de-registration and test sends.
 */
class CallbackRegistrar
{
    private const SETTING_REGISTRATION_ID = 'callback_registration_id';
    private const SETTING_REGISTERED_URL = 'callback_registered_url';

    public function __construct(private readonly VirtutelClient $client)
    {
    }

    /** The URL we want registered (requires HTTPS SystemURL). */
    public function desiredUrl(): string
    {
        $systemUrl = rtrim($this->systemUrl(), '/');
        if (!str_starts_with($systemUrl, 'https://')) {
            throw new \RuntimeException(
                'WHMCS SystemURL must be https:// for Virtutel callbacks (got: ' . $systemUrl . ')'
            );
        }

        return $systemUrl . '/modules/servers/virtutel_nbn/callback/webhook.php?token='
            . CallbackAuthenticator::ensureToken();
    }

    /**
     * Ensure the callback URL is registered. Returns the registration ID.
     */
    public function ensureRegistered(): string
    {
        $desired = $this->desiredUrl();

        $existing = $this->client->request('GET', VirtutelClient::PATH_CALLBACK_URLS, [
            'action' => 'ListCallbackUrls',
        ]);

        foreach ((array) $existing->get('registeredCallbacks', []) as $registration) {
            if (($registration['url'] ?? '') === $desired) {
                $id = (string) ($registration['id'] ?? '');
                Settings::set(self::SETTING_REGISTRATION_ID, $id);
                Settings::set(self::SETTING_REGISTERED_URL, $desired);

                return $id;
            }
        }

        $created = $this->client->request('POST', VirtutelClient::PATH_CALLBACK_URLS, [
            'action' => 'RegisterCallbackUrl',
            'json' => ['url' => $desired],
        ]);

        $id = (string) $created->get('id', '');
        Settings::set(self::SETTING_REGISTRATION_ID, $id);
        Settings::set(self::SETTING_REGISTERED_URL, $desired);

        if (function_exists('logActivity')) {
            logActivity(sprintf(
                'Virtutel NBN: registered callback URL (%s environment), registration %s',
                $this->client->getEnvironment(),
                $id
            ));
        }

        return $id;
    }

    /**
     * Ask Virtutel to POST a test callback to the registered URL.
     * A received test event satisfies part of certification.
     */
    public function sendTest(): void
    {
        $id = Settings::get(self::SETTING_REGISTRATION_ID) ?: $this->ensureRegistered();

        $this->client->request(
            'POST',
            VirtutelClient::PATH_CALLBACK_TESTS . '/' . rawurlencode($id),
            ['action' => 'SendTestCallback']
        );
    }

    private function systemUrl(): string
    {
        $url = (string) (Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')->value('value') ?? '');
        if ($url === '') {
            throw new \RuntimeException('WHMCS SystemURL is not configured');
        }

        return $url;
    }
}
