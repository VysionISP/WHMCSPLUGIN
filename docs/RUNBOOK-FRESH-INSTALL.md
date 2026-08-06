# Korvix WHMCS — Fresh-Install Bring-Up Runbook

Everything needed to take a brand-new WHMCS 8.13 install to the full working
Korvix stack (Virtutel NBN module, marketing site, portal skin, signup wizard).
Work top to bottom; each phase depends on the one before it.

---

## Phase 0 — Server prerequisites (before touching WHMCS admin)

1. **DNS + TLS**: `backend.korvix.co` must resolve to this server with a valid
   CA-signed certificate (Virtutel callbacks refuse self-signed). Confirm:
   `curl -sI https://backend.korvix.co | head -1`
2. **Outbound firewall**: the server's PUBLIC IP must be registered with
   Virtutel's firewall. **If this install changed the server/IP, email
   Virtutel with the new IP before anything API-related will work.**
3. **WHMCS cron** every 5 minutes (token refresh, callback poll backstop,
   daily jobs all hang off it):
   `*/5 * * * * php -q /path/to/whmcs/crons/cron.php`
4. **PHP**: 8.1/8.2 with pdo_mysql, curl, gd. Docker install: same image as
   before is fine.

## Phase 1 — WHMCS core settings

Admin → System Settings:

1. **General**: SystemURL `https://backend.korvix.co`. Company name Korvix.
2. **Friendly URLs**: set to *Full Friendly Rewrite* (the /login page and
   legal-page routing expect it).
3. **Trusted Proxies**: add `172.18.0.0/16` (docker bridge) so client IPs are
   real — rate limiting and bans depend on it.
4. **Currency**: AUD as default (pricing lookups use the default currency).
5. **Payment gateway**: activate Stripe, enter live keys.
6. **Tax**: GST 10%, prices entered inclusive.
7. **Mail**: SMTP 127.0.0.1:1025 (proton-bridge) or as per current mail path.
   Send a test email.
8. **Support department**: create **Support** FIRST so it gets ID 1 — the
   site's "Report a fault" links target `deptid=1`.
9. **Theme**: System Theme = Twenty-One (the portal skin overrides it via
   hooks; no template changes needed).
10. **Logo**: upload so it lands at `assets/img/logo.png` (Customisation →
    General → Logo). The marketing nav, portal nav, login page and invoice
    PDF all resolve this file first.
11. **Admin security**: re-check API IP restrictions, two-factor, etc.

## Phase 2 — Deploy the plugin bundle

First time on a fresh box (no updater script yet):

```bash
# 1. copy the updater from the repo (or scp it from the old server)
#    scripts/update-whmcs-plugin.sh  ->  /home/korvix/update-whmcs-plugin.sh
# 2. upload the latest zip (virtutel_nbn_vX.Y.Z.zip) to /home/korvix/
sudo bash /home/korvix/update-whmcs-plugin.sh /home/korvix/virtutel_nbn_vX.Y.Z.zip
```

The updater auto-detects the docroot, unzips, fixes ownership and restarts
PHP. The zip carries: both server modules, the admin addon, hooks, the
korvix-dark invoice template, all marketing pages (personal/business/legal/
contact), router card artwork, robots.txt and sitemap.

**Database tables, email templates, the DOB custom field and the email
header/footer all self-install** via migrations on the first page load after
deploy — nothing to import manually.

## Phase 3 — Server records (the API credentials)

Admin → System Settings → Servers → Add New Server:

| Field | Value |
|---|---|
| Name | Virtutel - NBN |
| Module | Virtutel NBN |
| Hostname | `mars.as24516.net` |
| Port | `443` (production) / `8443` (sandbox) |
| Username | Virtutel **client_id** |
| Password | Virtutel **client_secret** |
| Access Hash | `https://backend.korvix.co` (callback base URL) |

Click **Test Connection** — it must pass before anything else matters.
Repeat with Module = Virtutel Phone if phone services are in play (same
credentials).

Callback registration happens automatically on the next daily cron (or
immediately via any API activity) using the Access Hash URL — check the
activity log for "callback registration".

