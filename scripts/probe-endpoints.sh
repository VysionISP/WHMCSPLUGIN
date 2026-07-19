#!/usr/bin/env bash
#
# Virtutel sandbox connectivity check.
#
# Endpoint paths are now confirmed from the full Apiary export (docs/api/) —
# this script verifies credentials, firewall access, and live behaviour from
# a machine whose IP is registered with Virtutel's sandbox firewall (e.g. the
# WHMCS server). Read-only requests only (plus the token POST); no orders are
# created.
#
# Usage:
#   VT_CLIENT_ID=xxx VT_CLIENT_SECRET=yyy ./probe-endpoints.sh
#
# The client_secret is not echoed; the access token is truncated in output.

set -u

VT_HOST="${VT_HOST:-mars.as24516.net}"
VT_CLIENT_ID="${VT_CLIENT_ID:?Set VT_CLIENT_ID}"
VT_CLIENT_SECRET="${VT_CLIENT_SECRET:?Set VT_CLIENT_SECRET}"
VT_PORT="${VT_PORT:-8443}" # sandbox default; use 443 for production

BASE="https://${VT_HOST}:${VT_PORT}/api/v1"
echo "== Probing ${BASE} =="

# --- 1. Generate an access token ---------------------------------------------
body=$(curl -sS --max-time 20 -X POST "${BASE}/oauth/tokens" \
    -H 'Accept: application/json' -H 'Content-Type: application/json' \
    -d "{\"client_id\":\"${VT_CLIENT_ID}\",\"client_secret\":\"${VT_CLIENT_SECRET}\",\"audience\":\"mars.as24516.net\",\"grant_type\":\"client_credentials\"}" 2>&1)

echo "POST /oauth/tokens ->"
echo "${body}" | sed -E 's/("[A-Za-z_]*[Tt]oken[A-Za-z_]*"\s*:\s*")([^"]{12})[^"]*(")/\1\2...TRUNCATED\3/g'

TOKEN=$(echo "${body}" | grep -oE '"access_token"\s*:\s*"[^"]+"' \
    | head -1 | sed -E 's/.*:\s*"([^"]+)"/\1/')

if [ -z "${TOKEN}" ]; then
    echo "!! No token obtained — check credentials/firewall and paste the output above into the chat."
    exit 1
fi
echo "== Token obtained; running read-only probes =="

# --- 2. Probe read-only endpoints --------------------------------------------
probe() {
    local path="$1"
    local code
    code=$(curl -sS -o /tmp/vt_probe_body -w '%{http_code}' --max-time 20 \
        -H "Authorization: Bearer ${TOKEN}" -H 'Accept: application/json' \
        "${BASE}${path}" 2>/dev/null)
    echo "GET ${path} -> HTTP ${code}"
    head -c 400 /tmp/vt_probe_body; echo; echo "---"
}

probe /callbacks/urls
probe "/services?page=1&limit=5"
probe "/product-orders?page=1&limit=5"
probe "/outages?page=1&limit=5"

rm -f /tmp/vt_probe_body
echo "== Done. Paste everything above back into the chat. =="
