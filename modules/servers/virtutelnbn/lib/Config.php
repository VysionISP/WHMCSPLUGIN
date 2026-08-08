<?php

namespace Vysion\VirtutelNbn;

use Vysion\VirtutelNbn\Exception\ConfigurationException;

/**
 * Typed view over the WHMCS $params array passed to every module function.
 *
 * Credential mapping (WHMCS server record for the "VirtuTel NBN" server):
 *   - Hostname     -> API base URL (https://...)
 *   - Username     -> VirtuTel Client ID
 *   - Password     -> VirtuTel Client Secret (stored encrypted by WHMCS);
 *                     exchanged for a ~28-day access token by TokenManager
 *   - Access Hash  -> Webhook shared secret (HMAC key for inbound webhooks)
 *
 * Product-level module settings (ConfigOptions):
 *   - configoption1 -> Plan code (VirtuTel speed tier / product code)
 *   - configoption2 -> Test mode (yes/no)
 */
final class Config
{
    public function __construct(private readonly array $params)
    {
    }

    public function apiBaseUrl(): string
    {
        $host = trim((string) ($this->params['serverhostname'] ?? ''));
        if ($host === '') {
            throw new ConfigurationException('VirtuTel API base URL is not set on the server record (Hostname field).');
        }
        if (!preg_match('#^https?://#i', $host)) {
            $host = 'https://' . $host;
        }
        if (stripos($host, 'https://') !== 0) {
            throw new ConfigurationException('VirtuTel API base URL must use HTTPS.');
        }

        return rtrim($host, '/');
    }

    public function clientId(): string
    {
        $id = trim((string) ($this->params['serverusername'] ?? ''));
        if ($id === '') {
            throw new ConfigurationException('VirtuTel Client ID is not set on the server record (Username field).');
        }

        return $id;
    }

    public function clientSecret(): string
    {
        $secret = (string) ($this->params['serverpassword'] ?? '');
        if ($secret === '') {
            throw new ConfigurationException('VirtuTel Client Secret is not set on the server record (Password field).');
        }

        return $secret;
    }

    public function webhookSecret(): string
    {
        return (string) ($this->params['serveraccesshash'] ?? '');
    }

    public function planCode(): string
    {
        return trim((string) ($this->params['configoption1'] ?? ''));
    }

    public function testMode(): bool
    {
        return ($this->params['configoption2'] ?? '') === 'on';
    }

    public function whmcsServiceId(): int
    {
        return (int) ($this->params['serviceid'] ?? 0);
    }

    /**
     * Custom field lookup by (case-insensitive) name, e.g. "NBN Location ID".
     */
    public function customField(string $name): ?string
    {
        foreach (($this->params['customfields'] ?? []) as $key => $value) {
            if (strcasecmp(trim((string) $key), $name) === 0 && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    public function serviceAddress(): ?string
    {
        return $this->customField('Service Address');
    }

    public function nbnLocationId(): ?string
    {
        return $this->customField('NBN Location ID');
    }

    public function raw(): array
    {
        return $this->params;
    }
}
