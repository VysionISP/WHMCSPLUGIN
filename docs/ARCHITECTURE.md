# Virtutel NBN Provisioning Module for WHMCS — Architecture & Plan

**Status:** Draft v2 — updated against the Virtutel Customer API documentation
(Apiary export, July 2026). See `docs/virtutel-api-notes.txt` for the extracted
doc text. No implementation yet.

## Overview

A WHMCS provisioning (server) module, written in PHP, that automates the NBN
service lifecycle through the **Virtutel wholesale API** and a second external
API. NBN provisioning is asynchronous (orders progress through carrier states
over hours or days), so the module both **sends outbound API requests** and
**receives inbound webhooks** on a secured public endpoint to track order and
service state.

Core responsibilities:

- Service qualification (SQ) by address / NBN LOC ID before ordering.
- Lodge connect / modify / cease orders via the Virtutel API from WHMCS
  lifecycle events (`CreateAccount`, `TerminateAccount`, config changes).
- Receive signed webhooks for order-status, appointment, and service events;
  update WHMCS service status accordingly.
- Suspend / unsuspend on billing events.
- Admin and client-area views of the real carrier state (AVC ID, technology
  type, speed tier, order history, appointments).

## Virtutel API facts (from the official docs)

These are confirmed behaviours of the Virtutel Customer API that the design
below is built around:

- **Authentication:** POST `client_id` + `client_secret` to the Access Tokens
  endpoint to obtain a **Bearer token with a ~30-day lifetime**. Tokens must be
  **cached and reused** in production, sent as
  `Authorization: Bearer <token>` with `Accept`/`Content-Type:
  application/json`.
- **Environments:** Sandbox runs on port **8443**, Production on **443**.
  Credentials, tokens, test data, and firewalls are completely separate per
  environment. Both sides are firewalled — our server IPs must be registered
  with Virtutel.
- **Response envelope:** every response includes `vt_success` (bool),
  `vt_short_error` (string), `vt_error_desc` (string) at the root.
- **Scopes:** tokens carry scopes such as `read:service-qualifications`,
  `create:product-orders`, `read:product-orders`, `update:product-orders`,
  `create:appointments`, `read:services`, `create:callbacks`,
  `all:suspensions`, etc. Certification grants production scopes per endpoint.
- **Callbacks (webhooks):** JSON POSTs to up to five registered **HTTPS** URLs
  (valid CA-signed cert required; sender is `mars.as24516.net`). **No HMAC
  signing is provided** — a secret token may be embedded as a query parameter
  in the registered URL. Non-2xx responses are retried several times, then the
  callback is marked failed and never re-sent. A "send test callback" endpoint
  exists.
- **Callback envelope:** `eventUuid` (unique), `eventTime` (ISO8601),
  `eventType`, and `event {id, notificationType, reason, ...}`. Documented
  event families: product orders (e.g. `OrderAccepted`, `AppointmentRequired`,
  `RSPActionRequired`, `OrderCompleted`, and terminal `VTOrderCompleted` /
  `VTOrderCancelled`), appointments (`AppointmentBooked`,
  `AppointmentCompleted`, ...), services (`ProductInstanceUpdated`,
  `ProductInstanceDisconnected`), service health, service tests, and outages.
- **Order lifecycle:** Connect / Modify Speed / Disconnect orders are
  asynchronous with a rich state machine driven by callbacks. Orders can
  require action mid-flight: appointments, install-fee or New Development
  Charge confirmation (via PATCH), fibre-upgrade liability confirmation,
  RSP actions ("resume order" PATCH after end-user plugs in equipment).
- **Qualification:** address search (unstructured, structured, G-NAF,
  coordinates, reverse LOC ID) resolves an **NBN Location ID**; Service
  Qualification per LOC ID returns technology, **service class** (0–34),
  available speed tiers, and site restrictions. Churn orders require the
  existing service's **AVC ID** plus a customer authority date, validated via
  Enhanced SQ (`serviceIDMatch`).
- **Suspension:** dedicated Suspend/Resume endpoints exist but are **Beta and
  Layer 3 services only** — WHMCS suspend/unsuspend must degrade gracefully
  (e.g. flag for manual action) for Layer 2 services.
