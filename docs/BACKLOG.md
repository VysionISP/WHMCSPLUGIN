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

## Open questions (gating)

1. **Second external API — what is it?** If Virtutel supplies Layer 2, we
   need our own BNG/AAA (RADIUS) provisioning — is that what the external
   API is? Name/vendor + docs needed. Determines suspend/terminate flow.
2. **Layer 2 or Layer 3 services (or both)?** Gates suspension approach,
   AAA needs, and which speed enums (L3TC4... vs TC4...) we order.
3. **WHMCS version + PHP version + hosting** for the module (affects
   Capsule/Smarty targets), and **which public domain** hosts the callback
   URL (must be registered with Virtutel, CA-signed cert, reachable from
   mars.as24516.net).
4. **Checkout flow**: full self-serve qualification at checkout from day 1,
   or staff-driven ordering first (simpler MVP)?
5. **Who approves chargeable events** (install fee / NDC / fibre upgrade
   liability)? Auto-approve threshold?
6. **Billing start policy**: bill from order date or from activation date?
   Pro-rata?
7. **Phase 1 product scope**: residential NBN connect + churn only?
   Enterprise Ethernet, FW high-speed tiers, satellite, mobile later?
8. **Existing Virtutel services** to import into WHMCS at go-live, or
   greenfield?
9. Do we have **sandbox credentials + test data document** yet (API Licence
   Agreement signed)? Needed early in step 1 to confirm endpoint paths.
