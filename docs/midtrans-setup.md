# Midtrans Setup

## Konfigurasi Dashboard Midtrans

### Notification URLs (POST)
Isi di **Midtrans Dashboard > Settings > Snap > General Settings**:

| Nama | Endpoint | Route |
|------|----------|-------|
| Payment Notification | `{{BASE_URL}}/api/payment/callback` | `payment.callback` |
| Recurring Notification | `{{BASE_URL}}/api/payment/recurring-callback` | `payment.recurring` |
| Pay Account Notification | `{{BASE_URL}}/api/payment/pay-account-callback` | `payment.pay-account` |

### Redirect URLs
Digunakan oleh Midtrans Snap sebagai fallback redirect (dikirim via payload transaksi):

| Nama | Endpoint | Route |
|------|----------|-------|
| Finish Redirect | `{{BASE_URL}}/payment/finish` | `payment.finish` |
| Unfinish Redirect | `{{BASE_URL}}/payment/unfinish` | `payment.unfinish` |
| Error Redirect | `{{BASE_URL}}/payment/error` | `payment.error` |

**Catatan:**
- `{{BASE_URL}}` = domain aplikasi (sesuaikan environment: `https://example.com` untuk production, `https://your-ngrok-url.ngrok.io` untuk local/sandbox)
- Semua notification endpoint POST menggunakan `withoutMiddleware(PreventRequestForgery::class)`
- Redirect endpoints menampilkan halaman status pembayaran yang sudah ada (success, pending, error)

## Environment Variables (.env)
```
PAYMENT_GATEWAY=midtrans
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_MERCHANT_ID=
MIDTRANS_IS_PRODUCTION=false
```
