<?php

namespace WHMCS\Module\Server\VirtutelNbn\Api;

/**
 * Virtutel Customer API client.
 *
 * Wraps HttpClient with bearer-token auth: tokens come from TokenStore,
 * are generated with the client_id/client_secret when missing or stale,
 * and an auth_error/401 on any request triggers exactly one
 * refresh-and-retry before failing.
 */
class VirtutelClient
{
    /**
     * Endpoint paths. The Apiary export only captured the overview page, so
     * paths not confirmed there are marked TBC and must be verified against
     * the sandbox before certification. Confirmed in the docs:
     * /api/v1/service-qualifications/{locId}.
     */
    public const PATH_ACCESS_TOKENS = '/api/v1/access-tokens'; // TBC
    public const PATH_SERVICE_QUALIFICATIONS = '/api/v1/service-qualifications';
    public const PATH_CALLBACKS = '/api/v1/callbacks'; // TBC
    public const PATH_SERVICES = '/api/v1/services'; // TBC
    public const PATH_PRODUCT_ORDERS = '/api/v1/product-orders'; // TBC

    public const SANDBOX_PORT = 8443;
    public const PRODUCTION_PORT = 443;

    private HttpClient $http;
    private TokenStore $tokens;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $environment,
        ?HttpClient $http = null,
        ?TokenStore $tokens = null,
    ) {
        $this->http = $http ?? new HttpClient($baseUrl, [$clientSecret]);
        $this->tokens = $tokens ?? new TokenStore($environment, TokenStore::identity($baseUrl, $clientId));
    }

    /**
     * Build a client from WHMCS module $params (server-backed module).
     *
     * Server fields: hostname = API host, port = 8443 (sandbox) or 443
     * (production), username = client_id, password = client_secret.
     */
    public static function fromModuleParams(array $params): self
    {
        $host = trim((string) (($params['serverhostname'] ?? '') ?: ($params['serverip'] ?? '')));
        if ($host === '') {
            throw new ApiException('No API hostname configured on the server record');
        }

        $port = (int) ($params['serverport'] ?? 0);
        if ($port === 0) {
            $port = self::PRODUCTION_PORT;
        }

        $clientId = trim((string) ($params['serverusername'] ?? ''));
        $clientSecret = (string) ($params['serverpassword'] ?? '');
        if ($clientId === '' || $clientSecret === '') {
            throw new ApiException('client_id (username) and client_secret (password) must be set on the server record');
        }

        $environment = $port === self::SANDBOX_PORT ? 'sandbox' : 'production';
        $baseUrl = 'https://' . $host . ':' . $port;

        return new self($baseUrl, $clientId, $clientSecret, $environment);
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Ensure a valid cached access token exists, returning it.
     * Set $forceNew to bypass the cache (used by TestConnection's auth check
     * only when no token exists yet — normal traffic must reuse tokens).
     */
    public function ensureAccessToken(bool $forceNew = false): string
    {
        return $this->tokens->getValidToken(fn () => $this->generateAccessToken(), $forceNew);
    }

    /** True when the cached token is past its proactive-refresh point. */
    public function tokenNeedsRefresh(): bool
    {
        return $this->tokens->needsRefresh();
    }

    /**
     * Authenticated request with single refresh-and-retry on auth failure.
     */
    public function request(string $method, string $path, array $options = []): ApiResponse
    {
        $options['bearer'] = $this->ensureAccessToken();

        try {
            return $this->http->request($method, $path, $options);
        } catch (ApiException $e) {
            if (!$e->isAuthError()) {
                throw $e;
            }
            $this->tokens->invalidate();
            $options['bearer'] = $this->ensureAccessToken();

            return $this->http->request($method, $path, $options);
        }
    }

    /**
     * POST credentials to the Access Tokens endpoint.
     *
     * @return array{token:string,expires_at:int}
     */
    private function generateAccessToken(): array
    {
        $response = $this->http->request('POST', self::PATH_ACCESS_TOKENS, [
            'action' => 'GenerateAccessToken',
            'json' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        $token = $this->extractToken($response);
        if ($token === '') {
            throw new ApiException('Access token endpoint returned no recognisable token field');
        }
        $this->http->addMaskValue($token);

        return [
            'token' => $token,
            'expires_at' => $this->extractExpiry($response),
        ];
    }

    private function extractToken(ApiResponse $response): string
    {
        foreach (['access_token', 'accessToken', 'token'] as $key) {
            $value = $response->get($key) ?? $response->get('responseData.' . $key);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function extractExpiry(ApiResponse $response): int
    {
        foreach (['expires_in', 'expiresIn'] as $key) {
            $value = $response->get($key) ?? $response->get('responseData.' . $key);
            if (is_numeric($value) && (int) $value > 0) {
                return time() + (int) $value;
            }
        }
        foreach (['expires_at', 'expiresAt'] as $key) {
            $value = $response->get($key) ?? $response->get('responseData.' . $key);
            if (is_string($value) && ($ts = strtotime($value)) !== false && $ts > time()) {
                return $ts;
            }
        }

        return time() + TokenStore::DEFAULT_LIFETIME_SECONDS;
    }
}
