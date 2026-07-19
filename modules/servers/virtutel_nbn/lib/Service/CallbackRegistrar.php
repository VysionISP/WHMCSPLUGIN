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
    public const SETTING_BASE_URL = 'callback_base_url';

    /**
     * @param string|null $baseUrlOverride callback base URL (e.g. the server
     *        record's Access Hash field, which is where admins configure it:
     *        https://backend.korvix.co). Falls back to the stored setting,
     *        then the WHMCS SystemURL.
     */
    public function __construct(
        private readonly VirtutelClient $client,
        private readonly ?string $baseUrlOverride = null,
    ) {
    }

    /** The URL we want registered (requires an HTTPS base). */
    public function desiredUrl(): string
    {
        $base = rtrim($this->resolveBaseUrl(), '/');
        if (!str_starts_with($base, 'https://')) {
            throw new \RuntimeException(
                'Callback base URL must be https:// (got: ' . $base . '). '
                . 'Set it in the server record\'s Access Hash field, e.g. https://backend.korvix.co'
            );
        }

        return $base . '/modules/servers/virtutel_nbn/callback/webhook.php?token='
            . CallbackAuthenticator::ensureToken();
    }

    /**
     * Resolution order: explicit override (Access Hash field) -> stored
     * setting -> WHMCS SystemURL. The chosen value is persisted so the
     * webhook host stays stable even if later lookups fail.
     */
    private function resolveBaseUrl(): string
    {
        $override = trim((string) $this->baseUrlOverride);
        if ($override !== '') {
            Settings::set(self::SETTING_BASE_URL, $override);

            return $override;
        }

        $stored = trim((string) Settings::get(self::SETTING_BASE_URL, ''));
        if ($stored !== '') {
            return $stored;
        }

        return rtrim($this->systemUrl(), '/');
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
