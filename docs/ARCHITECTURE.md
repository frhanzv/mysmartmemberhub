# Architecture & File Map

## Layers

```
HTTP request
   │
   ▼
[Filter: Auth] ──► [Filter: Permission(slug)]
   │                          │
   ▼                          ▼
Controller ──► Model ──► Database (MySQL)
   │              │
   │              └──► Library (AutoNumber / PdfGenerator / ExcelExporter)
   │                                        │
   ▼                                        ▼
View (Bootstrap 5)                writable/ (PDFs, uploads)
```

Audit log writes are triggered by model `afterInsert/afterUpdate/afterDelete`
events through the `AuditableTrait`.

## Directory Map

```
mysmartmemberhub/
├── docs/                       # This documentation
│   ├── PROJECT_PLAN.md
│   ├── ARCHITECTURE.md
│   └── DATABASE_SCHEMA.md
├── app/
│   ├── Config/
│   │   ├── Routes.php          # All app routes
│   │   ├── Filters.php         # Registers auth + permission filters
│   │   ├── Database.php        # Default group (env-overridable)
│   │   └── Validation.php
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── Auth.php            # login / logout / forgot / reset
│   │   ├── Dashboard.php
│   │   ├── Members.php
│   │   ├── Plans.php
│   │   ├── Payments.php
│   │   ├── Invoices.php
│   │   ├── Receipts.php
│   │   ├── Reports.php
│   │   ├── Users.php
│   │   ├── Roles.php
│   │   ├── Settings.php
│   │   ├── DropdownOptions.php # CRUD dropdown option categories
│   │   ├── AuditLog.php
│   │   └── Notifications.php
│   ├── Filters/
│   │   ├── AuthFilter.php
│   │   └── PermissionFilter.php
│   ├── Models/
│   │   ├── BaseModel.php       # AuditableTrait wired up
│   │   ├── UserModel.php
│   │   ├── RoleModel.php
│   │   ├── PermissionModel.php
│   │   ├── MemberModel.php
│   │   ├── PlanModel.php
│   │   ├── PaymentModel.php
│   │   ├── InvoiceModel.php
│   │   ├── ReceiptModel.php
│   │   ├── SettingModel.php
│   │   ├── AuditLogModel.php
│   │   ├── NotificationModel.php
│   │   └── DropdownOptionModel.php
│   ├── Libraries/
│   │   ├── AutoNumber.php
│   │   ├── PdfGenerator.php
│   │   └── ExcelExporter.php
│   ├── Helpers/
│   │   ├── auth_helper.php
│   │   ├── permission_helper.php
│   │   └── format_helper.php
│   ├── Database/
│   │   ├── Migrations/
│   │   │   └── 2026-01-01-000001_InitialSchema.php   # everything
│   │   └── Seeds/
│   │       ├── DatabaseSeeder.php
│   │       ├── RolePermissionSeeder.php
│   │       ├── UserSeeder.php
│   │       ├── PlanSeeder.php
│   │       ├── SettingSeeder.php
│   │       └── DropdownOptionSeeder.php
│   └── Views/
│       ├── layouts/main.php           # Sidebar + topbar
│       ├── auth/{login,forgot,reset}.php
│       ├── dashboard/index.php
│       ├── members/{index,form,show,import}.php
│       ├── plans/{index,form}.php
│       ├── payments/{index,form,show}.php
│       ├── invoices/{index,show}.php
│       ├── receipts/{index,show}.php
│       ├── reports/{index}.php
│       ├── users/{index,form}.php
│       ├── roles/{index,form}.php
│       ├── settings/index.php
│       ├── dropdown_options/{index,form}.php
│       ├── audit/index.php
│       ├── notifications/index.php
│       ├── partials/{flash,pagination}.php
│       └── pdf/{invoice,receipt}.php  # dompdf templates
├── public/
│   └── index.php
└── writable/
    └── uploads/
        ├── proofs/
        ├── members/
        └── photos/
```

## Conventions

- **Controllers** are thin: validate → call model/library → redirect/render.
- **Models** extend `App\Models\BaseModel` which:
  - turns on `useTimestamps` and `useSoftDeletes`
  - emits an audit log event after every C/U/D
- **Permissions** are checked declaratively via filter alias on routes:
  ```php
  $routes->group('members', ['filter' => 'permission:member.view'], function ($r) { ... });
  ```
- **Auto-numbering** is fetched through `AutoNumber::next($key, $format)` which
  uses a row-locked update on the `settings` table to avoid duplicates.
- **PDF** rendering loads a `Views/pdf/*.php` template, then dompdf converts.
- **Excel** export streams via `PhpSpreadsheet` writer to download.

## Audit trail mechanism

`App\Models\BaseModel` registers callbacks:

```php
protected $afterInsert = ['logCreate'];
protected $afterUpdate = ['logUpdate'];
protected $afterDelete = ['logDelete'];
```

Each callback inserts into `audit_logs` with the diff between old/new values
serialised as JSON.

## Environment variables (`.env`)

```
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = mysmartmemberhub
database.default.username = mshub
database.default.password = mshub_local_pw
database.default.DBDriver = MySQLi
database.default.DBPrefix =

email.fromEmail = no-reply@mysmartmemberhub.test
email.fromName  = MySmartMemberHub

session.expiration = 7200
```
