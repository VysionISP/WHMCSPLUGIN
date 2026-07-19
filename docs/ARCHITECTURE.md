# Virtutel NBN Provisioning Module for WHMCS — Architecture & Plan

**Status:** Draft for agreement — no implementation yet.

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

## Security requirements

- All outbound calls TLS-only with certificate verification; no plaintext HTTP.
- API credentials and webhook secret stored via WHMCS password-type config
  fields (encrypted at rest by WHMCS). Never logged.
- `logModuleCall` used for all API traffic with an explicit mask list
  (API keys, tokens, signatures).
- Inbound webhooks: HMAC signature verification **before** payload parsing,
  timestamp check for replay protection, unique-event-id idempotency, and an
  optional source IP allowlist.
- Webhook endpoint returns quickly and never leaks internal errors in the
  response body.

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
        │   └── webhook.php           # public inbound webhook endpoint (thin:
        │                             #   verify signature, record, dispatch)
        ├── lib/
        │   ├── Api/
        │   │   ├── HttpClient.php            # cURL/Guzzle wrapper: TLS, timeouts,
        │   │   │                             #   retry w/ backoff, masked logging
        │   │   ├── VirtutelClient.php        # SQ, order, modify, cease endpoints
        │   │   └── ExternalApiClient.php     # the second external API
        │   ├── Service/
        │   │   ├── QualificationService.php  # address / LOC-ID qualification
        │   │   ├── ProvisioningService.php   # orchestrates order lifecycle
        │   │   └── StatusMapper.php          # carrier status -> WHMCS status
        │   ├── Webhook/
        │   │   ├── SignatureVerifier.php     # HMAC + timestamp/replay checks
        │   │   ├── WebhookDispatcher.php     # event type -> handler, idempotency
        │   │   └── Handlers/
        │   │       ├── OrderStatusHandler.php
        │   │       ├── AppointmentHandler.php
        │   │       └── ServiceStatusHandler.php
        │   ├── Repository/
        │   │   ├── ServiceRepository.php     # Capsule ORM over custom tables
        │   │   ├── OrderRepository.php
        │   │   └── WebhookEventRepository.php
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

### `mod_virtutel_services` — 1:1 with a WHMCS service

| Column | Notes |
|---|---|
| `id` | PK |
| `whmcs_service_id` | FK -> `tblhosting.id`, unique |
| `virtutel_service_id` | carrier-side service identifier |
| `avc_id` | NBN AVC identifier once active |
| `nbn_location_id` | NBN LOC ID from qualification |
| `technology_type` | FTTP / FTTN / FTTC / HFC / Fixed Wireless |
| `speed_tier` | ordered bandwidth profile |
| `carrier_status` | last known raw carrier status |
| `external_ref` | identifier in the second external API |
| `created_at` / `updated_at` | |

### `mod_virtutel_orders` — 1 service : N orders

| Column | Notes |
|---|---|
| `id` | PK |
| `service_id` | FK -> `mod_virtutel_services.id` |
| `order_type` | connect / modify / cease |
| `virtutel_order_id` | carrier order reference |
| `status` | pending / in_progress / appointment_required / complete / failed |
| `appointment_at` | nullable |
| `request_payload` / `response_payload` | JSON, masked |
| `completed_at`, `created_at`, `updated_at` | |

### `mod_virtutel_webhook_events` — audit + idempotency

| Column | Notes |
|---|---|
| `id` | PK |
| `event_id` | carrier's event ID, **unique** — duplicates become no-ops |
| `event_type` | e.g. order.status_changed, service.dropout |
| `order_id` | nullable FK -> `mod_virtutel_orders.id` |
| `service_id` | nullable FK -> `mod_virtutel_services.id` |
| `payload` | raw JSON |
| `signature_valid` | bool |
| `status` | received / processed / failed / skipped |
| `processed_at`, `created_at` | |

Relationships: `tblhosting 1—1 services 1—N orders 1—N webhook_events`
(events may also attach directly to a service for non-order events).

Status flow: webhook (or poll fallback) -> `StatusMapper` -> module tables ->
WHMCS service status + notifications.

## First 3 incremental steps

1. **Skeleton + activation + secure config.** Scaffolding, `composer.json`,
   `MetaData` / `ConfigOptions` (API URL, key, webhook secret as password
   fields), `Migrations` creating the three tables, working `TestConnection`.
   *Milestone: module installs, activates, authenticates.*
2. **Outbound API layer + CreateAccount happy path.** `HttpClient`,
   `VirtutelClient` (qualification + submit order), `ProvisioningService`
   wired to `CreateAccount`; order stored, service left Pending awaiting async
   completion. *Milestone: WHMCS order lodges a sandbox NBN order.*
3. **Inbound webhooks + status lifecycle.** `webhook.php`, HMAC
   `SignatureVerifier`, idempotent `WebhookDispatcher`, `OrderStatusHandler`
   flipping the WHMCS service to Active and storing the AVC ID; cron-hook
   poller as fallback for missed webhooks.
   *Milestone: full async connect lifecycle end-to-end.*

Steps 4–6 (after agreement): suspend/unsuspend/terminate, second external API
integration, appointments + client-area/admin UI polish.

## Open questions

1. Virtutel API auth scheme (API key header vs token) and sandbox availability.
2. Identity and role of the second external API (aggregator, porting/IPND,
   AAA/RADIUS?) — determines where it sits in the flow.
3. Whether Virtutel signs webhooks (HMAC header); if not, fall back to IP
   allowlist + shared-secret URL token.
