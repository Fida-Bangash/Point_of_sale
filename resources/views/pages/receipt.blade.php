<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Receipt {{ $sale->invoice_no }}</title>
	<style>
		* { box-sizing: border-box; }
		body { margin: 0; background: #f3f4f6; font-family: 'Courier New', monospace; color: #000; }
		.toolbar { padding: 10px; text-align: center; background: #fff; border-bottom: 1px solid #ddd; font-family: Arial, sans-serif; }
		.toolbar a, .toolbar button { display: inline-block; margin: 0 4px; padding: 6px 12px; border: 1px solid #ff9f43; background: #ff9f43; color: #fff; border-radius: 4px; text-decoration: none; cursor: pointer; font-size: 13px; }
		.toolbar a.light { background: #fff; color: #ff9f43; }

		.receipt { background: #fff; margin: 12px auto; padding: 4mm; font-size: 12px; line-height: 1.4; }
		@if($size === 'a4')
		.receipt { width: 190mm; font-size: 14px; }
		@page { size: A4; margin: 10mm; }
		@else
		.receipt { width: 76mm; }
		@page { size: 80mm auto; margin: 0; }
		@endif

		.center { text-align: center; }
		.right { text-align: right; }
		.logo { max-height: 60px; max-width: 100%; margin-bottom: 4px; }
		.shop-name { font-size: 16px; font-weight: bold; }
		.line { border-top: 1px dashed #000; margin: 6px 0; }
		table { width: 100%; border-collapse: collapse; }
		td { vertical-align: top; padding: 1px 0; }
		.total-row td { font-weight: bold; font-size: 14px; }

		@media print {
			body { background: #fff; }
			.toolbar { display: none; }
			.receipt { margin: 0; }
		}
	</style>
</head>
<body>
	<div class="toolbar">
		<button type="button" onclick="window.print()">Print</button>
		<a href="{{ route('pos') }}">New Sale</a>
		<a href="{{ route('sales-list') }}" class="light">Sales List</a>
		@if($size === 'a4')
			<a href="{{ route('sales.receipt', $sale) }}" class="light">80mm</a>
		@else
			<a href="{{ route('sales.receipt', [$sale, 'size' => 'a4']) }}" class="light">A4</a>
		@endif
	</div>

	<div class="receipt">
		<div class="center">
			@if($shop->logoUrl())
				<img src="{{ $shop->logoUrl() }}" alt="" class="logo">
			@endif
			<div class="shop-name">{{ $shop->shop_name }}</div>
			@if($shop->fullAddress())<div>{{ $shop->fullAddress() }}</div>@endif
			@if($shop->phone)<div>Tel: {{ $shop->phone }}</div>@endif
			@if($shop->tax_number)<div>NTN/STRN: {{ $shop->tax_number }}</div>@endif
		</div>

		<div class="line"></div>

		<table>
			<tr><td>Invoice</td><td class="right">{{ $sale->invoice_no }}</td></tr>
			<tr><td>Date</td><td class="right">{{ $sale->sold_at?->format('d M Y, h:i A') }}</td></tr>
			<tr><td>Cashier</td><td class="right">{{ $sale->user?->name ?? '-' }}</td></tr>
			@if($sale->customer_name)
			<tr><td>Customer</td><td class="right">{{ $sale->customer_name }}</td></tr>
			@endif
		</table>

		<div class="line"></div>

		<table>
			@foreach($sale->items as $item)
			<tr><td colspan="2">{{ $item->product_name }}</td></tr>
			<tr>
				<td>{{ \App\Models\Product::trimNumber($item->quantity) }} x {{ $shop->money($item->unit_price) }}</td>
				<td class="right">{{ $shop->money($item->line_total) }}</td>
			</tr>
			@endforeach
		</table>

		<div class="line"></div>

		<table>
			<tr><td>Subtotal</td><td class="right">{{ $shop->money($sale->subtotal) }}</td></tr>
			@if((float) $sale->discount > 0)
			<tr><td>Discount</td><td class="right">- {{ $shop->money($sale->discount) }}</td></tr>
			@endif
			<tr class="total-row"><td>TOTAL</td><td class="right">{{ $shop->money($sale->total) }}</td></tr>
			@if((float) $sale->exchange_credit > 0)
			<tr><td>Exchange credit</td><td class="right">- {{ $shop->money($sale->exchange_credit) }}</td></tr>
			<tr><td>To pay</td><td class="right">{{ $shop->money($sale->total - $sale->exchange_credit) }}</td></tr>
			@endif
			<tr><td>{{ $sale->payment_method === \App\Models\Sale::CREDIT_METHOD ? 'Paid now' : 'Paid (' . $sale->methodLabel() . ')' }}</td><td class="right">{{ $shop->money($sale->paid) }}</td></tr>
			<tr><td>Change</td><td class="right">{{ $shop->money($sale->change_amount) }}</td></tr>
		</table>

		{{-- Customer account: only for a registered customer who has a balance or took credit on this bill --}}
		@if($sale->showsAccountSummary())
		<div class="line"></div>
		<table>
			<tr><td>Previous balance</td><td class="right">{{ $shop->money($sale->previous_balance) }}</td></tr>
			<tr class="total-row"><td>Credit on this bill</td><td class="right">{{ $shop->money($sale->credit_amount) }}</td></tr>
			<tr><td>New balance</td><td class="right">{{ $shop->money($sale->balanceAfter()) }}</td></tr>
		</table>
		@endif

		@if($shop->receipt_footer)
		<div class="line"></div>
		<div class="center">{{ $shop->receipt_footer }}</div>
		@endif
	</div>

		@if(request()->boolean('print'))
	<script>window.addEventListener('load', function () { window.print(); });</script>
	@endif
</body>
</html>