@extends('layouts.app')

@section('title', 'Collections')

@push('styles')
<style>
	.bal-owes { color: #ea5455; font-weight: 600; }
	.bal-clear { color: #28c76f; font-weight: 600; }
	.stat-card { padding: 18px 20px; border: 1px solid #edf0f4; border-radius: 12px; background: #fff; height: 100%; }
	.stat-card h4 { margin: 0; font-weight: 700; }
	.stat-card p { margin: 2px 0 0; color: #6b7280; font-size: 13px; }
</style>
@endpush

@section('content')
@php
	$symbol = $shop->currency_symbol ?: 'Rs';
	$m = fn ($c) => $symbol . ' ' . \App\Support\Cents::format($c);
	$methods = \App\Models\CustomerLedger::PAYMENT_METHODS;
	$isToday = $from->isSameDay(now()) && $to->isSameDay(now());
@endphp
<div class="page-wrapper"><div class="content">
	<div class="page-header">
		<div class="page-title"><h4>{{ $isToday ? "Today's collections" : 'Collections' }}</h4><h6>Payments received from customers (wasooli)</h6></div>
		<div class="page-btn">
			<a href="{{ route('customer-report.collections.csv', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn btn-added"><i data-feather="download" class="me-2"></i>Export CSV</a>
		</div>
	</div>

	@include('partials.credit-report-nav')

	@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

	<form method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-3">
		<label class="mb-0">From</label>
		<input type="date" name="from" value="{{ $from->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" class="form-control w-auto">
		<label class="mb-0">To</label>
		<input type="date" name="to" value="{{ $to->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" class="form-control w-auto">
		<button class="btn btn-primary">Apply</button>
		<a href="{{ route('customer-report.collections') }}" class="btn btn-secondary">Today</a>
	</form>

	<div class="row g-3 mb-3">
		<div class="col-md-3 col-6"><div class="stat-card"><h4 class="bal-clear">{{ $m($net) }}</h4><p>Net collected</p></div></div>
		<div class="col-md-3 col-6"><div class="stat-card"><h4>{{ $payments }}</h4><p>Payments from {{ $customers }} customer(s)</p></div></div>
		<div class="col-md-3 col-6"><div class="stat-card"><h4>{{ $m($collected) }}</h4><p>Gross received</p></div></div>
		<div class="col-md-3 col-6"><div class="stat-card"><h4 class="{{ $cancelled > 0 ? 'bal-owes' : '' }}">{{ $m($cancelled) }}</h4><p>Cancelled payments</p></div></div>
	</div>

	@if($by_method)
	<div class="row g-3 mb-3">
		@foreach($by_method as $method => $cents)
		<div class="col-md-3 col-6"><div class="stat-card"><h4>{{ $m($cents) }}</h4><p>{{ $methods[$method] ?? ucfirst($method) }}</p></div></div>
		@endforeach
	</div>
	@endif

	<div class="card"><div class="card-body"><div class="table-responsive">
		<table class="table">
			<thead><tr><th>Date</th><th>Receipt</th><th>Customer</th><th>Method</th><th class="text-end">Amount</th><th>By</th></tr></thead>
			<tbody>
			@forelse($lines as $l)
				@php $e = $l['entry']; @endphp
				<tr>
					<td>{{ $e->entry_date->format('d M Y, h:i A') }}</td>
					<td>{{ $l['receipt'] ?: '-' }} @if($l['kind'] === 'cancelled')<span class="badge badge-linedanger">Cancelled</span>@endif</td>
					<td>@if($e->customer)<a href="{{ route('customers.statement', $e->customer) }}">{{ $e->customer->name }}</a>@endif</td>
					<td>{{ $methods[$l['method']] ?? ucfirst($l['method']) }}</td>
					<td class="text-end {{ $l['cents'] < 0 ? 'bal-owes' : '' }}">{{ $m($l['cents']) }}</td>
					<td>{{ $e->user?->name ?? '-' }}</td>
				</tr>
			@empty
				<tr><td colspan="6" class="text-center text-muted py-4">No payments in this period.</td></tr>
			@endforelse
			</tbody>
		</table>
	</div></div></div>
</div></div>
@endsection
