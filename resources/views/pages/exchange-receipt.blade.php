<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Exchange {{ $exchange->exchange_no }}</title>
	<style>
		* { box-sizing: border-box; }
		body { margin: 0; background: #f3f4f6; font-family: 'Courier New', monospace; color: #000; }
		.toolbar { padding: 10px; text-align: center; background: #fff; border-bottom: 1px solid #ddd; font-family: Arial, sans-serif; }
		.toolbar a, .toolbar button { display: inline-block; margin: 0 4px; padding: 6px 12px; border: 1px solid #ff9f43; background: #ff9f43; color: #fff; border-radius: 4px; text-decoration: none; cursor: pointer; font-size: 13px; }
		.toolbar a.light { background: #fff; color: #ff9f43; }
		.flash { padding: 8px; text-align: center; background: #e8f7ee; color: #1b6b3a; font-family: Arial, sans-serif; font-size: 13px; }

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
		.title { font-size: 14px; font-weight: bold; letter-spacing: 1px; margin-top: 6px; }
		.section { font-weight: bold; margin: 4px 0 2px; text-decoration: underline; }
		.line { border-top: 1px dashed #000; margin: 6px 0; }
		table { width: 100%; border-collapse: collapse; }
		td { vertical-align: top; padding: 1px 0; }
		.total-row td { font-weight: bold; font-size: 14px; }
		.muted { font-size: 11px; }

		@media print {
			body { background: #fff; }
			.toolbar, .flash { display: none; }
			.receipt { margin: 0; }
		}
	</style>
</head>
<body>
	<div class="toolbar">
		<button type="button" onclick="window.print()">Print</button>
		<a href="{{ route('exchanges') }}" class="light">Exchanges</a>
		<a href="{{ route('sales-list') }}" class="light">Sales List</a>
		@if($size === 'a4')
			<a href="{{ route('exchanges.receipt', $exchange) }}" class="light">80mm</a>
		@else
			<a href="{{ route('exchanges.receipt', [$exchange, 'size' => 'a4']) }}" class="light">A4</a>
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
			<div class="title">EXCHANGE</div>
		</div>

		<div class="line"></div>

		<table>
			<tr><td>Exchange no</td><td class="right">{{ $exchange->exchange_no }}</td></tr>
			<tr><td>Date</td><td class="right">{{ $exchange->exchanged_at?->format('d M Y, h:i A') }}</td></tr>
			<tr><td>Original invoice</td><td class="right">{{ $exchange->originalSale?->invoice_no ?? '-' }}</td></tr>
			<tr><td>New invoice</td><td class="right">{{ $exchange->newSale?->invoice_no ?? '-' }}</td></tr>
			<tr><td>Processed by</td><td class="right">{{ $exchange->user?->name ?? '-' }}</td></tr>
			@if($exchange->originalSale?->customer_name)
			<tr><td>Customer</td><td class="right">{{ $exchange->originalSale->customer_name }}</td></tr>
			@endif
		</table>

		<div class="line"></div>

		<div class="section">GOODS RETURNED</div>
		<table>
			@foreach($exchange->saleReturn?->items ?? [] as $item)
			<tr><td colspan="2">{{ $item->product_name }}</td></tr>
			<tr>
				<td>{{ \App\Models\Product::trimNumber($item->quantity) }} x {{ $shop->money($item->unit_price) }}</td>
				<td class="right">{{ $shop->money($item->refund_amount) }}</td>
			</tr>
			@endforeach
			<tr class="total-row"><td>Returned value</td><td class="right">{{ $shop->money($exchange->returned_value) }}</td></tr>
		</table>

		<div class="line"></div>

		<div class="section">NEW GOODS</div>
		<table>
			@foreach($exchange->newSale?->items ?? [] as $item)
			<tr><td colspan="2">{{ $item->product_name }}</td></tr>
			<tr>
				<td>{{ \App\Models\Product::trimNumber($item->quantity) }} x {{ $shop->money($item->unit_price) }}</td>
				<td class="right">{{ $shop->money($item->line_total) }}</td>
			</tr>
			@endforeach
			@if((float) ($exchange->newSale?->discount ?? 0) > 0)
			<tr><td>Discount</td><td class="right">- {{ $shop->money($exchange->newSale->discount) }}</td></tr>
			@endif
			<tr class="total-row"><td>New goods total</td><td class="right">{{ $shop->money($exchange->new_value) }}</td></tr>
		</table>

		<div class="line"></div>

		<table>
			@if((float) $exchange->cash_in > 0)
				<tr class="total-row"><td>CUSTOMER PAYS</td><td class="right">{{ $shop->money($exchange->cash_in) }}</td></tr>
				@if($exchange->newSale && (float) $exchange->newSale->change_amount > 0)
				<tr><td>Received</td><td class="right">{{ $shop->money($exchange->newSale->paid) }}</td></tr>
				<tr><td>Change given</td><td class="right">{{ $shop->money($exchange->newSale->change_amount) }}</td></tr>
				@endif
			@elseif((float) $exchange->cash_out > 0)
				<tr class="total-row"><td>REFUND TO CUSTOMER</td><td class="right">{{ $shop->money($exchange->cash_out) }}</td></tr>
			@else
				<tr class="total-row"><td>EVEN EXCHANGE</td><td class="right">{{ $shop->money(0) }}</td></tr>
			@endif
			<tr><td>Via</td><td class="right">{{ $exchange->paymentLabel() }}</td></tr>
		</table>

		<div class="line"></div>
		<div class="center">Signature: ____________________</div>
	</div>

	@if(request()->boolean('print'))
	<script>window.addEventListener('load', function () { window.print(); });</script>
	@endif
</body>
</html>