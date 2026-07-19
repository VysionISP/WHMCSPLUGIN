# WHMCS Virtutel NBN Provisioning Module

WHMCS provisioning (server) module in PHP that automates the NBN service
lifecycle through the **Virtutel Customer API**: qualification, connect/churn/
modify/disconnect orders, appointments, callbacks (webhooks), and status sync
back into WHMCS.

## Canonical references — read before touching API code

- `docs/ARCHITECTURE.md` — agreed architecture: folder layout, data models,
  security model, build phases. Keep it updated when the design changes.
- `docs/virtutel-api-notes.txt` — full extracted text of the official Virtutel
  Customer API documentation (Apiary, July 2026). **This is the authoritative
  API reference in this repo.** Search it before guessing any API behaviour.
- `docs/virtutel-api-apiary-original.html` — lossless original of the Apiary
  doc page the notes were extracted from.

Known gap: the Apiary export contains the overview page only. Exact per-
endpoint URI paths and full JSON request/response schemas were on sub-pages
not captured in the export — confirm against sandbox or updated docs before
hardcoding paths (one confirmed path: `GET /api/v1/service-qualifications/{locId}`).

## Virtutel API — key facts (memorise)

### Auth & environments
- Auth: POST `client_id` + `client_secret` to Access Tokens endpoint →
  **Bearer token, ~30-day lifetime**. Cache and reuse tokens (do NOT generate
  per request in production). Header: `Authorization: Bearer <token>`, with
  `Accept: application/json` and `Content-Type: application/json`.
- **Sandbox: port 8443. Production: port 443.** Separate credentials, tokens,
  firewalls, and test data per environment; live LOC IDs don't work in
  sandbox and vice versa. Our server IPs must be registered with Virtutel's
  firewall (sandbox first, production only after certification).
- Every response carries `vt_success` (bool), `vt_short_error`,
  `vt_error_desc` at the root. `vt_success=false` means error.
- Tokens carry OAuth-style scopes, e.g. `read:service-qualifications`,
  `create:product-orders`, `read:product-orders`, `update:product-orders`,
  `create:appointments`, `read:appointments`, `update:appointments`,
  `delete:appointments`, `read:services`, `create:callbacks`,
  `read:callbacks`, `delete:callbacks`, `read:locations`, `all:suspensions`,
  `read:invoices`, `read:outages`.
- Some endpoints publish rate limits (steady/burst) — back off on 429s.

### Callbacks (inbound webhooks)
- Register up to **5 HTTPS callback URLs** via the Callbacks endpoint. Valid
  CA-signed cert required (no self-signed); hostname must be pre-registered
  with Virtutel. Query params allowed in the URL → we embed a **secret token**
  (`?token=...`) as our auth, since **Virtutel does NOT sign callbacks (no
  HMAC)**. Callbacks originate from `mars.as24516.net`.
- Envelope: `eventUuid` (unique, use for idempotency), `eventTime` (ISO8601),
  `eventType`, `event {id, notificationType, reason, ...}`.
- Respond HTTP 2xx quickly. Non-2xx → retried several times, then the event
  is **permanently dropped** — a cron poll of `/product-orders` must backstop
  missed callbacks.
- A "send test callback" endpoint exists; receiving ≥1 callback is a
  certification requirement.

### Order lifecycle (Product Orders)
- Types: Connect (new or churn), Modify Speed, Modify DSL Stability Profile,
  Modify Service Restoration SLA, Disconnect. All asynchronous, driven by
  callbacks. IDs look like `VTORD00000000001`; services like `VT0000001`.
- Terminal notifications: **`VTOrderCompleted`** (success — order billed) and
  **`VTOrderCancelled`** (failure). `OrderCompleted` = NBN done but not yet in
  VT billing; don't treat it as final.
- Mid-flight action-required notifications we must surface and act on:
  `AppointmentRequired`, `AppointmentRescheduleRequired`,
  `InstallFeeConfirmationRequired`, `DevelopmentChargeConfirmationRequired`
  (approve via PATCH on the order), `FibreUpgradeConfirmationRequired`
  (PATCH `fibreUpgradeLiabilityConfirmed: true`), `RSPActionRequired`
  (PATCH "resume" once done, e.g. end user plugs in NTD),
  `DispatchDetailsRequired`, `DeviceDetailsRequired`, `DeviceOnlineRequired`,
  `ManualInterventionRequired`.
