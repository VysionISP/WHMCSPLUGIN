# Self-service signup flow (residential)

Design for the customer-facing order flow. Driven entirely by the Virtutel
API facts in `docs/virtutel-api-notes.txt`. Phase 1 is residential only —
`custData.business` stays false (business appointments additionally require
`siteAccess.inductionRequirements` + `siteAccess.operationalHours`; out of
scope until business is added).

All API calls happen server-side (AJAX to WHMCS, never token exposure to the
browser). Sandbox note: only test-data LOC IDs resolve in sandbox.

## Step 1 — Address → LOC ID

- Structured address search (autocomplete UI) with unstructured search as
  fallback; multiple matches → picker (results are ordered best-match-first).
- Reverse LOC ID lookup supported for customers who already know their
  LOC ID (shown on NBN equipment / other providers' letters).
- Address not found in NBN's database → collect contact email and lodge an
  NBN missing-address ticket (beta endpoint), order continues manually via
  staff.

## Step 2 — Service Qualification

Run Normal SQ on the LOC ID. Everything downstream branches on the result:

- **Orderability**: service classes 0, 4, 7, 10, 20, 30 are not orderable —
  show "coming soon / register interest".
- **Speed tiers**: filter WHMCS configurable options to
  `virtutelSpeedsAvailable`; for FW high-speed tiers additionally check
  `siteRestriction.supportingResource[ntd].speedTiersSupported` and NTD
  upgrade notes (NTD0001/NTD0002 = upgrade needed for all high tiers,
  NTD0005 = Superfast only → appointment, demandType 'Additional Install').
- **Fibre Upgrade offer**: if
  `siteRestriction.supportingTechnology.alternativeTechnology == 'Fibre'`,
  optionally offer FTTP: run Fibre SQ (`productType=NFAS`), require SC 1-3,
  ≥100Mbps downstream plan, fibreUpgrade flag on the order, liability
  confirmation workflow (12-month downgrade/cancel penalties), appointment
  with `fibreUpgrade=true` + demandType 'Standard Install', no copper pair
  ID.

## Step 3 — New connection or transfer (churn)

**Transfer (churn)** — customer states they have an active service:
- Collect the AVC ID (full `AVC123456789012` or last 5 digits) — shown in
  their current provider's portal — plus explicit consent (checkbox; the
  consent date becomes `customerAuthorityDate`).
- Run Enhanced SQ (Churn Validation): `serviceID` + `customerAuthorityDate`.
  The matching UNI-D port / copper pair comes back with
  `serviceIDMatch=true` → **port selection is automatic for churns**.
- No match → show the RJ002003-RJ002006 reason (doesn't exist / recently
  disconnected / different location) and fall back to new-connection flow or
  staff assistance. `supportingProduct` data is privacy-restricted: internal
  use only, never render to the end user.
- Order carries `service.nbn.churn.serviceIDToTransfer`.

**New connection** — per-technology device/port logic:

| Tech / class | What signup must handle |
|---|---|
| FTTP SC3 (NTD present) | Select NTD (if multiple) + free UNI-D port from `supportingResource`. Default: auto-pick first unused port; show a port dropdown when >1 NTD or the customer wants a specific port. All ports in use → offer additional install path (appointment). |
| FTTP SC1/SC2 (no NTD) | Appointment required (Standard Install). Optional `ntdType` (1-port vs 4-port) on the connect order — default auto ("appropriate default based on site configuration"), expose only as an advanced option. |
| FTTN/FTTB SC11-13 | Copper pair selection. If the customer has a legacy phone service on the line, collect the phone number (FNN) and run Enhanced SQ POTS Interconnect → `POTSInterconnectMatch` picks the right pair. Otherwise auto-select an available pair; SC12 (jumpering required) may need NBN work. |
| FTTC SC31-34 | NCD (NBN connection device) required; SC31-33 involve line/cut-in work → appointment likely; SC34 check NCD status. NCD ships to the premises. |
| HFC SC21-24 | SC24 = ready to connect. SC23 = NTD dispatch → `DispatchDetailsRequired` callback: collect delivery details (default service address) at checkout. SC21/22 = lead-in/wall-plate work → appointment. `DeviceDetailsRequired` (e.g. HFC MAC) handled post-order via client-area prompt + ticket. |
| Fixed Wireless SC5/SC6 | SC5 = NTD install appointment (Standard Install). SC6 = existing WNTD: port/speed checks as per FTTP SC3 + high-speed-tier NTD rules. |

## Step 4 — Feasibility (pre-payment gate)

Run a **Product Order Qualification** (same payload as the connect order,
no side effects; not available for HFC or churns) before taking payment:
- Confirms feasibility, expected appointment requirement, and the
  `demandType` to request.
- "Feasible, delayed" (e.g. FTTC SC34 example) → show expected-delay
  messaging rather than blocking.
- HFC / churn orders: skip POQ (unsupported) and rely on SQ + validation.

## Step 5 — Cart, payment, order lodgement

- Plan (filtered speeds) + any add-ons; store LOC ID, service class,
  technology, selected port/pair/NTD details, churn fields as WHMCS custom
  fields on the order.
- Payment → `CreateAccount` → `ProvisioningService` lodges the connect
  order. WHMCS service stays Pending until `VTOrderCompleted`.
- Appointment-required orders: on the `AppointmentRequired` callback, email
  the customer a client-area link with a timeslot picker (≤90 days out,
  max 25 slots shown; contact details from the WHMCS profile — phone number
  must be a required checkout field; appointment strings sanitised to the
  allowed ASCII set).
- Chargeable surprises mid-order (`InstallFeeConfirmationRequired`,
  `DevelopmentChargeConfirmationRequired`): per BACKLOG policy question —
  options are auto-approve under threshold, invoice the customer for
  approval, or staff review. New-development addresses should be warned at
  checkout that an NBN New Development Charge may apply.
- `RSPActionRequired` ("Awaiting Device installation" etc.): client-area
  banner + email telling the customer to plug in the NTD/NCD, with a
  "Done — continue my order" button that fires the Resume Order PATCH.

## Post-order status page (client area)

Progress tracker fed by stored callbacks: Submitted → Accepted by NBN →
(Appointment booked: date/window, reschedule button) → (Action needed:
plug in device / provide MAC / delivery details) → Connection test →
Active. Falls back to daily poll if callbacks were missed.
