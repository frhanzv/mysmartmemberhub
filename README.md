# MySmartMemberHub

A complete membership management system built on **CodeIgniter 4** + **MySQL/MariaDB**, with first-class **LHDN MyInvois e-Invoice** integration (sandbox-ready).

It covers six modules:

1. **Member Management** — register members, auto Membership ID (`MEM-0001`), renewal, status (active / expired).
2. **Payment Recording** — key in payments, upload proof, auto-match payments to invoices, reverse / refund.
3. **Auto Invoice** — auto invoice number (`INV-2026-0001`), PDF, email / download.
4. **Auto Receipt** — automatic receipt generation when a payment is confirmed (`RCPT-2026-0001`), PDF.
5. **Admin Dashboard** — KPIs (active / expired / expiring members, today / month collection, pending approvals).
6. **Finance Export** — Excel exports (members, payments, invoices, receipts), monthly statement, outstanding invoices.

…plus cross-cutting features:

- Authentication (email **or** username login, bcrypt, forgot/reset, idle session timeout).
- **RBAC** with 4 default roles (Super Admin / Membership Staff / Finance / Manager) and 22 permissions.
- CRUD with **soft delete**, server-side search / filter / pagination.
- **Auto numbering** with atomic `SELECT … FOR UPDATE` (no duplicates under load).
- **Audit trail** for every CREATE / UPDATE / DELETE.
- File upload (payment proof, member photo, branding logo).
- In-app notification system.
- **Module enable/disable** for Super Admin via `/settings`.
- **LHDN MyInvois e-Invoice** integration (UBL 2.1 builder, OAuth2, submit/poll/cancel, 72h cancel window, QR on PDF, refund-note flow).

## 📚 Documentation

| Doc | Purpose |
| --- | ------- |
| [`docs/API.md`](docs/API.md)             | **Every route, permission, request body, response, and end-to-end use case.** |
| [`docs/PROJECT_PLAN.md`](docs/PROJECT_PLAN.md) | Modules, RBAC, default credentials. |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | File structure, layer diagram, conventions. |
| [`docs/DATABASE_SCHEMA.md`](docs/DATABASE_SCHEMA.md) | Full schema (14 tables) with indexes & FKs. |

---

## 🚀 Deployment

The system is plain CodeIgniter 4 + MySQL — no Node, no compiled assets, no Redis. Anywhere PHP 8.1+ runs is enough.

