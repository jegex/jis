# JIS

Toko online (Laravel + Filament): katalog produk, checkout, pembayaran, dan dokumen terkait pesanan.

## Language

**Order**:
A customer's request to purchase products, recorded at checkout or created manually by an admin.
_Avoid_: transaction, purchase, pesanan

**Order Number**:
The unique human-facing identifier of an order, shown in URLs and admin lists; generated automatically from the configurable format at order creation.
_Avoid_: order code, invoice number

**Invoice**:
A billing document issued for a paid order, carrying its own sequential document number.
_Avoid_: order number, receipt

**Invoice Number**:
The sequential document number of an Invoice (`INV/2026/10/0001`), independent of the Order Number; generated automatically from the configurable format when the invoice is issued.
_Avoid_: order number

**Quantity**:
The number of units of a single product in an Order Item, chosen at checkout; the line total is the unit price multiplied by the quantity.
_Avoid_: amount, count, units

**Work Order Number**:
The Order Number as printed on an Invoice, so the customer can tie the Invoice back to their Order.
_Avoid_: sales order number, order code

**Quotation Number**:
The customer-facing reference of the quote an Order was accepted from; recorded per Order and printed on its Invoice.
_Avoid_: quote id, estimate number

**Purchase Order Number** (PO Number):
The customer's procurement reference for an Order; recorded per Order and printed on its Invoice.
_Avoid_: po, purchase reference

**Down Payment** (DP):
The portion of an Order's billable base charged up front on an Invoice, expressed as a percentage of the base; when set, the Invoice's Total Amount is the amount billed now (DP plus VAT).
_Avoid_: deposit, advance, uang muka

**VAT** (PPN):
Value-added tax applied to an Invoice's base at a globally configured rate; shown as `PPN {rate}%`, or `Non-VAT` with a zero amount when the rate is 0.
_Avoid_: tax, pajak, GST

**Due Date**:
The date by which an Invoice is payable, derived as the issue date plus the configured due-days period.
_Avoid_: payment date, expiry

**Payment Terms**:
Reusable boilerplate (including bank details) printed on an Invoice describing how payment should be made.
_Avoid_: payment method, payment info

**Amount in Words**:
An Invoice's Total Amount spelled out in words, in the Order's locale, using the currency's name and code.
_Avoid_: total in words, terbilang

**Guest Order**:
An Order placed without a User, identified by the orderer's email address.
_Avoid_: anonymous order, guest purchase

**Order Access Token**:
A rotatable secret embedded in an Order's access URL that grants its bearer access to that Order's digital items without login.
_Avoid_: download token, magic link, access key

**Order Access Page**:
The page at `/order/{token}` listing a digital Order's items together with its download and invoice actions.
_Avoid_: download page, delivery page

**Claim**:
The attachment of a Guest Order to a User account when that User registers with the same verified email.
_Avoid_: merge, link