- Appointments: get timeslots (≤90 days out, max 25 slots) → reserve (order
  must be in `NBN_APPOINTMENT_REQUIRED`/`APPOINTMENT_REQUIRED`) → PATCH to
  reschedule or update contacts (not both in one request), DELETE to cancel.
  Appointment strings: ASCII letters/numbers/space and `,./-@+_()` only.
- Churn (service transfer): requires the **AVC ID** of the losing service
  (full `AVC123456789012` or last 5 digits) + `customerAuthorityDate`.
  Validate via Enhanced SQ (`serviceID` + `customerAuthorityDate` params,
  check `serviceIDMatch` per UNI-D port/copper pair), then put the AVC in
  `service.nbn.churn.serviceIDToTransfer` on the connect order. We must also
  expose AVC IDs (from `/services`) to customers churning away from us.

### Qualification & addresses
- Address search modes: unstructured, structured, G-NAF ID, lat/long
  coordinates, reverse LOC ID lookup. Result: **NBN Location ID** (LOC ID).
- SQ per LOC ID returns technology, **service class 0–34** (see table in
  docs: 0–3 FTTP, 4–6 FW, 7–9 Satellite, 10–13 copper FTTN/FTTB, 20–24 HFC,
  30–34 FTTC), speed tiers (`virtutelSpeedsAvailable`), site restrictions.
- Fibre Upgrade (FTTN/FTTC → FTTP): normal SQ shows
  `siteRestriction.supportingTechnology.alternativeTechnology == 'Fibre'` →
  Fibre SQ with `productType=NFAS` → order with
  `service.nbn.nfas.fibreUpgrade: true` (≥100 Mbps down required; 12-month
  penalty rules apply).
- Enterprise Ethernet SQ: `productType=EEAS`.
- Speed enums include Accelerate Great tiers (500/50, 750/50, 1000/100
  replacing 100/20, 250/25, 1000/50) and FW high-speed tiers (`TC4FWHF`,
  `TC4FWSF`, `L3TC4FWHF`, `L3TC4FWSF`).

### Services & operations
- `/services`: lookup by VT service ID, supplier service ID, or search;
  paginated; includes `contractStartDate`, AVC ID.
- Service callbacks: `ProductInstanceUpdated` (NBN changed something, e.g.
  UNI-D port), `ProductInstanceDisconnected` (churned away/disconnected).
- **Suspend/Resume/Drop-session are BETA and Layer 3 NBN services only**
  (scope `all:suspensions`) — WHMCS suspend must fall back to manual handling
  for Layer 2 services.
- Also available: Service Health Checks and Service Tests (async, results via
  callbacks), Outages (planned CRQs + trouble tickets, callback-driven),
  CVC Entitlements, Demographics, Out-of-Band outage notifications (SMS/
  email contacts), Invoices (beta), AVC Utilisation reports (beta), One-Off
  Charges (beta), NBN missing-address tickets (beta).
- Mobile endpoints (SIMs, eSIM, plan changes, usage-threshold callbacks) are
  mostly ALPHA — out of phase-1 scope.

### Certification (gates production access)
- Must demonstrate successful calls to **all** methods of every endpoint we
  want in production (exceptions: Product Orders PATCH, Appointments
  PATCH/DELETE — sandbox moves too fast).
- Callback-generating endpoints require a registered URL + ≥1 received
  callback. **Product Orders + Appointments certify together** — must
  complete an appointment-required order (e.g. FTTP service class 2).
- Only order against LOC IDs from the issued test data; run fresh SQ first
  (sandbox data is shared and mutable).

## Project conventions

- Module name/namespace: `virtutel_nbn` under `modules/servers/virtutel_nbn/`.
- Thin WHMCS entrypoint (`virtutel_nbn.php`); all logic in testable `lib/`
  classes; DB via WHMCS Capsule; custom tables prefixed `mod_virtutel_`.
- Secrets (client_secret, tokens, callback token) only in WHMCS
  password-type config fields / encrypted columns; always mask them in
  `logModuleCall`.
- Development branch: `claude/nbn-provisioning-api-design-h7fjjt`.
- A second external API will also be integrated (identity TBC by Lockie) —
  keep `ExternalApiClient` isolated behind its own interface.
