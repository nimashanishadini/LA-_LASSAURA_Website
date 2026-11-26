# La Lassaura architecture and Stage 01 contract

One property contains four individually bookable rooms: Sandy and Shelly (family),
Coral and Pearl (double). Existing Villa/villas names remain internally for media
and amenity compatibility; UI terminology should be Room. Never hard-code IDs.

## Business configuration

- Confirmed by the owner: adult, child and total capacity are configurable by admin per room. No fixed capacity is assumed. Null means unknown, not unlimited.
- Check-in/out times in Asia/Colombo.
- Cancellation/refund terms.
- Whether one request can book multiple rooms. Null is undecided, not permission.

The default booking_policies record stores property rules. Do not enable actual
booking submission until the necessary rules, capacities and rates are agreed.
Defer booking/booking-item schema until the multi-room decision is confirmed.

## Setup

Run migrations, then php artisan db:seed --class=VillaSeeder and
php artisan db:seed --class=BookingPolicySeeder. DatabaseSeeder also calls both.
Repeated seeding preserves edits. Rooms start inactive with null capacity; no
amenities, photos or rates are assumed. Existing demo rows are preserved: review
and retire them explicitly before launch. Rollback removes policy/category data
but retains nullable capacities rather than inventing guest limits.

## Implementation sequence

Stage 04: roles, permissions, protected admin shell.
Stage 05: room CRUD, validated capacity, image upload/cover/order, maintenance
blocks and database-backed public room listing/detail. Store image files in
Laravel storage and metadata in villa_media; authorize every modification.
Stage 06: LKR rates and USD FX snapshots.
Stage 07: central transactional inventory, unique room/night allocations.
Stages 09–10: quotes, pending requests, approval, customer portal.

Pending does not hold nights. Confirmation checks availability and allocates
nights atomically. Stay interval excludes checkout day. Price changes require
customer acceptance; confirmed prices remain immutable. Phase 01 payments are
manual; live gateways and OTA sync are future integrations.

## Admin implementation

- `/admin` uses the existing session login and server-side access gate. Staff
  login redirects to admin; customer login keeps the customer dashboard.
- Owner, manager and editor can create/edit rooms, assign existing amenities,
  publish/deactivate rooms, and upload/edit/remove room images. Reception,
  finance and housekeeping currently have dashboard access only.
- Role is deliberately not mass assignable. A trusted server operator can grant
  an existing registered account access with
  `php artisan admin:grant person@example.com --role=owner`.
  No default administrator password or public staff registration is provided.
- `/admin/rooms` supports draft rooms with unknown capacities; publication
  requires all three limits. Total capacity cannot exceed combined adult/child
  limits or fall below either individual maximum.
- Image uploads accept JPEG/PNG/WebP, up to 30 MB and 8000px per dimension,
  ten files per request. Files use the public disk; run `php artisan storage:link`.
  The first image becomes cover; setting another cover replaces it. Removing
  the cover selects the first remaining image. Display order and alt text are
  editable. Cross-room media updates/deletions return 404.
- Dashboard counts represent configured/published/draft rooms and stored photos.
  Occupancy, availability and pending counts await the booking engine; they are
  not inferred from publication status. Only implemented navigation is shown.
- Settings editor, staff-role editing UI, audit history, maintenance calendar,
  public room data binding and booking features remain subsequent work.


## Stage 01 review pack — 25 November 2026

Status: engineering draft prepared for review; not an approved release gate.
The request to complete this pack is not a substitute for developer/owner sign-off.
Actual property values may be configured later; the configuration contract can be
reviewed now. Multi-room cardinality is still a separate product decision.

| Checklist | Review artifact | Status |
| --- | --- | --- |
| 01.01 Scope, inventory, roles, rules | [Scope and decisions](stage-01-scope.md) | Prepared; open decisions listed |
| 01.02 Screens, flow, navigation, breakpoints | [Wireframes](stage-01-wireframes.md) | Proposed; review needed |
| 01.03 ER draft and lifecycle | [Data contract](stage-01-data-contract.md) | Proposed; multi-room decision pending |
| 01.03 API payload/error contract | [OpenAPI draft](openapi.json) | Proposed; endpoints mostly not implemented |
| 01.03 Tests, review, NAS handover | [Acceptance and delivery](stage-01-acceptance.md) | Prepared; sign-off pending |

