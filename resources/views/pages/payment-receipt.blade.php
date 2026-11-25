<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Payment receipt {{ $entry->receipt_no }}</title>
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
		.title { font-size: 14px; font-weight: bold; letter-spacing: 1px; margin: 4px 0; }
		.line { border-top: 1px dashed #000; margin: 6px 0; }
		table { width: 100%; border-collapse: collapse; }
		td { vertical-align: top; padding: 1px 0; }
		.total-row td { font-weight: bold; font-size: 14px; }
		.cancelled { border: 2px solid #000; text-align: center; font-weight: bold; font-size: 16px; padding: 4px; margin: 6px 0; }
		.muted { font-size: 11px; }

		@media print {
			body { background: #fff; }
			.toolbar { display: none; }
			.receipt { margin: 0; }
		}
	</style>
</head>
<body>
@php
	// 1500 -> "Rs 1,500.00 (owes)", -200 -> "Rs 200.00 (advance)", 0 -> "Rs 0.00"
	$bal = fn ($v) => $shop->money(abs((float) $v)) . ((float) $v > 0 ? ' (owes)' : ((float) $v < 0 ? ' (advance)' : ''));
	$received = abs((float) $entry->amount);
	$before   = $entry->previous_balance;
	$after    = $entry->balanceAfter();
@endphp
	<div class="toolbar">
		<button type="button" onclick="window.print()">Print</button>
		<a href="{{ route('customers.ledger', $customer) }}" class="light">Ledger</a>
		@if($size === 'a4')
			<a href="{{ route('customers.payments.receipt', [$customer, $entry]) }}" class="light">80mm</a>
		@else
			<a href="{{ route('customers.payments.receipt', [$customer, $entry, 'size' => 'a4']) }}" class="light">A4</a>
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
			<div class="title">PAYMENT RECEIPT</div>
		</div>

		@if($cancelledBy)
			<div class="cancelled">CANCELLED</div>
		@endif

		<div class="line"></div>

		<table>
			<tr><td>Receipt</td><td class="right">{{ $entry->receipt_no }}</td></tr>
			<tr><td>Date</td><td class="right">{{ $entry->entry_date->format('d M Y, h:i A') }}</td></tr>
			<tr><td>Received by</td><td class="right">{{ $entry->user?->name ?? '-' }}</td></tr>
			<tr><td>Customer</td><td class="right">{{ $customer->name }}</td></tr>
			<tr><td>Phone</td><td class="right">{{ $customer->phone }}</td></tr>
			<tr><td>Method</td><td class="right">{{ $entry->methodLabel() }}</td></tr>
		</table>

		<div class="line"></div>

		<table>
			@if($before !== null)
			<tr><td>Previous balance</td><td class="right">{{ $bal($before) }}</td></tr>
			@endif
			<tr class="total-row"><td>Payment received</td><td class="right">{{ $shop->money($received) }}</td></tr>
			@if($after !== null)
			<tr><td>New balance</td><td class="right">{{ $bal($after) }}</td></tr>
			@endif
		</table>

		@if($entry->note)
			<div class="line"></div>
			<div class="muted">Note: {{ $entry->note }}</div>
		@endif

		@if($cancelledBy)
			<div class="line"></div>
			<div class="muted">
				Cancelled on {{ $cancelledBy->entry_date->format('d M Y, h:i A') }}
				by {{ $cancelledBy->user?->name ?? '-' }}.<br>
				{{ $cancelledBy->note }}
			</div>
		@endif

		@if($shop->receipt_footer)
			<div class="line"></div>
			<div class="center">{{ $shop->receipt_footer }}</div>
		@endif
	</div>
</body>
</html>
