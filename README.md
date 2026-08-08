# WHMCS VirtuTel NBN Provisioning Module

A WHMCS **server (provisioning) module** that provisions NBN services through the
VirtuTel wholesale API, keeps WHMCS in sync via **signed inbound webhooks**, and is
structured so additional upstream carriers can be added behind the same interface.

Module path: `modules/servers/virtutelnbn/`

## How it plugs into WHMCS

| WHMCS touchpoint | Behaviour |
|---|---|
| Setup → Servers | Add a server of type **VirtuTel NBN Provisioning**. Hostname = API base URL, Password = API key, Access Hash = webhook secret. |
| Product Module Settings | Select the module, set the VirtuTel **Plan Code** (speed tier) per product. |
| Order paid / accepted | WHMCS calls `CreateAccount` → address is qualified, connect order placed. |
| Overdue / cancellation automation | `SuspendAccount` / `UnsuspendAccount` / `TerminateAccount` → matching VirtuTel actions. |
| Upgrade/downgrade | `ChangePackage` → speed-tier modify order. |
| Admin service page | VirtuTel service id, AVC id, provider status and recent orders. |
| Client area | Connection status, AVC id, plan. |
| WHMCS cron | Processes the queued webhook events every run (`hooks.php`). |

## Installation

1. Copy `modules/servers/virtutelnbn/` into your WHMCS installation.
2. Create the server entry (Setup → Products/Services → Servers):
   - **Hostname**: VirtuTel API base URL (HTTPS enforced)
   - **Username**: VirtuTel Client ID
   - **Password**: VirtuTel Client Secret (WHMCS stores it encrypted)
   - **Access Hash**: webhook shared secret — generate with `openssl rand -hex 32`

   You never enter an access token yourself: the module exchanges the Client
   ID + Secret for a ~28-day token, caches it encrypted in the database
   (`mod_virtutel_token`), renews it automatically 2 days before expiry
   (checked on every WHMCS cron run), and refreshes immediately on a 401.
3. Configure your NBN product(s): Module Settings tab → VirtuTel NBN Provisioning → set the Plan Code.
4. Add two **custom fields** to each NBN product (admin-only or on order form as you prefer):
   - `NBN Location ID` (preferred, e.g. `LOC000012345678`)
   - `Service Address` (free-text fallback used for qualification)
5. Register the webhook endpoint with VirtuTel:
   `https://<your-whmcs>/modules/servers/virtutelnbn/webhook.php`
   with the same shared secret you put in Access Hash.

Database tables (`mod_virtutel_*`) are created automatically on first use.

## Webhook security model

- **HMAC-SHA256** over `"{timestamp}.{rawBody}"` with the shared secret,
  sent in `X-Virtutel-Signature`; compared with `hash_equals` (constant time).
- **Replay protection**: `X-Virtutel-Timestamp` must be within ±5 minutes.
- **Idempotency**: every event carries an id, unique-indexed in
  `mod_virtutel_webhook_event` — duplicate deliveries are acknowledged but not reprocessed.
- **Fast ACK, async work**: the endpoint stores and returns 200; the WHMCS cron
  processes the queue.
- **Trust but verify**: critical transitions (active / cancelled / terminated) are
  re-fetched from the VirtuTel API before WHMCS state changes.
- Outbound calls: TLS verification on, bearer auth, bounded timeouts/retries,
  secrets redacted from all logs.

## Pending: VirtuTel API specification

Endpoint paths, payload field names and webhook header names are a placeholder REST
shape, isolated in `lib/Provider/Virtutel/`. Search for `TODO(virtutel-spec)` — those
are the only places to adjust once the official spec/sandbox credentials are available.

## Development

```bash
cd modules/servers/virtutelnbn
composer install
composer test        # phpunit — lib/ runs without a live WHMCS
```

Architecture and data-model details: [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).
