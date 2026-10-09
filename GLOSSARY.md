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
