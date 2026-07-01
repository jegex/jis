# Deployment Guide — DKIKonsultan (cPanel)

## Persyaratan Server

| Komponen | Minimal |
|----------|---------|
| PHP | 8.3 atau 8.4 |
| MySQL | 8.0+ / MariaDB 10.6+ |
| Composer | 2.x |
| Node.js | 20+ (untuk build asset) |
| NPM | 10+ |
| Extensions PHP | `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `MySQL`, `Tokenizer`, `XML`, `GD`, `Exif` |

---

## 1. Struktur Folder di Server

Untuk keamanan, file Laravel diletakkan **di luar `public_html`**. Hanya folder `public/` yang masuk ke document root.

```
/home/username/
├── laravel/                              # Semua file Laravel (di luar public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   │   ├── app/
│   │   ├── framework/
│   │   └── logs/
│   ├── vendor/
│   ├── .env
│   ├── artisan
│   └── composer.json
│
└── public_html/                          # ✅ Document Root cPanel
    ├── index.php                         # Diedit — path mengarah ke ../laravel/
    ├── .htaccess
    ├── build/                            # Vite build output
    ├── assets/
    ├── favicon.ico
    ├── robots.txt
    └── storage/ → ../laravel/storage/app/public   # Symlink
```

### 1.1 Isi `public_html/index.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../laravel/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../laravel/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

**Perubahan dari default:**
- `__DIR__.'/../storage/...'` → `__DIR__.'/../laravel/storage/...'`
- `__DIR__.'/../vendor/...'` → `__DIR__.'/../laravel/vendor/...'`
- `__DIR__.'/../bootstrap/...'` → `__DIR__.'/../laravel/bootstrap/...'`

---

## 2. Upload File ke cPanel

### Opsi A — Git Deployment (rekomendasi)
```bash
# Login SSH via Terminal cPanel
cd ~/laravel
git clone <repository-url> .

# Copy isi public/ ke public_html/
cp -r public/* ../public_html/
cp public/.htaccess ../public_html/

# Hapus public/ dari laravel (sudah tidak perlu)
rm -rf public
```

### Opsi B — Upload Manual
1. Upload seluruh project (kecuali `vendor/`, `node_modules/`, `.env`) ke `~/laravel/`
2. Pindahkan isi `public/` ke `~/public_html/`:
   ```bash
   cp -r ~/laravel/public/* ~/public_html/
   cp ~/laravel/public/.htaccess ~/public_html/
   ```
3. Hapus folder `public/` dari `~/laravel/` (sudah tidak dipakai)
4. Upload folder `vendor/` terpisah (atau jalankan `composer install` di server)
5. Upload `node_modules/` (atau jalankan `npm install` di server)

### 1.3 Setup Symlink Storage

```bash
# Dari folder public_html
cd ~/public_html
ln -s ../laravel/storage/app/public storage
```

---

## 3. Setup Environment

Buat file `.env` di root project:

```bash
cp ~/laravel/.env.example ~/laravel/.env
```

**Isi dengan konfigurasi produksi:**

```ini
APP_NAME=DKIKonsultan
APP_ENV=production
APP_KEY=base64:...   # Hasil dari: php artisan key:generate
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostmail.com
MAIL_PORT=465
MAIL_USERNAME=email@domain.com
MAIL_PASSWORD=password_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=email@domain.com
MAIL_FROM_NAME="${APP_NAME}"

PAYMENT_GATEWAY=midtrans
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_SERVER_KEY=Mid-server-...  # Dari dashboard Midtrans produksi
MIDTRANS_CLIENT_KEY=Mid-client-...  # Dari dashboard Midtrans produksi
MIDTRANS_MERCHANT_ID=G...

LOG_LEVEL=error
```

---

## 4. Install Dependencies & Build

Jalankan semua perintah dari folder `~/laravel/`:

```bash
cd ~/laravel

# 1. Install PHP dependencies (tanpa dev)
composer install --optimize-autoloader --no-dev

# 2. Generate APP_KEY (hanya sekali)
php artisan key:generate

# 3. Migrasi database
php artisan migrate --force

# 4. Seed data awal
php artisan db:seed --force

# 5. Cache untuk produksi
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan icons:cache

# 6. Install & build frontend
npm install --ignore-scripts
npm run build
```

