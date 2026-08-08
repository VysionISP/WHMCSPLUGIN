# Architecture — Virtutel NBN Suite

Canonical source imported from the production v1.40.x line. This doc is a
map, not a spec — the code is the source of truth.

## Core provisioning module (`modules/servers/virtutel_nbn/`)

- **`lib/Api/`** — `VirtutelClient` (endpoint catalogue for
  `https://<host>:<port>/api/v1`: `/oauth/tokens`, `/locations`,
  `/service-qualifications`, `/product-order-qualifications`,
  `/product-orders`, `/appointments{,/timeslots}`, `/services`,
  `/suspensions`, `/callbacks/{urls,tests}`), `HttpClient` (masked logging),
  `TokenStore` (cached ~30-day bearer tokens per environment, proactive
  refresh, single refresh-and-retry on auth errors), `ApiResponse`,
  `ApiException`.
- **`lib/Service/`** — provisioning orchestration (`ProvisioningService`,
  `LifecycleService`, `OrderPayloadBuilder`, `OrderCompletion`,
  `ConnectReadiness`), qualification (`QualificationService`), appointments
  (`AppointmentService`, booking page), signup capture, speed tiers,
  `CustomFields` (auto-creates admin-only service fields: Location ID,
  Churn AVC, Authority Date, NTD ID, UNI-D Port, Copper Pair ID),
  notifications (`EmailNotifier`, `Sms`), `CallbackRegistrar` (self-registers
  the webhook URL from the server record's Access Hash field),
  `RateLimiter`, `Diagnostics`, `StatusMapper`.
- **`lib/Webhook/`** — `callback/webhook.php` endpoint →
  `CallbackAuthenticator` / `CallbackEnvelope` / `CallbackDispatcher` →
  `Handlers/` (OrderStatus, ServiceStatus, ServiceHealth, ServiceTest,
  Appointment).
- **`lib/Radius/`** — FreeRADIUS SQL provisioning + CoA client so active
  services get session credentials; product config option `radius_group`.
- **`lib/Migrations.php`** — creates/evolves `mod_virtutel_settings`,
  `mod_virtutel_leads`, `mod_virtutel_ratelimit`, `mod_virtutel_tokens`,
  `mod_virtutel_services`, `mod_virtutel_orders`,
  `mod_virtutel_appointments`, `mod_virtutel_callback_events`.
- **`hooks.php`** — cron (Daily + AfterCron: token keep-warm, queues),
  checkout capture/validation, extensive client-area UI hooks;
  `includes/hooks/virtutel_nbn.php` guarantees loading.
- **`pages/`** — public signup portal (qualify/order/onboard + JSON APIs,
  service test) rendered through `site-shell.php`.

## Companions

- `modules/servers/virtutel_phone/` — phone service module.
- `modules/addons/virtutel_nbn_admin/` — admin addon.
- `templates/korvix-dark/` — WHMCS theme + invoice PDF template.
- Webroot pages (`business/`, `personal/`, legal pages, robots/sitemap).

## Release engineering (this repo)

- `build.sh` → `dist/virtutel_nbn_v<version>.zip`, version read from
  `modules/servers/virtutel_nbn/VERSION`, ZIP rooted at the WHMCS webroot.
- `scripts/updatewhmcsplugin.sh` → validated install/update with full
  overlay backup to /tmp and ownership/permission normalisation.
- CI (`.github/workflows/ci.yml`): php -l on 8.1–8.3 + package build.
- Releases (`.github/workflows/release.yml`): tag `v*` must match VERSION;
  builds and attaches the ZIP to a GitHub Release.
