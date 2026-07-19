# Ideas backlog & open questions

Working list agreed with Lockie. Items graduate into ARCHITECTURE.md phases
once scoped. Nothing here is committed scope until agreed.

## Confirmed behaviours (agreed)

- Module config asks only for: environment (sandbox/production), client_id,
  client_secret. Everything else is automated.
- **Token auto-renewal**: proactive refresh at ~80% of the 30-day lifetime
  (daily cron check); reactive refresh-once-and-retry on `auth_error`/401;
  DB lock to prevent concurrent double-refresh; per-environment token rows.
- **Callback self-registration**: module generates its own secret token,
  registers/updates its callback URL via the Callbacks API, and can trigger
  Virtutel's "send test callback" from admin to verify the loop.

## Ideas — checkout & sales

- Address qualification widget at checkout: AJAX address search -> LOC ID ->
  SQ; only show plans/speeds the site actually supports
  (virtutelSpeedsAvailable + service class); store LOC ID on the order.
- Churn-in support at signup: capture existing AVC ID (full or last 5) +
  customer authority date; validate with Enhanced SQ before order submission.
- Product modelling: one WHMCS product per technology with speed tiers as
  configurable options (maps cleanly to Modify Speed orders for upgrades).
- Fibre Upgrade upsell: detect alternativeTechnology=Fibre during SQ and
  offer FTTP where eligible (with liability-confirmation workflow).

## Ideas — operations & admin

- Admin services tab: live order timeline (all callbacks in order), current
  raw status, AVC/LOC/service class, buttons for: refresh from API, approve
  install fee / NDC, confirm fibre-upgrade liability, resume order (RSP
  action complete), rebook appointment, run service health check / service
  test, suspend/resume (Layer 3).
- Action-required queue: any *Required callback opens a WHMCS ticket/todo
  with deep-link to the service; optional auto-approve of install fees under
  a configurable dollar threshold.
- Certification helper: admin page tracking which sandbox endpoints have been
  exercised (Virtutel certifies by reviewing sandbox logs) so we know when
  we're ready to request production scopes.
- Import/reconcile tool: pull existing services from GET /services and link
  them to WHMCS services (for services predating the module).
- Watchdog alerts: token refresh failing, no callbacks received in N hours
  while orders are in flight, callback events stuck in failed state.

## Ideas — customer experience

- Client area page: connection status, technology, speed, AVC ID (needed if
  they churn away), current order progress bar, appointment date.
- Appointment self-service: show reserved slot; allow reschedule requests
  (staff-approved or direct PATCH — TBD).
- Outage surfacing: map NBN outage callbacks (CRQs/trouble tickets) to
  affected services; banner in client area + optional email; register
  end-user out-of-band contacts (email free, SMS has monthly fee — make SMS
  opt-in per service).
- Speed change self-service: config option upgrade/downgrade -> Modify Speed
  order automatically (with fibre-upgrade penalty guard in first 12 months).

## Ideas — billing & lifecycle

- Activation gating: keep WHMCS service Pending until VTOrderCompleted (not
  OrderCompleted); set activation/billing start date from completion.
- One-off charge pass-through: install fees, NDCs, missed-appointment fees
  -> WHMCS billable items (per-item admin approval initially).
- Disconnect flow: TerminateAccount -> Disconnect order; only mark terminated
  on ServiceDisconnected/VTOrderCompleted; handle ProductInstanceDisconnected
  (churned away) by flagging the WHMCS service for cancellation + final bill.
- Suspension policy: Layer 3 -> API suspend/resume; Layer 2 -> ticket for
  manual action (or optional integration with external AAA — see below).

## Answered (2026-07-19, Lockie)

- Second external system = **RADIUS/AAA** — all services are Layer 2 IPoE,
  identified by AVC ID (Option 82). Suspend/terminate/speed enforcement are
  RADIUS-side (profile change + CoA/DM); Virtutel L3 suspension API unused.
- WHMCS **8.13.2**.
- Sandbox AND production credentials in hand.
- **Self-service qualification** at checkout; **residential only** phase 1.
- **Go-live import required**: map existing Virtutel services (GET /services)
  to existing WHMCS services — auto-match by address / known IDs, admin
  review screen for the rest, RADIUS consistency diff.

- RADIUS platform = **FreeRADIUS** (SQL backend). Module config will hold
  connection settings + CoA target, each with a Test Connection action.
- **Self-service signup confirmed**, including NTD/UNI-D port selection
  where needed — full flow design in `docs/SIGNUP-FLOW.md`.
- **Port selection UX**: auto-select the free port/pair; show the dropdown
  only when there's a real choice.
- **Appointments**: customer self-service timeslot picker in the client area.
- **Mid-order charges** (install fee / New Development Charge): staff review
  each one — no auto-approval.
- **Payment before lodging the order**: yes (refund if infeasible/cancelled).
- **Static IP add-on**: later phase, not phase 1 (design RADIUS attributes to
  allow it).

## Open questions (gating)

1. **BNG vendor/model** (Mikrotik, Cisco, Juniper, Nokia?) — determines
   rate-limit attribute format (e.g. Mikrotik-Rate-Limit vs Cisco policing
   AVPairs) and CoA/Disconnect support details.
2. **Walled garden or hard reject on suspension?** (Redirect to a pay page
   vs no session.)
3. **Callback public domain** for the WHMCS instance (must be registered
   with Virtutel, CA-signed cert, reachable from mars.as24516.net).
4. **Billing start policy**: bill from order date or from activation date?
   Pro-rata?
5. **Copper-pair locations**: collect the existing phone number (FNN) at
   checkout for POTS Interconnect matching, or auto-select a pair?
6. ~~Exact API endpoint paths~~ — RESOLVED: full Apiary export in
   `docs/api/` confirms every URI, base URL
   (https://mars.as24516.net/api/v1/), and request/response schema.
