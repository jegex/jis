<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_no ?? 'Invoice' }}</title>

    <style>
        @page {
            size: A4;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #222;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        body {
            width: 210mm;
            min-height: 297mm;
        }

        .page {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            padding: 18mm 13mm 22mm 13mm;
        }

        .header {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: top;
        }

        .header-left {
            width: 58%;
        }

        .header-right {
            width: 42%;
            text-align: left;
            padding-top: 1mm;
        }

        .logo {
            width: 42mm;
            height: auto;
            display: block;
            margin-bottom: 8mm;
        }

        .company-address {
            line-height: 1.42;
        }

        .document-title {
            margin: 0 0 12mm 0;
            font-size: 24px;
            line-height: 1;
            font-weight: 700;
            color: #111;
            text-align: left;
        }

        .meta-table,
        .customer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 1.2mm 0;
            vertical-align: top;
            font-size: 11px;
        }

        .meta-label {
            width: 33mm;
        }

        .meta-colon {
            width: 5mm;
            text-align: center;
        }

        .customer {
            margin-top: 8mm;
            line-height: 1.4;
        }

        .customer-title {
            font-weight: 700;
            margin-bottom: 0.5mm;
        }

        .customer-table td {
            padding: 0.7mm 0;
            vertical-align: top;
        }

        .customer-label {
            width: 18mm;
        }

        .customer-colon {
            width: 4mm;
            text-align: center;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 7mm;
            table-layout: fixed;
        }

        .items th {
            background: #080884;
            color: #fff;
            border: 1px solid #222;
            padding: 1.5mm 2mm;
            font-size: 11px;
            font-weight: 400;
            text-align: left;
        }

        .items td {
            border: 1px solid #222;
            padding: 2mm;
            vertical-align: middle;
            font-size: 11px;
        }

        .items .no {
            width: 12%;
            text-align: center;
        }

        .items .description {
            width: 43%;
        }

        .items .qty {
            width: 13%;
            text-align: center;
        }

        .items .price,
        .items .amount {
            width: 16%;
            text-align: right;
        }

        .items .unit {
            width: 12%;
            text-align: center;
        }

        .totals {
            width: 42%;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 4mm;
        }

        .totals td {
            padding: 1.4mm 2mm;
            border-bottom: 1px solid #aaa;
        }

        .totals .label {
            text-align: right;
            font-weight: 600;
        }

        .totals .value {
            text-align: right;
            width: 42%;
        }

        .totals .grand-total td {
            border-top: 2px solid #080884;
            border-bottom: 2px solid #080884;
            font-weight: 700;
            font-size: 12px;
        }

        .remarks {
            margin-top: 7mm;
            border: 1px solid #222;
        }

        .remarks-title {
            background: #080884;
            color: #fff;
            padding: 1.5mm 2mm;
            font-weight: 700;
        }

        .remarks-body {
            padding: 1.5mm 2mm 2mm 2mm;
            line-height: 1.45;
        }

        .remarks-body ol {
            margin: 0;
            padding-left: 5mm;
        }

        .signature {
            width: 42%;
            margin-left: auto;
            margin-top: 8mm;
            text-align: center;
            min-height: 35mm;
        }

        .signature .approved {
            margin-bottom: 1.5mm;
        }

        .signature .company {
            font-weight: 700;
        }

        .signature-space {
            height: 17mm;
        }

        .signature .name {
            margin-top: 1mm;
        }

        .footer {
            position: absolute;
            left: 13mm;
            right: 13mm;
            bottom: 10mm;
            font-size: 11px;
            font-weight: 700;
        }

        .text-right {
            text-align: right;
        }

        .nowrap {
            white-space: nowrap;
        }

        @media print {
            body {
                width: 210mm;
                min-height: 297mm;
            }

            .page {
                page-break-after: always;
            }
        }
    </style>
</head>