## Phase 4 — Addon module

Admin → System Settings → Addon Modules → **Virtutel NBN Tools** → Activate,
then Configure:

- **Google Places API Key** — address autocomplete (restrict key to your
  domains).
- **Modem/Router Product IDs** — comma-separated, display order (set after
  Phase 5 creates the products).
- **Ethernet-only Router IDs** — the MikroTik (hidden on FTTN/FTTB).
- **ClickSend username / API key / sender** — arms SMS verification in the
  signup wizard.
- **FreeRADIUS DB + CoA settings** — when the RADIUS side is ready; each has
  its own Test Connection.

Grant addon access to the right admin roles.

The addon page's **"Signup wizard:" status line** tells you exactly what the
modem step will render and whether SMS is armed — use it to verify this phase.

## Phase 5 — Products

Product group (e.g. "NBN Plans"), then per plan:

1. Module = **Virtutel NBN**, server group = the server from Phase 3.
2. **Module setting 1 (configoption1) = the speed enum** exactly as the SQ
   returns it (run a qualification in the addon page and copy the enum from
   `virtutelSpeedsAvailable` — e.g. `25/10`, `50/20`, FW tiers include FW in
   the product NAME, e.g. "NBN FW 25/5", so tech-matching works).
3. Monthly price on the default currency. Payment type recurring.
4. Naming: `NBN 100/20` style — the checker parses down/up from the name.

Router products: any group, **hidden** is fine, one-time or monthly price,
description = one feature per line (becomes the card bullets). Note their
IDs into the addon config (Phase 4). Card photos land at
`assets/img/routers/<pid>.png` (bundled SVG art covers 28/29 already —
IDs will differ on a fresh install, so re-check).

Home phone products (optional): any group with "phone" in the group name
auto-populates the marketing home-phone page.

## Phase 6 — Re-link existing customers & services

Fresh DB = no clients. For each real customer (e.g. Steph, Zac):

1. Create the client, then Add New Order → the right NBN product →
   status Active (skip payment).
2. Open the service, set **Domain = their AVC ID** (`AVC…`).
3. On the admin service tab use **Link** (paste AVC or VT id) — pulls
   address, speed, carrier status, raw record. Or just view the service in
   the client area once: unlinked Active services **auto-link themselves**
   from the Domain field (throttled hourly).
4. Confirm the service tab shows VT id / AVC / address / plan / POI.

Bulk alternative: GET /services import tooling exists in the module —
ask Claude to run the go-live import flow if there are many.

## Phase 7 — Verification sweep (in order)

1. **Test Connection** on the server record — token minted.
2. Addon page: PRODUCTION banner, ops dashboard renders, signup-wizard
   status line lists modem cards.
3. **Marketing site**: `/` redirects to `/personal/`; check `/personal/`,
   `/business/`, `/contact/`, a legal page; plans slider shows live prices.
4. **Checker**: qualify a known address end-to-end from a plan card popup.
5. **Signup wizard**: full run with a fresh email — stepper, billing step,
   modem cards, review, terms tick → checkout logged-in, plan + router in
   cart. (THE critical test.)
6. **Portal**: /login (dedicated dark page), /clientarea.php dashboard
   (connection card with real data), a service page (line test runs and
   renders the dark verdict modal), submit-ticket helper cards.
7. **Callbacks**: fire a test callback (admin/API `POST /callbacks/tests/{id}`)
   and see it in the addon's recent-callbacks list; confirm "Callback poll
   backstop last ran" updates within the hour.
8. **Emails**: welcome/set-password arrives on a wizard signup; invoice PDF
   renders branded (open any invoice → Download).
9. **Stripe**: one live payment.

## Phase 8 — Deferred / when ready

- FreeRADIUS DB + CoA config → arms provisioning; then ask for the radacct
  live-session panel.
- Virtutel sandbox certification runs (needs their test-data sheet).
- Rotate any credentials that were shared in chat/tickets historically.
- Real ABN/entity details in the legal pages; privacy@/hardship@ mailboxes.

---

*Kept in step with the module — regenerate via Claude when the architecture
changes. Last updated for module v1.40.3.*
