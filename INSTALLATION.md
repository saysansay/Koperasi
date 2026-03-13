# Production Installation Guide

## Overview
This document explains how to deploy the Cooperative Management System to a production environment.

Application stack:
- PHP 8.2+
- Laravel 12
- MySQL 8+ or MariaDB 10.6+
- Apache or Nginx
- Composer 2+
- Optional: Supervisor for queues

## 1. Server Requirements
Install these components on the production server:
- PHP 8.2 or newer
- PHP extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`
- Composer
- MySQL / MariaDB
- Web server: Apache or Nginx

Recommended:
- Linux server (Ubuntu 22.04 LTS or newer)
- SSL certificate
- Process manager for queues and scheduled tasks

## 2. Upload Project
Deploy the project source to the server.

Example target directory:
- `/var/www/koperasi`

Make sure the web server points to:
- `/var/www/koperasi/public`

## 3. Install PHP Dependencies
Run in project root:

```bash
composer install --no-dev --optimize-autoloader
```

## 4. Create Environment File
Copy the example file:

```bash
cp .env.example .env
```

Edit `.env` for production.

Minimum recommended values:

```env
APP_NAME="KoperasiSys"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=koperasi
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

## 5. Generate Application Key

```bash
php artisan key:generate
```

## 6. Create Database
Create a MySQL database and user.

Example:

```sql
CREATE DATABASE koperasi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'koperasi_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON koperasi.* TO 'koperasi_user'@'localhost';
FLUSH PRIVILEGES;
```

## 7. Run Migrations and Seeders
If this is a fresh installation:

```bash
php artisan migrate --seed --force
```

If you do not want demo/sample data in production, run:

```bash
php artisan migrate --force
```

Note:
- current seeder creates demo users and sample transactions
- remove or modify `database/seeders/DatabaseSeeder.php` before production if demo data is not desired

## 8. Storage and Cache Permissions
Make sure these directories are writable by the web server user:
- `storage`
- `bootstrap/cache`

Example:

```bash
chown -R www-data:www-data /var/www/koperasi
chmod -R 775 storage bootstrap/cache
```

## 9. Optimize Laravel for Production
Run:

```bash
php artisan optimize
php artisan view:cache
php artisan route:cache
```

If config changes later:

```bash
php artisan optimize:clear
php artisan optimize
```

## 10. Queue Worker
This project uses:
- `QUEUE_CONNECTION=database`

Create queue tables if not already migrated:
- included in Laravel base migrations

Run worker manually for testing:

```bash
php artisan queue:work --tries=1
```

Recommended in production:
- run queue with Supervisor

Example Supervisor config:

```ini
[program:koperasi-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/koperasi/artisan queue:work --sleep=3 --tries=1 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/koperasi/storage/logs/queue.log
stopwaitsecs=3600
```

Then reload Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start koperasi-queue:*
```

## 11. Scheduler
If future scheduled jobs are used, add cron:

```bash
* * * * * cd /var/www/koperasi && php artisan schedule:run >> /dev/null 2>&1
```

## 12. Nginx Example
Example server block:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/koperasi/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## 13. Apache Notes
If using Apache:
- enable `mod_rewrite`
- point document root to `public/`
- allow `.htaccess`

## 14. First Login
If seeded with demo data, default accounts are:
- `admin@koperasi.test` / `password`
- `manager@koperasi.test` / `password`
- `staff@koperasi.test` / `password`
- `member@koperasi.test` / `password`

Production recommendation:
- log in as admin
- change all default passwords immediately
- remove demo users if not needed

## 15. Security Checklist
Before go-live:
- set `APP_DEBUG=false`
- use HTTPS
- change default passwords
- restrict database user permissions where possible
- back up database regularly
- protect server with firewall
- keep PHP, Composer packages, and OS packages updated

## 16. Update / Redeploy Procedure
For updates:

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan queue:restart
```

## 17. Troubleshooting
If the application shows old content:

```bash
php artisan optimize:clear
```

If language, routes, or views do not update:

```bash
php artisan optimize:clear
php artisan view:cache
php artisan route:cache
```

If permission errors appear:
- verify ownership of `storage` and `bootstrap/cache`

If database connection fails:
- recheck `.env`
- test MySQL user manually
- verify `pdo_mysql` is enabled

## 18. Recommended Production Flow
1. Upload code
2. Set `.env`
3. Create database
4. Run `composer install --no-dev --optimize-autoloader`
5. Run `php artisan key:generate`
6. Run `php artisan migrate --force`
7. Optionally run seeder only if production data should start from demo template
8. Run `php artisan optimize`
9. Configure queue worker and web server
10. Change admin password immediately after first login
