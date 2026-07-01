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
- [Panduan Install di cPanel](#panduan-install-di-cpanel)
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
   https://domain-anda.com/api/payment/callback
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

## Panduan Install di cPanel

Untuk panduan deployment lengkap ke cPanel server (struktur folder, upload file, .env production, migrasi, cron job, permission, troubleshooting), lihat file terpisah:

📄 **[DEPLOYMENT.md](DEPLOYMENT.md)**

> Panduan ini mencakup: struktur folder aman (Laravel di luar public_html), setting .env production, migrasi database, cron job scheduler & queue worker, file permission, PHP settings untuk produksi, SSL, dan troubleshooting umum.

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
