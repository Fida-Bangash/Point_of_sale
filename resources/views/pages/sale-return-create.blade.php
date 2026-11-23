@extends('layouts.app')

@section('title', 'Return sale ' . $sale->invoice_no)

@section('content')
<div class="page-wrapper">
	<div class="content">
		<div class="page-header">
			<div class="page-title">
				<h4>Return items</h4>
				<h6>Invoice {{ $sale->invoice_no }} &middot; {{ $sale->sold_at?->format('d M Y, h:i A') }} &middot; {{ $sale->customer_name ?: 'Walk-in' }}</h6>
			</div>
			<div class="page-btn">
				<a href="{{ route('sales-list') }}" class="btn btn-secondary">Back to sales</a>
			</div>
		</div>

		@if($errors->any())
		<div class="alert alert-danger">
			<ul class="mb-0">
				@foreach($errors->all() as $error)
				<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
		@endif

		<form method="POST" action="{{ route('sales.return.store', $sale) }}" id="return-form">
			@csrf

			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center mb-3">
						<p class="text-muted mb-0">
							Enter the quantity coming back. Untick <strong>Restock</strong> for damaged goods
							(stock will not be increased).
							@if((float) $sale->discount > 0)
								The refund already has this bill's discount taken off.
							@endif
						</p>
						<button type="button" class="btn btn-sm btn-outline-primary" id="return-all">Return everything</button>
					</div>

					<div class="table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th>Product</th>
									<th class="text-end">Sold</th>
									<th class="text-end">Returned</th>
									<th class="text-end">Can return</th>
									<th style="width:140px">Return qty</th>
									<th class="text-center">Restock</th>
									<th class="text-end">Refund (est.)</th>
								</tr>
							</thead>
							<tbody>
								@foreach($states->values() as $i => $s)
								@php
									$item = $s['item'];
									$unit = $service->netUnitPrice($s);
								@endphp
								<tr class="return-row" data-max="{{ $s['available'] }}" data-unit="{{ $unit }}">
									<td>
										{{ $item->product_name }}
										<input type="hidden" name="items[{{ $i }}][sale_item_id]" value="{{ $item->id }}">
									</td>
									<td class="text-end">{{ \App\Models\Product::trimNumber($s['sold']) }}</td>
									<td class="text-end">{{ \App\Models\Product::trimNumber($s['returned']) }}</td>
									<td class="text-end">{{ \App\Models\Product::trimNumber($s['available']) }}</td>
									<td>
										@if($s['available'] > 0)
											<input type="number" class="form-control return-qty"
												name="items[{{ $i }}][quantity]"
												value="{{ old('items.' . $i . '.quantity') }}"
												min="0" max="{{ $s['available'] }}" step="0.001" placeholder="0">
										@else
											<span class="text-muted">Fully returned</span>
										@endif
									</td>
									<td class="text-center">
										@if($s['available'] > 0)
											<input type="hidden" name="items[{{ $i }}][restock]" value="0">
											<input type="checkbox" name="items[{{ $i }}][restock]" value="1"
												{{ old('items.' . $i . '.restock', '1') ? 'checked' : '' }}>
										@endif
									</td>
									<td class="text-end return-line-refund">{{ $shop->money(0) }}</td>
								</tr>
								@endforeach
							</tbody>
							<tfoot>
								<tr>
									<th colspan="6" class="text-end">Estimated refund</th>
									<th class="text-end" id="return-total">{{ $shop->money(0) }}</th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="card-body">
					<div class="row g-3">
						<div class="col-md-4">
							<label class="form-label" for="refund_method">Refund via</label>
							<select name="refund_method" id="refund_method" class="form-select">
								@foreach($methods as $key => $label)
								<option value="{{ $key }}" @selected(old('refund_method', $sale->payment_method) === $key)>{{ $label }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-8">
							<label class="form-label" for="reason">Reason</label>
							<input type="text" name="reason" id="reason" class="form-control" maxlength="255"
								value="{{ old('reason') }}" placeholder="e.g. Damaged, wrong item, customer changed mind">
						</div>
						<div class="col-12">
							<label class="form-label" for="note">Note (optional)</label>
							<input type="text" name="note" id="note" class="form-control" maxlength="500" value="{{ old('note') }}">
						</div>
					</div>

					<div class="mt-3 text-end">
						<button type="submit" class="btn btn-danger">Confirm return &amp; refund</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
	var symbol = @json($shop->currency_symbol);
	var rows   = document.querySelectorAll('.return-row');

	function money(n) {
		return symbol + ' ' + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function recalc() {
		var total = 0;
		rows.forEach(function (row) {
			var input = row.querySelector('.return-qty');
			var cell  = row.querySelector('.return-line-refund');
			var max   = parseFloat(row.dataset.max) || 0;
			var unit  = parseFloat(row.dataset.unit) || 0;
			var qty   = input ? (parseFloat(input.value) || 0) : 0;

			if (qty > max) { qty = max; }
			if (qty < 0)   { qty = 0; }

			var line = Math.round(qty * unit * 100) / 100;
			cell.textContent = money(line);
			total += line;
		});
		document.getElementById('return-total').textContent = money(total);
	}

	document.querySelectorAll('.return-qty').forEach(function (el) {
		el.addEventListener('input', recalc);
	});

	document.getElementById('return-all').addEventListener('click', function () {
		rows.forEach(function (row) {
			var input = row.querySelector('.return-qty');
			if (input) { input.value = row.dataset.max; }
		});
		recalc();
	});

	recalc();
})();
</script>
@endpush