- **Certification:** production access requires demonstrating every endpoint
  we intend to use in sandbox, including registering a callback URL and
  receiving at least one callback. **Product Orders and Appointments are
  certified together** (an appointment-required order, e.g. FTTP SC2, must be
  completed). This ordering constraint shapes the build steps below.
- **Rate limits:** some endpoints publish steady/burst limits — the HTTP
  client must honour HTTP 429-style backoff.

## Security requirements

- All outbound calls TLS-only with certificate verification; no plaintext HTTP.
- `client_id` / `client_secret` stored via WHMCS password-type config fields
  (encrypted at rest by WHMCS). Never logged. Access tokens cached server-side
  (30-day lifetime), refreshed proactively before expiry, never exposed to
  templates or logs.
- `logModuleCall` used for all API traffic with an explicit mask list
  (`client_secret`, `access_token`, `Authorization`, callback URL token).
- Inbound callbacks — Virtutel does not sign payloads, so defence layers are:
  1. HTTPS-only endpoint with a valid CA-signed certificate (Virtutel
     requirement — self-signed certs are rejected at registration).
  2. A long random **shared-secret query token** embedded in the registered
     callback URL (supported by Virtutel), compared with `hash_equals()`
     before the payload is parsed. Rotatable from module config.
  3. Optional source allowlist (callbacks originate from `mars.as24516.net`).
  4. Strict schema validation of the payload; unknown `eventType`s are stored
     but not acted on.
  5. Replay/duplicate protection via unique `eventUuid` (DB unique key) and
     an `eventTime` staleness check.
- Callback endpoint responds 2xx fast (record first, process after) and never
  leaks internal errors in the response body — Virtutel retries on non-2xx and
  permanently drops the event after several failures, so a cron
  reconciliation poll backstops missed events.

## Folder and file structure

Repo mirrors a WHMCS installation root:

```
modules/
└── servers/
    └── virtutel_nbn/
        ├── virtutel_nbn.php          # WHMCS entrypoint: MetaData, ConfigOptions,
        │                             #   CreateAccount, Suspend/Unsuspend, Terminate,
        │                             #   TestConnection, ClientArea, AdminServicesTab
        ├── hooks.php                 # WHMCS hooks (daily status-sync cron fallback)
        ├── whmcs.json                # module metadata
        ├── callback/
        │   └── webhook.php           # public HTTPS callback endpoint (thin:
        │                             #   authenticate token, record, 200 fast,
        │                             #   then dispatch)
        ├── lib/
        │   ├── Api/
        │   │   ├── HttpClient.php            # cURL/Guzzle wrapper: TLS, timeouts,
        │   │   │                             #   retry w/ backoff + rate-limit
        │   │   │                             #   handling, vt_success envelope
        │   │   │                             #   parsing, masked logging
        │   │   ├── TokenStore.php            # cache/reuse 30-day bearer tokens,
        │   │   │                             #   proactive refresh, per-environment
        │   │   ├── VirtutelClient.php        # access tokens, address search, SQ,
        │   │   │                             #   product orders, appointments,
        │   │   │                             #   services, callbacks registration,
        │   │   │                             #   suspensions (beta)
        │   │   └── RadiusProvisioner.php     # AAA provisioning behind an
        │   │                                 #   interface: AVC-ID-keyed entry,
        │   │                                 #   speed attrs, CoA/DM on change
        │   ├── Service/
        │   │   ├── QualificationService.php  # address search -> LOC ID -> SQ,
        │   │   │                             #   service class / speed validation
        │   │   ├── ProvisioningService.php   # connect/modify/disconnect/churn
        │   │   │                             #   order orchestration
        │   │   ├── AppointmentService.php    # timeslots, reserve, reschedule
        │   │   └── StatusMapper.php          # order status + notificationType ->
        │   │                                 #   WHMCS service status
        │   ├── Webhook/
        │   │   ├── CallbackAuthenticator.php # shared-secret URL token check
        │   │   │                             #   (hash_equals), staleness check,
        │   │   │                             #   optional source allowlist
        │   │   ├── CallbackDispatcher.php    # eventUuid idempotency, route by
        │   │   │                             #   eventType/notificationType
        │   │   └── Handlers/
        │   │       ├── OrderStatusHandler.php      # incl. action-required states
        │   │       ├── AppointmentHandler.php
        │   │       └── ServiceStatusHandler.php    # ProductInstanceUpdated /
        │   │                                       #   ProductInstanceDisconnected
        │   ├── Repository/
        │   │   ├── ServiceRepository.php     # Capsule ORM over custom tables
        │   │   ├── OrderRepository.php
        │   │   └── CallbackEventRepository.php
        │   └── Migrations.php                # create/upgrade custom tables
        ├── templates/
        │   ├── clientarea.tpl
        │   └── admin.tpl
        └── tests/
            ├── Unit/
            └── Fixtures/                     # canned API + webhook payloads
docs/
└── ARCHITECTURE.md
composer.json
```

