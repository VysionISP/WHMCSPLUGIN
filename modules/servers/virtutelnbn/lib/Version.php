<?php

namespace Vysion\VirtutelNbn;

final class Version
{
    /**
     * Module version. Bump this together with the git tag (v0.2.0) when
     * cutting a release — the release workflow packages the ZIP and the
     * in-admin update checker compares GitHub's latest tag against this.
     */
    public const VERSION = '0.2.0';

    public const GITHUB_REPO = 'VysionISP/WHMCSPLUGIN';

    public const RELEASES_URL = 'https://github.com/' . self::GITHUB_REPO . '/releases';
}
