# Deploy MultiEcom on cPanel (guhan.in)

## Why you see "500 Internal Server Error"

On cPanel, the domain folder is usually `public_html/guhan.in/`. Laravel must run from the **`public`** subfolder. Without the root `.htaccess` + `index.php` (included in this repo), Apache cannot start the app.

Other common causes:

| Cause | Fix |
|-------|-----|
| No `.env` on server | Create it manually (see below) — it is **not** in Git |
| Wrong database credentials | Update `DB_*` in `.env` |
| Missing `APP_KEY` | Run `php artisan key:generate` |
| `vendor/` missing | Run `composer install --no-dev` on server |
| Storage not writable | `chmod -R 775 storage bootstrap/cache` |

---

## Step 1 — Document root (recommended)

In **cPanel → Domains → guhan.in → Document Root**, set:

```
/home/qxdmzlcus1wr/public_html/guhan.in/public
```

If you can do this, you only need the `public` folder as web root (most secure).

If you **cannot** change document root, keep root at `guhan.in/` — the included root `.htaccess` and `index.php` will forward requests to `public/`.

---

## Step 2 — Create `.env` on the server

SSH or **cPanel → File Manager** → `public_html/guhan.in/` → create file `.env`:

```env
APP_NAME=MultiEcom
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://guhan.in

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=YOUR_CPANEL_DB_NAME
DB_USERNAME=YOUR_CPANEL_DB_USER
DB_PASSWORD=YOUR_CPANEL_DB_PASSWORD

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=single
LOG_LEVEL=error
```

Replace `DB_*` with values from **cPanel → MySQL Databases**.

---

## Step 3b — Download product & category images (after seed)

Requires internet on the server:

```bash
php artisan catalog:fetch-images
```

Re-download all images:

```bash
php artisan catalog:fetch-images --force
```

Uses [OMGS.in](https://omgs.in/) images for custom acrylic products and stock photos for shoes, clothes, electronics, etc.

---

## Step 3 — Run commands on server (SSH or Terminal in cPanel)

```bash
cd /home/qxdmzlcus1wr/public_html/guhan.in

composer install --no-dev --optimize-autoloader
php artisan key:generate
chmod -R 775 storage bootstrap/cache
php artisan storage:link

**No SSH?** Add to `.env`: `STORAGE_SETUP_TOKEN=some-long-random-string`  
Then open once in the browser: `https://guhan.in/setup/storage-link?token=some-long-random-string`  
You should see JSON: `"ok": true`. Change or remove the token afterward.
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Step 4 — PHP version

**cPanel → Select PHP Version** → choose **PHP 8.1** or **8.2** (Laravel 10 requirement).

Enable extensions: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`.

---

## Step 5 — Check error log if still failing

**cPanel → Errors** or open:

```
/home/qxdmzlcus1wr/public_html/guhan.in/storage/logs/laravel.log
```

Also check Apache error log in cPanel.

---

## Git deploy note

`.cpanel.yml` was updated to copy **all files** (including `.htaccess`), not `*` which skips dotfiles.

`.env` is never deployed from Git — you must create it once on the server.
