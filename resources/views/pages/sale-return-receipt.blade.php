<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Return {{ $return->return_no }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f4f6;
            font-family: 'Courier New', monospace;
            color: #000;
        }

        .toolbar {
            padding: 10px;
            text-align: center;
            background: #fff;
            border-bottom: 1px solid #ddd;
            font-family: Arial, sans-serif;
        }

        .toolbar a,
        .toolbar button {
            display: inline-block;
            margin: 0 4px;
            padding: 6px 12px;
            border: 1px solid #ff9f43;
            background: #ff9f43;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
        }

        .toolbar a.light {
            background: #fff;
            color: #ff9f43;
        }

        .flash {
            padding: 8px;
            text-align: center;
            background: #e8f7ee;
            color: #1b6b3a;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .receipt {
            background: #fff;
            margin: 12px auto;
            padding: 4mm;
            font-size: 12px;
            line-height: 1.4;
        }

        @if($size ==='a4') .receipt {
            width: 190mm;
            font-size: 14px;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        @else .receipt {
            width: 76mm;
        }

        @page {
            size: 80mm auto;
            margin: 0;
        }

        @endif .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .logo {
            max-height: 60px;
            max-width: 100%;
            margin-bottom: 4px;
        }

        .shop-name {
            font-size: 16px;
            font-weight: bold;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 6px;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 1px 0;
        }

        .total-row td {
            font-weight: bold;
            font-size: 14px;
        }

        .muted {
            font-size: 11px;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar,
            .flash {
                display: none;
            }

            .receipt {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print</button>
        <a href="{{ route('sales-returns') }}" class="light">Sales Returns</a>
        <a href="{{ route('sales-list') }}" class="light">Sales List</a>
        @if($size === 'a4')
        <a href="{{ route('sales-returns.receipt', $return) }}" class="light">80mm</a>
        @else
        <a href="{{ route('sales-returns.receipt', [$return, 'size' => 'a4']) }}" class="light">A4</a>
        @endif
    </div>

    @if(session('status'))
    <div class="flash">{{ session('status') }}</div>
    @endif

    <div class="receipt">
        <div class="center">
            @if($shop->logoUrl())
            <img src="{{ $shop->logoUrl() }}" alt="" class="logo">
            @endif
            <div class="shop-name">{{ $shop->shop_name }}</div>
            @if($shop->fullAddress())<div>{{ $shop->fullAddress() }}</div>@endif
            @if($shop->phone)<div>Tel: {{ $shop->phone }}</div>@endif
            @if($shop->tax_number)<div>NTN/STRN: {{ $shop->tax_number }}</div>@endif
            <div class="title">RETURN / REFUND</div>
        </div>

        <div class="line"></div>

        <table>
            <tr>
                <td>Return no</td>
                <td class="right">{{ $return->return_no }}</td>
            </tr>
            <tr>
                <td>Date</td>
                <td class="right">{{ $return->returned_at?->format('d M Y, h:i A') }}</td>
            </tr>
            <tr>
                <td>Original invoice</td>
                <td class="right">{{ $return->sale?->invoice_no ?? '-' }}</td>
            </tr>
            <tr>
                <td>Processed by</td>
                <td class="right">{{ $return->user?->name ?? '-' }}</td>
            </tr>
            @if($return->sale?->customer_name)
            <tr>
                <td>Customer</td>
                <td class="right">{{ $return->sale->customer_name }}</td>
            </tr>
            @endif
            @if($return->reason)
            <tr>
                <td>Reason</td>
                <td class="right">{{ $return->reason }}</td>
            </tr>
            @endif
        </table>

        <div class="line"></div>

        <table>
            @foreach($return->items as $item)
            <tr>
                <td colspan="2">{{ $item->product_name }}</td>
            </tr>
            <tr>
                <td>{{ \App\Models\Product::trimNumber($item->quantity) }} x {{ $shop->money($item->unit_price) }}</td>
                <td class="right">{{ $shop->money($item->refund_amount) }}</td>
            </tr>
            @endforeach
        </table>

        <div class="line"></div>

        <table>
            <tr class="total-row">
                <td>TOTAL REFUND</td>
                <td class="right">{{ $shop->money($return->refund_total) }}</td>
            </tr>
            <tr>
                <td>Refunded via</td>
                <td class="right">{{ $return->refundMethodLabel() }}</td>
            </tr>
            @if($return->hasLedgerPart())
            @if($return->cashAmount() > 0)
            <tr><td>Paid out</td><td class="right">{{ $shop->money($return->cashAmount()) }}</td></tr>
            @endif
            <tr><td>Taken off account</td><td class="right">{{ $shop->money($return->ledger_amount) }}</td></tr>
            @endif
        </table>

        @if((float) ($return->sale?->discount ?? 0) > 0)
        <div class="muted">Refund is net of the discount given on the original bill.</div>
        @endif

        <div class="line"></div>
        <div class="center">Signature: ____________________</div>
    </div>

    @if(request()->boolean('print'))
    <script>window.addEventListener('load', function () { window.print(); });</script>
    @endif
</body>

</html>