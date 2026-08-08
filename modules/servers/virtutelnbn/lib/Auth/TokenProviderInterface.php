<?php

namespace Vysion\VirtutelNbn\Auth;

interface TokenProviderInterface
{
    /**
     * Return a valid access token, obtaining/renewing one as needed.
     * $forceRefresh discards any cached token first (used after a 401).
     */
    public function token(bool $forceRefresh = false): string;
}
