<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Statement {{ $customer->name }}</title>
	<style>
		* { box-sizing: border-box; }
		body { margin: 0; background: #f3f4f6; font-family: Arial, sans-serif; color: #111; font-size: 13px; }
		.toolbar { padding: 10px; text-align: center; background: #fff; border-bottom: 1px solid #ddd; }
		.toolbar button { padding: 6px 14px; border: 1px solid #ff9f43; background: #ff9f43; color: #fff; border-radius: 4px; cursor: pointer; }
		.sheet { width: 190mm; margin: 12px auto; background: #fff; padding: 8mm; }
		@page { size: A4; margin: 10mm; }
		h1 { font-size: 20px; margin: 0; } h2 { font-size: 15px; margin: 14px 0 4px; }
		.muted { color: #555; }
		.head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #111; padding-bottom: 8px; }
		table { width: 100%; border-collapse: collapse; margin-top: 10px; }
		th, td { padding: 5px 6px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top; }
		th { background: #f3f4f6; font-size: 12px; }
		.r { text-align: right; } .b { font-weight: bold; }
		.summary td { border: 1px solid #ddd; text-align: center; }
		.note { display: block; font-size: 11px; color: #555; }
		@media print { body { background: #fff; } .toolbar { display: none; } .sheet { margin: 0; width: auto; padding: 0; } tr { page-break-inside: avoid; } thead { display: table-header-group; } }
	</style>
</head>
@php
	$symbol = $shop->currency_symbol ?: 'Rs';
	$m = fn ($c) => \App\Support\Cents::format($c);
	$s = $statement;
@endphp
<body>
	<div class="toolbar"><button onclick="window.print()">Print / Save as PDF</button></div>
	<div class="sheet">
		<div class="head">
			<div>
				<h1>{{ $shop->shop_name }}</h1>
				<div class="muted">{{ $shop->fullAddress() }} {{ $shop->phone ? ' | ' . $shop->phone : '' }}</div>
			</div>
			<div style="text-align:right">
				<div class="b" style="font-size:16px">CUSTOMER STATEMENT</div>
				<div class="muted">Printed {{ now()->format('d M Y, h:i A') }}</div>
			</div>
		</div>

		<h2>{{ $customer->name }} <span class="muted" style="font-weight:normal">{{ $customer->phone }}</span></h2>
		<div class="muted">
			Period: {{ $from ? $from->format('d M Y') : 'Beginning' }} to {{ $to ? $to->format('d M Y') : now()->format('d M Y') }}
			@if($customer->address) &middot; {{ $customer->address }} @endif
		</div>

		<table class="summary">
			<tr><th>Opening balance</th><th>Total debit (udhaar)</th><th>Total credit (wasooli)</th><th>{{ $s['closing'] < 0 ? 'Closing (advance)' : 'Closing balance' }}</th></tr>
			<tr class="b"><td>{{ $symbol }} {{ $m($s['opening']) }}</td><td>{{ $symbol }} {{ $m($s['debit_total']) }}</td><td>{{ $symbol }} {{ $m($s['credit_total']) }}</td><td>{{ $symbol }} {{ $m($s['closing']) }}</td></tr>
		</table>

		<table>
			<thead><tr><th>Date</th><th>Entry</th><th class="r">Debit</th><th class="r">Credit</th><th class="r">Balance</th></tr></thead>
			<tbody>
				<tr class="b"><td colspan="4">Opening balance</td><td class="r">{{ $m($s['opening']) }}</td></tr>
				@foreach($s['rows'] as $r)
				@php $e = $r['entry']; @endphp
				<tr>
					<td>{{ $e->entry_date->format('d M Y') }}<span class="note">{{ $e->entry_date->format('h:i A') }}</span></td>
					<td>{{ $e->typeLabel() }}
						@if($e->receipt_no)<span class="note">{{ $e->receipt_no }} &middot; {{ $e->methodLabel() }}</span>@endif
						@if($e->note)<span class="note">{{ $e->note }}</span>@endif
					</td>
					<td class="r">{{ $r['debit'] > 0 ? $m($r['debit']) : '' }}</td>
					<td class="r">{{ $r['credit'] > 0 ? $m($r['credit']) : '' }}</td>
					<td class="r">{{ $m($r['balance']) }}</td>
				</tr>
				@endforeach
				<tr class="b"><td colspan="2">Total / Closing balance</td><td class="r">{{ $m($s['debit_total']) }}</td><td class="r">{{ $m($s['credit_total']) }}</td><td class="r">{{ $m($s['closing']) }}</td></tr>
			</tbody>
		</table>

		<p class="muted" style="margin-top:12px">Balance above zero = the customer owes the shop. Below zero = advance held by the shop.</p>
	</div>

	@if(request()->boolean('autoprint'))
	<script>window.addEventListener('load', function () { window.print(); });</script>
	@endif
</body>
</html>
