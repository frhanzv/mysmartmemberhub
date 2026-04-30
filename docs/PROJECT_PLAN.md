# MySmartMemberHub — Project Plan

A CodeIgniter 4 + MySQL membership management system covering member registration, payments, invoicing, receipts, finance reporting and admin tooling.

This document is the single source of truth for **what** is being built and **how** the project is organised. Future prompts should read this first.

---

## 1. Tech Stack

| Layer | Choice | Rationale |
| --- | --- | --- |
| Language | PHP 8.1+ | Required by CodeIgniter 4.6 |
| Framework | CodeIgniter 4 | Per user requirement |
| Database | MySQL 5.7+ / MariaDB 10.4+ | Per user requirement |
| Frontend | Bootstrap 5 + vanilla JS | Lightweight admin UI, no build step |
| PDF | `dompdf/dompdf` | Invoices & receipts |
| Excel | `phpoffice/phpspreadsheet` | Finance export & member import |
| Auth | Custom (sessions + password_hash) | Lightweight, full control over RBAC |
| Mail | CI4 `Email` service | Notifications (SMTP configurable) |

---

## 2. Modules (per user spec)

### Module 1 — Member Management
- Register member (CRUD + soft delete)
- Auto Membership ID `MEM-0001`
- Renewal date (computed from plan duration)
- Status: `active` / `expired` / `suspended` (auto recalculated daily)

### Module 2 — Payment Recording
- Key in payment / upload proof (file upload)
- Auto-match payment to outstanding invoice (by member + amount + reference)
- Payment history per member
- Status: `pending` / `confirmed` / `rejected`

### Module 3 — Auto Invoice
- Auto invoice no. `INV-YYYY-0001`
- PDF invoice via dompdf
- Email or download

### Module 4 — Auto Receipt
- Auto-generate receipt the moment payment is confirmed
- Running receipt no. `RCPT-YYYY-0001`
- PDF receipt via dompdf

### Module 5 — Admin Dashboard
- Total payment today / month
- Pending payment count
- Expired / expiring members
- Global member search

### Module 6 — Finance Export
- Excel export (members, payments, invoices, receipts)
- Monthly report
- Outstanding payment list

---

## 3. Cross-Cutting Requirements

### 3.1 Authentication & Security
- Email **or** username login
- `password_hash()` (bcrypt) for passwords
- Forgot password flow with token table + email
- Session timeout (`sessionExpiration` configurable)
- RBAC via `Permission` filter on routes/controllers
- Default roles: **Super Admin**, **Membership Staff**, **Finance**, **Manager**

### 3.2 RBAC Permissions (seeded)
Grouped slugs (used as filter arguments):
- `member.create`, `member.edit`, `member.delete`, `member.view`
- `plan.manage`
- `payment.create`, `payment.edit`, `payment.approve`, `payment.delete`
- `invoice.generate`, `invoice.view`, `invoice.email`
- `receipt.generate`, `receipt.view`
- `report.export`, `report.view`
- `user.manage`, `role.manage`
- `setting.manage`
- `audit.view`

| Role | Permissions |
| --- | --- |
| Super Admin | `*` (all) |
| Membership Staff | member.\*, payment.create, payment.edit, invoice.view, receipt.view |
| Finance | invoice.\*, receipt.\*, payment.approve, payment.view, report.\* |
| Manager | dashboard view + report.view + audit.view (read-only) |

### 3.3 CRUD with soft delete
All major entities have `deleted_at` and use CI4 model `useSoftDeletes = true`.

### 3.4 Search / Filter / Pagination
Each listing page (members, payments, invoices, receipts, users, audit log) supports:
- `?q=` keyword search across key columns
- Filter dropdowns (status, month, plan, role)
- Server-side pagination via CI4 `paginate()`

### 3.5 Export / Import
- Export: Excel (.xlsx) & PDF on relevant listings
- Monthly statement export
- Bulk member import from `.xlsx` (template provided)
- Bulk renewal: tick-list → renew with current plan

### 3.6 Auto Numbering (`App\Libraries\AutoNumber`)
Atomic counters via `settings` table keys:
- `counter.invoice.{YYYY}`
- `counter.receipt.{YYYY}`
- `counter.member`

Format: `INV-2026-0001`, `RCPT-2026-0001`, `MEM-0001`.

