#!/usr/bin/env bash
# Package the Virtutel suite into an installable ZIP:
#   dist/virtutel_nbn_v<version>.zip
# The ZIP is rooted at the WHMCS webroot (modules/, templates/, pages, ...)
# so it extracts straight over an install:
#   sudo bash scripts/updatewhmcsplugin.sh dist/virtutel_nbn_v<version>.zip
set -euo pipefail
cd "$(dirname "$0")"

VERSION=$(awk '{print $1; exit}' modules/servers/virtutel_nbn/VERSION)
OUT="dist/virtutel_nbn_v${VERSION}.zip"

# Everything that belongs on the server — repo tooling (scripts/, .github/,
# docs/, build.sh, README) deliberately excluded.
OVERLAY=(
    modules includes templates assets
    business personal residential residental
    terms privacy acceptable-use complaints financial-hardship
    critical-information contact
    404.php robots.txt sitemap.xml
)

mkdir -p dist
rm -f "$OUT"
zip -rq "$OUT" "${OVERLAY[@]}"

echo "Built ${OUT}"
unzip -l "$OUT" | tail -1
