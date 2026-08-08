#!/usr/bin/env bash
# updatewhmcsplugin.sh — install or update the VirtuTel NBN WHMCS module
# from a release ZIP, with backup and permission fix-up.
#
# Usage:
#   sudo bash updatewhmcsplugin.sh /path/to/virtutelnbn-<version>.zip [/path/to/whmcs]
#
# The WHMCS root can also be set via the WHMCS_ROOT environment variable.
# If neither is given, common install locations are scanned.
set -euo pipefail

MODULE_REL="modules/servers/virtutelnbn"

err() { echo "ERROR: $*" >&2; exit 1; }
log() { echo "==> $*"; }

ZIP="${1:-}"
[ -n "$ZIP" ] || err "usage: sudo bash $0 /path/to/virtutelnbn-<version>.zip [/path/to/whmcs]"
[ -f "$ZIP" ] || err "ZIP not found: $ZIP"
command -v unzip >/dev/null 2>&1 || err "unzip is not installed (apt install unzip / yum install unzip)"

# Sanity check: the ZIP must actually contain this module.
unzip -l "$ZIP" | grep -q "${MODULE_REL}/virtutelnbn.php" \
    || err "That ZIP does not look like a VirtuTel NBN module package (missing ${MODULE_REL}/virtutelnbn.php)"

# Locate the WHMCS root: argument > env var > scan of common locations.
WHMCS_ROOT="${2:-${WHMCS_ROOT:-}}"
if [ -z "$WHMCS_ROOT" ]; then
    for candidate in /var/www/whmcs /var/www/html/whmcs /var/www/html /usr/local/whmcs /home/*/public_html /home/*/www; do
        if [ -f "$candidate/configuration.php" ] && [ -f "$candidate/init.php" ]; then
            WHMCS_ROOT="$candidate"
            break
        fi
    done
fi
[ -n "$WHMCS_ROOT" ] || err "Could not find a WHMCS install. Pass it explicitly: sudo bash $0 $ZIP /path/to/whmcs"
[ -f "$WHMCS_ROOT/configuration.php" ] || err "$WHMCS_ROOT does not look like a WHMCS root (no configuration.php)"
log "WHMCS root: $WHMCS_ROOT"

MODULE_DIR="$WHMCS_ROOT/$MODULE_REL"

# Back up the currently installed module so a bad update can be rolled back:
#   tar -xzf <backup>.tar.gz -C "$WHMCS_ROOT"
if [ -d "$MODULE_DIR" ]; then
    BACKUP="/tmp/virtutelnbn-backup-$(date +%Y%m%d%H%M%S).tar.gz"
    tar -czf "$BACKUP" -C "$WHMCS_ROOT" "$MODULE_REL"
    log "Existing module backed up to $BACKUP"
fi

log "Extracting $ZIP"
unzip -oq "$ZIP" -d "$WHMCS_ROOT"

# Ownership matches the rest of the WHMCS install; standard perms.
OWNER=$(stat -c '%U:%G' "$WHMCS_ROOT/configuration.php")
chown -R "$OWNER" "$MODULE_DIR"
find "$MODULE_DIR" -type d -exec chmod 755 {} +
find "$MODULE_DIR" -type f -exec chmod 644 {} +

VERSION=$(php -r "require '$MODULE_DIR/lib/Version.php'; echo \Vysion\VirtutelNbn\Version::VERSION;" 2>/dev/null || echo unknown)
log "Done. VirtuTel NBN module v$VERSION installed at $MODULE_DIR (owner $OWNER)"
log "Database migrations run automatically on the next WHMCS page load / cron run."
