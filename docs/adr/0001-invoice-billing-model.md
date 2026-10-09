# 1. Invoice billing model: down payment, VAT, due date, and references

Date: 2026-10-10

Status: Accepted

## Context

The Invoice document must reproduce professional-services invoices (see `Contoh Invoice.pdf`): a bill titled INVOICE with Quotation/PO references, a Due Date, a Down Payment line, VAT (PPN), an amount in words, and payment terms. Today an Invoice only carries a number and issue date; an Order carries no quotation/PO/DP concept, and the document is a generic delivery note.

Two ways to hold the new billing concepts were considered: make them global Invoice settings, or hold usage-specific values on the Order. References (quotation, PO) and the DP percentage vary per engagement, while the tax rate, due period, payment terms and letterhead are house-wide defaults.

## Decision

- Keep **one Invoice per Order**.
- Hold **per-Order**: `quotation_number`, `po_number`, `down_payment_percent`. Hold **global Invoice settings**: `invoice_due_days` (default 14), `invoice_tax_percent` (default 0), `invoice_payment_terms`, `invoice_banner`.
- The Invoice's **Total Amount is the amount billed now**, not the full Order total:
  - `base = subtotal − discount`
  - `dp = down_payment_percent > 0 ? round(base × down_payment_percent / 100) : base` (a blank or zero percentage bills the full base)
  - `tax = round(base × invoice_tax_percent / 100)`
  - `total = dp + tax`
- **Due Date** = issue date + `invoice_due_days`.
- **VAT** is a flat rate on the base: `Non-VAT` (zero) when the rate is 0, otherwise `PPN {rate}%`.
- **Amount in words** is derived at render time, localized to the Order's locale, using the currency's name and code.
- Item descriptions are a per-Order-Item snapshot, falling back to the product name when empty.

## Consequences

- The stored Order total and the Invoice's Total Amount intentionally diverge when a Down Payment is used; nothing rewrites the stored Order totals from DP/VAT.
- New columns on `orders` and `order_items`, plus four new settings, require a migration.
- Amount-in-words is a new locale-aware service, tested independently of the template.
