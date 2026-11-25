@extends('layouts.app')

@section('title', 'Customer Statement')

@push('styles')
<style>
	.bal-owes { color: #ea5455; font-weight: 600; }
	.bal-clear { color: #28c76f; font-weight: 600; }
	.stat-card { padding: 18px 20px; border: 1px solid #edf0f4; border-radius: 12px; background: #fff; height: 100%; }
	.stat-card h4 { margin: 0; font-weight: 700; }
	.stat-card p { margin: 2px 0 0; color: #6b7280; font-size: 13px; }
	.entry-note { display: block; color: #6b7280; font-size: 12px; }
</style>
@endpush

@section('content')
@php
	$symbol = $shop->currency_symbol ?: 'Rs';
	$m  = fn ($c) => \App\Support\Cents::format($c);
	$qs = array_filter(['from' => $from?->format('Y-m-d'), 'to' => $to?->format('Y-m-d')]);
	$s  = $statement;
	$cls = fn ($c) => $c > 0 ? 'bal-owes' : ($c < 0 ? 'bal-clear' : '');
@endphp
<div class="page-wrapper"><div class="content">
	<div class="page-header">
		<div class="page-title"><h4>Statement: {{ $customer->name }}</h4><h6>{{ $customer->phone }}</h6></div>
		<div class="page-btn">
			<a href="{{ route('customers.statement.print', [$customer] + $qs + ['autoprint' => 1]) }}" target="_blank" class="btn btn-added me-2"><i data-feather="printer" class="me-2"></i>Print / Save as PDF</a>
			<a href="{{ route('customers.statement.csv', [$customer] + $qs) }}" class="btn btn-secondary me-2"><i data-feather="download" class="me-2"></i>CSV</a>
			<a href="{{ route('customers.ledger', $customer) }}" class="btn btn-secondary">Ledger</a>
		</div>
	</div>

	@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

	<form method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-3">
		<label class="mb-0">From</label>
		<input type="date" name="from" value="{{ $from?->format('Y-m-d') }}" class="form-control w-auto">
		<label class="mb-0">To</label>
		<input type="date" name="to" value="{{ $to?->format('Y-m-d') }}" class="form-control w-auto">
		<button class="btn btn-primary">Apply</button>
		<a href="{{ route('customers.statement', $customer) }}" class="btn btn-secondary">All time</a>
	</form>

	<div class="row g-3 mb-3">
		<div class="col-md-3 col-6"><div class="stat-card"><h4 class="{{ $cls($s['opening']) }}">{{ $symbol }} {{ $m($s['opening']) }}</h4><p>Opening balance</p></div></div>
		<div class="col-md-3 col-6"><div class="stat-card"><h4>{{ $symbol }} {{ $m($s['debit_total']) }}</h4><p>Total udhaar (debit)</p></div></div>
		<div class="col-md-3 col-6"><div class="stat-card"><h4>{{ $symbol }} {{ $m($s['credit_total']) }}</h4><p>Total wasooli (credit)</p></div></div>
		<div class="col-md-3 col-6"><div class="stat-card"><h4 class="{{ $cls($s['closing']) }}">{{ $symbol }} {{ $m($s['closing']) }}</h4><p>{{ $s['closing'] < 0 ? 'Closing (advance)' : 'Closing balance' }}</p></div></div>
	</div>

	<div class="card"><div class="card-body"><div class="table-responsive">
		<table class="table">
			<thead><tr><th>Date</th><th>Entry</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th><th>By</th></tr></thead>
			<tbody>
				<tr class="table-light"><td colspan="4"><strong>Opening balance</strong></td><td class="text-end {{ $cls($s['opening']) }}">{{ $m($s['opening']) }}</td><td></td></tr>
				@forelse($s['rows'] as $r)
				@php $e = $r['entry']; @endphp
				<tr>
					<td>{{ $e->entry_date->format('d M Y, h:i A') }}</td>
					<td>
						{{ $e->typeLabel() }}
						@if($e->receipt_no)<span class="entry-note">{{ $e->receipt_no }} &middot; {{ $e->methodLabel() }}</span>@endif
						@if(in_array($e->id, $s['reversed_ids'], true))<span class="badge badge-linedanger">Cancelled</span>@endif
						@if($e->note)<span class="entry-note">{{ $e->note }}</span>@endif
					</td>
					<td class="text-end">{{ $r['debit'] > 0 ? $m($r['debit']) : '' }}</td>
					<td class="text-end">{{ $r['credit'] > 0 ? $m($r['credit']) : '' }}</td>
					<td class="text-end {{ $cls($r['balance']) }}">{{ $m($r['balance']) }}</td>
					<td>{{ $e->user?->name ?? '-' }}</td>
				</tr>
				@empty
				<tr><td colspan="6" class="text-center text-muted py-4">No entries in this period.</td></tr>
				@endforelse
				<tr class="table-light fw-bold"><td colspan="2">Total / Closing</td><td class="text-end">{{ $m($s['debit_total']) }}</td><td class="text-end">{{ $m($s['credit_total']) }}</td><td class="text-end {{ $cls($s['closing']) }}">{{ $m($s['closing']) }}</td><td></td></tr>
			</tbody>
		</table>
	</div></div></div>
</div></div>
@endsection
