#!/usr/bin/env bash
#
# Virtutel sandbox endpoint discovery.
#
# Run this from a machine whose IP is registered with Virtutel's sandbox
# firewall (e.g. the WHMCS server). It confirms the API base URL and the
# exact endpoint paths that the Apiary docs export didn't include, using
# read-only requests (plus the token POST). No orders are created.
#
# Usage:
#   VT_HOST=<api-hostname> VT_CLIENT_ID=xxx VT_CLIENT_SECRET=yyy ./probe-endpoints.sh
#
# Then paste the full output back into the chat. The client_secret is not
# echoed; the access token is truncated in output.

set -u

VT_HOST="${VT_HOST:?Set VT_HOST to the Virtutel API hostname (from your onboarding email)}"
VT_CLIENT_ID="${VT_CLIENT_ID:?Set VT_CLIENT_ID}"
VT_CLIENT_SECRET="${VT_CLIENT_SECRET:?Set VT_CLIENT_SECRET}"
VT_PORT="${VT_PORT:-8443}" # sandbox default; use 443 for production

BASE="https://${VT_HOST}:${VT_PORT}"
echo "== Probing ${BASE} =="

# --- 1. Find the access-token endpoint ---------------------------------------
TOKEN=""
TOKEN_PATH=""
for path in /api/v1/access-tokens /api/v1/access_tokens /api/v1/tokens /api/v1/auth/tokens; do
    body=$(curl -sS --max-time 20 -X POST "${BASE}${path}" \
        -H 'Accept: application/json' -H 'Content-Type: application/json' \
        -d "{\"client_id\":\"${VT_CLIENT_ID}\",\"client_secret\":\"${VT_CLIENT_SECRET}\"}" 2>&1)
    code=$(curl -sS -o /dev/null -w '%{http_code}' --max-time 20 -X POST "${BASE}${path}" \
        -H 'Accept: application/json' -H 'Content-Type: application/json' \
        -d "{\"client_id\":\"${VT_CLIENT_ID}\",\"client_secret\":\"${VT_CLIENT_SECRET}\"}" 2>/dev/null)
    echo "POST ${path} -> HTTP ${code}"
    if [ "${code}" = "200" ]; then
        TOKEN_PATH="${path}"
        # Print the response with any token value truncated for safety.
        echo "${body}" | sed -E 's/("[A-Za-z_]*[Tt]oken[A-Za-z_]*"\s*:\s*")([^"]{12})[^"]*(")/\1\2...TRUNCATED\3/g'
        TOKEN=$(echo "${body}" | grep -oE '"(access_token|accessToken|token)"\s*:\s*"[^"]+"' \
            | head -1 | sed -E 's/.*:\s*"([^"]+)"/\1/')
        break
    fi
done

if [ -z "${TOKEN}" ]; then
    echo "!! No token obtained — paste the output above so the paths can be corrected."
    exit 1
fi
echo "== Token obtained via ${TOKEN_PATH} (using it for read-only probes) =="

# --- 2. Probe read-only endpoints --------------------------------------------
probe() {
    local path="$1"
    local code
    code=$(curl -sS -o /tmp/vt_probe_body -w '%{http_code}' --max-time 20 \
        -H "Authorization: Bearer ${TOKEN}" -H 'Accept: application/json' \
        "${BASE}${path}" 2>/dev/null)
    echo "GET ${path} -> HTTP ${code}"
    # 404 vs 200/4xx-with-vt-envelope distinguishes wrong path from right path.
    head -c 400 /tmp/vt_probe_body; echo; echo "---"
}

probe /api/v1/callbacks
probe /api/v1/services
probe "/api/v1/services?search=VT"
probe /api/v1/product-orders
probe /api/v1/outages
probe /api/v1/service-qualifications/LOC000000000001

rm -f /tmp/vt_probe_body
echo "== Done. Paste everything above back into the chat. =="