The existing admin UI and room foundation are implemented. The review pack
specifies future behavior and must not be read as evidence that bookings,
pricing, policies editor, CMS or reporting already exist.


## Public room data binding

Public `/rooms` now lists active database rooms. `/rooms/{slug}` displays that
room's current description, capacity, active amenities and ordered uploaded media;
inactive/unknown rooms return 404. The legacy `/rooms/accommodation` URL redirects
to the listing. Missing photographs show an honest empty state, not another room.
Room details link to contact while booking cardinality/rates remain undecided.
The owner deferred the multi-room decision; it does not block room content work.


## Room rates and dual-currency pricing

Implemented rate configuration lives at `/admin/rates`, restricted to owner and
manager. Each room has date-inclusive rate rules, all/weekday/weekend matching,
priority, LKR nightly amount, percentage discount and active status. Highest
priority wins; equal-priority overlaps on an applicable night are rejected under
room-row transaction locks. A selected rule's discount applies once, without
stacking. Saturday/Sunday are the initial weekend definition. These precedence
rules are documented implementation defaults for owner review.

Public room cards show today's nightly rate when configured; details accept dates
and calculate a server-side stay estimate (1–90 nights). Estimates do not reserve
inventory, validate guest selections or promise availability. Checkout is excluded.
Missing nightly rates produce an unavailable message rather than a zero price.
No property prices, exchange rates or tax rules have been invented/seeded.

Calculations use existing Laravel dependency brick/math BigDecimal: discount each
night with HALF_UP to two decimal places, sum LKR lines, convert the LKR total to
USD at six-decimal LKR-per-USD precision, then round USD HALF_UP to two places.
The service returns detached nightly and FX snapshot arrays. Persisting snapshots
with confirmed bookings remains dependent on the future booking engine/schema;
this work does not claim historic booking persistence already exists.

Exchange rates are append-only snapshots. Admin override records include actor,
reason, source and fetched time. The latest valid positive rate is used; after
24 hours public UI marks it stale. No valid rate means USD unavailable. Settlement
remains LKR. An override lasts until the next successful provider refresh.

Automatic refresh is wired as hourly `rates:refresh-fx` with overlap protection.
Deployment must configure a trusted `FX_RATE_URL`, `FX_RATE_JSON_PATH` (default
`rates.LKR`) and `FX_RATE_SOURCE` in the environment. The provider must return
LKR per 1 USD, not the reciprocal. No provider or credentials are configured by
this change. It has been tested with HTTP fakes, not a live provider. Refresh
validates positive decimal data, uses timeouts/retry and retains the last valid
snapshot on failure. Deploy Laravel Scheduler (`schedule:run` every minute) to
activate the hourly job. Manual rate entry works without a provider.

Public pages always show both currency amounts when available. The existing
navbar/footer currency preference controls are not used as the pricing authority;
a unified site preference remains separate UI work.


## Stage 07 — central inventory and calendar

Implemented 26 November 2026. `Availability` is the authority for occupied nights;
FullCalendar is a read-only projection. Admin Calendar supports day/week/month,
room filtering, date-click prefilling and maintenance create/release. Owner and
manager can manage maintenance; reception and housekeeping may view the calendar;
customers/editors/finance cannot read its feed. Events omit guest/contact details.
Public `GET /availability` accepts check_in/check_out/adults/children and returns
only published rooms that fit configured capacity and have no allocated nights.

Every stay is [check_in, check_out), using Asia/Colombo calendar dates. Maintenance
uses the same exclusive end-date rule. Unique `(villa_id, stay_date)` prevents
multiple allocations; a DB check (SQLite triggers in tests) requires exactly one
booking or maintenance owner. Transactions lock rooms in stable ID order and
rollback every allocation when any night conflicts. Pending bookings allocate
nothing. Confirmation rechecks capacity and dates. Date/room changes and
extensions replace allocations atomically. Cancellation/releasing maintenance
removes only today/future allocations; historical nights and inventory_events
remain. Calendar reads the actual retained rows instead of guessing from status.

A minimal internal single-room booking record supports inventory contract tests;
this is not a public booking/approval feature. Stage 09 must add customer/source,
quote/FX/policy snapshots, price acceptance, authorization and notifications
before exposing booking actions. Stage 07's internal methods do not claim price
acceptance or complete guest-stay workflows. Started stays require later front-office
policy review. Multi-room product decision remains deferred; no multi-room UI or
partial allocation behavior has been introduced.