> **Catatan:** `php artisan storage:link` **tidak perlu** dijalankan karena symlink sudah dibuat manual di section 1.3.

---

## 5. Setup Cron Job

Buka **cPanel → Cron Jobs** → Tambahkan cron job baru.

### 5.1 Laravel Scheduler (WAJIB)

Jalankan tiap menit untuk trigger scheduled tasks:

```cron
* * * * * /usr/local/bin/php /home/username/laravel/artisan schedule:run >> /dev/null 2>&1
```

**Sesuaikan:**
- `username` → username cPanel Anda
- `laravel` → sesuaikan jika folder penamaan berbeda

> Saat ini belum ada scheduled task yang didefinisikan. Setelah menambahkan task di `routes/console.php`, cron job ini akan menjalankannya otomatis.

### 5.2 Queue Worker (jika pakai antrian)

```cron
* * * * * /usr/local/bin/php /home/username/laravel/artisan queue:work --sleep=3 --tries=3 --max-time=3600 >> /dev/null 2>&1
```

Atau isi via form **cPanel → Cron Jobs**:

| Field | Value |
|-------|-------|
| Common Settings | Once Per Minute (`* * * * *`) |
| Command | `/usr/local/bin/php /home/username/laravel/artisan schedule:run` |

### 5.3 Verifikasi Cron

Cek apakah cron berjalan:

```bash
# Cek log
cat ~/laravel/storage/logs/laravel.log | grep "processed successfully"

# Cek jadwal
cd ~/laravel && php artisan schedule:list
```

---

## 6. File Permission (PENTING)

```bash
# Dari root folder laravel
cd ~/laravel

find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Storage & cache bisa ditulis
chmod -R 775 storage bootstrap/cache

# Pastikan owner benar (sesuaikan dengan user cPanel)
chown -R username:username ~/laravel
chown -R username:username ~/public_html
```

---

## 7. PHP Settings

Di **cPanel → MultiPHP INI Editor**:

| Setting | Value |
|---------|-------|
| `upload_max_filesize` | 64M |
| `post_max_size` | 64M |
| `max_execution_time` | 300 |
| `memory_limit` | 256M |

Atau via file `.user.ini` di `~/laravel/`:
```ini
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
memory_limit = 256M
```

---

## 8. SSL/HTTPS

Pastikan HTTPS aktif:

1. **cPanel → SSL/TLS** → Install SSL certificate
2. **Install & activate** (bisa via AutoSSL)
3. Pastikan `.env`: `SESSION_SECURE_COOKIE=true`

---

## 9. Verifikasi

Setelah deploy, akses:

| URL | Fungsi |
|-----|--------|
| `https://domain.com` | Halaman depan |
| `https://domain.com/up` | Health check (harus return 200) |
| `https://domain.com/admin` | Filament Admin Panel |

### Debug jika ada error:
```bash
# Cek log
tail -f ~/laravel/storage/logs/laravel.log
```

Jika muncul error putih (500 blank):
```bash
# Aktifkan debug sementara di .env untuk lihat error
# Edit ~/laravel/.env
APP_DEBUG=true

# Setelah selesai, kembalikan ke false
APP_DEBUG=false
cd ~/laravel && php artisan config:cache
```

---

## 10. Troubleshooting Umum

### Error: "Unable to locate file in Vite manifest"
```bash
cd ~/laravel && npm run build
```

### Error: "No application encryption key"
```bash
cd ~/laravel && php artisan key:generate && php artisan config:cache
```

### Error: "The stream or file could not be opened in write mode"
```bash
chmod -R 775 ~/laravel/storage ~/laravel/bootstrap/cache
```

### Session tidak bekerja / "CSRF token mismatch"
- Pastikan `SESSION_SECURE_COOKIE=true` jika pakai HTTPS
- Hapus cache browser

### Error 500 Filament / Livewire
```bash
# Bersihkan cache
cd ~/laravel
php artisan optimize:clear
php artisan view:clear

# Re-cache
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan icons:cache
npm run build
```

---

**Dibuat:** 28 Juni 2026  
**PHP:** 8.4 | **Laravel:** 13.17 | **Filament:** 5.6 | **cPanel**
