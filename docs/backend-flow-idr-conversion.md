# Backend Flow: Konversi ke IDR (Product Detail → Payment Success)

---

## Ringkasan

Produk digital bisa di-set dalam berbagai mata uang (USD, IDR, dll). Karena **Midtrans hanya menerima IDR**, sistem akan mendeteksi jika mata uang produk berbeda dari IDR dan melakukan konversi otomatis sebelum mengirim permintaan ke Midtrans.

---

## Flow Diagram

```
Product Detail (harga USD $10)
      │
      ▼
Checkout → Pay Now diklik
      │
      ├── 1. Create Order (currency_code = USD, total = 1000)
      │
      ├── 2. Cek: currency_code ≠ target_currency (IDR)?
      │         └── Ya → lakukan konversi
      │
      ├── 3. CurrencyService::convert()
      │         ├── Ambil rate USD (17000) dan IDR (1) dari DB
      │         ├── Hitung: amount / rate_from * rate_to
      │         │         → 1000 / 17000 * 1 = 170000 (IDR)
      │         └── Simpan hasil: converted_amount, exchange_rate
      │
      ├── 4. Update Order: exchange_rate, payment_currency_code
      │
      ├── 5. MidtransGateway::charge() dengan amount IDR
      │
      ├── 6. Snap::createTransaction() → Midtrans (amount dalam IDR)
      │
      └── 7. Callback → settlement → markAsPaid()
```

---

## Step-by-Step Detail

### Step 1: Product Detail Page

**File:** `app/Livewire/ProductDetail.php`  
**View:** `resources/views/livewire/product-detail.blade.php`

- Produk memiliki atribut `price` (disimpan dalam satuan terkecil) dan `currency_code`
- Contoh: harga **$10.00 USD** → `price = 1000`, `currency_code = 'USD'`
- Harga ditampilkan menggunakan helper `Str::price()`
- Tombol **"Buy Now"** mengarah ke `route('checkout.create', $product)`

### Step 2: Checkout — Mount Component

**File:** `app/Livewire/CheckoutForm.php:32-47`

```php
public function mount(Product $product): void
{
    $this->product = $product;
    $this->subtotal = (int) $product->price;  // 1000
    $this->total = $this->subtotal;            // 1000
}
```

- `subtotal` = harga produk (1000)
- `total` = subtotal - diskon (1000, jika tidak ada diskon)
- Produk tetap dalam currency asli (USD) di halaman checkout

### Step 3: Create Order

**File:** `app/Services/OrderService.php:21-79`  
**Dipanggil dari:** `CheckoutForm.php:98-104`

```php
$order = app(OrderService::class)->createOrder(
    product: $this->product,
    user: $user,
);
```

Data yang tersimpan di tabel `orders`:

| Kolom | Nilai | Keterangan |
|---|---|---|
| `currency_code` | `'USD'` | Currency asli produk |
| `subtotal` | `1000` | Harga produk (dalam cents) |
| `discount` | `0` | Diskon |
| `total` | `1000` | Total (dalam cents) |
| `status` | `'pending'` | Status awal |

### Step 4: Deteksi Perbedaan Currency

**File:** `CheckoutForm.php:106-138`

```php
$order->update(['status' => OrderStatus::CreatingPayment]);

$defaultGateway = config('payment.default', 'midtrans');
$targetCurrency = config("payment.types.{$defaultGateway}.target_currency", 'IDR');
```

- `target_currency` untuk Midtrans = **IDR** (dari `config/payment.php:18`)
- Cek: apakah `$order->currency_code` (USD) ≠ `$targetCurrency` (IDR)?
- **Ya** → lanjut ke konversi

### Step 5: Currency Conversion

**File:** `app/Services/CurrencyService.php:11-23`

```php
public function convert(float|int $amount, string $from, string $to): float
{
    $fromCurrency = Currency::where('code', $from)->firstOrFail();  // USD, rate=17000
    $toCurrency = Currency::where('code', $to)->firstOrFail();      // IDR, rate=1

    $defaultAmount = $amount / $fromCurrency->exchange_rate;
    // 1000 / 17000 = 0.0588...

    return round($defaultAmount * $toCurrency->exchange_rate, $toCurrency->decimal_place);
    // 0.0588 * 1 = 0.0588 → dibulatkan ke 0 desimal = 0
}
```

