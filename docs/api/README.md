# Virtutel Customer API — Full Documentation Export

Exported from the private Apiary docs at `virtutelcustomerapi.docs.apiary.io`
(account: lockie@korvix.co / Virtutel team) on 2026-07-19.

## What's here
- **Virtutel_Customer_API_FULL.md** — the entire API in one file (~700 KB): intro,
  base URLs, all 21 resource groups, every endpoint with parameters, request &
  response examples (headers + JSON bodies), and 27 data structures.
- **INDEX.md** — links to the 21 per-section files.
- **01..21_*.md** — one file per resource group (Access Tokens, Callbacks,
  Service Qualifications, Mobile, Product Orders, Appointments, Services, etc.).
- **_raw_apiary_export.json** — the canonical raw Apiary export (API Blueprint
  JSON, 8.6 MB). Machine-readable source of truth if the markdown loses nuance.

## Base URLs
- Production: https://mars.as24516.net/api/v1/
- Mock:       https://private-91407b-virtutelcustomerapi.apiary-mock.com/api/v1/

## Auth
POST /oauth/tokens with client_id/client_secret (client_credentials grant) returns a
Bearer token, ~30 day lifetime. Send as `Authorization: Bearer <token>`.

## Resource groups (21)
Access Tokens, Callbacks, Address Searches, Service Qualifications, Mobile,
Product Order Qualifications, Product Orders, Appointments, Services,
CVC Entitlements, Service Health Checks, Service Tests, Outages, Demographics,
Tickets (Beta), Out of Band Notifications, Invoices (Beta), AVC Utilisation (Beta),
Suspensions (Beta), Overview (Beta), One-Off Charges.
