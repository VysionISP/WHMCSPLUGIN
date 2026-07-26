#!/usr/bin/env bash
#
# Virtutel NBN plugin updater — run ON the WHMCS server.
#   Usage: sudo bash update-whmcs-plugin.sh /path/to/virtutel_nbn_vX.Y.Z.zip
#
# Extracts over the docroot, fixes ownership/permissions, restarts the
# WHMCS container (busts PHP opcache), then prints the deployed version.
set -euo pipefail

ZIP="${1:?Usage: sudo bash $0 /path/to/virtutel_nbn_vX.Y.Z.zip}"
# Bare-metal install default; override for other layouts, e.g.
#   DOCROOT=/home/korvix/whmcs/whmcs bash $0 ... (old docker bind mount)
DOCROOT="${DOCROOT:-/var/www/whmcs}"
[ -d "$DOCROOT/modules" ] || DOCROOT="/home/korvix/whmcs/whmcs"
CONTAINER="${CONTAINER:-whmcs}"
# Default owner = whoever owns the WHMCS install itself.
OWNER="${OWNER:-$(stat -c '%U' "$DOCROOT/configuration.php" 2>/dev/null || echo www-data)}"

[ -f "$ZIP" ] || { echo "Zip not found: $ZIP"; exit 1; }
[ -d "$DOCROOT/modules" ] || { echo "Docroot not found: $DOCROOT"; exit 1; }

echo "==> Extracting $(basename "$ZIP") to $DOCROOT"
unzip -oq "$ZIP" -d "$DOCROOT"

echo "==> Fixing ownership and permissions"
PATHS=(
  "$DOCROOT/modules/servers/virtutel_nbn"
  "$DOCROOT/modules/servers/virtutel_phone"
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

# Bust PHP opcache: docker restart when the whmcs container exists,
# otherwise reload the host PHP-FPM / Apache.
if command -v docker >/dev/null 2>&1 && docker ps --format '{{.Names}}' 2>/dev/null | grep -qx "$CONTAINER"; then
  echo "==> Restarting container '$CONTAINER' (clears PHP opcache)"
  docker restart "$CONTAINER" >/dev/null
else
  echo "==> Reloading PHP-FPM/Apache (clears PHP opcache)"
  restarted=0
  for svc in php8.2-fpm php8.1-fpm php-fpm; do
    if systemctl is-active --quiet "$svc" 2>/dev/null; then
      systemctl reload "$svc" && restarted=1
    fi
  done
  systemctl is-active --quiet apache2 2>/dev/null && systemctl reload apache2 || true
  systemctl is-active --quiet nginx 2>/dev/null && systemctl reload nginx || true
  [ "$restarted" = 1 ] || echo "    (no php-fpm service found — if using mod_php, the apache reload covered it)"
fi

echo "==> Deployed plugin version: $(cat "$DOCROOT/modules/servers/virtutel_nbn/VERSION" 2>/dev/null || echo 'UNKNOWN — VERSION file missing, old build?')"
echo "==> Done. Hard-refresh your browser (Cmd/Ctrl+Shift+R)."
