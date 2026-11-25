@extends('layouts.app')

@section('title', 'Debt Aging')

@push('styles')
<style>
	.bal-owes { color: #ea5455; font-weight: 600; }
	.stat-card { padding: 18px 20px; border: 1px solid #edf0f4; border-radius: 12px; background: #fff; height: 100%; }
	.stat-card h4 { margin: 0; font-weight: 700; }
	.stat-card p { margin: 2px 0 0; color: #6b7280; font-size: 13px; }
</style>
@endpush

@section('content')
@php $symbol = $shop->currency_symbol ?: 'Rs'; $m = fn ($c) => \App\Support\Cents::format($c); @endphp
<div class="page-wrapper"><div class="content">
	<div class="page-header">
		<div class="page-title"><h4>Debt aging</h4><h6>How old is the money customers owe (oldest debts are assumed to be paid first)</h6></div>
		<div class="page-btn">
			<a href="{{ route('customer-report.aging.csv', ['as_of' => $as_of->format('Y-m-d')]) }}" class="btn btn-added"><i data-feather="download" class="me-2"></i>Export CSV</a>
		</div>
	</div>

	@include('partials.credit-report-nav')

	<form method="GET" class="d-flex align-items-center gap-2 mb-3">
		<label class="mb-0">As of</label>
		<input type="date" name="as_of" value="{{ $as_of->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" class="form-control w-auto">
		<button class="btn btn-primary">Apply</button>
	</form>

	<div class="row g-3 mb-3">
		@foreach(\App\Services\CustomerReportService::BUCKETS as $label => $_)
		<div class="col-md col-6"><div class="stat-card"><h4 class="{{ $label === '90+' && $totals[$label] > 0 ? 'bal-owes' : '' }}">{{ $symbol }} {{ $m($totals[$label]) }}</h4><p>{{ $label }} days</p></div></div>
		@endforeach
		<div class="col-md col-12"><div class="stat-card"><h4 class="bal-owes">{{ $symbol }} {{ $m($totals['balance']) }}</h4><p>Total outstanding</p></div></div>
	</div>

	<div class="card"><div class="card-body"><div class="table-responsive">
		<table class="table">
			<thead><tr>
				<th>Customer</th><th>Phone</th>
				@foreach(\App\Services\CustomerReportService::BUCKETS as $label => $_)<th class="text-end">{{ $label }}</th>@endforeach
				<th class="text-end">Total</th><th class="text-end">Oldest (days)</th>
			</tr></thead>
			<tbody>
			@forelse($rows as $r)
				<tr>
					<td><a href="{{ route('customers.statement', $r['customer']) }}">{{ $r['customer']->name }}</a></td>
					<td>{{ $r['customer']->phone }}</td>
					@foreach($r['buckets'] as $cents)<td class="text-end">{{ $cents > 0 ? $m($cents) : '-' }}</td>@endforeach
					<td class="text-end bal-owes">{{ $m($r['balance']) }}</td>
					<td class="text-end">{{ $r['oldest'] }}</td>
				</tr>
			@empty
				<tr><td colspan="8" class="text-center text-muted py-4">No outstanding debt on this date.</td></tr>
			@endforelse
			</tbody>
		</table>
	</div></div></div>
</div></div>
@endsection
