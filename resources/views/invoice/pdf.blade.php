<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('INVOICE') }} - {{ $invoice->number }}</title>

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
            margin: 36pt;
        }

        .page {
            position: relative;
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

        .logo-fallback {
            font-size: 30px;
            font-weight: 700;
            color: #080884;
            margin-bottom: 8mm;
            text-transform: uppercase;
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
            letter-spacing: 2px;
        }

        .meta-table,
        .customer-table,
        .items,
        .totals {
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

        .totals tr.grand-total td {
            border-top: 1px solid #080884;
            border-bottom: 1px solid #080884;
            font-weight: 700;
            font-size: 12px;
        }

        .box {
            margin-top: 7mm;
            border: 1px solid #222;
        }

        .box-title {
            background: #080884;
            color: #fff;
            padding: 1.5mm 2mm;
            font-weight: 700;
        }

        .box-body {
            padding: 2mm;
            line-height: 1.45;
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
            left: 0;
            right: 13mm;
            bottom: 10mm;
            font-size: 11px;
            font-weight: 700;
        }

        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>

<body>
<div class="page">

    <div class="header">
        <div class="header-left">
            @if ($logoData)
                <img src="{{ $logoData }}" alt="{{ $seller['name'] }}" class="logo">
            @else
                <div class="logo-fallback">{{ $seller['name'] }}</div>
            @endif

            <div class="company-address">
                {!! nl2br(e($seller['address'])) !!}
                @if ($seller['phone'])
                    <div>{{ __('Phone') }}: {{ $seller['phone'] }}</div>
                @endif
                @if ($seller['email'])
                    <div>{{ __('Email') }}: {{ $seller['email'] }}</div>
                @endif
                @if ($seller['npwp'])
                    <div>NPWP: {{ $seller['npwp'] }}</div>
                @endif
            </div>
        </div>

        <div class="header-right">
            <h1 class="document-title">{{ __('DELIVERY NOTE') }}</h1>

            <table class="meta-table">
                <tr>
                    <td class="meta-label">{{ __('Date') }}</td>
                    <td class="meta-colon">:</td>
                    <td>{{ ($order->paid_at ?? $order->created_at)->translatedFormat('j F Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="meta-label">{{ __('Sales Order No.') }}</td>
                    <td class="meta-colon">:</td>
                    <td>{{ $invoice->number }}</td>
                </tr>
                <tr>
                    <td class="meta-label">{{ __('Work Order No.') }}</td>
                    <td class="meta-colon">:</td>
                    <td>{{ $order->order_number }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="customer">
        <div class="customer-title">{{ __('Customer') }}</div>

        <table class="customer-table">
            <tr>
                <td class="customer-label">{{ __('Name') }}</td>
                <td class="customer-colon">:</td>
                <td>{{ $customer['name'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="customer-label">{{ __('Address') }}</td>
                <td class="customer-colon">:</td>
                <td>{{ $customer['address'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="customer-label">{{ __('Phone') }}</td>
                <td class="customer-colon">:</td>
                <td>{{ $customer['phone'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="customer-label">{{ __('Email') }}</td>
                <td class="customer-colon">:</td>
                <td>{{ $customer['email'] ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th class="no">No.</th>
                <th class="description">{{ __('Item Description') }}</th>
                <th class="qty">{{ __('Quantity') }}</th>
                <th class="price">{{ __('Unit Price') }}</th>
                <th class="amount">{{ __('Amount') }}</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($order->items as $item)
                <tr>
                    <td class="no">{{ $loop->iteration }}</td>
                    <td class="description">{{ $item->product_name }}</td>
                    <td class="qty">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }}</td>
                    <td class="price nowrap">{{ Str::price($item->price, $order->currency_code) }}</td>
                    <td class="amount nowrap">{{ Str::price($item->price * $item->quantity, $order->currency_code) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">&nbsp;</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">{{ __('Subtotal') }}</td>
            <td class="value nowrap">{{ Str::price($order->subtotal, $order->currency_code) }}</td>
        </tr>

        @if ((float) $order->discount > 0)
            <tr>
                <td class="label">
                    {{ __('Discount') }}
                    @if ($couponCode)
                        ({{ $couponCode }})
                    @endif
                </td>
                <td class="value nowrap">-{{ Str::price($order->discount, $order->currency_code) }}</td>
            </tr>
        @endif

        <tr class="grand-total">
            <td class="label">{{ __('Grand Total') }}</td>
            <td class="value nowrap">{{ Str::price($order->total, $order->currency_code) }}</td>
        </tr>
    </table>

    @if ($payment)
        <div class="box">
            <div class="box-title">{{ __('Payment information') }}</div>
            <div class="box-body">
                {{ __('Payment method') }}: {{ ucfirst($payment->gateway) }}<br>
                {{ __('Paid at') }}: {{ $payment->paid_at?->translatedFormat('j F Y H:i') }}<br>
                {{ __('Transaction ID') }}: {{ $payment->gateway_transaction_id ?? '-' }}
            </div>
        </div>
    @endif

    <div class="box">
        <div class="box-title">{{ __('Remarks') }}</div>
        <div class="box-body">
            {!! nl2br(e($footerNote)) !!}
        </div>
    </div>

    <div class="signature">
        <div class="approved">{{ __('Approved by (Customer)') }}</div>
        <div class="company">{{ $customer['name'] ?? '-' }}</div>
        <div class="signature-space"></div>
        <div class="name">{{ __('Name & Signature') }}</div>
    </div>

    <div class="footer">
        DKI | <span style="font-weight: 500;">PT Desain Konsultan Indonesia</span>
    </div>

</div>
</body>
</html>