Verification: AvailabilityTest covers boundaries, permissions, capacity, pending,
maintenance conflicts, atomic changes/release and DB ownership constraints.
`php tests/Support/inventory-concurrency.php` is an opt-in real MySQL/MariaDB test:
it creates its own random `la_lassaura_inventory_test_*` database, migrates only
that isolated schema, runs simultaneous approval processes, checks one 200/one
409 and exactly one booking's inventory, then drops only that test database. It
requires CREATE/DROP DATABASE permissions. It never clears the application DB.

Next: Stage 09 quote/approval lifecycle and Stage 10 portal. Calendar currently
contains maintenance events and any internally confirmed inventory; empty calendar
does not mean configured rates, approval or full booking operation is complete.


## Customer requests and admin approval — 26 November 2026

The public Book Your Stay actions and room-specific Request links now lead to
`/booking` (login/registration required). Selected dates survive sign-in and
registration. The old seven-step non-saving preview has been replaced by the
actual request flow. There is one room per request in this implementation; the
owner's decision about a future multi-room flow is still deferred. The misleading
home room-count control is removed rather than silently ignoring a count.

1. Customer selects published room/dates/guest counts; backend rechecks capacity
   and inventory and creates an owned, 15-minute server quote.
2. Customer reviews saved nightly LKR/USD/FX and policy snapshots, supplies guest
   name/international phone, and explicitly accepts terms. Email comes from the
   authenticated account, never an arbitrary submitted recipient.
3. Submit locks the quote and room, checks expiry and latest price/policy, then
   creates a pending request with a unique reference. A quote can create only one
   booking. Exact duplicate submission returns the same booking; conflicting
   payload replay returns 409. Browser totals/status fields are ignored.
4. Owner/manager/reception reviews Admin > Bookings. Approval locks the booking
   and room, rechecks current dates/capacity/rates/terms, then calls the central
   allocation service. Confirmed nights appear in the calendar immediately.
5. Changed settlement economics or property terms generate a proposed revision,
   keep status pending and allocate nothing. My Bookings shows the proposal.
   Customer accepts the displayed revision hash; if rates changed yet again, a
   new proposal replaces it without acceptance/confirmation. Admin must approve
   after acceptance. FX-only changes do not reprice LKR settlement; accepted FX
   snapshots remain historical display information.
6. Rejection records a reason; customer can cancel their own pending request.
   Confirmed cancellations/changes require property review and are not exposed
   as automatic refund/cancellation operations. Priced bookings cannot be changed
   through the earlier low-level inventory change method without a new workflow.

Owner-only Admin > Booking policies configures the actual check-in/out times and
cancellation/refund text. Null/unconfigured policies block quote creation; no
property rules or prices are seeded by this feature. Confirmed quotes and terms
remain unchanged when current rates/policies are edited.

Customer My Bookings details are scoped to user_id and UUID reference; customers
cannot see other customers' requests/quotes. Editor/finance/housekeeping cannot
approve bookings. Internal submission hashes are not serialized in API output.
Account deletion with booking records returns an explicit review-needed error,
not a foreign-key failure; unused quotes cascade on account deletion. Retention
policy remains a property decision.

Same-origin session/CSRF JSON routes are provided at POST /api/v1/quotes,
POST /api/v1/bookings, GET /api/v1/me/bookings and POST
/api/v1/admin/bookings/{reference}/approve or /reject. OpenAPI marks this live
subset; other draft endpoints are not promised as implemented. External token
access, PDFs, manual payments, guest check-in/out, no-show and full front-office
operations remain later work.

Request, confirmation, revision, rejection and cancellation notifications enqueue
only after commit. BookingNotice retries delivery; queue failures are logged and
never roll back a committed booking. Start `php artisan queue:work` for database
queue processing. Current local mailer is log, so messages are not delivered to
live inboxes until SMTP is configured. Customer portal remains the source of
current booking state. Confirmation PDF is not included in this change.

Validation includes owned/expired quotes, duplicate and altered-payload replay,
revised prices/terms, unauthorized approval, inventory conflicts and calendar
visibility. The opt-in real MySQL concurrency test also accepts `workflow` to test
two full approval workflows against the same nights in its isolated schema.
