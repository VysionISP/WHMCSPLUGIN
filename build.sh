#!/usr/bin/env bash
# Package the module into an installable ZIP: dist/virtutelnbn-<version>.zip
# The ZIP is rooted at modules/, so it extracts straight into a WHMCS root:
#   unzip -o virtutelnbn-<version>.zip -d /path/to/whmcs
set -euo pipefail
cd "$(dirname "$0")"

VERSION=$(php -r "require 'modules/servers/virtutelnbn/lib/Version.php'; echo \Vysion\VirtutelNbn\Version::VERSION;")
OUT="dist/virtutelnbn-${VERSION}.zip"

mkdir -p dist
rm -f "$OUT"

zip -r "$OUT" modules/servers/virtutelnbn \
    -x 'modules/servers/virtutelnbn/vendor/*' \
    -x 'modules/servers/virtutelnbn/tests/*' \
    -x 'modules/servers/virtutelnbn/phpunit.xml' \
    -x 'modules/servers/virtutelnbn/composer.lock' \
    -x 'modules/servers/virtutelnbn/.phpunit.result.cache'

echo "Built ${OUT}"
