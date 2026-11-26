# Stage 01 acceptance, review and handover

Stage 01 is not signed off. These are review criteria and future test contracts.

## Feature acceptance matrix

| ID | Scenario | Expected result | Evidence today |
| --- | --- | --- | --- |
| A01 | Four named room seeds, repeat run | No duplicates/overwrite; unknown capacity preserved | RoomFoundationTest |
| A02 | Customer or guest requests admin | Login redirect/403; no staff escalation at signup | AdminRoomTest |
| A03 | Reception attempts room edit | 403, unchanged room | AdminRoomTest |
| A04 | Authorized edit/image upload | Save, one cover, order maintained, old cover replaced | AdminRoomTest |
| A05 | Invalid upload/cross-room image access | Validation failure/404; no unrelated deletion | AdminRoomTest |
| A06 | Publish unknown/invalid capacity | Validation error | AdminRoomTest |
| A07 | Active room public details / inactive URL | Edited data visible / 404 | Not implemented |
| B01 | Same room/night concurrent approvals | Exactly one confirmation; loser gets 409 | Not implemented |
| B02 | Pending and adjacent stays | Pending does not block; checkout-day reuse succeeds | Not implemented |
| B03 | Confirm price then edit rates/FX/terms | Historical snapshots unchanged | Not implemented |
| B04 | FX outage/no historical rate | Stale display or unavailable USD, no invented price | Not implemented |
| B05 | Repriced approval | Requires customer acceptance before allocation | Not implemented |
| B06 | Extension/room change conflicts | Entire operation rolled back | Not implemented |
| B07 | Other customer booking/PDF | No information disclosed | Not implemented |
| B08 | Duplicate request/payment | One result; mismatched payload key returns 409 | Not implemented |
| B09 | Partial payment/refund | Ledger/receipt/balance reconcile; no deletion | Not implemented |
| B10 | Failed email | Confirmed booking remains; retry logged | Not implemented |
| B11 | Optional multi-room approval | All rooms allocate together or none, if approved | D03 pending |
| B12 | Mobile/keyboard/calendar | No page overflow; accessible errors/dialog controls | Browser acceptance pending |
| B13 | Mock channel duplicate/out-of-order events | Central inventory intact, conflicts reviewable | Not implemented |

Existing automated suite was last run with 34 passing tests/134 assertions.
That is evidence for current functionality only, not sign-off for planned tests.

## NAS and peer review procedure from the guide

Project lead must confirm access to Company NAS / Processing Project /
Active Project / La Lassaura. Proposed subfolders: Source, Daily Updates,
Database, Documentation, Tests, Releases. No NAS connection or upload has been
performed by this work.

Each developer uploads Daily Updates/YYYY-MM-DD/Dev-01 or Dev-02 with recoverable
source baseline, changed files/assets, migrations, lockfiles, test evidence and
progress report. Never overwrite the other developer. No .env/secrets/customer
exports; include .env.example and sanitized fixtures. A failed upload is reported
and retried. Review/test integrated local source before replacing Source; retain
last working snapshot. Do not run a database directly from the NAS share.

Daily report: developer, date, baseline, task IDs, completed/in progress, blockers,
commands/results, changed files, next step, reviewer and upload verification.
Reviewer checks authorization, validation, transactions, snapshots, tests and
migration recovery. Stage demo plus named reviewer acceptance is recorded.

## Stage 01 release gate

- [ ] D03 room cardinality decided and ER/API branch resolved.
- [ ] Required pages and low-fidelity wireframes/navigation reviewed.
- [ ] Role matrix, configuration ownership and booking states agreed.
- [ ] Rates/FX, guest fields, policy configuration and retention decisions assigned.
- [ ] API draft reviewed by frontend/backend developers.
- [ ] NAS names/access and peer-review ownership confirmed by project lead.
- [ ] Both developers approve scope, ER draft and API contract.

| Sign-off | Name | Date | Decision |
| --- | --- | --- | --- |
| Backend developer | Pending | Pending | Pending |
| Frontend developer | Pending | Pending | Pending |
| Project reviewer | Pending | Pending | Pending |
| Property owner (business decisions) | Pending | Pending | Pending |

Record approvals explicitly; creating this checklist does not tick its boxes.
