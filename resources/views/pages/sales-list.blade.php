@extends('layouts.app')

@section('title', 'Sales')

@section('content')
<div class="page-wrapper">
	<div class="content">
		<div class="page-header">
			<div class="page-title">
				<h4>Sales</h4>
				<h6>All completed sales</h6>
			</div>
			<div class="page-btn">
				<a href="{{ route('pos') }}" class="btn btn-added">New Sale</a>
			</div>
		</div>

		@if(session('status'))
		<div class="alert alert-success">{{ session('status') }}</div>
		@endif
		@if($errors->any())
		<div class="alert alert-danger">{{ $errors->first() }}</div>
		@endif

		<div class="row mb-3">
			<div class="col-md-6">
				<div class="card mb-0"><div class="card-body">
					<p class="mb-1 text-muted">Today's sales</p>
					<h3 class="mb-0">{{ $todayCount }}</h3>
				</div></div>
			</div>
			<div class="col-md-6">
				<div class="card mb-0"><div class="card-body">
					<p class="mb-1 text-muted">Today's total</p>
					<h3 class="mb-0">{{ $shop->money($todayTotal) }}</h3>
				</div></div>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<form method="GET" action="{{ route('sales-list') }}" class="mb-3">
					<input type="text" name="q" value="{{ $q }}" class="form-control"
						placeholder="Search by invoice number or customer name, then press Enter">
				</form>

				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th>Invoice</th>
								<th>Date</th>
								<th>Cashier</th>
								<th>Customer</th>
								<th>Items</th>
								<th>Payment</th>
								<th>Status</th>
								<th class="text-end">Total</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@forelse($sales as $sale)
							<tr>
								<td>{{ $sale->invoice_no }}</td>
								<td>{{ $sale->sold_at?->format('d M Y, h:i A') }}</td>
								<td>{{ $sale->user?->name ?? '-' }}</td>
								<td>{{ $sale->customer_name ?: 'Walk-in' }}</td>
								<td>{{ $sale->items_count }}</td>
								<td>{{ $sale->paymentLabel() }}</td>
								<td>
									<span class="badge {{ $sale->statusBadgeClass() }}">{{ $sale->statusLabel() }}</span>
									@if($sale->isExchangeSale())<span class="badge bg-info">Exchange</span>@endif
								</td>
								
								<td class="text-end">{{ $shop->money($sale->total) }}</td>
								<td class="text-end">
									<a href="{{ route('sales.receipt', $sale) }}" class="btn btn-sm btn-outline-primary">Receipt</a>
									@if(auth()->user()->hasRole('admin', 'manager') && $sale->status !== \App\Models\Sale::STATUS_RETURNED)
									<a href="{{ route('sales.return.create', $sale) }}" class="btn btn-sm btn-outline-danger">Return</a>
									<a href="{{ route('sales.exchange.create', $sale) }}" class="btn btn-sm btn-outline-warning">Exchange</a>
									@endif
								</td>
							</tr>
							@empty
							<tr><td colspan="9" class="text-center text-muted">No sales yet.</td></tr>
							@endforelse
						</tbody>
					</table>
				</div>

				{{ $sales->links('pagination::bootstrap-5') }}
			</div>
		</div>
	</div>
</div>
@endsection