@extends('layouts.app')

@section('title', 'Exchanges')

@section('content')
<div class="page-wrapper">
	<div class="content">
		<div class="page-header">
			<div class="page-title">
				<h4>Exchanges</h4>
				<h6>Old goods back, new goods out, difference settled</h6>
			</div>
			<div class="page-btn">
				<a href="{{ route('sales-list') }}" class="btn btn-added">Find a sale to exchange</a>
			</div>
		</div>

		@if(session('status'))
		<div class="alert alert-success">{{ session('status') }}</div>
		@endif

		<div class="card">
			<div class="card-body">
				<form method="GET" action="{{ route('exchanges') }}" class="mb-3">
					<input type="text" name="q" value="{{ $q }}" class="form-control"
						placeholder="Search by exchange number or invoice number, then press Enter">
				</form>

				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th>Exchange</th>
								<th>Date</th>
								<th>Original invoice</th>
								<th>New invoice</th>
								<th>Processed by</th>
								<th class="text-end">Returned</th>
								<th class="text-end">New goods</th>
								<th class="text-end">Difference</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@forelse($exchanges as $exchange)
							<tr>
								<td>{{ $exchange->exchange_no }}</td>
								<td>{{ $exchange->exchanged_at?->format('d M Y, h:i A') }}</td>
								<td>
									@if($exchange->originalSale)
										<a href="{{ route('sales.receipt', $exchange->originalSale) }}">{{ $exchange->originalSale->invoice_no }}</a>
									@else - @endif
								</td>
								<td>
									@if($exchange->newSale)
										<a href="{{ route('sales.receipt', $exchange->newSale) }}">{{ $exchange->newSale->invoice_no }}</a>
									@else - @endif
								</td>
								<td>{{ $exchange->user?->name ?? '-' }}</td>
								<td class="text-end">{{ $shop->money($exchange->returned_value) }}</td>
								<td class="text-end">{{ $shop->money($exchange->new_value) }}</td>
								<td class="text-end">
									@if($exchange->totalDue() > 0)
										<span class="text-success">Paid {{ $shop->money($exchange->cash_in) }}</span>
										@if((float) $exchange->ledger_in > 0)
										<span class="text-warning">+ {{ $shop->money($exchange->ledger_in) }} on account</span>
										@endif
									@elseif($exchange->totalRefund() > 0)
										<span class="text-danger">Refunded {{ $shop->money($exchange->cash_out) }}</span>
										@if((float) $exchange->ledger_out > 0)
										<span class="text-warning">+ {{ $shop->money($exchange->ledger_out) }} to account</span>
										@endif
									@else
										Even
									@endif
								</td>
								<td class="text-end">
									<a href="{{ route('exchanges.receipt', $exchange) }}" class="btn btn-sm btn-outline-primary">Receipt</a>
								</td>
							</tr>
							@empty
							<tr><td colspan="9" class="text-center text-muted">No exchanges yet.</td></tr>
							@endforelse
						</tbody>
					</table>
				</div>

				{{ $exchanges->links('pagination::bootstrap-5') }}
			</div>
		</div>
	</div>
</div>
@endsection