# LHDN MyInvois e-Invoice Integration

## Goal

Sandbox-validated end-to-end integration with LHDN MyInvois (Malaysia e-Invoice
portal) for the membership system. Supports document submission, polling,
72-hour cancellation, refund-note flow, and QR-coded PDF invoices.

## Status

**Pass 1 — Stub-mode framework (this branch)**

- DB schema: `einvoice_documents` table; LHDN fields on `members`, `membership_plans`, `invoices`, `settings`
- Libraries:
  - `App\Libraries\Einvoice\UblDocumentBuilder` — UBL 2.1 JSON v1.0 generation (Invoice/Credit/Debit/Refund Note)
  - `App\Libraries\Einvoice\MyInvoisClient` — OAuth2 + `submitDocuments` + `getSubmission` + `cancelDocument`
  - `App\Libraries\Einvoice\EInvoiceService` — orchestration + audit + 72h cancel window enforcement
  - `App\Libraries\Einvoice\QrRenderer` — base64 PNG QR for IRBM portal URL
- Controller actions: `Invoices::einvoiceSubmit`, `::einvoiceCancel`, `::einvoiceRefresh`, `Payments::reverse` (auto refund-note)
- Views: invoice show page LHDN panel; PDF template QR + IRBM block; member/plan forms with LHDN buyer-identity & classification fields
- Settings: 17 `einvoice.*` keys seeded with sandbox defaults
- Stub mode (`einvoice.environment = stub`) returns synthetic `valid` responses with no network call — all flows fully exercisable offline
- Smoke tests: `tools/einvoice_smoke.php` and `tools/einvoice_pdf_smoke.php` (run after `php spark migrate:refresh && php spark db:seed`)

**Pass 2 — Live sandbox (pending user input)**

User to provide:

1. MyInvois sandbox `client_id` / `client_secret` (via `MYINVOIS_CLIENT_ID` /
   `MYINVOIS_CLIENT_SECRET` env vars)
2. Real supplier identity values (TIN, BRN, MSIC, registered address) — set
   via `/settings` UI under the **Einvoice** group
3. Flip `einvoice.environment` from `stub` to `sandbox`

Then we'll run an end-to-end test on the MyInvois preprod portal.

## Sandbox URLs

- Portal: `https://preprod.myinvois.hasil.gov.my`
- API base: `https://preprod-api.myinvois.hasil.gov.my`
- Token endpoint: `POST {api}/connect/token`
- Submit: `POST {api}/api/v1.0/documentsubmissions`
- Get submission: `GET {api}/api/v1.0/documentsubmissions/{submissionUid}`
- Cancel: `PUT {api}/api/v1.0/documents/state/{uuid}/state`

Production URLs differ only in host (`api.myinvois.hasil.gov.my`).

## Default Values (configurable)

| Setting | Default | Notes |
|--|--|--|
| `einvoice.environment` | `sandbox` | Use `stub` for offline development |
| `einvoice.supplier.tin` | `EI00000000010` | LHDN generic individual sandbox TIN |
| `einvoice.supplier.brn` | `202001012345` | Sample value |
| `einvoice.supplier.msic` | `94991` | Activities of membership organisations n.e.c. |
| `einvoice.supplier.state` | `14` | WP Kuala Lumpur |
| `einvoice.supplier.country` | `MYS` | ISO 3166-1 alpha-3 |
| Default plan classification | `022` | "Others" — confirm with your accountant |
| Default tax type | `06` | Not Applicable |
| Default unit | `MON` | Monthly |

## Document Types

| Code | Name | Trigger |
|--|--|--|
| 01 | Invoice | Auto-issued on member registration / renewal |
| 02 | Credit Note | (manual, future) |
| 03 | Debit Note | (manual, future) |
| 04 | Refund Note | Auto on `Payments::reverse` if original invoice has a validated e-Invoice |

## 72-Hour Cancellation Window

Stored on `invoices.einvoice_cancellable_until` at validation time. The Cancel
form is rendered on the invoice show page only when `now < cancellable_until`.
After expiry, a credit note is the only path to reverse — that's outside Pass 1
scope.

## Audit Trail

Every submission writes a row in `einvoice_documents` containing:

- Full UBL JSON request payload
- Raw API response (JSON-encoded)
- Error payload (if any)
- IRBM `submissionUid`, `uuid`, `longId`
- `environment` (stub / sandbox / prod) for traceability
