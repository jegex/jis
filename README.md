# CMS Shop Digital

Multi-language CMS & digital shop platform — aplikasi e-commerce multi-bahasa untuk penjualan produk digital.

![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)
![Laravel](https://img.shields.io/badge/Laravel-13-F05340?logo=laravel)
![Filament](https://img.shields.io/badge/Filament-5-FF2D20?logo=filament)
![Livewire](https://img.shields.io/badge/Livewire-4-4E56A6?logo=livewire)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql)
![Midtrans](https://img.shields.io/badge/Payment-Midtrans-00A79D)
![License](https://img.shields.io/badge/License-MIT-blue)

---

## Daftar Isi

- [Tentang Aplikasi](#tentang-aplikasi)
- [Fitur](#fitur)
- [Tech Stack](#tech-stack)
- [Persyaratan Server](#persyaratan-server)
- [Quick Setup Lokal](#quick-setup-lokal)
- [Panduan Konfigurasi Environment](#panduan-konfigurasi-environment)
  - [Setting Database](#1-setting-database)
  - [Setting Midtrans](#2-setting-midtrans-payment-gateway)
  - [Setting Google Login](#3-setting-google-oauth-login)
  - [Setting Email / SMTP](#4-setting-email--smtp)
- [Panduan Install di cPanel Tanpa Terminal](#panduan-install-di-cpanel-tanpa-terminal)
  - [Persiapan File](#1-persiapan-file)
  - [Setting .env via File Manager](#2-setting-env-via-file-manager)
  - [Upload Database via phpMyAdmin](#3-upload-database-via-phpmyadmin)
  - [Jalankan Artisan Command (Setup Script)](#4-jalankan-artisan-command-via-setup-script)
  - [Setup Storage Symlink Manual](#5-setup-storage-symlink-manual)
  - [Setup Cron Job via cPanel UI](#6-setup-cron-job-di-cpanel-ui)
  - [Setting File Permission](#7-setting-file-permission)
  - [Verifikasi](#8-verifikasi)
- [Perintah Development](#perintah-development)
- [Cara Menambahkan Produk / Konten](#cara-menambahkan-produk--konten)
- [Deployment ke Production](#deployment-ke-production)
- [Bantuan & Kontribusi](#bantuan--kontribusi)
- [License](#license)

---

## Tentang Aplikasi

**CMS Shop Digital** adalah aplikasi web e-commerce multi-bahasa (Indonesia & Inggris) aplikasi e-commerce multi-bahasa untuk penjualan produk digital. Aplikasi ini digunakan untuk:

- **Menjual** produk digital (dokumen, desain, blueprint, file teknis) secara online
- **Menampilkan** portofolio proyek yang pernah dikerjakan
- **Blog** artikel informatif
- **Customer portal** — pelanggan bisa login, lihat riwayat pembelian, dan download file digital yang sudah dibeli

Dibangun dengan **Laravel 13** sebagai backend, **Filament 5** sebagai admin panel, dan **Livewire 4** untuk interaktivitas frontend tanpa perlu JavaScript framework berat.

---

## Fitur

| Status | Fitur | Keterangan |
|--------|-------|------------|
| ✅ | **E-commerce Produk Digital** | Katalog produk digital, detail produk, filter kategori |
| ✅ | **Midtrans Payment Gateway** | Pembayaran via Snap popup (kartu kredit, GoPay, ShopeePay, QRIS, dll) |
| ✅ | **Multi-Currency** | Harga otomatis dikonversi ke mata uang yang dipilih, dengan nilai tukar dinamis |
| ✅ | **Multi-Language** | Tersedia bahasa Indonesia & Inggris, switch bahasa real-time |
| ✅ | **Blog Engine** | Posting artikel dengan kategori, tag, author, related posts, pagination |
| ✅ | **Portfolio Proyek** | Menampilkan proyek dengan spesifikasi, foto, filter tipe proyek |
| ✅ | **Page Builder** | Halaman statis yang bisa dibuat/diedit dari admin (tentang kami, kontak, dll) |
| ✅ | **Homepage Builder** | Blok dinamis: featured products, latest posts, projects — diatur via admin |
| ✅ | **Customer Dashboard** | Pelanggan login, lihat riwayat order, download file digital, edit profil |
| ✅ | **Fortify Authentication** | Register, login, lupa password, verifikasi email, 2FA (TOTP), passkeys |
| ✅ | **Google Login** | Login pakai akun Google (Laravel Socialite) |
| ✅ | **Kupon / Diskon** | Kode kupon dengan berbagai tipe diskon (nominal, persen), batas pemakaian, expired date |
| ✅ | **Email Transaksional** | Email otomatis: order confirmation, download link, reset password, welcome email |
| ✅ | **Newsletter** | Kirim newsletter ke seluruh user via queue |
| ✅ | **Admin Panel Filament** | Dashboard dengan grafik revenue, stats order, manage resources |
| ✅ | **Menu Builder** | Buat menu navigasi dari admin, drag & drop |
| ✅ | **SEO Tools** | Sitemap.xml, OpenGraph, Schema.org, meta tags per halaman |
| ✅ | **Media Library** | Upload & manage gambar/foto via Spatie Media Library |
| ✅ | **Queue Jobs** | Proses background (email, notifikasi) dengan database driver |
| ✅ | **Testing** | Smoke test, admin pages, public pages, localization — via Pest |
| ⏳ | **Stripe Payment Gateway** | **Belum diimplementasikan** — masih stub |
| ⏳ | **Xendit Payment Gateway** | **Belum diimplementasikan** — masih stub |

---

## Tech Stack

- **Backend:** PHP 8.4, Laravel 13, Livewire 4
- **Admin Panel:** Filament 5
- **Frontend:** Tailwind CSS 4, Alpine.js
- **Database:** MySQL 8.0+ / MariaDB 10.6+
- **Payment:** Midtrans (aktif), Stripe (stub), Xendit (stub)
- **Authentication:** Laravel Fortify, Laravel Socialite
- **Email:** SMTP / Mailgun / Postmark (via Laravel Mail)
- **Auth Social:** Google OAuth 2.0
- **Testing:** Pest 4, PHPUnit 12
- **Code Quality:** Laravel Pint

---

## Persyaratan Server

### Untuk Development Lokal
| Komponen | Minimal |
|----------|---------|
| PHP | 8.3 atau 8.4 |
| MySQL | 8.0+ / MariaDB 10.6+ |
| Composer | 2.x |
| Node.js | 20+ |
| NPM | 10+ |

### PHP Extensions yang Harus Aktif
```
BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, MySQL, Tokenizer, XML, GD, Exif, Zip
```

Cek extensions di cPanel: **Select PHP Version → Extensions Tab**

---

## Quick Setup Lokal

Buat yang ingin menjalankan di komputer sendiri (local development):

```bash
# 1. Clone repositori
git clone <url-repository> cms-shop
cd cms-shop

# 2. Install dependency PHP
composer install

# 3. Buat file .env dari contoh
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Edit .env — sesuaikan database dan konfigurasi lainnya
#    (baca panduan konfigurasi di bawah)

# 6. Jalankan migrasi database + seed data awal
php artisan migrate --seed

# 7. Buat symlink storage (agar file upload bisa diakses)
php artisan storage:link

# 8. Install & build frontend
npm install
npm run build

# 9. Jalankan development server
composer run dev
```

Akses di browser: `http://localhost:8000`

---

## Panduan Konfigurasi Environment

### 1. Setting Database

Buka file `.env` dan isi bagian database:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1     # Biarkan localhost/localhost
DB_PORT=3306           # Port default MySQL
DB_DATABASE=jis        # Nama database (buat dulu di phpMyAdmin)
DB_USERNAME=root       # User database
DB_PASSWORD=           # Password database
```

**Di cPanel:**
- Login ke cPanel → **MySQL Databases**
- Buat database baru (contoh: `cms_shop_db`)
- Buat user database (contoh: `cms_shop_user`)
- Attach user ke database, kasih **ALL PRIVILEGES**
- Isi `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di `.env` sesuai yang dibuat

---

### 2. Setting Midtrans Payment Gateway

#### a. Buat Akun Midtrans

1. Buka https://dashboard.midtrans.com
2. Klik **Register** → isi data
3. Pilih **Sandbox** mode (untuk testing)

#### b. Dapatkan API Keys

1. Login ke dashboard Midtrans
2. Di sidebar kiri, klik **Settings → Access Keys**
3. Di halaman Access Keys, kamu akan lihat:

| Field | Value |
|-------|-------|
| `MIDTRANS_SERVER_KEY` | Server Key — copy paste, misal `SB-Mid-server-xxx` |
| `MIDTRANS_CLIENT_KEY` | Client Key — copy paste, misal `SB-Mid-client-xxx` |
| `MIDTRANS_MERCHANT_ID` | Merchant ID — copy paste, misal `G822801271` |

#### c. Set Notification URL (PENTING!)

1. Dashboard Midtrans → **Settings → Notification**
2. Isi **HTTP Notification URL** dengan:
   ```
   https://domain-anda.com/payment/callback
   ```
   *(untuk local, bisa skip atau pakai ngrok)*

#### d. Isi di File .env

```ini
PAYMENT_GATEWAY=midtrans
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxxxxxxxxxxxxx
MIDTRANS_MERCHANT_ID=G822801271
MIDTRANS_IS_PRODUCTION=false
```

| Variable | Keterangan |
|----------|------------|
| `PAYMENT_GATEWAY` | Biarkan `midtrans` |
| `MIDTRANS_SERVER_KEY` | Server Key dari dashboard |
| `MIDTRANS_CLIENT_KEY` | Client Key dari dashboard |
| `MIDTRANS_MERCHANT_ID` | Merchant ID dari dashboard |
| `MIDTRANS_IS_PRODUCTION` | `false` untuk testing, `true` saat live |

#### e. Testing Pembayaran

1. Buka website → pilih produk → checkout
2. Akan muncul popup Midtrans Snap
3. Pilih metode pembayaran
4. Untuk kartu kredit testing, gunakan:
   - No kartu: `4811 1111 1111 1114`
   - Bulan/Tahun: sembarang (asalkan > sekarang)
   - CVV: `123`
   - OTP: `112233`

---

### 3. Setting Google OAuth Login

Google login memungkinkan user login menggunakan akun Google mereka.

#### a. Buka Google Cloud Console

1. Buka https://console.cloud.google.com
2. Login dengan akun Google (bisa email biasa, tidak harus G Suite)

#### b. Buat Project Baru

1. Klik dropdown project (atas kiri, samping logo Google Cloud)
2. Klik **New Project**
3. Isi **Project name**: `CMS Shop Digital` (atau terserah)
4. Klik **Create**

#### c. Aktifkan OAuth Consent Screen

1. Setelah project jadi, di sidebar kiri → **APIs & Services → OAuth consent screen**
2. Pilih **External** → **Create**
3. **App Information:**
   - App name: `CMS Shop Digital`
   - User support email: pilih email kamu
   - Logo: bisa upload logo atau skip
4. **Developer contact information:**
   - Isi email kamu
5. Klik **Save and Continue**
6. **Scopes** → klik **Add or Remove Scopes**
   - Cari dan centang: `.../auth/userinfo.email` dan `.../auth/userinfo.profile` dan `openid`
   - Klik **Update** → **Save and Continue**
7. **Test users** → klik **Add Users**
   - Masukkan email kamu (email admin untuk testing)
   - Klik **Save and Continue**
8. Kembali ke halaman **OAuth consent screen**
   - Klik **Publish App** → **Confirm**

#### d. Buat OAuth Client ID

1. Sidebar → **APIs & Services → Credentials**
2. Klik **Create Credentials → OAuth client ID**
3. **Application type:** Web application
4. **Name:** `CMS Shop Digital Login`
5. **Authorized redirect URIs:**
   - Klik **Add URI**
   - Masukkan (ganti `domainmu.com` dengan domain asli):
     ```
     https://domainmu.com/auth/google/callback
     ```
   - Untuk local tambah juga:
     ```
     http://localhost:8000/auth/google/callback
     ```

#### e. Copy Client ID & Secret

Setelah create, akan muncul popup:

| Field | Value |
|-------|-------|
| `Client ID` | `xxxxxxxxxx-xxxxxxxxxxxxx.apps.googleusercontent.com` |
| `Client Secret` | `GOCSPX-xxxxxxxxxxxxxxxxxxx` |

> **PENTING:** Simpan Client Secret. Jika terlewat, hapus credential dan buat ulang.

#### f. Isi di File .env

```ini
GOOGLE_CLIENT_ID=xxxxxxxxxx-xxxxxxxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxxxxxxxxxxxxx
GOOGLE_REDIRECT_URI=https://domainmu.com/auth/google/callback
```

#### g. Testing Google Login

1. Buka website → klik **Login** → klik **Login with Google**
2. Akan redirect ke halaman pilih akun Google
3. Pilih akun → akan redirect balik ke website
4. Jika sukses, user langsung login

---

### 4. Setting Email / SMTP

Untuk mengirim email (order confirmation, reset password, dll):

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostgator.com           # Server SMTP
MAIL_PORT=465                           # 465 untuk SSL, 587 untuk TLS
MAIL_USERNAME=email@domainmu.com        # Email pengirim
MAIL_PASSWORD=password_email            # Password email
MAIL_ENCRYPTION=ssl                     # ssl atau tls
MAIL_FROM_ADDRESS=email@domainmu.com    # Email pengirim
MAIL_FROM_NAME="${APP_NAME}"            # Nama pengirim (CMS Shop Digital)
```

**Setting Email Hosting (cPanel):**
1. Buka cPanel → **Email Deliverability**
2. Cari domain kamu → **Manage**
3. Pastikan SPF, DKIM, DMARC sudah aktif (agar tidak masuk spam)
4. Di cPanel → **Email Accounts** → buat email (contoh: `info@domainmu.com`)
5. Gunakan credential dari email tersebut di `.env`

**Testing tanpa email dulu:**
Untuk development, set `MAIL_MAILER=log`. Email akan ditulis ke file `storage/logs/laravel.log`, bukan dikirim beneran.

---

## Panduan Install di cPanel Tanpa Terminal

Panduan ini untuk yang **tidak punya akses SSH/terminal** di cPanel. Semua dikerjakan via **File Manager** dan **phpMyAdmin**.

### 1. Persiapan File

#### a. Struktur Folder

Aplikasi harus diletakkan dengan struktur berikut:

```
/home/username/                     # Root akun cPanel kamu
├── laravel/                        # ✨ Semua file Laravel (DI LUAR public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   ├── artisan
│   └── composer.json
│
└── public_html/                    # ✅ Document root website
    ├── index.php                   # (diedit pathnya)
    ├── .htaccess
    ├── build/                      # Hasil build Vite
    ├── assets/
    ├── favicon.ico
    ├── robots.txt
    └── storage → ../laravel/storage/app/public   # Symlink
```

#### b. Upload via File Manager

1. **Login cPanel**
2. Buka **File Manager**
3. Masuk ke folder **`/home/username/`** (root)
4. Buat folder baru: **`laravel`**
5. **Upload file aplikasi:**
   - Karena File Manager cPanel hanya upload 1 file per 1 file, ada dua opsi:
     - **Opsi 1:** Upload file ZIP → Extract (lebih cepat)
     - **Opsi 2:** Upload folder per folder (lama)
   - **Cara ZIP:**
     a. Di komputer kamu, zip seluruh folder project (kecuali `vendor` dan `node_modules`, bisa dikompres terpisah)
     b. Upload ZIP ke folder `laravel/`
     c. Klik kanan file ZIP → **Extract**
     d. Hapus file ZIP setelah extract
6. **Upload vendor & node_modules:**
   - Bisa upload terpisah (folder `vendor/` dan `node_modules/`)
   - Atau nanti jalankan `composer install` dan `npm install` lewat setup script (bisa gagal di cPanel shared hosting)
   - **Rekomendasi:** Upload folder `vendor/` dari hasil `composer install` di komputer lokal

#### c. Pindahkan public/ ke public_html/

1. Masuk ke folder `laravel/`
2. Pilih folder **`public`** → **Copy**
3. Masuk ke folder **`public_html/`**
4. **Paste**
5. Akan ada folder `public` di dalam `public_html/`
6. Pilih semua isi folder `public/` → **Move**
7. Hapus folder `public/` yang sudah kosong

#### d. Edit public_html/index.php

Buka file `public_html/index.php` untuk diedit. Ubah semua path dari `__DIR__.'/../'` menjadi `__DIR__.'/../laravel/'`:

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../laravel/vendor/autoload.php';

$app = require_once __DIR__.'/../laravel/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

**Yang diubah:**
- `__DIR__.'/../storage/...'` → `__DIR__.'/../laravel/storage/...'`
- `__DIR__.'/../vendor/...'` → `__DIR__.'/../laravel/vendor/...'`
- `__DIR__.'/../bootstrap/...'` → `__DIR__.'/../laravel/bootstrap/...'`

---

### 2. Setting .env via File Manager

1. Di File Manager, masuk ke folder **`laravel/`**
2. Klik kanan → **New File**
3. Nama file: **`.env`** (pastiin ada titik di depan)
4. Klik kanan file `.env` → **Edit**
5. Isi dengan konfigurasi berikut (sesuaikan dengan data kamu):

```ini
APP_NAME="CMS Shop Digital"
APP_ENV=production
APP_KEY=                   # Nanti di-generate via setup script
APP_DEBUG=false
APP_URL=https://domainmu.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cms_shop_db     # Ganti dengan nama database yang dibuat
DB_USERNAME=cms_shop_user   # Ganti dengan user database
DB_PASSWORD=password_user       # Ganti dengan password database

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostgator.com
MAIL_PORT=465
MAIL_USERNAME=info@domainmu.com
MAIL_PASSWORD=password_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@domainmu.com
MAIL_FROM_NAME="${APP_NAME}"

MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxx
MIDTRANS_MERCHANT_ID=G822801271
MIDTRANS_IS_PRODUCTION=false

GOOGLE_CLIENT_ID=xxxxxxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxxxxxx
GOOGLE_REDIRECT_URI=https://domainmu.com/auth/google/callback

VITE_APP_NAME="${APP_NAME}"
```

6. Klik **Save Changes**

---

### 3. Upload Database via phpMyAdmin

#### a. Export Database dari Komputer Lokal

Di komputer kamu:
```bash
# Di terminal lokal
mysqldump -u root -p jis > cms_shop.sql
```
 
 Atau pakai **phpMyAdmin**:
 1. Buka `http://localhost/phpmyadmin`
 2. Pilih database `jis`
 3. Tab **Export** → **Go**
 4. Simpan file `cms_shop.sql`

#### b. Import Database di cPanel

1. Di cPanel → **phpMyAdmin**
2. Klik **Databases** (atas kiri)
3. Pilih database yang sudah dibuat (misal: `cms_shop_db`)
4. Klik tab **Import**
5. **Choose File** → pilih file `cms_shop.sql`
6. Klik **Go**
7. Tunggu sampai selesai (ada tulisan **Import has been successfully finished**)

---

### 4. Jalankan Artisan Command via Setup Script

Karena tidak punya akses SSH/terminal, kita buat file PHP temporary untuk menjalankan perintah-perintah Artisan sekaligus.

#### a. Buat File setup.php

Di File Manager, masuk ke folder **`public_html/`**.
Buat file baru: **`setup.php`**

Isi dengan:

```php
<?php

// ⚠️ HAPUS FILE INI SETELAH SELESAI!

declare(strict_types=1);

require __DIR__.'/../laravel/vendor/autoload.php';

$app = require_once __DIR__.'/../laravel/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$commands = [
    'key:generate'            => 'Generate APP_KEY',
    'storage:link'            => 'Buat symlink storage',
    'migrate --force'         => 'Migrasi database',
    'db:seed --force'         => 'Seed data awal',
];

echo "<pre>";

foreach ($commands as $command => $label) {
    echo "⏳ {$label}...\n";
    try {
        $exitCode = $kernel->call($command);
        echo $kernel->output() . "\n";
        echo $exitCode === 0 ? "✅ Selesai\n" : "❌ Gagal (exit code: {$exitCode})\n";
    } catch (Throwable $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "✅ Semua perintah selesai. <strong>HAPUS FILE setup.php SEKARANG!</strong></pre>";
```

#### b. Jalankan di Browser

1. Buka browser
2. Akses: `https://domainmu.com/setup.php`
3. Akan muncul log seperti:
   ```
   ⏳ Generate APP_KEY...
   Application key set successfully.
   ✅ Selesai

   ⏳ Buat symlink storage...
   The [public/storage] link has been connected.
   ✅ Selesai
   ...
   ```
4. **HAPUS FILE `setup.php` SEGERA SETELAH SELESAI!**
   - Buka File Manager
   - Cari `public_html/setup.php`
   - Klik kanan → **Delete**

> ⚠️ **KENAPA HARUS DIHAPUS?** File ini bisa dipakai orang lain untuk menjalankan perintah berbahaya di server kamu. Jangan pernah meninggalkan file ini.

---

### 5. Setup Storage Symlink Manual

Symlink memungkinkan file yang diupload (gambar produk, dll) bisa diakses dari browser.

#### a. Via File Manager (cPanel modern)

1. File Manager → masuk ke **`public_html/`**
2. Klik **Create Symlink** (ada di toolbar atas)
3. Isi:
   - **Source:** `../laravel/storage/app/public`
   - **Name:** `storage`
4. Klik **Create**

Setelah jadi, akan muncul folder `storage/` di dalam `public_html/` dengan icon shortcut/link.

#### b. Via PHP Script (alternatif)

Buat file `symlink.php` di folder `laravel/`:

```php
<?php
symlink(
    __DIR__ . '/storage/app/public',
    __DIR__ . '/../public_html/storage'
);
echo "Symlink created";
```

Akses `https://domainmu.com/../symlink.php` — tapi ini jarang bisa. Makanya cara File Manager lebih mudah.

---

### 6. Setup Cron Job di cPanel UI

Cron job diperlukan untuk menjalankan task otomatis:
1. **Scheduler** — trigger task terjadwal (saat ini belum ada, tapi untuk jaga-jaga)
2. **Queue Worker** — memproses antrian email (order confirmation, newsletter)

#### a. Buka Cron Jobs

1. cPanel → **Cron Jobs**
2. Scroll ke **Add New Cron Job**

#### b. Tambah Cron Job #1: Scheduler

| Field | Value |
|-------|-------|
| Common Settings | Once Per Minute (`* * * * *`) |
| Command | `/usr/local/bin/php /home/username/laravel/artisan schedule:run >> /dev/null 2>&1` |

> **Ganti `username`** dengan username cPanel kamu. Cek di sidebar kiri cPanel biasanya ada tulisan "User: username".

#### c. Tambah Cron Job #2: Queue Worker

Klik **Add New Cron Job** lagi:

| Field | Value |
|-------|-------|
| Common Settings | Once Per Minute (`* * * * *`) |
| Command | `/usr/local/bin/php /home/username/laravel/artisan queue:work --sleep=3 --tries=3 --max-time=3600 >> /dev/null 2>&1` |

#### d. Verifikasi Cron Job

Setelah ditambahkan, kamu akan lihat daftar cron job:

```
*/1 * * * * /usr/local/bin/php /home/username/laravel/artisan schedule:run
*/1 * * * * /usr/local/bin/php /home/username/laravel/artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

---

### 7. Setting File Permission

Permission yang salah bisa menyebabkan error 500 atau "file not writable".

Di File Manager:

1. Masuk ke folder **`laravel/`**
2. Klik kanan folder **`storage`** → **Change Permissions**
   - Centang semua: **Owner (Read, Write, Execute)**, **Group (Read, Write, Execute)**, **Others (Read, Execute)**
   - Atau isi numeric: **775**
3. Klik kanan folder **`bootstrap/cache`** → **Change Permissions**
   - Isi numeric: **775**
4. Klik kanan folder **`public_html`** → **Change Permissions**
   - Isi numeric: **755**

Kalau ada error "The stream or file could not be opened in write mode", ulangi step 2 & 3.

---

### 8. Verifikasi

Cek apakah semuanya berjalan:

| URL | Fungsi | Seharusnya |
|-----|--------|------------|
| `https://domainmu.com` | Halaman depan website | Tampil homepage |
| `https://domainmu.com/admin` | Admin panel Filament | Redirect ke login |
| `https://domainmu.com/login` | Halaman login | Form login + tombol Google Login |
| `https://domainmu.com/register` | Halaman register | Form register |

Verifikasi login:
1. Buka `https://domainmu.com/admin`
2. Login dengan akun yang dibuat saat seeding
   - Default: `admin@cms-shop.com` / `password` (jika tidak diubah di seeder)
3. Jika login sukses, masuk ke dashboard admin Filament

---

## Perintah Development

### Menjalankan Development Server (lokal)

```bash
composer run dev
```

Perintah ini menjalankan 4 service sekaligus:
- `php artisan serve` — web server
- `php artisan queue:listen` — queue worker
- `php artisan pail` — log viewer (real-time)
- `npm run dev` — Vite dev server (hot reload)

### Menjalankan Test

```bash
# Semua test
composer run test

# Test spesifik
php artisan test --compact --filter=AdminPagesTest

# Pakai Pest langsung
./vendor/bin/pest --filter=test_homepage_loads
```

### Code Formatting

```bash
vendor/bin/pint --format agent
```

### Queue

```bash
# Proses queue sekali (untuk testing)
php artisan queue:work --stop-when-empty

# Proses queue terus-menerus (production)
php artisan queue:work --sleep=3 --tries=3
```

### Cache

```bash
# Bersihkan cache
php artisan optimize:clear

# Cache untuk production
php artisan config:cache
php artisan route:cache
php artisan event:cache
```

---

## Cara Menambahkan Produk / Konten

Via admin panel di `https://domainmu.com/admin`:

1. **Produk:** Sidebar → **Products** → **Create**
2. **Kategori:** Sidebar → **Categories** → **Create**
3. **Blog Post:** Sidebar → **Posts** → **Create**
4. **Kupon:** Sidebar → **Coupons** → **Create**
5. **Halaman:** Sidebar → **Pages** → **Create**
6. **Portfolio:** Sidebar → **Projects** → **Create**
7. **Menu:** Sidebar → **Menu Items** → **Create**
8. **Email Template:** Sidebar → **Mail** → **Email Templates** → **Create**
9. **Setting:** Sidebar → **Settings** (logo, homepage, sosial media, SEO)

---

## Deployment ke Production

Untuk panduan deployment lengkap ke cPanel (termasuk setting PHP, optimasi, troubleshooting), lihat file:

📄 **[DEPLOYMENT.md](DEPLOYMENT.md)**

---

## Bantuan & Kontribusi

Untuk laporan bug atau saran fitur, silakan buka issue di repositori ini.

---

## License

Proyek ini menggunakan lisensi MIT.
