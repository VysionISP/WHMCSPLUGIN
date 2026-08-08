<?php

namespace Vysion\VirtutelNbn\Auth;

/** Fixed token, used in tests (and available should VirtuTel ever issue non-expiring keys). */
final class StaticTokenProvider implements TokenProviderInterface
{
    public function __construct(private readonly string $token)
    {
    }

    public function token(bool $forceRefresh = false): string
    {
        return $this->token;
    }
}