<body>
<div class="page">

    <div class="header">
        <div class="header-left">
            @php
                $logoPath = public_path('images/dki-logo.png');
                $logoData = file_exists($logoPath)
                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                    : null;
            @endphp

            @if ($logoData)
                <img src="{{ $logoData }}" alt="DKI" class="logo">
            @else
                <div style="font-size:30px;font-weight:700;color:#080884;margin-bottom:8mm;">DK<span style="color:#f00;">I</span></div>
            @endif

            <div class="company-address">
                <div>Gold Coast Office, Eifel Tower 12-E.</div>
                <div>Jl. Pantai Indah Kapuk, Jakarta Utara,</div>
                <div>DKI Jakarta.</div>
                <div>Phone: +62 812 1314 1751</div>
                <div>Email: info@dkikonsultan.com</div>
            </div>
        </div>

        <div class="header-right">
            <h1 class="document-title">INVOICE</h1>

            <table class="meta-table">
                <tr>
                    <td class="meta-label">Date</td>
                    <td class="meta-colon">:</td>
                    <td>{{ $invoice->date ?? now()->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Invoice No.</td>
                    <td class="meta-colon">:</td>
                    <td>{{ $invoice->invoice_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Sales Order No.</td>
                    <td class="meta-colon">:</td>
                    <td>{{ $invoice->sales_order_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Work Order No.</td>
                    <td class="meta-colon">:</td>
                    <td>{{ $invoice->work_order_no ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="customer">
        <div class="customer-title">Customer</div>

        <table class="customer-table">
            <tr>
                <td class="customer-label">Name</td>
                <td class="customer-colon">:</td>
                <td>{{ $invoice->customer_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="customer-label">Address</td>
                <td class="customer-colon">:</td>
                <td>{{ $invoice->customer_address ?? '-' }}</td>
            </tr>
            <tr>
                <td class="customer-label">Phone</td>
                <td class="customer-colon">:</td>
                <td>{{ $invoice->customer_phone ?? '-' }}</td>
            </tr>
            <tr>
                <td class="customer-label">Email</td>
                <td class="customer-colon">:</td>
                <td>{{ $invoice->customer_email ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
        <tr>
            <th class="no">No.</th>
            <th class="description">Item Description</th>
            <th class="qty">Quantity</th>
            <th class="unit">Unit</th>
            <th class="price">Unit Price</th>
            <th class="amount">Amount</th>
        </tr>
        </thead>

        <tbody>
        @forelse (($invoice->items ?? []) as $index => $item)
            @php
                $qty = (float) ($item->quantity ?? $item['quantity'] ?? 0);
                $unitPrice = (float) ($item->unit_price ?? $item['unit_price'] ?? 0);
                $amount = $qty * $unitPrice;
            @endphp
            <tr>
                <td class="no">{{ $index + 1 }}</td>
                <td class="description">{{ $item->description ?? $item['description'] ?? '-' }}</td>
                <td class="qty">{{ rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.') }}</td>
                <td class="unit">{{ $item->unit ?? $item['unit'] ?? '-' }}</td>
                <td class="price nowrap">Rp {{ number_format($unitPrice, 0, ',', '.') }}</td>
                <td class="amount nowrap">Rp {{ number_format($amount, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td class="no">1</td>
                <td class="description">Hull Construction Drawings / Machinery Drawings / Electrical Drawings / Other Drawings.</td>
                <td class="qty">1</td>
                <td class="unit">package</td>
                <td class="price">Rp 0</td>
                <td class="amount">Rp 0</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    @php
        $subtotal = (float) ($invoice->subtotal ?? 0);
        $tax = (float) ($invoice->tax ?? 0);
        $discount = (float) ($invoice->discount ?? 0);
        $grandTotal = (float) ($invoice->grand_total ?? ($subtotal - $discount + $tax));
    @endphp

    <table class="totals">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value nowrap">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
        </tr>

        @if ($discount > 0)
            <tr>
                <td class="label">Discount</td>
                <td class="value nowrap">- Rp {{ number_format($discount, 0, ',', '.') }}</td>
            </tr>
        @endif

        @if ($tax > 0)
            <tr>
                <td class="label">Tax</td>
                <td class="value nowrap">Rp {{ number_format($tax, 0, ',', '.') }}</td>
            </tr>
        @endif

        <tr class="grand-total">
            <td class="label">Grand Total</td>
            <td class="value nowrap">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="remarks">
        <div class="remarks-title">Remarks:</div>
        <div class="remarks-body">
            <ol>
                <li>The drawing will be prepared based on the requirements and specifications provided.</li>
                <li>The estimated completion time is approximately 2 weeks from the date of order confirmation.</li>
                <li>Upon completion, the final drawing will be delivered in PDF format.</li>
                <li>{{ $invoice->remark_confirmation ?? 'Please review the invoice and supporting deliverables upon receipt.' }}</li>
            </ol>
        </div>
    </div>

    <div class="signature">
        <div class="approved">Approved by (Customer)</div>
        <div class="company">{{ $invoice->customer_company ?? 'Company Name' }}</div>
        <div class="signature-space"></div>
        <div class="name">Name &amp; Signature</div>
    </div>

    <div class="footer">
        DKI | PT Desain Konsultan Indonesia
    </div>

</div>
</body>
</html>