### 3.7 Audit Trail (`App\Models\AuditLogModel`)
All Create/Update/Delete events on members/plans/payments/invoices/receipts/users/roles/settings are logged with:
- `user_id`, `action`, `entity`, `entity_id`, `old_values` (JSON), `new_values` (JSON), `ip`, `user_agent`, `created_at`

Viewer at `/audit-log` (permission: `audit.view`).

### 3.8 File Upload
- Payment proof → `writable/uploads/proofs/`
- Member documents → `writable/uploads/members/{id}/`
- Profile photo → `writable/uploads/photos/`
- All uploads MIME-validated and size-capped via CI4 validation rules.

### 3.9 Notifications
In-app notifications table. Hooks:
- Membership expiring in ≤14 days
- Payment pending approval
- Receipt ready
Email channel uses CI4 Email; WhatsApp is a future placeholder.

### 3.10 Dashboard / Analytics
Cards + charts (Chart.js CDN):
- Active members
- Expired members
- Monthly collection (last 12 months)
- Pending approvals

### 3.11 System Settings
Key-value table editable from `/settings`:
- `company.name`, `company.address`, `company.phone`, `company.email`
- `branding.logo` (uploaded)
- `invoice.footer`, `invoice.tax_percent`
- `payment.instructions`
- `system.session_timeout_minutes`

### 3.12 Configurable Dropdown Options
Database-driven dropdown management at `/settings/dropdown-options` (permission: `setting.manage`).
Admins can CRUD options per category. Forms load options dynamically instead of hardcoding.

Seeded categories:
- `payment_method` — cash, transfer, card, cheque, online, other
- `registration_type` — Individual, Company, Government, Foreign
- `country` — 17 countries (MYS, SGP, IDN, …)
- `state` — 17 Malaysian states with LHDN codes (01–17)
- `tax_type` — LHDN tax types (01–06, E)

New categories can be created on the fly from the UI.

---

## 4. URL Map

| URL | Permission |
| --- | --- |
| `/login`, `/logout`, `/forgot-password`, `/reset-password/:token` | public |
| `/` (dashboard) | authenticated |
| `/members[/...]` | `member.*` |
| `/plans[/...]` | `plan.manage` |
| `/payments[/...]` | `payment.*` |
| `/invoices[/...]` | `invoice.*` |
| `/receipts[/...]` | `receipt.*` |
| `/reports`, `/reports/export/:type` | `report.*` |
| `/users[/...]`, `/roles[/...]` | `user.manage` / `role.manage` |
| `/settings` | `setting.manage` |
| `/settings/dropdown-options[/...]` | `setting.manage` |
| `/audit-log` | `audit.view` |
| `/notifications` | authenticated |

---

## 5. Default Credentials (seeded)

After `php spark migrate && php spark db:seed DatabaseSeeder`:

| Role | Email | Password |
| --- | --- | --- |
| Super Admin | `admin@mysmartmemberhub.test` | `Admin@123` |
| Membership Staff | `staff@mysmartmemberhub.test` | `Staff@123` |
| Finance | `finance@mysmartmemberhub.test` | `Finance@123` |
| Manager | `manager@mysmartmemberhub.test` | `Manager@123` |

**Change these immediately in production.**

---

## 6. Local Setup

```bash
# 1. Install PHP + composer + MySQL/MariaDB
sudo apt install -y php php-cli php-mbstring php-xml php-curl php-mysql \
                    php-intl php-zip php-gd php-bcmath mariadb-server unzip
curl -sS https://getcomposer.org/installer | sudo php -- \
    --install-dir=/usr/local/bin --filename=composer

# 2. Install dependencies
cd mysmartmemberhub
composer install

# 3. Create database
sudo mysql -e "CREATE DATABASE mysmartmemberhub CHARACTER SET utf8mb4 \
               COLLATE utf8mb4_unicode_ci;
               CREATE USER 'mshub'@'localhost' IDENTIFIED BY 'mshub_local_pw';
               GRANT ALL ON mysmartmemberhub.* TO 'mshub'@'localhost';
               FLUSH PRIVILEGES;"

# 4. Configure env
cp env .env
# edit .env -> CI_ENVIRONMENT=development, database.default.* values

# 5. Migrate + seed
php spark migrate
php spark db:seed DatabaseSeeder

# 6. Run
php spark serve  # http://localhost:8080
```

---

## 7. Repository Layout

See [`docs/ARCHITECTURE.md`](ARCHITECTURE.md) for the full file map and
[`docs/DATABASE_SCHEMA.md`](DATABASE_SCHEMA.md) for the schema.
