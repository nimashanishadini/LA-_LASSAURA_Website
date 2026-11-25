# Room and booking foundation

One property contains four individually bookable rooms: Sandy and Shelly (family),
Coral and Pearl (double). Existing Villa/villas names remain internally for media
and amenity compatibility; UI terminology should be Room. Never hard-code IDs.

## Unconfirmed business decisions

- Adult, child and total capacity for each room. Null means unknown, not unlimited.
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

## Next stages

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