Design choices:

- **Thin entrypoint, testable lib/**: `virtutel_nbn.php` only translates WHMCS
  `$params` into calls on `lib/` classes, which are plain PHP and unit-testable.
- **Webhook as a module callback file** at
  `/modules/servers/virtutel_nbn/callback/webhook.php`, bootstrapping WHMCS via
  `init.php`.
- **Repositories over raw SQL** using WHMCS's bundled Capsule (Laravel) query
  builder.

## Data models

Custom tables prefixed `mod_virtutel_`, linked to WHMCS `tblhosting`.

### `mod_virtutel_tokens` — cached API access tokens

| Column | Notes |
|---|---|
| `id` | PK |
| `environment` | sandbox / production, unique with `api_identity` |
| `api_identity` | hash of client_id (supports credential rotation) |
| `access_token` | encrypted at rest (WHMCS `encrypt()`) |
| `expires_at` | refreshed proactively (e.g. at 80% of 30-day lifetime) |
| `created_at` / `updated_at` | |

### `mod_virtutel_services` — 1:1 with a WHMCS service

| Column | Notes |
|---|---|
| `id` | PK |
| `whmcs_service_id` | FK -> `tblhosting.id`, unique |
| `vt_service_id` | Virtutel service ID (e.g. VT0000001) |
| `avc_id` | NBN AVC ID once active (needed for churn-away + modify) |
| `nbn_location_id` | NBN LOC ID from qualification |
| `technology_type` | FTTP(NFAS) / FTTN / FTTB / FTTC(NCAS) / HFC(NHAS) / FW / Satellite |
| `service_class` | NBN service class 0–34 at order time |
| `network_layer` | layer2 / layer3 — gates the beta Suspend/Resume endpoints |
| `speed_tier` | Virtutel speed enumeration (e.g. TC4FWHF, 100/20) |
| `carrier_status` | last known raw status from /services |
| `external_ref` | identifier in the second external API |
| `created_at` / `updated_at` | |

### `mod_virtutel_orders` — 1 service : N orders

| Column | Notes |
|---|---|
| `id` | PK |
| `service_id` | FK -> `mod_virtutel_services.id` |
| `order_type` | connect / churn / modify_speed / disconnect |
| `vt_order_id` | Virtutel order ID (e.g. VTORD00000000001), unique |
| `status` | raw Virtutel order status enum |
| `whmcs_status` | mapped: pending / action_required / appointment_required / complete / cancelled |
| `action_required` | nullable: install_fee / ndc / fibre_upgrade_liability / rsp_action / dispatch_details |
| `request_payload` / `response_payload` | JSON, secrets masked |
| `completed_at`, `created_at`, `updated_at` | terminal on VTOrderCompleted / VTOrderCancelled |

### `mod_virtutel_appointments` — 1 order : N appointments

Appointments have their own callback lifecycle and are mandatory for
certification (Product Orders + Appointments are certified together).

| Column | Notes |
|---|---|
| `id` | PK |
| `order_id` | FK -> `mod_virtutel_orders.id` |
| `appointment_id` | carrier appointment reference |
| `status` | created / booked / rescheduled / tech_on_site / completed / incomplete / cancelled |
| `slot_start` / `slot_end` | reserved window |
| `demand_type` | e.g. Standard Install / Additional Install |
| `created_at` / `updated_at` | |

### `mod_virtutel_callback_events` — audit + idempotency

| Column | Notes |
|---|---|
| `id` | PK |
| `event_uuid` | Virtutel `eventUuid`, **unique** — duplicate deliveries no-op |
| `event_time` | Virtutel `eventTime` (ISO8601) |
| `event_type` | e.g. ProductOrderStateChangeNotification |
| `notification_type` | e.g. OrderCompleted, AppointmentRequired |
| `vt_object_id` | `event.id` (order/service/appointment reference) |
| `order_id` / `service_id` | nullable FKs, resolved at dispatch time |
| `payload` | raw JSON |
| `auth_ok` | bool — URL token matched |
| `status` | received / processed / failed / skipped |
| `processed_at`, `created_at` | |

Relationships: `tblhosting 1—1 services 1—N orders 1—N appointments`,
callbacks attach to orders, appointments, or services depending on event
family.

Status flow: callback (or `/product-orders` poll fallback) -> `StatusMapper`
-> module tables -> WHMCS service status + notifications, with
"action required" states (install fee, NDC, fibre-upgrade liability,
RSP action) surfaced to admins via ticket/todo rather than silently stalling.

## First 3 incremental steps

Ordered to line up with Virtutel's sandbox certification requirements (callback
registration + one received callback is itself a certification item, and
Product Orders certify together with Appointments).

1. **Skeleton + activation + auth.** *(in progress)* Scaffolding, `composer.json`,
   `MetaData` / `ConfigOptions` (environment sandbox/production, client_id,
   client_secret, callback secret token as password fields), `Migrations`
   creating the tables, `TokenStore` + `HttpClient` (vt_success envelope,
   retries, rate-limit backoff, masked logging), working `TestConnection`
   that generates/reuses an access token.
   *Milestone: module installs, activates, and authenticates against the
   sandbox (port 8443).*
2. **Callback endpoint + qualification.** `webhook.php` with
   `CallbackAuthenticator` (URL secret token) and `CallbackDispatcher`
   (eventUuid idempotency, event stored then processed); register the URL via
   the Callbacks endpoint and pass Virtutel's "send test callback"; address
   search -> LOC ID -> Service Qualification service with service-class and
   speed-tier validation, exposed to admins for pre-sales checks.
   *Milestone: test callback received and stored; SQ works end-to-end —
   callback certification achievable.*
3. **Connect order lifecycle (incl. appointments).** `ProvisioningService`
   wired to WHMCS `CreateAccount` (new connect + churn variants),
   `OrderStatusHandler` walking the full callback state machine
   (action-required states surfaced to admins), `AppointmentService`
   (timeslots / reserve / reschedule) since FTTP SC2-style orders require it,
   AVC ID + VT service ID stored on completion (`VTOrderCompleted` -> WHMCS
   Active), cron poll of `/product-orders` as missed-callback backstop.
   *Milestone: appointment-required sandbox order completes end-to-end —
   Product Orders + Appointments certification achievable.*

Later steps (agreed direction, not yet scheduled):

4. **RADIUS integration.** All services are Layer 2 IPoE identified by AVC ID
   (DHCP Option 82). On `VTOrderCompleted`: create the RADIUS entry (AVC ID
   key + speed-profile attributes). `SuspendAccount`/`UnsuspendAccount`/
   `TerminateAccount` act on RADIUS (profile swap or removal + CoA/Disconnect
   to drop live sessions) — Virtutel's beta L3 suspension API is not used.
   Speed changes: Virtutel Modify Speed order, then RADIUS attribute update
   + CoA on completion.
5. **Go-live import & mapping tool.** Pull all existing services via
   `GET /services`, auto-match to WHMCS services by address and known
   AVC/service IDs, admin review screen for ambiguous/unmatched entries,
   RADIUS-vs-Virtutel consistency diff. Linked services get full callback
   handling.
6. Self-serve checkout qualification widget (residential only), client-area
   status page, outage + service-health surfacing, disconnect flow polish.

## Open questions

Resolved so far: external system = RADIUS/AAA (Layer 2 IPoE, AVC-ID keyed);
WHMCS 8.13.2; sandbox + production credentials in hand; self-service
qualification; residential-only phase 1; go-live import required.
See `docs/BACKLOG.md` for the live list. Still open:

1. **BNG vendor** — RADIUS platform is FreeRADIUS (SQL backend, confirmed);
   the BNG model drives rate-limit attribute format and CoA support.
2. **Callback hostname** — Virtutel requires the callback hostname to be
   registered with them and served over HTTPS with a CA-signed cert. Which
   domain will the WHMCS instance expose for this?
3. ~~Exact request/response schemas~~ — RESOLVED: the full Apiary export
   now lives in `docs/api/` with every URI and JSON schema. Production base
   URL is `https://mars.as24516.net/api/v1/` (sandbox: port 8443).
