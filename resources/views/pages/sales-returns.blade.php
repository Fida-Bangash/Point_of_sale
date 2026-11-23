@extends('layouts.app')

@section('title', 'Sales Returns')

@section('content')
<div class="page-wrapper">
	<div class="content">
		<div class="page-header">
			<div class="page-title">
				<h4>Sales Returns</h4>
				<h6>Bill back, stock back, money back</h6>
			</div>
			<div class="page-btn">
				<a href="{{ route('sales-list') }}" class="btn btn-added">Find a sale to return</a>
			</div>
		</div>

		@if(session('status'))
		<div class="alert alert-success">{{ session('status') }}</div>
		@endif

		<div class="row mb-3">
			<div class="col-md-6">
				<div class="card mb-0"><div class="card-body">
					<p class="mb-1 text-muted">Today's returns</p>
					<h3 class="mb-0">{{ $todayCount }}</h3>
				</div></div>
			</div>
			<div class="col-md-6">
				<div class="card mb-0"><div class="card-body">
					<p class="mb-1 text-muted">Today's refunds</p>
					<h3 class="mb-0">{{ $shop->money($todayRefund) }}</h3>
				</div></div>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<form method="GET" action="{{ route('sales-returns') }}" class="mb-3">
					<input type="text" name="q" value="{{ $q }}" class="form-control"
						placeholder="Search by return number or invoice number, then press Enter">
				</form>

				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th>Return</th>
								<th>Date</th>
								<th>Original invoice</th>
								<th>Processed by</th>
								<th>Items</th>
								<th>Refund via</th>
								<th class="text-end">Refund</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@forelse($returns as $return)
							<tr>
								<td>{{ $return->return_no }}</td>
								<td>{{ $return->returned_at?->format('d M Y, h:i A') }}</td>
								<td>
									@if($return->sale)
										<a href="{{ route('sales.receipt', $return->sale) }}">{{ $return->sale->invoice_no }}</a>
									@else
										-
									@endif
								</td>
								<td>{{ $return->user?->name ?? '-' }}</td>
								<td>{{ $return->items_count }}</td>
								<td>{{ $return->refundMethodLabel() }}</td>
								<td class="text-end">{{ $shop->money($return->refund_total) }}</td>
								<td class="text-end">
									<a href="{{ route('sales-returns.receipt', $return) }}" class="btn btn-sm btn-outline-primary">Receipt</a>
								</td>
							</tr>
							@empty
							<tr><td colspan="8" class="text-center text-muted">No returns yet.</td></tr>
							@endforelse
						</tbody>
					</table>
				</div>

				{{ $returns->links('pagination::bootstrap-5') }}
			</div>
		</div>
	</div>
</div>
@endsection