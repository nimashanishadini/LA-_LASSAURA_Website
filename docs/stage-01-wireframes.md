# Stage 01.02 — screen and navigation contract

Low-fidelity text wireframes for review, not approved Figma screens. Public UI
already exists in Blade; the complete Figma Make flow has not been verified.

## Navigation and responsive proposal

Public: Home / Rooms / Experiences / Gallery / Location / Contact; add explicit
Restaurant/Menu, Offers and Policies destinations when CMS pages are implemented.
About/facilities may be home sections with accessible direct links. Customer:
My Bookings / Booking Details / Profile / Sign out. Admin: Dashboard / Rooms
now; later Calendar / Bookings / Customers / Payments / Rates / Restaurant /
CMS / Reports / Settings, each gated by permission and actual availability.

CSS uses a project-specific md=50rem (800px); retain it. Use 640px sm, 800px md,
1024px lg and 1280px xl. Current public nav becomes desktop at xl; admin sidebar
at lg. Review at 375, 640, 800, 1024, 1280 and 1440px. Tables/calendar scroll
inside their containers. Stack cards/forms on mobile; preserve labels, keyboard
focus, visible errors, touch targets, Escape close and focus return for dialogs.

## Public home

[Announcement / brand / navigation / currency / Book your stay]
[Hero image + title + CTA]
[Check-in | check-out | adults | children | search]
[About + facilities]
[Room previews: cover, category, name, capacity, from-price]
[Restaurant/menu + experiences + gallery + location/contact]
[Footer / policies / verified contact links]

Currency preference must be shared across navbar/footer/quotes. Both LKR and USD
must remain visible where the guide requires; selecting USD is not USD settlement.

## Room listing and detail

Listing: [filters] [Sandy] [Shelly] [Coral] [Pearl] as database-backed cards.
Detail: [room name/category] [cover + ordered gallery/lightbox]
[description] [adult/child/total capacity] [amenities]
[dates + guests] [server quote LKR/USD + FX age] [request booking]
Only active rooms are public; inactive/nonexistent detail URLs return 404.
No synthetic bedroom photos or invented capacities/prices.

## Booking flow

1. Dates and guest counts; validate stay dates in Asia/Colombo.
2. Backend availability results; explicit room selection.
3. Server quote: nightly lines, nights, LKR total, USD display, FX timestamp.
4. Login/register (preserve valid room/date selection), guest contact details.
5. Review current quote and cancellation/check-in/out terms; explicit acceptance.
6. Submit once with idempotency key; show reference and Pending status.
7. My Bookings shows request; confirmation arrives only after admin approval.

Multi-room branch is not approved: do not turn the existing room-count UI into
multiple independent confirmed requests. Finalize D03 before implementation.
Show 409 conflict and refresh options; changed quote needs fresh acceptance.
Never label a preview or pending request Confirmed. No card collection.

## Customer portal

[My Bookings: reference | room | stay | booking status | payment balance]
[Details: agreed quote/policy | event history | confirmation PDF]
[Request cancellation with reason] [past stays] [profile]
Empty/loading/error states; ownership checks on every detail/PDF endpoint.

## Admin dashboard and room management

[Collapsible dark sidebar] [header: staff name/role + sign out]
[Total rooms] [Published] [Draft] [Photos] — implemented counts.
Later: [available today] [occupied] [pending] [arrivals] [departures]
[Room overview] [operational calendar once inventory exists]

Rooms: [cards + cover + draft status + Edit]
Edit: [name/slug/category] [capacity] [amenities] [descriptions/status]
[upload photographs] [gallery: alt text / order / cover / remove]
Draft allows unknown capacity; publication requires configured capacity.

## Calendar and booking review (future)

[Date navigator | day/week/month | filters | New manual booking]
             Mon  Tue  Wed  Thu  Fri
Sandy        [ confirmed stay ]
Shelly                  [maintenance]
Coral           [confirmed]
Pearl        [free cells]
[Pending requests in separate queue / nonblocking indicators]

Use FullCalendar as the visual client of central inventory, not the authority.
Legend includes text as well as colour: confirmed green, maintenance grey,
pending orange. Orange must not imply held inventory. Check-out is exclusive.
Click booking: minimum guest details, quote, source, history and permitted
approve/reject/check-in/out/change/cancel actions. Conflict errors stay visible.
