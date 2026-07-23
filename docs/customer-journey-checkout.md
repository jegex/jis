# Customer Journey: Buka Website hingga Payment Page (Midtrans Snap)

**Proyek:** JIS (Digital Product Store)  
**Integration:** Midtrans Snap (Popup)  
**Dokumen:** Additional Data untuk Onboarding Midtrans

---

## Daftar Isi

1. [Ringkasan Arsitektur](#1-ringkasan-arsitektur)
2. [User Journey Map](#2-user-journey-map)
3. [Detail Alur per Langkah](#3-detail-alur-per-langkah)
4. [Data Flow Diagram](#4-data-flow-diagram)
5. [API Calls & Payload](#5-api-calls--payload)
6. [Callback & Redirect Flow](#6-callback--redirect-flow)
7. [Status Mapping](#7-status-mapping)

---

## 1. Ringkasan Arsitektur

| Komponen | Detail |
|---|---|
| **Platform** | Laravel 13 + PHP 8.4 |
| **Frontend** | Livewire 4 + Tailwind CSS 4 |
| **Payment Gateway** | Midtrans Snap (Popup mode) |
| **SDK** | `midtrans/midtrans-php` v2.6.2 |
| **Payment Methods** | Credit Card (hanya kartu kredit yang diaktifkan) |
| **Tipe Produk** | Produk digital (download instan setelah bayar) |
| **Autentikasi** | Laravel Fortify (login/register required sebelum checkout) |
| **Currency** | Multi-currency, dikonversi ke IDR untuk Midtrans |
| **Environment** | Sandbox / Production (dikontrol via `.env`) |

### Alur Tingkat Tinggi

```
User → Product Page → Checkout Form → Midtrans Snap Popup → Payment Result → Redirect Page
                                                                   ↓
                                                            Midtrans Callback (server-to-server)
                                                                   ↓
                                                            Payment Success Event → Email Notifikasi
```

---

## 2. User Journey Map

### Stage 1: Landing / Product Discovery

| Langkah | User Action | System Action | Halaman |
|---|---|---|---|
| 1.1 | Membuka website (homepage) | Menampilkan daftar produk unggulan | `/` atau `/{locale}` |
| 1.2 | Navigasi ke halaman produk | Routing ke `ProductList` Livewire component | `/{locale}/products` |
| 1.3 | Klik salah satu produk | Routing ke `ProductDetail` Livewire component | `/{locale}/products/{product}` |

### Stage 2: Product Detail

| Langkah | User Action | System Action | Halaman |
|---|---|---|---|
| 2.1 | Melihat detail produk (gambar, deskripsi, harga) | Load data produk + relasi (media, category, tags) | Product Detail |
| 2.2 | Melihat harga dan informasi "Secure checkout with Midtrans" | Render statis dari data produk | Sidebar |
| 2.3 | Klik tombol **"Buy Now"** | Redirect ke halaman checkout (dicek auth & verified middleware) | `/{locale}/checkout/{product}` |

### Stage 3: Checkout Page

| Langkah | User Action | System Action | Halaman |
|---|---|---|---|
| 3.1 | Halaman checkout tampil dengan ringkasan produk | `CheckoutForm` Livewire component di-mount, load product + hitung subtotal/total | Checkout Form |
| 3.2 | (Opsional) Input kode kupon | `applyCoupon()` — validasi kupon via `CouponService`, update discount & total | Checkout Form |
| 3.3 | Klik tombol **"Pay Now"** | Trigger method `pay()` di Livewire component | Checkout Form |

#### Stage 3 - Backend Process (saat tombol Pay Now diklik)

| Urutan | Proses | Kode |
|---|---|---|
| 3.3.1 | **Create Order** — `OrderService::createOrder()` membuat record order baru dengan status `pending` | `CheckoutForm.php:98` |
| 3.3.2 | **Update Status** — Order diupdate ke `creating_payment` | `CheckoutForm.php:106` |
| 3.3.3 | **Currency Check** — Jika currency produk bukan IDR, lakukan konversi via `CurrencyService` | `CheckoutForm.php:114-133` |
| 3.3.4 | **Charge ke Midtrans** — Panggil `app('payment')->charge($order, $params)` → `MidtransGateway::charge()` | `CheckoutForm.php:144` |
| 3.3.5 | **Build Snap Payload** — `MidtransGateway` menyusun payload (order_id, customer_details, item_details, callbacks, notification_url) | `MidtransGateway.php:63-109` |
| 3.3.6 | **Call Midtrans API** — `Snap::createTransaction($payload)` → response berisi `token` dan `redirect_url` | `MidtransGateway.php:112` |
| 3.3.7 | **Simpan Payment Record** — Buat record di tabel `payments` dengan snap_token, status `pending` | `CheckoutForm.php:154-163` |
| 3.3.8 | **Update Order Status** — Order diupdate ke `awaiting_payment` | `CheckoutForm.php:165` |
| 3.3.9 | **Flash Token + Dispatch Event** — Session flash `snap_token` + dispatch browser event `snap-token-ready` | `CheckoutForm.php:167-169` |

### Stage 4: Midtrans Snap Popup Payment

| Langkah | User Action | System Action | Halaman |
|---|---|---|---|
| 4.1 | Snap popup muncul secara otomatis | JavaScript `window.snap.pay(token, callbacks)` dijalankan | Overlay di Checkout |
| 4.2 | User mengisi data kartu kredit (nomor kartu, expiry, CVV) | Midtrans Snap UI — request divert ke Midtrans server | Snap popup |
| 4.3 | (Jika 3DS) User diarahkan ke halaman 3DS bank | Midtrans menangani autentikasi 3DS | Halaman bank |
| 4.4 | Transaksi diproses | Midtrans memproses pembayaran | Snap popup (loading) |

### Stage 5: Payment Result

| Hasil | User Redirect | Backend Action | Halaman Akhir |
|---|---|---|---|
| **Success** | Callback `onSuccess` → redirect ke `payment.success` | `finishRedirect()` verifikasi status via `Transaction::status()`, mark order as paid, dispatch `PaymentSuccess` | `/{locale}/payment/success` |
| **Pending** | Callback `onPending` → redirect ke `payment.pending` | `unfinishRedirect()` redirect ke halaman pending | `/{locale}/payment/pending` |
| **Error** | Callback `onError` → redirect ke `payment.error` | — | `/{locale}/payment/error` |
| **Close** | Popup ditutup (tanpa redirect) | `onClose` — enable kembali tombol Pay Now | Tetap di Checkout |

### Stage 6: Server-to-Server Callback

| Langkah | Proses |
|---|---|
| 6.1 | Midtrans mengirim POST notification ke `/api/payment/callback` |
| 6.2 | `PaymentController::callback()` → `handleCallback()` |
| 6.3 | **Signature Verification** — `hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey)` dicocokkan dengan `signature_key` dari request |
| 6.4 | Parse `order_id` (format: `ORDER-{id}-{timestamp}`) → extract internal order ID |
| 6.5 | Cari Order berdasarkan ID |
| 6.6 | Jika status `settlement` atau `capture` + `fraud_status = accept`: mark as paid, dispatch `PaymentSuccess` event |
| 6.7 | Jika status `deny`/`cancel`/`expire`/`failure`: update order status ke `failed` |
| 6.8 | Jika status `refund`/`partial_refund`: update order status ke `refunded` |
| 6.9 | Return `200 OK` |

### Stage 7: Post-Payment

| Langkah | Proses |
|---|---|
| 7.1 | `PaymentSuccess` event di-dispatch |
| 7.2 | `TriggerOrderEmails` listener (queued) menjalankan 2 job: |
| 7.3 | Job 1: Kirim email **Order Confirmation** ke customer |
| 7.4 | Job 2: Kirim email **Download Link** (signed URL) ke customer |
| 7.5 | Customer bisa download produk via link di email atau via dashboard customer |

---

## 3. Detail Alur per Langkah

### 3.1 Product Detail Page

**Route:** `/{locale}/products/{product}` (name: `products.show`)  
**Component:** `App\Livewire\ProductDetail`  
**View:** `resources/views/livewire/product-detail.blade.php`

Elemen-elemen di halaman:
- Gallery produk (cover image + gallery images)
- Judul, deskripsi singkat, deskripsi lengkap
- Harga produk
- Tombol **"Buy Now"** → link ke `route('checkout.create', $product)`
- Informasi keamanan: "Secure checkout with Midtrans"
- Related products section

### 3.2 Checkout Page

**Route:** `/{locale}/checkout/{product}` (name: `checkout.create`)  
**Middleware:** `auth`, `verified`  
**Component:** `App\Livewire\CheckoutForm`  
**View:** `resources/views/livewire/checkout-form.blade.php`

Elemen-elemen di halaman:
- Ringkasan produk (thumbnail, judul, harga)
- (Commented out) Kupon diskon
- Subtotal
- Discount (jika ada)
- Total
- Tombol **"Pay Now"**
- JavaScript: load `snap.js` dari Midtrans, listener event `snap-token-ready`

### 3.3 Payment Result Pages

**Success Page** (`/{locale}/payment/success`):
- Pesan sukses
- Informasi email notifikasi akan dikirim
- Tombol "Back to Home"

**Pending Page** (`/{locale}/payment/pending`):
- Pesan pending
- Informasi akan dinotifikasi setelah konfirmasi
- Tombol "Back to Home"

**Error Page** (`/{locale}/payment/error`):
- Pesan gagal
- Tombol "Try Again"

---

## 4. Data Flow Diagram

```
┌──────────────┐      ┌──────────────────┐      ┌───────────────────┐
│   Browser     │      │   Laravel App    │      │  Midtrans Server  │
│   (User)      │      │   (Backend)      │      │                   │
└──────┬───────┘      └────────┬─────────┘      └─────────┬─────────┘
       │                       │                          │
       │  GET /product/{id}    │                          │
       │──────────────────────>│                          │
       │  HTML + Product Data  │                          │
       │<──────────────────────│                          │
       │                       │                          │
       │  GET /checkout/{id}   │                          │
       │──────────────────────>│                          │
       │  Checkout Form (HTML) │                          │
       │  + snap.js + client_key│                         │
       │<──────────────────────│                          │
       │                       │                          │
       │  Click "Pay Now"      │                          │
       │──────────────────────>│                          │
       │                       │  POST /v1/transactions   │
       │                       │  (Snap::createTransaction)│
       │                       │─────────────────────────>│
       │                       │                          │
       │                       │  { token, redirect_url } │
       │                       │<─────────────────────────│
       │                       │                          │
       │  Browser Event:       │                          │
       │  snap-token-ready     │                          │
       │<──────────────────────│                          │
       │                       │                          │
       │  window.snap.pay()    │                          │
       │─────────────────────────────────────────────────>│
       │                       │                          │
       │  Snap Popup UI        │                          │
       │<─────────────────────────────────────────────────│
       │                       │                          │
       │  User input card data │                          │
       │─────────────────────────────────────────────────>│
       │                       │                          │
       │  3DS / Processing     │                          │
       │<─────────────────────────────────────────────────│
       │                       │                          │
       │  onSuccess/onPending  │                          │
       │  /onError callback    │                          │
       │<─────────────────────────────────────────────────│
       │                       │                          │
       │  Redirect to:         │                          │
       │  /payment/finish      │                          │
       │──────────────────────>│                          │
       │                       │  GET /status/{id}        │
       │                       │─────────────────────────>│
       │                       │  transaction_status      │
       │                       │<─────────────────────────│
       │                       │                          │
       │  Redirect to:         │                          │
       │  /payment/success     │                          │
       │<──────────────────────│                          │
       │                       │                          │
       │                       │  POST /api/payment/      │
       │                       │  callback               │
       │                       │  (server-to-server)     │
       │                       │<─────────────────────────│
       │                       │                          │
       │                       │  Verify Signature        │
       │                       │  Update Order Status     │
       │                       │  Dispatch Event          │
       │                       │                          │
       │                       │  200 OK                  │
       │                       │─────────────────────────>│
```

---

## 5. API Calls & Payload

### 5.1 Create Snap Transaction

**Call:** `Midtrans\Snap::createTransaction($payload)`  

**Payload yang dikirim ke Midtrans:**

```json
{
  "transaction_details": {
    "order_id": "ORDER-{id}-{timestamp}",
    "gross_amount": <integer>
  },
  "customer_details": {
    "first_name": "<customer name>",
    "email": "<customer email>"
  },
  "item_details": [
    {
      "id": "<product_id>",
      "price": <integer>,
      "quantity": 1,
      "name": "<product name>"
    }
  ],
  "callbacks": {
    "finish": "https://{domain}/{locale}/payment/finish",
    "unfinish": "https://{domain}/{locale}/payment/unfinish",
    "error": "https://{domain}/{locale}/payment/error"
  },
  "notification_url": "https://{domain}/api/payment/callback",
  "enabled_payments": ["credit_card"]
}
```

**Response dari Midtrans:**

```json
{
  "token": "<snap-token>",
  "redirect_url": "https://app.sandbox.midtrans.com/snap/v2/vtweb/{token}"
}
```

### 5.2 Payment Notification (Server Callback)

**Endpoint:** `POST /api/payment/callback`  
**Origin:** Midtrans Server

**Payload dari Midtrans:**

```json
{
  "transaction_time": "2024-01-01 10:00:00",
  "transaction_id": "<trans-id>",
  "transaction_status": "settlement",
  "status_code": "200",
  "merchant_id": "<merchant-id>",
  "gross_amount": "100000.00",
  "order_id": "ORDER-{id}-{timestamp}",
  "signature_key": "<sha512-hash>",
  "fraud_status": "accept",
  "payment_type": "credit_card",
  "currency": "IDR"
}
```

### 5.3 Signature Verification

Metode: SHA512 hash

```
signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey)
```

**Contoh:**
```
Input:    "ORDER-1-1234567890200200IDR  ServerKey"
Output:   <64-character hex string>
```

### 5.4 Status Check (Finish Redirect)

**Call:** `Midtrans\Transaction::status($transactionId)`  

**Response:**
```json
{
  "transaction_status": "settlement",
  "transaction_id": "<trans-id>",
  "order_id": "ORDER-{id}-{timestamp}",
  "gross_amount": "100000.00",
  "payment_type": "credit_card",
  "transaction_time": "2024-01-01 10:00:00",
  "status_code": "200",
  ...
}
```

---

## 6. Callback & Redirect Flow

### Redirect Flow (via Browser)

```
User selesai di Snap popup
         │
         ├── onSuccess ──> /payment/finish?order_id=ORDER-{id}&transaction_id={tid}&status_code=200
         │                      │
         │                      ├── checkStatus(transaction_id) → settlement
         │                      │       └── markAsPaid() + dispatch PaymentSuccess
         │                      └── redirect → /payment/success
         │
         ├── onPending ──> /payment/unfinish → redirect → /payment/pending
         │
         └── onError ────> redirect → /payment/error
```

### Server-to-Server Callback Flow

```
Midtrans Server → POST /api/payment/callback
         │
         ├── Validate signature (SHA512)
         │       └── Gagal → Log error, return 200 OK
         │
         ├── Parse order_id → extract ID → cari Order
         │       └── Not found → return 200 OK
         │
         ├── Status = settlement / capture + fraud accept
         │       └── markAsPaid() → dispatch PaymentSuccess
         │
         ├── Status = deny / cancel / expire / failure
         │       └── update status → failed
         │
         └── Status = refund / partial_refund
                 └── update status → refunded
```

---

## 7. Status Mapping

### Order Status (App)

| Status | Deskripsi | Transisi |
|---|---|---|
| `pending` | Order baru dibuat | → `creating_payment` |
| `creating_payment` | Sedang memproses pembayaran ke Midtrans | → `awaiting_payment` / `pending` |
| `awaiting_payment` | Snap token sudah didapat, menunggu pembayaran | → `paid` / `failed` / `expired` |
| `paid` | Pembayaran berhasil | final |
| `failed` | Pembayaran gagal | final |
| `expired` | Pembayaran kadaluwarsa | final |
| `refunded` | Transaksi di-refund | final |

### Payment Status (App)

| Status | Deskripsi |
|---|---|
| `pending` | Payment record dibuat, menunggu notifikasi |
| `success` | Pembayaran terkonfirmasi |
| `failed` | Pembayaran gagal |

### Midtrans Transaction Status → App Status Mapping

| Midtrans Status | Fraud Status | App Order Status | App Payment Status |
|---|---|---|---|
| `settlement` | — | `paid` | `success` |
| `capture` | `accept` | `paid` | `success` |
| `capture` | `deny` / `challenge` | `failed` / `pending` | `failed` / `pending` |
| `pending` | — | `awaiting_payment` | `pending` |
| `deny` | — | `failed` | `failed` |
| `cancel` | — | `failed` | `failed` |
| `expire` | — | `expired` | `failed` |
| `failure` | — | `failed` | `failed` |
| `refund` | — | `refunded` | `success` |
| `partial_refund` | — | `refunded` | `success` |

---

## Lampiran

### A. Daftar File Terkait

| Path | Fungsi |
|---|---|
| `app/Livewire/CheckoutForm.php` | Checkout page logic (create order, charge Midtrans) |
| `app/Livewire/ProductDetail.php` | Product detail page |
| `app/Payments/Gateways/MidtransGateway.php` | Midtrans Snap integration (charge, callback, status) |
| `app/Payments/PaymentManager.php` | Payment gateway manager |
| `app/Http/Controllers/Payment/PaymentController.php` | Callback handler + redirect handler |
| `app/Services/OrderService.php` | Order creation & status management |
| `app/Services/CurrencyService.php` | Currency conversion |
| `config/payment.php` | Payment gateway configuration |
| `resources/views/livewire/checkout-form.blade.php` | Checkout form view |
| `resources/views/payment/success.blade.php` | Payment success page |
| `resources/views/payment/pending.blade.php` | Payment pending page |
| `resources/views/payment/error.blade.php` | Payment error page |

### B. Environment Variables

| Variable | Value |
|---|---|
| `PAYMENT_GATEWAY` | `midtrans` |
| `MIDTRANS_SERVER_KEY` | `<server-key>` |
| `MIDTRANS_CLIENT_KEY` | `<client-key>` |
| `MIDTRANS_MERCHANT_ID` | `<merchant-id>` |
| `MIDTRANS_IS_PRODUCTION` | `false` (sandbox) / `true` (production) |

### C. Database Tables Terkait

| Table | Key Columns |
|---|---|
| `orders` | `id`, `order_number`, `user_id`, `total`, `currency_code`, `payment_currency_code`, `exchange_rate`, `status` |
| `order_items` | `id`, `order_id`, `product_id`, `product_name`, `price`, `quantity` |
| `payments` | `id`, `order_id`, `gateway`, `gateway_transaction_id`, `snap_token`, `redirect_url`, `amount`, `status`, `raw_response` |