Ada 4 kali konversi yang dilakukan di `CheckoutForm.php:114-133`:

```php
// 1. Exchange rate (harga 1 unit currency asli ke IDR)
$exchangeRate = app(CurrencyService::class)->convert(
    1, $order->currency_code, $targetCurrency,
);
// convert(1, 'USD', 'IDR') = 17000

// 2. Total yang sudah dikonversi
$params['converted_amount'] = app(CurrencyService::class)->convert(
    $order->total, $order->currency_code, $targetCurrency,
);
// convert(1000, 'USD', 'IDR') = 170000 (IDR)

$params['converted_currency'] = $targetCurrency; // 'IDR'

// 3. Harga item yang sudah dikonversi
$params['converted_item_price'] = app(CurrencyService::class)->convert(
    $item->price, $order->currency_code, $targetCurrency,
);
// convert(1000, 'USD', 'IDR') = 170000

// 4. Diskon (jika ada)
if ($order->discount > 0) {
    $params['converted_discount'] = app(CurrencyService::class)->convert(
        $order->discount, $order->currency_code, $targetCurrency,
    );
}
```

### Step 6: Simpan Exchange Rate ke Order

**File:** `CheckoutForm.php:135-138`

```php
$order->update([
    'exchange_rate' => $exchangeRate,              // 17000
    'payment_currency_code' => $targetCurrency,    // 'IDR'
]);
```

Kolom baru di tabel `orders`:

| Kolom | Nilai |
|---|---|
| `exchange_rate` | `17000.0000` |
| `payment_currency_code` | `'IDR'` |

### Step 7: Kirim ke Midtrans Gateway

**File:** `CheckoutForm.php:144`

```php
$paymentResult = app('payment')->charge($order, $params);
```

Alur `PaymentManager::createDriver('midtrans')` → set config dari `config/payment.php`:

```php
// config/payment.php:midtrans
[
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'enabled_payments' => ['credit_card'],
    'target_currency' => 'IDR',
]
```

### Step 8: MidtransGateway — Build Snap Payload

**File:** `app/Payments/Gateways/MidtransGateway.php:47-129`

```php
public function charge(Order $order, array $params = []): PaymentResult
{
    $grossAmount = (int) ($params['converted_amount'] ?? $order->total);
    // 170000 (IDR)
    
    $itemPrice = (int) ($params['converted_item_price'] ?? $params['converted_amount'] ?? $item->price);
    // 170000 (IDR)

    $transactionDetails = [
        'order_id' => 'ORDER-'.$order->id.'-'.time(),
        'gross_amount' => $grossAmount,    // 170000
    ];
}
```

Payload Snap yang dikirim ke Midtrans:

```json
{
  "transaction_details": {
    "order_id": "ORDER-1-1712345678",
    "gross_amount": 170000
  },
  "customer_details": {
    "first_name": "John Doe",
    "email": "john@example.com"
  },
  "item_details": [
    {
      "id": "1",
      "price": 170000,
      "quantity": 1,
      "name": "Nama Produk"
    }
  ],
  "callbacks": {
    "finish": "https://domain.com/en/payment/finish",
    "unfinish": "https://domain.com/en/payment/unfinish",
    "error": "https://domain.com/en/payment/error"
  },
  "notification_url": "https://domain.com/api/payment/callback",
  "enabled_payments": ["credit_card"]
}
```

**Semua amount dalam payload sudah dalam IDR.**

### Step 9: Simpan Payment Record

**File:** `CheckoutForm.php:154-163`

```php
$order->payments()->create([
    'gateway' => 'midtrans',
    'gateway_transaction_id' => $paymentResult->transactionId,
    'gateway_status' => 'pending',
    'snap_token' => $paymentResult->snapToken,
    'redirect_url' => $paymentResult->redirectUrl,
    'currency_code' => $params['converted_currency'] ?? $order->currency_code,  // 'IDR'
    'amount' => (int) ($params['converted_amount'] ?? $order->total),           // 170000
    'status' => PaymentStatus::Pending,
]);
```

Data di tabel `payments`:

