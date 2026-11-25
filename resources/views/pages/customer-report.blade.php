@extends('layouts.app')

@section('title', 'Outstanding Credit')

@push('styles')
<style>
	.bal-owes { color: #ea5455; font-weight: 600; }
	.stat-card { padding: 18px 20px; border: 1px solid #edf0f4; border-radius: 12px; background: #fff; height: 100%; }
	.stat-card h4 { margin: 0; font-weight: 700; }
	.stat-card p { margin: 2px 0 0; color: #6b7280; font-size: 13px; }
</style>
@endpush

@section('content')
@php $symbol = $shop->currency_symbol ?: 'Rs'; $m = fn ($c) => $symbol . ' ' . \App\Support\Cents::format($c); @endphp
<div class="page-wrapper"><div class="content">
	<div class="page-header">
		<div class="page-title"><h4>Customer credit report</h4><h6>Total outstanding market credit and the biggest debtors</h6></div>
		<div class="page-btn">
			<a href="{{ route('customer-report.debtors.csv', ['top' => $top]) }}" class="btn btn-added"><i data-feather="download" class="me-2"></i>Export CSV</a>
		</div>
	</div>

	@include('partials.credit-report-nav')

	<div class="row g-3 mb-3">
		<div class="col-md-3 col-6"><div class="stat-card"><h4 class="bal-owes">{{ $m($receivable) }}</h4><p>Total outstanding (udhaar)</p></div></div>
		<div class="col-md-3 col-6"><div class="stat-card"><h4>{{ $debtors }} <small class="text-muted fs-6">of {{ $customers }}</small></h4><p>Customers who owe money</p></div></div>
		<div class="col-md-3 col-6"><div class="stat-card"><h4>{{ $m($advances) }}</h4><p>Advances held (shop owes customers)</p></div></div>
		<div class="col-md-3 col-6"><div class="stat-card"><h4 class="{{ $over_limit ? 'bal-owes' : '' }}">{{ $over_limit }}</h4><p>Customers over their credit limit</p></div></div>
	</div>

	<div class="card"><div class="card-body">
		<form method="GET" class="d-flex align-items-center gap-2 mb-3">
			<label class="mb-0">Show</label>
			<select name="top" class="form-select w-auto" onchange="this.form.submit()">
				@foreach([10 => 'Top 10', 25 => 'Top 25', 50 => 'Top 50', 0 => 'All debtors'] as $v => $l)
				<option value="{{ $v }}" @selected($top === $v)>{{ $l }}</option>
				@endforeach
			</select>
			<span class="text-muted">sorted by highest outstanding balance</span>
		</form>
		<div class="table-responsive">
			<table class="table">
				<thead><tr><th>#</th><th>Customer</th><th>Phone</th><th class="text-end">Outstanding</th><th class="text-end">Credit limit</th><th>Last payment</th><th></th></tr></thead>
				<tbody>
				@forelse($rows as $i => $r)
					<tr>
						<td>{{ $i + 1 }}</td>
						<td>{{ $r['customer']->name }} @if($r['over'])<span class="badge badge-linedanger">Over limit</span>@endif</td>
						<td>{{ $r['customer']->phone }}</td>
						<td class="text-end bal-owes">{{ $m($r['balance']) }}</td>
						<td class="text-end">{{ $r['limit'] === null ? 'No limit' : $m($r['limit']) }}</td>
						<td>{{ $r['last_paid']?->format('d M Y') ?? 'Never' }}</td>
						<td class="text-end"><a href="{{ route('customers.statement', $r['customer']) }}">Statement</a></td>
					</tr>
				@empty
					<tr><td colspan="7" class="text-center text-muted py-4">Nobody owes money.</td></tr>
				@endforelse
				</tbody>
			</table>
		</div>
	</div></div>
</div></div>
@endsection
