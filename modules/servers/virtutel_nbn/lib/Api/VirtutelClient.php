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
     * Endpoint paths, confirmed against the full Apiary export
     * (docs/api/). All are relative to the /api/v1 base, which is part of
     * the base URL. Production base: https://mars.as24516.net/api/v1/
     */
    public const PATH_ACCESS_TOKENS = '/oauth/tokens';
    public const PATH_LOCATIONS = '/locations';
    public const PATH_SERVICE_QUALIFICATIONS = '/service-qualifications';
    public const PATH_CALLBACK_URLS = '/callbacks/urls';
    public const PATH_CALLBACK_TESTS = '/callbacks/tests';
    public const PATH_PRODUCT_ORDER_QUALIFICATIONS = '/product-order-qualifications';
    public const PATH_PRODUCT_ORDERS = '/product-orders';
    public const PATH_APPOINTMENTS = '/appointments';
    public const PATH_APPOINTMENT_TIMESLOTS = '/appointments/timeslots';
    public const PATH_SERVICES = '/services';
    public const PATH_SUSPENSIONS = '/suspensions';

    public const DEFAULT_HOST = 'mars.as24516.net';
    public const API_BASE_PATH = '/api/v1';
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
            $host = self::DEFAULT_HOST;
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
        $baseUrl = 'https://' . $host . ':' . $port . self::API_BASE_PATH;

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
        try {
            $response = $this->http->request('POST', self::PATH_ACCESS_TOKENS, [
                'action' => 'GenerateAccessToken',
                'json' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'audience' => self::DEFAULT_HOST,
                    'grant_type' => 'client_credentials',
                ],
            ]);
        } catch (ApiException $e) {
            if ($e->isAuthError()) {
                // Surface what was actually sent (never the secret itself) so
                // truncated/mispasted credentials are diagnosable from the UI.
                throw new ApiException(
                    sprintf(
                        '%s [sent client_id "%s", client_secret length %d]',
                        $e->getMessage(),
                        $this->clientId,
                        strlen($this->clientSecret)
                    ),
                    $e->getShortError(),
                    $e->getHttpStatus(),
                    $e
                );
            }
            throw $e;
        }

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
