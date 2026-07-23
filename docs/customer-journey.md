# Customer Journey: Dari Buka Website hingga Pembayaran

---

## Stage 1: Landing di Website

**Halaman:** Homepage / Halaman Produk

User membuka website dan melihat tampilan utama. User bisa langsung melihat produk-produk yang tersedia atau mencari produk yang diinginkan.

**Tujuan user:** Menemukan produk yang ingin dibeli.

---

## Stage 2: Melihat Detail Produk

**Halaman:** Halaman Detail Produk

User memilih salah satu produk dan melihat detail lengkapnya:

- Gambar produk (cover + gallery)
- Nama produk
- Deskripsi produk
- Harga produk
- Tombol **"Buy Now"**

**Tujuan user:** Memastikan produk sesuai dengan yang dicari.

---

## Stage 3: Masuk ke Halaman Checkout

**Halaman:** Checkout Page

User menekan tombol **"Buy Now"** dan diarahkan ke halaman checkout. Di halaman ini user melihat ringkasan pesanan:

- Thumbnail produk
- Nama produk
- Harga
- Total yang harus dibayar

User juga bisa memasukkan **kode kupon** (opsional) jika ada.

**Tujuan user:** Melakukan pembayaran untuk produk yang dipilih.

---

## Stage 4: Melakukan Pembayaran

**Halaman:** Checkout Page (dengan popup Midtrans Snap)

User menekan tombol **"Pay Now"**. Sistem memproses pesanan dan menampilkan popup pembayaran dari **Midtrans Snap**.

Di dalam popup, user diminta mengisi data pembayaran:

- Nomor kartu kredit
- Bulan/Tahun kadaluwarsa kartu
- CVV

Jika bank penerbit kartu menerapkan **3DS (3D Secure)**, user akan diarahkan ke halaman autentikasi bank untuk memasukkan OTP atau PIN.

**Tujuan user:** Menyelesaikan pembayaran dengan aman.

---

## Stage 5: Hasil Pembayaran

**Terdapat 3 kemungkinan hasil:**

### ✅ Pembayaran Berhasil

User diarahkan ke halaman **Payment Success** yang menampilkan:

- Pesan: "Pembayaran Berhasil!"
- Informasi: "Link download akan dikirim ke email Anda."
- Tombol: "Kembali ke Beranda"

### ⏳ Pembayaran Tertunda

User diarahkan ke halaman **Payment Pending** yang menampilkan:

- Pesan: "Pembayaran Sedang Diproses"
- Informasi: "Kami akan memberitahu Anda setelah pembayaran dikonfirmasi."
- Tombol: "Kembali ke Beranda"

### ❌ Pembayaran Gagal

User diarahkan ke halaman **Payment Error** yang menampilkan:

- Pesan: "Pembayaran Gagal"
- Informasi: "Terjadi kesalahan. Silakan coba lagi."
- Tombol: "Coba Lagi"

---

## Stage 6: Menerima Notifikasi (Pasca Pembayaran)

Setelah pembayaran berhasil dikonfirmasi oleh sistem Midtrans, sistem akan mengirimkan **2 email** ke user:

1. **Email Konfirmasi Pesanan** — Memberitahu bahwa pembayaran telah berhasil
2. **Email Link Download** — Berisi link untuk mengunduh produk yang sudah dibeli

**Tujuan user:** Mendapatkan akses ke produk yang sudah dibeli.

---

## Stage 7: Mengunduh Produk

**Halaman:** Link Download (via email) atau Dashboard Customer

User bisa mengunduh produk dengan dua cara:

1. **Via email** — Klik link download yang dikirim ke email
2. **Via dashboard** — Login ke akun, buka halaman pesanan, klik tombol download

**Tujuan user:** Mendapatkan dan menikmati produk yang sudah dibeli.

---

## Ringkasan Visual Journey

```
Buka Website
      │
      ▼
Lihat Produk
      │
      ▼
Detail Produk
      │
      ▼
Halaman Checkout
      │
      ▼
Popup Midtrans Snap (Input Kartu Kredit)
      │
      ├── ✅ Berhasil → Halaman Sukses → Email → Download
      │
      ├── ⏳ Tertunda → Halaman Pending
      │
      └── ❌ Gagal → Halaman Error → Coba Lagi
```
