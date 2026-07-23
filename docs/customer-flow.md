# Customer Flow: Buka Website → Beli → Terima File & Email

---

## 1. Buka Website

Customer membuka website melalui browser. Halaman utama menampilkan produk-produk yang tersedia.

**Yang customer lihat:** Homepage dengan daftar produk, navigasi, header.

---

## 2. Lihat Produk

Customer klik salah satu produk untuk melihat detailnya.

**Yang customer lihat:**
- Gambar produk
- Nama dan deskripsi
- Harga
- Tombol **"Buy Now"**

---

## 3. Klik "Buy Now"

Customer klik tombol **"Buy Now"**.

**Yang terjadi:**
- Jika belum login → diarahkan ke halaman login/register
- Jika sudah login → masuk ke halaman checkout

---

## 4. Halaman Checkout

Customer melihat ringkasan pesanan:

- Nama produk
- Harga
- Total yang harus dibayar

Customer klik tombol **"Pay Now"**.

---

## 5. Popup Pembayaran

Popup Midtrans Snap muncul. Customer mengisi data kartu kredit:

- Nomor kartu
- Bulan/Tahun kadaluwarsa
- CVV

Jika ada 3DS, customer memasukkan OTP di halaman bank.

---

## 6. Halaman Sukses

Setelah bayar berhasil, customer diarahkan ke halaman sukses:

**"Pembayaran Berhasil! Link download akan dikirim ke email Anda."**

Customer klik **"Kembali ke Beranda"**.

---

## 7. Terima Email (dalam beberapa menit)

Customer mendapat 2 email:

**Email 1 — Konfirmasi Pesanan:**
> "Hai [Nama], pembayaran kamu untuk [Nama Produk] sudah diterima. Terima kasih!"

**Email 2 — Link Download:**
> "Hai [Nama], berikut link download untuk [Nama Produk]: [Link Download]"

---

## 8. Download File

Customer klik link download di email. File langsung terdownload.

Atau customer bisa login ke akun → buka halaman pesanan → klik tombol download.

**Selesai.** Customer sudah memiliki file produknya.

---

## Visual Flow

```
Buka Website
     │
     ▼
Lihat Produk
     │
     ▼
Klik "Buy Now"
     │
     ├── Belum login → Login / Register
     │
     ▼
Halaman Checkout
     │
     ▼
Klik "Pay Now"
     │
     ▼
Popup Pembayaran (isi kartu kredit)
     │
     ▼
Pembayaran Berhasil ✅
     │
     ▼
     ├── Halaman Sukses → "Link dikirim ke email"
     │
     └── Email Masuk (2 email)
               │
               ├── Email 1: Konfirmasi Pesanan
               │
               └── Email 2: Link Download → Klik → File terdownload ✅
```