| Kolom | Nilai |
|---|---|
| `gateway` | `'midtrans'` |
| `currency_code` | `'IDR'` |
| `amount` | `170000` |
| `snap_token` | `'<snap-token>'` |
| `status` | `'pending'` |

### Step 10: Update Order Status & Kirim Snap Token

**File:** `CheckoutForm.php:165-169`

```php
$order->update(['status' => OrderStatus::AwaitingPayment]);

session()->flash('snap_token', $paymentResult->snapToken);

$this->dispatch('snap-token-ready', token: $paymentResult->snapToken);
```

### Step 11: Frontend — Snap Popup

**File:** `resources/views/livewire/checkout-form.blade.php:86-124`

- JavaScript listener menangkap event `snap-token-ready`
- Memanggil `window.snap.pay(token, callbacks)`
- Midtrans Snap popup muncul, user input kartu kredit
- Amount yang dibayar = **Rp170.000** (sudah IDR)

### Step 12: Callback dari Midtrans

**File:** `app/Http/Controllers/Payment/PaymentController.php:88-155`

```php
// Signature verification
$calculatedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
```

**PENTING:** `$grossAmount` dari callback adalah **string** (contoh: `"170000.00"`), bukan integer.

### Step 13: Mark as Paid

**File:** `app/Services/OrderService.php:90-119`

```php
public function markAsPaid(Order $order, string $gateway, string $transactionId, ?string $orderId = null): void
{
    DB::transaction(function () use ($order, $gateway, $transactionId, $orderId) {
        $lockedOrder = Order::lockForUpdate()->findOrFail($order->id);

        if ($lockedOrder->status === OrderStatus::Paid) {
            return;  // Idempotent: sudah paid, skip
        }

        $lockedOrder->update([
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
        ]);

        $lockedOrder->payments()->updateOrCreate(
            ['gateway_transaction_id' => $orderId ?? $transactionId],
            [
                'gateway' => $gateway,
                'gateway_transaction_id' => $transactionId,
                'gateway_status' => 'success',
                'status' => PaymentStatus::Success->value,
                'paid_at' => now(),
                'currency_code' => $lockedOrder->currency_code,  // 'USD' (currency asli)
                'amount' => (int) $lockedOrder->total,           // 1000 (USD cents)
            ],
        );
    });
}
```

**Catatan:** Saat mark as paid, `currency_code` dan `amount` dikembalikan ke **currency asli produk** (USD), bukan IDR hasil konversi.

### Step 14: Dispatch Event & Kirim Email

**File:** `PaymentController.php:133`

```php
PaymentSuccess::dispatch($order);
```

Listener `TriggerOrderEmails` menjalankan 2 job:
1. Kirim email **Order Confirmation**
2. Kirim email **Download Link** (signed URL untuk download)

---

## Ringkasan Data per Step

| Step | Currency | Amount | Keterangan |
|---|---|---|---|
| Product Detail (DB) | USD | 1000 | `price = 1000` (cents, = $10.00) |
| Order Created | USD | 1000 | `currency_code = USD`, `total = 1000` |
| Setelah Konversi | IDR | 170000 | `converted_amount = 170000` |
| Payload ke Midtrans | IDR | 170000 | `gross_amount = 170000` |
| Payment Record | IDR | 170000 | `amount = 170000`, `currency_code = IDR` |
| Callback dari Midtrans | IDR | "170000.00" | `gross_amount` sebagai **string** |
| Mark as Paid | USD | 1000 | `amount = 1000` (kembali ke currency asli) |

---

## File-file Terkait

| File | Peran |
|---|---|
| `app/Livewire/CheckoutForm.php` | Orchestrator: create order, konversi, charge |
| `app/Services/CurrencyService.php` | Logic konversi mata uang |
| `app/Services/OrderService.php` | Create order, mark as paid |
| `app/Models/Currency.php` | Model currency dengan exchange_rate |
| `app/Payments/Gateways/MidtransGateway.php` | Build payload Snap, kirim ke Midtrans |
| `app/Http/Controllers/Payment/PaymentController.php` | Handle callback & finish redirect |
| `config/payment.php` | Konfigurasi gateway & target_currency |
| `database/seeders/CurrencySeeder.php` | Data currency (USD rate 17000, IDR rate 1) |
