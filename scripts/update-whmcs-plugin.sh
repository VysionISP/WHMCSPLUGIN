#!/usr/bin/env bash
#
# Virtutel NBN plugin updater — run ON the WHMCS server.
#   Usage: sudo bash update-whmcs-plugin.sh /path/to/virtutel_nbn_vX.Y.Z.zip
#
# Extracts over the docroot, fixes ownership/permissions, restarts the
# WHMCS container (busts PHP opcache), then prints the deployed version.
set -euo pipefail

ZIP="${1:?Usage: sudo bash $0 /path/to/virtutel_nbn_vX.Y.Z.zip}"
DOCROOT="${DOCROOT:-/home/korvix/whmcs/whmcs}"
CONTAINER="${CONTAINER:-whmcs}"
OWNER="${OWNER:-korvix}"

[ -f "$ZIP" ] || { echo "Zip not found: $ZIP"; exit 1; }
[ -d "$DOCROOT/modules" ] || { echo "Docroot not found: $DOCROOT"; exit 1; }

echo "==> Extracting $(basename "$ZIP") to $DOCROOT"
unzip -oq "$ZIP" -d "$DOCROOT"

echo "==> Fixing ownership and permissions"
PATHS=(
  "$DOCROOT/modules/servers/virtutel_nbn"
  "$DOCROOT/modules/addons/virtutel_nbn_admin"
  "$DOCROOT/templates/korvix-dark"
  # marketing site + legal page stubs (top-level docroot dirs)
  "$DOCROOT/residental"
  "$DOCROOT/residential"
  "$DOCROOT/personal"
  "$DOCROOT/business"
  "$DOCROOT/terms"
  "$DOCROOT/privacy"
  "$DOCROOT/acceptable-use"
  "$DOCROOT/complaints"
  "$DOCROOT/financial-hardship"
  "$DOCROOT/critical-information"
)
for p in "${PATHS[@]}"; do
  [ -d "$p" ] || continue
  chown -R "$OWNER:$OWNER" "$p"
  find "$p" -type d -exec chmod 755 {} +
  find "$p" -type f -exec chmod 644 {} +
done
if [ -f "$DOCROOT/includes/hooks/virtutel_nbn.php" ]; then
  chown "$OWNER:$OWNER" "$DOCROOT/includes/hooks/virtutel_nbn.php"
  chmod 644 "$DOCROOT/includes/hooks/virtutel_nbn.php"
fi

echo "==> Restarting container '$CONTAINER' (clears PHP opcache)"
docker restart "$CONTAINER" >/dev/null

echo "==> Deployed plugin version: $(cat "$DOCROOT/modules/servers/virtutel_nbn/VERSION" 2>/dev/null || echo 'UNKNOWN — VERSION file missing, old build?')"
echo "==> Done. Hard-refresh your browser (Cmd/Ctrl+Shift+R)."
