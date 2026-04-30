# MySmartMemberHub

A complete membership management system built on **CodeIgniter 4** + **MySQL**.

It covers six modules:

1. **Member Management** — register members, auto Membership ID (`MEM-0001`), renewal, status (active / expired).
2. **Payment Recording** — key in payments, upload proof, auto-match payments to invoices, payment history.
3. **Auto Invoice** — auto invoice number (`INV-2026-0001`), PDF, email / download.
4. **Auto Receipt** — automatic receipt generation when a payment is confirmed (`RCPT-2026-0001`), PDF.
5. **Admin Dashboard** — KPIs (active / expired / expiring members, today / month collection, pending approvals), member search.
6. **Finance Export** — Excel exports (members, payments, invoices, receipts), monthly statement, outstanding invoices report.

…and the cross-cutting requirements:

- Authentication (email **or** username login, bcrypt, forgot/reset password, idle session timeout).
- **RBAC** with 4 default roles (Super Admin / Membership Staff / Finance / Manager) and 22 permissions across 7 groups.
- CRUD with **soft delete** for every major entity.
- Server-side **search / filter / pagination** on every listing.
- **Auto numbering** with atomic `SELECT … FOR UPDATE` so there are no duplicates under load.
- **Audit trail** for every CREATE / UPDATE / DELETE (user, action, entity, old & new values, IP, user-agent).
- File upload (payment proof, member photo, branding logo).
- In-app notification system (with email stubs ready to wire up).
- Settings UI for company info, logo, invoice footer, tax %, payment instructions.

## Documentation

| Doc | Purpose |
| --- | ------- |
| [`docs/PROJECT_PLAN.md`](docs/PROJECT_PLAN.md) | Modules, RBAC, default credentials, local setup. |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | File structure, layer diagram, conventions, audit-trail mechanism. |
| [`docs/DATABASE_SCHEMA.md`](docs/DATABASE_SCHEMA.md) | Schema for all 14 tables, indexes, foreign keys. |

## Quick start

```bash
composer install
cp env .env

# adjust .env to match your DB:
#   database.default.database = mysmartmemberhub
#   database.default.username = mshub
#   database.default.password = ...
#   app.baseURL = 'http://localhost:8080/'

# create db & user (mysql -u root):
#   CREATE DATABASE mysmartmemberhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
#   CREATE USER 'mshub'@'localhost' IDENTIFIED BY 'mshub_local_pw';
#   GRANT ALL ON mysmartmemberhub.* TO 'mshub'@'localhost';

php spark migrate
php spark db:seed DatabaseSeeder

php spark serve --port 8080
# open http://localhost:8080/
```

### Default accounts

| Role | Username | Password |
| --- | --- | --- |
| Super Admin       | `admin`   | `Admin@123`   |
| Membership Staff  | `staff`   | `Staff@123`   |
| Finance           | `finance` | `Finance@123` |
| Manager           | `manager` | `Manager@123` |

### Smoke tests after setup

After running migrations + seeds the following end-to-end flow has been verified:

1. `POST /login` with admin creds → 303 to `/`.
2. `POST /members/store` → member `MEM-0001` created **and** invoice `INV-2026-0001` auto-generated.
3. `POST /payments/store` → payment recorded as `pending` (auto-matched to the invoice).
4. `POST /payments/{id}/approve` → payment `confirmed`, receipt `RCPT-2026-0001` issued, PDF saved to `writable/uploads/receipts/`, invoice flipped to `paid`, audit-trail rows written.
5. `GET /invoices/{id}/pdf`, `GET /receipts/{id}/pdf`, `GET /members/export`, `GET /reports/export/monthly` all return the expected file (PDF / xlsx).
6. RBAC enforced: Manager + Finance get `403` on `/users` / `/settings`.
