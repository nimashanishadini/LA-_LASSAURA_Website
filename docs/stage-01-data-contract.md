# Stage 01.03 — logical data and lifecycle contract

Draft for review. This logical model is not a claim that all tables exist.

## Entity relationship mapping

| Entity | Relationship / integrity | State |
| --- | --- | --- |
| users | role string; policies restrict access; future roles/permissions tables if required | Exists, simple gates |
| customers | optional unique user_id; manual-source guest contact | Planned |
| villas | one property room; slug unique, category/capacity/active | Exists |
| villa_media | many per room; disk/path/alt/order/cover | Exists |
| amenities, villa_amenities | room many-to-many; composite unique key | Exists |
| booking_policies | unique default key; check-in/out, cancellation, multi-room option | Exists, no editor |
| villa_rates | room 1:N; date interval, LKR decimal, priority/discount rules | Planned |
| exchange_rates | LKR per USD > 0; source/time/stale/override actor | Planned |
| bookings | Internal room/stay/status/reference foundation exists; customer/source/quote/terms still planned | Partial |
| booking_price_lines | booking 1:N nightly LKR amounts, discounts/tax snapshots | Planned |
| booking_events | booking 1:N; actor, transition/reason/time | Planned |
| maintenance_blocks | room 1:N exclusive-end date interval/reason | Exists |
| villa_nights | unique(room_id represented by villa_id, stay_date); exactly one booking or maintenance owner | Exists |
| payments | booking 1:N; actor, currency, decimal amount, settlement/FX, reference, idempotency | Planned |
| payments.parent_payment_id | self-reference for reversals/refunds, never delete settled records | Planned |
| payment_events | provider/event unique deduplication key | Planned |
| audit_logs | actor + entity/action + restricted changed fields; no secrets | Planned |
| notifications | booking optional; recipient/type/delivery/retry state | Planned |
| cms_pages, gallery_items | editable content/SEO and ordered imagery | Planned |
| menu_categories, menu_items | category 1:N items, LKR price and active status | Planned |
| booking_sources | website/phone/WhatsApp/walk-in/manual OTA source | Planned |
| channels/channel_mappings | disabled provider; external room/rate-plan mapping | Planned |
| external_bookings | unique channel + external reference, booking FK | Planned |
| sync_events | channel events/outbox, retries/conflicts, unique event keys | Planned |

## Booking cardinality gate

Guide baseline: each booking references one room. Current database has no booking
table, so do not prematurely commit to this cardinality. If D03 approves multiple
rooms, add booking_rooms (booking 1:N room assignments with per-room guests and
nightly pricing); inventory rows reference the assignment. Lock every affected
room in stable ID order and allocate all-or-none. A request must never partially
confirm. Final ER/OpenAPI approval must resolve this branch first.

## Booking state transitions

| From | To | Actor/guard | Inventory effect |
| --- | --- | --- | --- |
| new | pending | Customer owns request or authorized staff; accepted valid quote | None |
| pending | confirmed | Authorized approval; availability, capacity, accepted price rechecked | Allocate atomically |
| pending | rejected | Authorized staff; reason recorded | None |
| pending | cancelled | Customer cancels own pending or permitted staff | None |
| confirmed | checked_in | Staff; property check-in policy | Retain |
| checked_in | checked_out | Staff; settlement rules | Retain historical nights |
| confirmed | cancelled | Staff after policy review; reason | Release eligible nights atomically |
| confirmed | no_show | Staff according to agreed policy | Apply documented release policy |

Customer cancellation of confirmed stays is a request, not immediate deletion or
automatic refund. Proposed representation: cancellation request with pending,
approved/rejected outcome and actor/reason; settle exact retention/refund policy
before coding. A room/date change or extension validates availability and price
acceptance, then swaps allocations in a single transaction. Disallow unlisted
status jumps with 409. Payment status is independent from booking status.

## Inventory and pricing invariants

- Stay [check_in, check_out), allowing adjacent stays on check-out day.
- Pending never owns villa_nights. Maintenance and confirmed stays share inventory.
- Lock room rows in stable order, allocate unique room/date rows, rollback all
  writes on conflict. Cancellation preserves historical records.
- UTC event timestamps; Asia/Colombo stay dates and local policy times.
- LKR is settlement baseline; USD = LKR / stored LKR-per-USD.
- DECIMAL database amounts, decimal strings in JSON; no floating point ledger.
- Sum nightly LKR values, then convert/round the total. Save nightly, FX and
  accepted terms snapshots. Rate edits affect new quotes only.
- Stale FX uses last valid rate and visible timestamp; no valid rate means USD
  unavailable, never invented. Decide whether LKR-only quote acceptance is allowed.
- Duplicate idempotent writes cannot create duplicate requests/payments; same key
  with different payload returns 409. Authorization precedes replay response.
- Queue notifications/outbox only after commit; email failure never undoes booking.

## HTTP and privacy conventions

Versioned /api/v1; same-origin session/CSRF, Sanctum only for approved token use.
401 unauthenticated, 403 forbidden, 404 missing/unauthorized resource where
appropriate, 409 conflict/stale/state/idempotency mismatch, 422 invalid input.
Envelopes: data and optional meta; errors: message, code and optional errors.
Never accept browser totals, arbitrary room capacity or arbitrary staff role.
Minimize guest ID data; retention and deletion/anonymization rules need review.
