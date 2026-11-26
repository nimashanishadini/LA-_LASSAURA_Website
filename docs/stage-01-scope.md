# Stage 01.01 — scope and decisions

Source: La Lassaura developer guide Rev 2.0, 25 November 2026, interpreted with
owner clarification: one property, four rooms rather than four separate villas.
Status: draft for review. No commercial scope or inferred property rules added.

## Confirmed inventory and configuration contract

| Room | Category | Capacity source | Photography |
| --- | --- | --- | --- |
| Sandy | Family | Admin-configured adult/child/total limits | Separate room gallery |
| Shelly | Family | Admin-configured adult/child/total limits | Separate room gallery |
| Coral | Double | Admin-configured adult/child/total limits | Separate room gallery |
| Pearl | Double | Admin-configured adult/child/total limits | Separate room gallery |

Owner confirmed on 25 November: leave capacities configurable in admin. Category
alone never defines capacity. Unknown limits remain null; zero children means
no children, not unknown. Adult maximum >= 1; total maximum >= each individual
maximum and <= adult maximum + child maximum. New rooms start inactive.
Current admin implements these rules. Existing Villa table/model names remain
internal only, preserving media/amenity compatibility.

## Phase 01 core

- Public home, about, restaurant/menu, rooms/list/details, facilities, gallery,
  offers, location/contact, booking/cancellation policies and SEO metadata.
- CMS for text, menus, banners, photos, hours and verified contact/social links.
- Four-room inventory, room-specific images, capacity, rates and maintenance.
- LKR pricing and USD display with scheduled FX refresh and snapshot pricing.
- Availability, pending requests, manual approval, customer booking portal/PDF.
- Front office: manual-source bookings, arrivals/departures, check-in/out,
  extensions, room changes, cancellation/no-show and operational calendar.
- Manual payment ledger, partial balances, receipts, reversals and refunds.
- Reports, queued notifications, permissions, audit logs, backup/restore.
- Future payment/channel interfaces, disabled settings and mock contract tests.

Housekeeping workflow and table reservations are deferred unless explicitly
approved. Restaurant menu CMS is core. POS, kitchen tickets, stock/accounting,
live payment gateway and live OTA connections are outside Phase 01.

## Proposed permission contract

All checks must run server-side. Menu hiding alone is insufficient.

| Capability | Owner | Manager | Reception | Finance | Editor | Housekeeping |
| --- | --- | --- | --- | --- | --- | --- |
| Admin overview | Yes | Yes | Yes | Yes | Yes | Yes |
| Room content/images | Yes | Yes | No | No | Yes | No |
| Rates | Yes | Yes | Read | Read | No | No |
| Booking approval/operations | Yes | Yes | Yes | Read minimum | No | No |
| Payment/refund records | Yes | Yes | No | Yes | No | No |
| CMS/menu | Yes | Yes | No | No | Yes | No |
| Operational reports | Yes | Yes | Limited | Finance only | No | No |
| Roles/sensitive settings | Yes | No | No | No | No | No |
| Housekeeping tasks | Optional | Optional | Optional read | No | No | Optional |

Only dashboard access and room-management gates are implemented today. Other
permissions above are a proposal, not currently available modules. Customers
may view only their own profile/bookings/PDFs; no admin access.

## Property settings contract (editor is future work)

Check-in/out local times and cancellation/refund terms belong in the default
booking policy. Authorized property management configures them; do not invent
values. Terms shown to the customer must be snapshotted with each booking so
later edits cannot change agreed conditions. Actual bookings require configured
rules, rates and capacities. Draft room content can be edited beforehand.

## Decision register

| ID | Decision | State / owner |
| --- | --- | --- |
| D01 | Capacity is editable per room | Confirmed by owner; UI implemented |
| D02 | Check-in/out times and cancellation/refund values | Property owner configures before booking release |
| D03 | One room per request or multiple rooms per request | Owner explicitly deferred confirmation; skip dependent booking cardinality work. Current modal's room count does not establish backend scope |
| D04 | Proposed role matrix above | Reviewer/owner confirmation pending |
| D05 | LKR settlement; USD display | PDF baseline; USD settlement requires explicit approval |
| D06 | FX provider/refresh interval, discount/tax precedence | Owner/backend review pending |
| D07 | Required guest fields, ID collection and retention | Owner/security review pending |
| D08 | Optional housekeeping/table reservations | Deferred pending owner approval |
| D09 | Domain, SMTP, hosting and exact runtime versions | Deployment reviewer confirmation pending |
| D10 | NAS access/location and daily peer reviewer identities | Project lead confirmation pending |

No business value in this register is silently filled with demo data.
