@extends('layouts.app')

@section('title', 'Customer Ledger')

@push('styles')
<style>
	.bal-owes { color: #ea5455; font-weight: 600; }
	.bal-clear { color: #28c76f; font-weight: 600; }
	.ledger-summary { display: flex; flex-wrap: wrap; gap: 24px; padding: 18px 20px; border: 1px solid #edf0f4; border-radius: 12px; background: #fff; margin-bottom: 20px; }
	.ledger-summary h5 { margin: 0; font-size: 20px; font-weight: 700; }
	.ledger-summary p { margin: 2px 0 0; color: #6b7280; font-size: 13px; }
	.entry-note { display: block; color: #6b7280; font-size: 12px; }
</style>
@endpush

@section('content')
@php $symbol = $shop->currency_symbol ?: 'Rs'; @endphp

<div class="page-wrapper">
	<div class="content">

		<div class="page-header">
			<div class="add-item d-flex">
				<div class="page-title">
					<h4>{{ $customer->name }}</h4>
					<h6>Customer ledger (khata)</h6>
				</div>
			</div>
			<div class="page-btn">
				<a href="#" class="btn btn-added me-2" data-bs-toggle="modal" data-bs-target="#receive-payment">
					<i data-feather="plus-circle" class="me-2"></i>Receive payment
				</a>
				<a href="{{ route('customers.statement', $customer) }}" class="btn btn-secondary me-2">
					<i data-feather="printer" class="me-2"></i>Statement
				</a>
				<a href="{{ route('customers') }}" class="btn btn-secondary">
					<i data-feather="arrow-left" class="me-2"></i>Back to customers
				</a>
			</div>
		</div>

		@if(session('status'))
		<div class="alert alert-success">{{ session('status') }}</div>
		@endif
		@if($errors->any() && old('_form') !== 'payment')
		<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
		@endif

		<div class="ledger-summary">
			<div>
				<h5 class="{{ $balance > 0 ? 'bal-owes' : ($balance < 0 ? 'bal-clear' : '') }}">
					{{ $symbol }} {{ number_format(abs($balance), 2) }}
				</h5>
				<p>{{ $balance > 0 ? 'Customer owes' : ($balance < 0 ? 'Advance (shop owes customer)' : 'Settled') }}</p>
			</div>
			<div>
				<h5>{{ $customer->phone }}</h5>
				<p>Phone</p>
			</div>
			<div>
				<h5>{{ $customer->credit_limit !== null ? $symbol.' '.number_format((float) $customer->credit_limit, 2) : 'No limit' }}</h5>
				<p>Credit limit</p>
			</div>
		</div>

		@if($canAdjust)
		<details class="card mb-3" @if($errors->has('direction') || $errors->has('reason') || old('_form') === 'adjust') open @endif>
			<summary class="card-body py-2 fw-semibold" style="cursor:pointer">Adjust balance (admin / manager)</summary>
			<div class="card-body pt-0">
				<form method="POST" action="{{ route('customers.adjustments.store', $customer) }}" class="row g-2 align-items-end">
					@csrf
					<input type="hidden" name="_form" value="adjust">
					<div class="col-md-3">
						<label class="form-label">Change</label>
						<select name="direction" class="form-select" required>
							<option value="increase" @selected(old('direction') === 'increase')>Customer owes MORE</option>
							<option value="decrease" @selected(old('direction') === 'decrease')>Customer owes LESS</option>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">Amount ({{ $symbol }})</label>
						<input type="number" name="amount" step="0.01" min="0.01" max="9999999999.99" class="form-control" value="{{ old('amount') }}" required>
					</div>
					<div class="col-md-5">
						<label class="form-label">Reason (required)</label>
						<input type="text" name="reason" maxlength="400" class="form-control" value="{{ old('reason') }}" required>
					</div>
					<div class="col-md-2"><button class="btn btn-warning w-100">Save adjustment</button></div>
				</form>
			</div>
		</details>
		@endif

		<div class="card table-list-card">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th>Date</th>
								<th>Entry</th>
								<th class="text-end">Udhaar (Dr)</th>
								<th class="text-end">Wasooli (Cr)</th>
								<th class="text-end">Balance</th>
								<th>By</th>
								<th class="text-center">Action</th>
							</tr>
						</thead>
						<tbody>
							@forelse($rows as $row)
							@php
								$entry     = $row['entry'];
								$amount    = (float) $entry->amount;
								$isPayment = $entry->isPayment() && $entry->receipt_no;
								$cancelled = in_array($entry->id, $reversedIds, true);
							@endphp
							<tr>
								<td>{{ $entry->entry_date->format('d M Y, h:i A') }}</td>
								<td>
									{{ $entry->typeLabel() }}
									@if($isPayment)<span class="entry-note">{{ $entry->receipt_no }} &middot; {{ $entry->methodLabel() }}</span>@endif
									@if($cancelled)<span class="badge badge-linedanger">Cancelled</span>@endif
									@if($entry->note)<span class="entry-note">{{ $entry->note }}</span>@endif
								</td>
								<td class="text-end">{{ $amount > 0 ? number_format($amount, 2) : '' }}</td>
								<td class="text-end">{{ $amount < 0 ? number_format(abs($amount), 2) : '' }}</td>
								<td class="text-end {{ $row['balance'] > 0 ? 'bal-owes' : ($row['balance'] < 0 ? 'bal-clear' : '') }}">
									{{ number_format($row['balance'], 2) }}
								</td>
								<td>{{ $entry->user?->name ?? '-' }}</td>
								<td class="text-center">
									@if($isPayment)
										<a href="{{ route('customers.payments.receipt', [$customer, $entry]) }}" class="me-2" title="Receipt" target="_blank">
											<i data-feather="printer" class="feather-printer"></i>
										</a>
										@if($canCancel && ! $cancelled)
										<a href="#" title="Cancel payment" class="text-danger"
											data-bs-toggle="modal" data-bs-target="#cancel-payment"
											data-cancel-payment
											data-action="{{ route('customers.payments.cancel', [$customer, $entry]) }}"
											data-receipt="{{ $entry->receipt_no }}"
											data-amount="{{ number_format(abs($amount), 2) }}">
											<i data-feather="x-circle" class="feather-x-circle"></i>
										</a>
										@endif
									@endif
								</td>
							</tr>
							@empty
							<tr><td colspan="7" class="text-center text-muted py-4">No entries yet.</td></tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>

	</div>
</div>

{{-- ===================== Receive payment modal ===================== --}}
<div class="modal fade" id="receive-payment" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="POST" action="{{ route('customers.payments.store', $customer) }}" id="payment-form">
				@csrf
				<input type="hidden" name="_form" value="payment">
				<div class="modal-header">
					<h5 class="modal-title">Receive payment from {{ $customer->name }}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					@if($errors->any() && old('_form') === 'payment')
					<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
					@endif

					<p class="text-muted mb-3">
						@if($balance > 0) Customer owes <strong>{{ $symbol }} {{ number_format($balance, 2) }}</strong>
						@elseif($balance < 0) Customer has an advance of <strong>{{ $symbol }} {{ number_format(abs($balance), 2) }}</strong>
						@else Balance is settled @endif
					</p>

					<div class="mb-3">
						<label class="form-label" for="pay-amount">Amount ({{ $symbol }}) <span class="text-danger">*</span></label>
						<input type="number" step="0.01" min="0.01" max="9999999999.99" class="form-control" id="pay-amount" name="amount" value="{{ old('amount') }}" required>
					</div>

					<div class="mb-3" id="pay-advance-box" style="display:none">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" value="1" id="pay-advance" name="confirm_advance" @checked(old('confirm_advance'))>
							<label class="form-check-label" for="pay-advance">
								This is more than the customer owes. Keep the extra <strong id="pay-extra"></strong> as advance.
							</label>
						</div>
					</div>

					<div class="mb-3">
						<label class="form-label" for="pay-method">Method <span class="text-danger">*</span></label>
						<select class="form-select" id="pay-method" name="payment_method" required>
							@foreach($paymentMethods as $key => $label)
								<option value="{{ $key }}" @selected(old('payment_method', 'cash') === $key)>{{ $label }}</option>
							@endforeach
						</select>
					</div>

					<div class="mb-3">
						<label class="form-label" for="pay-date">Date</label>
						<input type="date" class="form-control" id="pay-date" name="payment_date" max="{{ now()->toDateString() }}" value="{{ old('payment_date', now()->toDateString()) }}">
					</div>

					<div class="mb-0">
						<label class="form-label" for="pay-note">Note</label>
						<input type="text" class="form-control" id="pay-note" name="note" maxlength="500" value="{{ old('note') }}">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-submit">Save &amp; print receipt</button>
				</div>
			</form>
		</div>
	</div>
</div>

{{-- ===================== Cancel payment modal (admin only) ===================== --}}
@if($canCancel)
<div class="modal fade" id="cancel-payment" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="POST" action="#" id="cancel-form">
				@csrf
				<div class="modal-header">
					<h5 class="modal-title">Cancel payment <span id="cancel-receipt"></span></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p class="text-muted">
						{{ $symbol }} <strong id="cancel-amount"></strong> will be added back to the customer's balance.
						The original entry stays in the ledger and a reversal entry is added.
					</p>
					<label class="form-label" for="cancel-reason">Reason <span class="text-danger">*</span></label>
					<input type="text" class="form-control" id="cancel-reason" name="reason" maxlength="400" required>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Keep payment</button>
					<button type="submit" class="btn btn-danger">Cancel payment</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endif
@endsection

@push('scripts')
<script>
	document.addEventListener('DOMContentLoaded', function () {
		var BALANCE = @json((float) $balance);
		var amount  = document.getElementById('pay-amount');
		var box     = document.getElementById('pay-advance-box');
		var check   = document.getElementById('pay-advance');
		var extra   = document.getElementById('pay-extra');

		// Show the "keep as advance" tick only when the amount is more than the customer owes
		function syncAdvance() {
			var value = parseFloat(amount.value) || 0;
			var over  = value - Math.max(BALANCE, 0);
			var show  = over > 0.004;

			box.style.display = show ? '' : 'none';
			check.required = show;
			if (!show) check.checked = false;
			extra.textContent = show ? '{{ $symbol }} ' + over.toFixed(2) : '';
		}
		amount.addEventListener('input', syncAdvance);
		syncAdvance();

		@if($canCancel)
		// Cancel icons fill the cancel modal (admin only: the modal is not even rendered for others)
		document.querySelectorAll('[data-cancel-payment]').forEach(function (a) {
			a.addEventListener('click', function () {
				document.getElementById('cancel-form').action = a.dataset.action;
				document.getElementById('cancel-receipt').textContent = a.dataset.receipt;
				document.getElementById('cancel-amount').textContent = a.dataset.amount;
				document.getElementById('cancel-reason').value = '';
			});
		});
		@endif

		// Open the payment form from the customers list (?pay=1) or again after a validation error
		@if(request()->boolean('pay') || ($errors->any() && old('_form') === 'payment'))
		new bootstrap.Modal(document.getElementById('receive-payment')).show();
		@endif
	});
</script>
@endpush