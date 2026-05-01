# Database Schema

All tables use `InnoDB`, charset `utf8mb4`, collation `utf8mb4_unicode_ci`.
Every entity table has `created_at`, `updated_at`, and (where indicated) `deleted_at` for soft delete.

## `users`
| col | type | notes |
| --- | --- | --- |
| id | INT PK auto | |
| name | VARCHAR(120) | |
| username | VARCHAR(80) UNIQUE | |
| email | VARCHAR(160) UNIQUE | |
| password_hash | VARCHAR(255) | bcrypt |
| role_id | INT FK roles.id | |
| status | ENUM('active','disabled') | default 'active' |
| last_login_at | DATETIME NULL | |
| reset_token | VARCHAR(64) NULL | |
| reset_expires_at | DATETIME NULL | |
| created_at, updated_at, deleted_at | | soft delete |

## `roles`
| col | type | notes |
| --- | --- | --- |
| id | INT PK auto | |
| name | VARCHAR(80) | |
| slug | VARCHAR(80) UNIQUE | e.g. `super_admin` |
| description | VARCHAR(255) | |
| created_at, updated_at, deleted_at | | soft delete |

## `permissions`
| col | type |
| --- | --- |
| id | INT PK auto |
| slug | VARCHAR(80) UNIQUE |
| group | VARCHAR(60) |
| description | VARCHAR(255) |

## `role_permissions`
Composite PK `(role_id, permission_id)`.

## `membership_plans`
| col | type | notes |
| --- | --- | --- |
| id | INT PK auto | |
| code | VARCHAR(40) UNIQUE | e.g. `STD-1Y` |
| name | VARCHAR(120) | |
| price | DECIMAL(12,2) | |
| duration_months | INT | |
| description | TEXT NULL | |
| is_active | TINYINT(1) | default 1 |
| created_at, updated_at, deleted_at | | soft delete |

## `members`
| col | type | notes |
| --- | --- | --- |
| id | INT PK auto | |
| membership_id | VARCHAR(20) UNIQUE | `MEM-0001` |
| name | VARCHAR(160) | |
| ic_no | VARCHAR(40) NULL | |
| email | VARCHAR(160) NULL | |
| phone | VARCHAR(40) NULL | |
| address | TEXT NULL | |
| plan_id | INT FK membership_plans.id | |
| joined_date | DATE | |
| expiry_date | DATE | computed = joined_date + plan.duration |
| status | ENUM('active','expired','suspended') | recalculated by cron |
| photo_path | VARCHAR(255) NULL | |
| notes | TEXT NULL | |
| created_by | INT FK users.id | |
| created_at, updated_at, deleted_at | | soft delete |

## `payments`
| col | type | notes |
| --- | --- | --- |
| id | INT PK auto | |
| member_id | INT FK members.id | |
| invoice_id | INT FK invoices.id NULL | filled on auto-match |
| amount | DECIMAL(12,2) | |
| payment_date | DATE | |
| method | ENUM('cash','transfer','card','cheque','online','other') | |
| reference_no | VARCHAR(120) NULL | |
| proof_path | VARCHAR(255) NULL | uploaded file |
| status | ENUM('pending','confirmed','rejected','reversed') | default 'pending' (`reversed` added by LHDN migration) |
| notes | TEXT NULL | |
| approved_by | INT FK users.id NULL | |
| approved_at | DATETIME NULL | |
| receipt_id | INT FK receipts.id NULL | filled on confirm |
| created_by | INT FK users.id | |
| created_at, updated_at, deleted_at | | soft delete |

## `invoices`
| col | type | notes |
| --- | --- | --- |
| id | INT PK auto | |
| invoice_no | VARCHAR(30) UNIQUE | `INV-2026-0001` |
| member_id | INT FK members.id | |
| plan_id | INT FK membership_plans.id NULL | |
| amount | DECIMAL(12,2) | subtotal |
| tax_percent | DECIMAL(5,2) | from settings |
| tax_amount | DECIMAL(12,2) | |
| total | DECIMAL(12,2) | |
| status | ENUM('draft','issued','paid','cancelled') | |
| issued_at | DATE | |
| due_at | DATE | |
| pdf_path | VARCHAR(255) NULL | |
| notes | TEXT NULL | |
| created_by | INT FK users.id | |
| created_at, updated_at, deleted_at | | soft delete |

## `receipts`
| col | type | notes |
| --- | --- | --- |
| id | INT PK auto | |
| receipt_no | VARCHAR(30) UNIQUE | `RCPT-2026-0001` |
| payment_id | INT FK payments.id | |
| invoice_id | INT FK invoices.id NULL | |
| member_id | INT FK members.id | |
| amount | DECIMAL(12,2) | |
| issued_at | DATETIME | |
| pdf_path | VARCHAR(255) NULL | |
| created_by | INT FK users.id | |
| created_at, updated_at | | |

## `settings`
| col | type | notes |
| --- | --- | --- |
| id | INT PK auto | |
| `key` | VARCHAR(120) UNIQUE | |
| value | TEXT NULL | |
| type | ENUM('string','int','decimal','bool','file','json') | default 'string' |
| group | VARCHAR(60) NULL | |
| created_at, updated_at | | |

## `audit_logs`
| col | type | notes |
| --- | --- | --- |
| id | BIGINT PK auto | |
| user_id | INT FK users.id NULL | |
| action | ENUM('create','update','delete','restore','login','logout','approve','reject') | |
| entity | VARCHAR(60) | e.g. `members` |
| entity_id | VARCHAR(60) NULL | |
| old_values | JSON NULL | |
| new_values | JSON NULL | |
| ip | VARCHAR(45) NULL | |
| user_agent | VARCHAR(255) NULL | |
| created_at | DATETIME | indexed |

## `notifications`
| col | type | notes |
| --- | --- | --- |
| id | BIGINT PK auto | |
| user_id | INT FK users.id NULL | NULL = broadcast |
| type | VARCHAR(60) | e.g. `payment.pending` |
| title | VARCHAR(160) | |
| message | TEXT | |
| link | VARCHAR(255) NULL | |
| read_at | DATETIME NULL | |
| created_at | DATETIME | |

## `member_documents`
| col | type | notes |
| --- | --- | --- |
| id | INT PK auto | |
| member_id | INT FK members.id | |
| file_path | VARCHAR(255) | |
| file_name | VARCHAR(160) | |
| mime | VARCHAR(80) | |
| uploaded_by | INT FK users.id | |
| created_at | DATETIME | |

## `dropdown_options`
| col | type | notes |
| --- | --- | --- |
| id | INT PK auto | |
| category | VARCHAR(60) | e.g. `country`, `payment_method`, `state` |
| label | VARCHAR(120) | Display text shown in form |
| value | VARCHAR(80) | Stored value submitted by form |
| sort_order | INT | Default 0, determines display order |
| is_active | TINYINT(1) | Default 1, inactive options hidden from forms |
| created_at | DATETIME | |
| updated_at | DATETIME | |

Indexes: `(category, is_active, sort_order)` for fast `byCategory()` lookups, and `UNIQUE(category, value)` so the seeder is idempotent.

Seeded categories: `payment_method`, `registration_type`, `country`, `state`, `tax_type`.

