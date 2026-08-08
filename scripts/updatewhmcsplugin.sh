#!/usr/bin/env bash
# updatewhmcsplugin.sh — install or update the Virtutel NBN suite
# from a release ZIP, with backup and permission fix-up.
#
# Usage:
#   sudo bash updatewhmcsplugin.sh /path/to/virtutel_nbn_v<version>.zip [/path/to/whmcs]
#
# The WHMCS root can also be set via the WHMCS_ROOT environment variable.
# If neither is given, common install locations are scanned.
set -euo pipefail

MODULE_REL="modules/servers/virtutel_nbn"

err() { echo "ERROR: $*" >&2; exit 1; }
log() { echo "==> $*"; }

ZIP="${1:-}"
[ -n "$ZIP" ] || err "usage: sudo bash $0 /path/to/virtutel_nbn_v<version>.zip [/path/to/whmcs]"
[ -f "$ZIP" ] || err "ZIP not found: $ZIP"
command -v unzip >/dev/null 2>&1 || err "unzip is not installed (apt install unzip / yum install unzip)"

# Sanity check: the ZIP must actually contain the module. (grep without -q
# reads the whole stream — with pipefail, -q would SIGPIPE unzip on large
# listings and falsely fail the pipeline.)
unzip -Z1 "$ZIP" | grep -F "${MODULE_REL}/virtutel_nbn.php" >/dev/null \
    || err "That ZIP does not look like a Virtutel NBN package (missing ${MODULE_REL}/virtutel_nbn.php)"

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

# Back up everything the overlay will touch (module, addon, hooks, theme,
# site pages) so a bad update can be rolled back:
#   tar -xzf <backup>.tar.gz -C "$WHMCS_ROOT"
EXISTING=()
while IFS= read -r entry; do
    [ -e "$WHMCS_ROOT/$entry" ] && EXISTING+=("$entry")
done < <(unzip -Z1 "$ZIP" | awk -F/ 'NF && $1 != "" {print $1}' | sort -u)
if [ "${#EXISTING[@]}" -gt 0 ]; then
    BACKUP="/tmp/virtutel_nbn-backup-$(date +%Y%m%d%H%M%S).tar.gz"
    tar -czf "$BACKUP" -C "$WHMCS_ROOT" "${EXISTING[@]}"
    log "Existing files backed up to $BACKUP"
fi

log "Extracting $ZIP"
unzip -oq "$ZIP" -d "$WHMCS_ROOT"

# Ownership matches the rest of the WHMCS install; standard perms.
OWNER=$(stat -c '%U:%G' "$WHMCS_ROOT/configuration.php")
while IFS= read -r entry; do
    [ -e "$WHMCS_ROOT/$entry" ] || continue
    chown -R "$OWNER" "$WHMCS_ROOT/$entry"
    find "$WHMCS_ROOT/$entry" -type d -exec chmod 755 {} +
    find "$WHMCS_ROOT/$entry" -type f -exec chmod 644 {} +
done < <(unzip -Z1 "$ZIP" | awk -F/ 'NF && $1 != "" {print $1}' | sort -u)

VERSION=$(awk '{print $1; exit}' "$MODULE_DIR/VERSION" 2>/dev/null || echo unknown)
log "Done. Virtutel NBN suite v$VERSION installed at $WHMCS_ROOT (owner $OWNER)"
log "Database migrations run automatically on the next WHMCS page load / cron run."
