# Architecture — VirtuTel NBN Provisioning Module

## Overview

A WHMCS server (provisioning) module. All business logic lives in a PSR-4
autoloaded `lib/` (`Vysion\VirtutelNbn\`) so it is unit-testable without a
running WHMCS; the WHMCS-facing files (`virtutelnbn.php`, `hooks.php`,
`webhook.php`) are thin adapters.

```
WHMCS billing events ──> virtutelnbn.php ──> ProvisioningService ──> ProviderInterface ──> VirtuTel API
                                                     │
                                                     └──> mod_virtutel_service / mod_virtutel_order

VirtuTel webhooks ──> webhook.php ──> WebhookController (verify+dedupe+store)
                                              │
WHMCS cron (hooks.php) ──> EventProcessor ────┴──> StatusMapper ──> WHMCS service status
                                (re-fetches order state from the API on critical transitions)
```

## Layers

- **`Provider/`** — `ProviderInterface` abstracts the upstream carrier
  (qualify / connect / modify / suspend / unsuspend / disconnect / getOrder /
  getService / ping). `Provider/Virtutel/` is the only code that knows
  VirtuTel's wire format; a second carrier is a new implementation, no other
  changes.
- **`Service/`** — orchestration (`ProvisioningService`), NBN service
  qualification (`QualificationService`), and pure status mapping
  (`StatusMapper`).
- **`Webhook/`** — inbound pipeline. `WebhookController` verifies + stores;
  `EventProcessor` runs on the WHMCS cron and applies state changes.
- **`Repository/`** — Capsule (Laravel query builder) access to the module's
  tables. `Installer` creates them lazily (server modules have no activation
  hook).

## Data model

| Table | Purpose | Key relationships |
|---|---|---|
| `mod_virtutel_service` | Bridge between WHMCS `tblhosting` and the carrier service (AVC id, LOC id, plan, status) | 1:1 `tblhosting.id` |
| `mod_virtutel_order` | Every upstream mutation (connect/modify/disconnect) with request/response audit | N:1 service |
| `mod_virtutel_webhook_event` | Inbound event queue; `external_event_id` unique → idempotency | N:1 order (nullable — matched during processing) |
| `mod_virtutel_api_log` | Redacted outbound request/response log | standalone |

Status vocabulary: provider strings → internal
(`pending / in_progress / held / active / suspended / cancelled / terminated`)
→ WHMCS `domainstatus` (only for terminal-ish states; order-progress statuses
don't touch WHMCS state).

## Security decisions

1. Secrets live in the WHMCS server record (Username = Client ID, Password =
   Client Secret, WHMCS-encrypted); never in code. Access tokens obtained from
   them are cached WHMCS-encrypted in `mod_virtutel_token`.
2. Auth: OAuth2-style client-credentials flow (`Auth/TokenManager`). VirtuTel
   issues ~28-day access tokens; the manager renews 2 days before expiry, a
   cron keep-alive (hooks.php) guarantees renewal even with no traffic, and a
   401 from the API triggers exactly one forced refresh + retry in
   `VirtutelClient`.
3. Outbound: HTTPS enforced at config parse time, TLS peer verification on,
   bounded timeouts and retries, bearer auth, log redaction
   (`VirtutelClient::redact`).
4. Inbound: HMAC-SHA256 over `timestamp.body`, constant-time compare, ±300 s
   replay window, 1 MiB payload cap, POST-only, generic error bodies,
   unique-event-id idempotency.
5. Webhook payloads are treated as *signals*, not facts: transitions to
   active/cancelled/terminated re-fetch the order from the API before WHMCS
   state changes.
6. Processing is asynchronous (cron) so the public endpoint does minimal work
   under attacker-controllable input.

## Incremental roadmap

1. ✅ Skeleton, installer, config, TestConnection, unit tests.
2. ✅ API client + qualification + CreateAccount happy path (placeholder wire
   format pending VirtuTel spec — grep `TODO(virtutel-spec)`).
3. ✅ Webhook pipeline + cron processor + status sync + admin/client views.
4. ⬜ Bind wire format to the official VirtuTel API spec; sandbox end-to-end test.
5. ⬜ Order-status history table in admin UI, notifications (ticket/email on held
   orders), optional IP allowlist for the webhook endpoint.
6. ⬜ Second provider implementation if/when required.