| Stack | Best for | Section |
| --- | --- | --- |
| **Laragon** (Windows)        | Local dev on Windows, easy SSL/auto-vhost | [§ A](#a-deploy-on-windows-with-laragon) |
| **XAMPP** (Windows / macOS)  | Long-running staging on Windows lab PCs   | [§ B](#b-deploy-on-windows-with-xampp) |
| **Ubuntu Linux** (22.04 / 24.04) | Production with nginx + php-fpm + MariaDB | [§ C](#c-deploy-on-ubuntu-linux-2204-2404) |

Default test accounts (created by `DatabaseSeeder`):

| Role | Username | Password |
| --- | --- | --- |
| Super Admin       | `admin`   | `Admin@123`   |
| Membership Staff  | `staff`   | `Staff@123`   |
| Finance           | `finance` | `Finance@123` |
| Manager           | `manager` | `Manager@123` |

> Change all 4 passwords in production via `/users`.

---

## A. Deploy on Windows with Laragon

Tested on **Laragon Full 6.0**, which ships with PHP 8.2, MariaDB 10.11, Apache 2.4 and Composer.

### A1. Install Laragon

1. Download Laragon **Full** from <https://laragon.org/download/> and install (default path `C:\laragon`).
2. Launch **Laragon** → **Start All** (Apache + MySQL boot).
3. Confirm tooling versions in the Laragon terminal (right-click tray icon → **Terminal**):
   ```cmd
   php -v        :: must be 8.1+
   composer -V
   mysql --version
   ```

### A2. Get the code

```cmd
cd C:\laragon\www
git clone https://github.com/frhanzv/mysmartmemberhub.git
cd mysmartmemberhub
composer install --no-dev --optimize-autoloader
```

> Stop Apache before `composer install` if Windows Defender flags `chillerlan/php-qrcode` extraction — it's a false positive.

### A3. Create the database

In the Laragon menu: **MySQL → HeidiSQL** (auto-logs in as `root` with no password). Run:

```sql
CREATE DATABASE mysmartmemberhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mshub'@'localhost' IDENTIFIED BY 'mshub_local_pw';
GRANT ALL ON mysmartmemberhub.* TO 'mshub'@'localhost';
FLUSH PRIVILEGES;
```

### A4. Configure `.env`

```cmd
copy env .env
notepad .env
```

Edit at minimum:

```
CI_ENVIRONMENT = development

app.baseURL = 'http://mysmartmemberhub.test/'

database.default.hostname = 127.0.0.1
database.default.database = mysmartmemberhub
database.default.username = mshub
database.default.password = mshub_local_pw
database.default.DBDriver = MySQLi
database.default.charset  = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci
database.default.port     = 3306
```

### A5. Migrate + seed

```cmd
php spark migrate
php spark db:seed DatabaseSeeder
```

Expected output ends with `Done seeding.` and the DB now has 14 tables.

### A6. Auto-vhost (Pretty URL)

Laragon detects `C:\laragon\www\mysmartmemberhub` and creates `http://mysmartmemberhub.test/`. If the URL doesn't resolve:

1. Right-click tray icon → **Apache → Reload**.
2. Right-click tray icon → **Quick app → Auto Virtual Hosts** must be **on**.
3. **Public root**: Laragon serves `/public` automatically because `app/Config/App.php` declares it. If you see a directory listing, set the document root manually in `C:\laragon\etc\apache2\httpd.conf` to `C:/laragon/www/mysmartmemberhub/public`.

Open <http://mysmartmemberhub.test/> → log in as `admin` / `Admin@123`.

### A7. Common Laragon errors

| Symptom | Fix |
| --- | --- |
| `SQLSTATE[HY000] [1045] Access denied for user 'mshub'@'localhost'` | Re-run the `CREATE USER … GRANT …` block in HeidiSQL; check `.env` password. |
| `Cannot create file…uploads/photos/...` | Run terminal as **Administrator** once, then `mkdir -p writable/uploads writable/cache writable/logs writable/session` and `icacls writable /grant Everyone:M /T`. |
| `mod_rewrite` 404 on every URL | Right-click tray → **Apache → Modules → rewrite_module** must be checked. |
| dompdf `Image not found or type unknown` | `writable/uploads` must be writable; restart Apache after first run. |

---

## B. Deploy on Windows with XAMPP

Tested on **XAMPP 8.2.x** (Apache 2.4 + MariaDB 10.4 + PHP 8.2).

### B1. Install + verify XAMPP

1. Download XAMPP from <https://www.apachefriends.org/download.html> and install (default path `C:\xampp`).
2. Open the **XAMPP Control Panel** → **Start** Apache + MySQL.
3. Confirm tooling versions (Start menu → **Command Prompt**):
   ```cmd
   C:\xampp\php\php.exe -v
   ```
4. Composer is **not** bundled with XAMPP — install it from <https://getcomposer.org/Composer-Setup.exe> and point it at `C:\xampp\php\php.exe` when prompted.

### B2. Get the code

```cmd
cd C:\xampp\htdocs
git clone https://github.com/frhanzv/mysmartmemberhub.git
cd mysmartmemberhub
composer install --no-dev --optimize-autoloader
```

### B3. Create the database

Open <http://localhost/phpmyadmin/> (default user `root`, blank password). In the **SQL** tab paste:

```sql
CREATE DATABASE mysmartmemberhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mshub'@'localhost' IDENTIFIED BY 'mshub_local_pw';
GRANT ALL ON mysmartmemberhub.* TO 'mshub'@'localhost';
FLUSH PRIVILEGES;
```

### B4. Configure `.env`

```cmd
copy env .env
notepad .env
```

```
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost/mysmartmemberhub/public/'
database.default.hostname = 127.0.0.1
database.default.database = mysmartmemberhub
database.default.username = mshub
database.default.password = mshub_local_pw
database.default.port     = 3306
```

> The `app.baseURL` **must** end with `/public/` because XAMPP doesn't auto-rewrite to subfolders the way Laragon does.

### B5. Migrate + seed

```cmd
cd C:\xampp\htdocs\mysmartmemberhub
C:\xampp\php\php.exe spark migrate
C:\xampp\php\php.exe spark db:seed DatabaseSeeder
```

### B6. Make the writable folders writable

In an Administrator command prompt:

```cmd
icacls C:\xampp\htdocs\mysmartmemberhub\writable /grant Everyone:M /T
```

### B7. Enable Apache `mod_rewrite`

XAMPP ships with `mod_rewrite` disabled in some installers. In `C:\xampp\apache\conf\httpd.conf`:

```apache
LoadModule rewrite_module modules/mod_rewrite.so   # uncomment if commented
<Directory "C:/xampp/htdocs">
    AllowOverride All                              # change "None" to "All"
</Directory>
```

Restart Apache from the Control Panel. Open <http://localhost/mysmartmemberhub/public/> → log in.

### B8. Optional — pretty URL via vhost

In `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName mysmartmemberhub.test
    DocumentRoot "C:/xampp/htdocs/mysmartmemberhub/public"
    <Directory "C:/xampp/htdocs/mysmartmemberhub/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add to `C:\Windows\System32\drivers\etc\hosts` (as Administrator):

```
127.0.0.1   mysmartmemberhub.test
```

Restart Apache. Set `app.baseURL = 'http://mysmartmemberhub.test/'` in `.env`.

### B9. Common XAMPP errors

| Symptom | Fix |
| --- | --- |
| `Class "MySQLi" not found` | `php_mysqli` is disabled in `php.ini`; uncomment `extension=mysqli`. |
| `Could not find driver` (PDO_MYSQL) | uncomment `extension=pdo_mysql` in `php.ini`. |
| dompdf `Permission denied … cache` | `icacls writable /grant Everyone:M /T` (see B6). |
| `Forbidden — You don't have permission to access /mysmartmemberhub/public/` | `AllowOverride All` missing in `httpd.conf` (see B7). |
| `php spark` says `Database connection refused` | MariaDB not running; **Start** it from the Control Panel. |

---

## C. Deploy on Ubuntu Linux (22.04 / 24.04)

Production-style deployment with **nginx + php-fpm 8.2 + MariaDB 10.11**. Steps below assume a fresh Ubuntu Server.

### C1. Install required packages

```bash
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

sudo apt install -y \
  nginx \
  mariadb-server \
  php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl \
  php8.2-gd php8.2-zip php8.2-bcmath php8.2-intl \
  unzip git curl

# composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```

> All of `mbstring`, `xml`, `curl`, `gd`, `zip`, `bcmath`, `intl` are required: dompdf needs `gd`+`mbstring`+`xml`, PhpSpreadsheet needs `zip`+`xml`, MyInvois HTTP client needs `curl`. **Do not skip any.**

### C2. Secure MariaDB + create the database

```bash
sudo mysql_secure_installation     # set root pw, disallow remote root, drop test db

sudo mysql <<SQL
CREATE DATABASE mysmartmemberhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mshub'@'localhost' IDENTIFIED BY 'CHANGE_ME_STRONG_PW';
GRANT ALL ON mysmartmemberhub.* TO 'mshub'@'localhost';
FLUSH PRIVILEGES;
SQL
```

### C3. Get the code

```bash
sudo mkdir -p /var/www
sudo chown -R $USER:$USER /var/www
cd /var/www
git clone https://github.com/frhanzv/mysmartmemberhub.git
cd mysmartmemberhub
composer install --no-dev --optimize-autoloader
```

### C4. Configure `.env`

```bash
cp env .env
nano .env
```

```
CI_ENVIRONMENT = production

app.baseURL = 'https://your-domain.example/'
app.indexPage = ''
app.forceGlobalSecureRequests = true

database.default.hostname = 127.0.0.1
database.default.database = mysmartmemberhub
database.default.username = mshub
database.default.password = CHANGE_ME_STRONG_PW
database.default.DBDriver = MySQLi
database.default.charset  = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci

encryption.key = hex2bin:GENERATE_THIS_VIA_php_spark_key_generate
session.savePath = /var/www/mysmartmemberhub/writable/session
```

Generate the encryption key:

```bash
php spark key:generate          # writes to .env automatically
```

### C5. Filesystem permissions

```bash
sudo chown -R www-data:www-data /var/www/mysmartmemberhub
sudo find /var/www/mysmartmemberhub -type d -exec chmod 755 {} \;
sudo find /var/www/mysmartmemberhub -type f -exec chmod 644 {} \;
sudo chmod -R 775 /var/www/mysmartmemberhub/writable
sudo chown -R www-data:www-data /var/www/mysmartmemberhub/writable
```

### C6. Migrate + seed (run as the web user)

```bash
sudo -u www-data php /var/www/mysmartmemberhub/spark migrate
sudo -u www-data php /var/www/mysmartmemberhub/spark db:seed DatabaseSeeder
```

> Running migrations as `www-data` ensures any files written by Spark (cache, logs) are owned by the right user.

### C7. nginx vhost

`/etc/nginx/sites-available/mysmartmemberhub.conf`:

```nginx
server {
    listen 80;
    server_name your-domain.example;

    root /var/www/mysmartmemberhub/public;
    index index.php;

    client_max_body_size 12M;
    access_log /var/log/nginx/mysmartmemberhub_access.log;
    error_log  /var/log/nginx/mysmartmemberhub_error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(env|git|ht) { deny all; }
}
```

Enable + test + reload:

```bash
sudo ln -s /etc/nginx/sites-available/mysmartmemberhub.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### C8. HTTPS via Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.example
```

Certbot rewrites the vhost to listen on 443 with automatic redirect.

### C9. Tighten php-fpm

`/etc/php/8.2/fpm/php.ini`:

```ini
expose_php = Off
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 256M
max_execution_time = 60
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = "Lax"
date.timezone = "Asia/Kuala_Lumpur"
```

Then:

```bash
sudo systemctl restart php8.2-fpm
```

### C10. Verify

```bash
curl -I https://your-domain.example/
# → HTTP/2 302  Location: https://your-domain.example/login
```

Log in as `admin` / `Admin@123` and immediately change all default passwords in `/users`.

### C11. Common Ubuntu errors

| Symptom | Fix |
| --- | --- |
| `502 Bad Gateway` on every URL | `systemctl status php8.2-fpm` — start it; check the socket path in nginx matches `/run/php/php8.2-fpm.sock`. |
| `403 Forbidden` on `/login` | nginx `root` is wrong — must be `…/public`, not the project root. |
| `SQLSTATE[HY000] [2002] No such file or directory` | `database.default.hostname = 127.0.0.1` (not `localhost`) so PHP uses TCP not the unix socket. |
| `writable not writable` | re-run the chown/chmod from § C5. |
| dompdf `Permission denied … fonts` | `chmod -R 775 writable/uploads writable/cache vendor/dompdf/dompdf/lib/fonts/cache` then `restart php8.2-fpm`. |
| `Class "DOMDocument" not found` | install `php8.2-xml` and restart php-fpm. |
| `composer install` killed (OOM) on a 1 GB VPS | `sudo dd if=/dev/zero of=/swapfile bs=1M count=2048 && sudo chmod 600 /swapfile && sudo mkswap /swapfile && sudo swapon /swapfile`. |
| Migrations error `Specified key was too long; max key length is 1000 bytes` | DB created with `utf8mb4_general_ci` instead of `utf8mb4_unicode_ci` on an old MariaDB. Drop & recreate per § C2. |

---

## 🧰 Day-2 operations

```bash
# new release on production:
cd /var/www/mysmartmemberhub
sudo -u www-data git pull
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php spark migrate          # apply any new migrations
sudo systemctl reload php8.2-fpm
```

```bash
# logs
tail -f writable/logs/log-$(date +%F).log
sudo tail -f /var/log/nginx/mysmartmemberhub_error.log
```

```bash
# backup
mysqldump -u mshub -p mysmartmemberhub | gzip > /backup/msmh-$(date +%F).sql.gz
tar -czf /backup/msmh-uploads-$(date +%F).tgz writable/uploads
```

---

## 🧪 Smoke test (after first install)

```bash
php spark migrate:refresh && php spark db:seed DatabaseSeeder
php spark serve --port 8080
```

1. `GET /` while not logged in → 302 to `/landing` → "Sign in" button → `/login`.
2. `POST /login` with body `login=admin&password=Admin@123` → 303 to `/`.
3. `POST /members/store` → member `MEM-0001` + invoice `INV-2026-0001` auto-created. Country / state / registration-type selects render entries from `dropdown_options`.
4. `POST /payments/store` (no `invoice_id`) → payment recorded as `pending` and auto-matched to the invoice. Payment-method select pulls from `dropdown_options` (`category=payment_method`).
5. `POST /payments/{id}/approve` → payment `confirmed`, receipt `RCPT-2026-0001` issued, PDF saved, invoice flipped to `paid`.
6. `GET /invoices/{id}/pdf`, `GET /receipts/{id}/pdf`, `GET /reports/export/monthly` all return the expected file.
7. `GET /settings` (Super Admin) → toggle **LHDN e-Invoice** off → confirm `/invoices/{id}/einvoice/submit` returns `404 — module disabled` and the LHDN card on `/invoices/{id}` disappears.
8. `GET /settings/dropdown-options` (Super Admin) → add a row `category=payment_method`, `label=Test`, `value=test`, save → reload `/payments/create` → "Test" appears in the Payment method select.
9. RBAC: as `manager` user, `GET /users` and `GET /settings` return 403.

---

## 📡 LHDN MyInvois (e-Invoice)

The codebase ships a full UBL 2.1 builder, OAuth2 client, and submit / poll / cancel flow under `app/Libraries/Einvoice/`. By default `module.einvoice.enabled = 0` and `einvoice.environment = stub` — meaning all flows are exercisable offline without LHDN credentials.

To go live against the LHDN sandbox (`https://preprod-api.myinvois.hasil.gov.my`):

1. Register your supplier TIN at <https://preprod.myinvois.hasil.gov.my>.
2. **Profile → ERP Configuration → Add ERP** to obtain `client_id` and `client_secret`.
3. Add to `.env`:
   ```
   MYINVOIS_CLIENT_ID = your_client_id
   MYINVOIS_CLIENT_SECRET = your_client_secret
   ```
4. In `/settings`, set `einvoice.environment = sandbox`, fill the supplier identity (`einvoice.supplier.tin`, `…brn`, `…msic`, registered address) and toggle **LHDN e-Invoice → Enabled**.
5. Open any invoice → **Submit to LHDN**.

See [`docs/API.md` § 14](docs/API.md#14-settings-keys-managed-via-settings) for every LHDN setting key.

---

## License

MIT.
