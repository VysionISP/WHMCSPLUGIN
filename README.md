# Virtutel NBN Suite for WHMCS (Korvix)

Production WHMCS integration for provisioning NBN services through the
**Virtutel wholesale API**, plus the surrounding Korvix retail stack. This repo
is the canonical source (adopted from the v1.40.x line); releases are built and
published from here.

## What's in the package

| Path | Purpose |
|---|---|
| `modules/servers/virtutel_nbn/` | The core provisioning module: Virtutel API client (token auth, cached ~30-day tokens), qualification, product orders, appointments, webhooks/callbacks, RADIUS (FreeRADIUS SQL + CoA) provisioning, SMS + email notifications, diagnostics |
| `modules/servers/virtutel_phone/` | Phone service module |
| `modules/addons/virtutel_nbn_admin/` | Admin addon (dashboard/tools) |
| `includes/hooks/virtutel_nbn.php` | Unconditional hook loader |
| `modules/servers/virtutel_nbn/pages/` | Public signup portal (qualify, order, onboard, service test APIs) |
| `templates/korvix-dark/` | Korvix dark WHMCS theme + invoice PDF |
| `business/`, `personal/`, `residential/`, legal pages, `404.php`, `robots.txt`, `sitemap.xml` | Public site pages served from the WHMCS webroot |

Database: `mod_virtutel_*` tables (settings, leads, tokens, services, orders,
appointments, callback_events, ratelimit) are created/updated automatically by
`lib/Migrations.php` — updates never need manual SQL.

## WHMCS configuration (server record)

*System Settings → Products/Services → Servers*:

| Field | Value |
|---|---|
| **Hostname** | Virtutel API host — `mars.as24516.net` (default if blank) |
| **Port** | `443` = production, `8443` = sandbox |
| **Username** | Virtutel **client_id** |
| **Password** | Virtutel **client_secret** (WHMCS-encrypted) |
| **Access Hash** | **Callback base URL** — the public HTTPS base Virtutel should send webhooks to, e.g. `https://backend.korvix.co` |

Access tokens are generated via `/oauth/tokens`, cached in
`mod_virtutel_tokens`, kept warm by cron, and refreshed-and-retried once on any
auth failure. **Test Connection** verifies auth *and* self-registers the
callback URL with Virtutel.

Product **Module Settings**: module `Virtutel NBN`, plus
**Speed Tier** (e.g. `100/20`, blank when using configurable options) and
**RADIUS Group** (`radusergroup` applied to the product's services).

Service custom fields (**Location ID, Churn AVC, Authority Date, NTD ID,
UNI-D Port, Copper Pair ID**) are created automatically on the product
(admin-only) — do not create them by hand.

## Install / update on the server

```bash
sudo bash scripts/updatewhmcsplugin.sh /path/to/virtutel_nbn_v<version>.zip
```

The script validates the package, finds the WHMCS root (pass it as a second
argument to override), backs up everything the overlay touches to
`/tmp/virtutel_nbn-backup-<timestamp>.tar.gz`, extracts, and fixes
ownership/permissions. Rollback = extract the backup tar over the WHMCS root.

## Cutting a release

1. Update `modules/servers/virtutel_nbn/VERSION` (first token is the version).
2. Commit, then tag and push: `git tag v1.41.0 && git push origin v1.41.0`.
3. GitHub Actions lints, verifies the tag matches VERSION, builds
   `virtutel_nbn_v<version>.zip` and attaches it to a GitHub Release.

`./build.sh` produces the same ZIP locally in `dist/`.
