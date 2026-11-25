@extends('layouts.app')

@section('title', 'Customers')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">
<style>
	.cust-stat { display: flex; align-items: center; gap: 14px; padding: 18px 20px; border: 1px solid #edf0f4; border-radius: 12px; background: #fff; height: 100%; }
	.cust-stat .stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
	.cust-stat .stat-icon svg { width: 22px; height: 22px; }
	.cust-stat h3 { margin: 0; font-size: 22px; font-weight: 700; line-height: 1.1; }
	.cust-stat p { margin: 2px 0 0; color: #6b7280; font-size: 13px; }
	.stat-total { background: #fff4e6; color: #ff9f43; }
	.stat-owed { background: #fdeaea; color: #ea5455; }
	.stat-clear { background: #e8f7ee; color: #28c76f; }
	.bal-owes { color: #ea5455; font-weight: 600; }
	.bal-clear { color: #28c76f; font-weight: 600; }
	.cust-sub { display: block; color: #6b7280; font-size: 12px; }
	.page-alert { display: flex; align-items: center; gap: 10px; }
	.page-alert svg { width: 18px; height: 18px; }
	.field-note { display: block; margin-top: 4px; color: #6b7280; font-size: 12px; }
	.required-star { color: #ea5455; }
	.modal-body .alert { margin-bottom: 16px; }
</style>
@endpush

@section('content')
@php
	$symbol   = $shop->currency_symbol ?: 'Rs';
	$owing    = $customers->filter(fn ($c) => (float) $c->balance > 0);
	$totalOwed = round((float) $owing->sum('balance'), 2);
	$addOld   = old('_form') === 'add-customer';
	$editOld  = old('_form') === 'edit-customer';
@endphp

<div class="page-wrapper">
	<div class="content">

		@if(session('status'))
		<div class="alert alert-success alert-dismissible fade show page-alert" role="alert">
			<i data-feather="check-circle"></i>
			<span>{{ session('status') }}</span>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		@endif

		<div class="page-header">
			<div class="add-item d-flex">
				<div class="page-title">
					<h4>Customers</h4>
					<h6>Customer accounts and their khata</h6>
				</div>
			</div>
			<ul class="table-top-head">
				<li>
					<a href="{{ route('customers') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i data-feather="rotate-ccw" class="feather-rotate-ccw"></i></a>
				</li>
				<li>
					<a href="javascript:void(0);" onclick="window.print()" data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i data-feather="printer" class="feather-rotate-ccw"></i></a>
				</li>
			</ul>
			<div class="page-btn">
				<a href="#" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#add-customer">
					<i data-feather="plus-circle" class="me-2"></i>Add New Customer
				</a>
			</div>
		</div>

		<div class="row g-3 mb-4">
			<div class="col-xl-4 col-sm-6">
				<div class="cust-stat">
					<div class="stat-icon stat-total"><i data-feather="users"></i></div>
					<div><h3>{{ $customers->count() }}</h3><p>Total customers</p></div>
				</div>
			</div>
			<div class="col-xl-4 col-sm-6">
				<div class="cust-stat">
					<div class="stat-icon stat-owed"><i data-feather="trending-up"></i></div>
					<div><h3>{{ $symbol }} {{ number_format($totalOwed, 2) }}</h3><p>Total udhaar outstanding</p></div>
				</div>
			</div>
			<div class="col-xl-4 col-sm-6">
				<div class="cust-stat">
					<div class="stat-icon stat-clear"><i data-feather="check-circle"></i></div>
					<div><h3>{{ $owing->count() }}</h3><p>Customers with a balance</p></div>
				</div>
			</div>
		</div>

		<div class="card table-list-card">
			<div class="card-body">
				<div class="table-top">
					<div class="search-set">
						<div class="search-input">
							<a href="" class="btn btn-searchset"><i data-feather="search" class="feather-search"></i></a>
						</div>
					</div>
				</div>

				<div class="table-responsive">
					<table class="table datanew" data-order='[[4,"desc"]]'>
						<thead>
							<tr>
								<th>Customer</th>
								<th>Phone</th>
								<th>City</th>
								<th>Credit limit</th>
								<th>Balance</th>
								<th>Status</th>
								<th class="no-sort text-center">Action</th>
							</tr>
						</thead>
						<tbody>
							@foreach($customers as $customer)
							@php $balance = round((float) ($customer->balance ?? 0), 2); @endphp
							<tr>
								<td>
									{{ $customer->name }}
									@if($customer->address)<span class="cust-sub">{{ $customer->address }}</span>@endif
								</td>
								<td>{{ $customer->phone }}</td>
								<td>{{ $customer->city ?: '-' }}</td>
								<td>{{ $customer->credit_limit !== null ? $symbol.' '.number_format((float) $customer->credit_limit, 2) : 'No limit' }}</td>
								<td data-order="{{ $balance }}">
									@if($balance > 0)
										<span class="bal-owes">{{ $symbol }} {{ number_format($balance, 2) }}</span>
										<span class="cust-sub">owes</span>
									@elseif($balance < 0)
										<span class="bal-clear">{{ $symbol }} {{ number_format(abs($balance), 2) }}</span>
										<span class="cust-sub">advance</span>
									@else
										<span class="text-muted">{{ $symbol }} 0.00</span>
									@endif
								</td>
								<td>
									@if($customer->is_active)
										<span class="badge badge-linesuccess">Active</span>
									@else
										<span class="badge badge-linedanger">Inactive</span>
									@endif
								</td>
								<td class="action-table-data text-center">
									<div class="edit-delete-action">

										@if($balance > 0 && preg_match('/^03\d{9}$/', (string) $customer->phone))
  											<a href="{{ route('customers.reminder', $customer) }}" class="me-2 p-2" title="WhatsApp reminder">
    											<i data-feather="message-circle" class="feather-message-circle"></i>
  											</a>
										@endif
										<a href="{{ route('customers.ledger', [$customer, 'pay' => 1]) }}" class="me-2 p-2" title="Receive payment">
											<i data-feather="dollar-sign" class="feather-dollar-sign"></i>
										</a>
										<a href="{{ route('customers.statement', $customer) }}" class="me-2 p-2" title="Statement">
											<i data-feather="printer" class="feather-printer"></i>
										</a>
										<a href="{{ route('customers.ledger', $customer) }}" class="me-2 p-2" title="Ledger">
											<i data-feather="file-text" class="feather-file-text"></i>
										</a>
										<a data-bs-toggle="modal" data-bs-target="#edit-customer" title="Edit customer" class="p-2"
											data-customer-edit data-id="{{ $customer->id }}"
											data-name="{{ $customer->name }}" data-phone="{{ $customer->phone }}"
											data-address="{{ $customer->address }}" data-city="{{ $customer->city }}"
											data-limit="{{ $customer->credit_limit }}" data-notes="{{ $customer->notes }}"
											data-active="{{ $customer->is_active ? 1 : 0 }}">
											<i data-feather="edit" class="feather-edit"></i>
										</a>
									</div>
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>

	</div>
</div>

{{-- ===================== Add customer modal ===================== --}}
<div class="modal fade" id="add-customer" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered custom-modal-two">
		<div class="modal-content">
			<div class="page-wrapper-new p-0">
				<div class="content">
					<div class="modal-header border-0 custom-modal-header">
						<div class="page-title"><h4>Add Customer</h4></div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body custom-modal-body">

						@if($addOld && $errors->any())
						<div class="alert alert-danger">
							<ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
						</div>
						@endif

						<form action="{{ route('customers.store') }}" method="POST">
							@csrf
							<input type="hidden" name="_form" value="add-customer">

							<div class="row">
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Name <span class="required-star">*</span></label>
										<input type="text" name="name" class="form-control" maxlength="255" required
											value="{{ $addOld ? old('name') : '' }}">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Phone <span class="required-star">*</span></label>
										<input type="text" name="phone" class="form-control" inputmode="tel" maxlength="20"
											placeholder="03001234567" required value="{{ $addOld ? old('phone') : '' }}">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>City</label>
										<input type="text" name="city" class="form-control" maxlength="100" value="{{ $addOld ? old('city') : '' }}">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Address</label>
										<input type="text" name="address" class="form-control" maxlength="500" value="{{ $addOld ? old('address') : '' }}">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Opening balance ({{ $symbol }})</label>
										<input type="number" name="opening_balance" class="form-control" min="0" step="0.01"
											placeholder="0.00" value="{{ $addOld ? old('opening_balance') : '' }}">
										<span class="field-note">Amount the customer already owes from your old khata.</span>
									</div>
								</div>
								@if($canSetLimit)
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Credit limit ({{ $symbol }})</label>
										<input type="number" name="credit_limit" class="form-control" min="0" step="0.01"
											placeholder="No limit" value="{{ $addOld ? old('credit_limit') : '' }}">
										<span class="field-note">Leave empty for no limit.</span>
									</div>
								</div>
								@endif
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Status</label>
										<select class="select" name="is_active">
											<option value="1" @selected(! $addOld || old('is_active') === '1')>Active</option>
											<option value="0" @selected($addOld && old('is_active') === '0')>Inactive</option>
										</select>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="input-blocks">
										<label>Notes</label>
										<input type="text" name="notes" class="form-control" maxlength="500" value="{{ $addOld ? old('notes') : '' }}">
									</div>
								</div>
							</div>
							<div class="modal-footer-btn">
								<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-submit">Create customer</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

{{-- ===================== Edit customer modal ===================== --}}
<div class="modal fade" id="edit-customer" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered custom-modal-two">
		<div class="modal-content">
			<div class="page-wrapper-new p-0">
				<div class="content">
					<div class="modal-header border-0 custom-modal-header">
						<div class="page-title"><h4>Edit Customer</h4></div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body custom-modal-body">

						@if($editOld && $errors->any())
						<div class="alert alert-danger">
							<ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
						</div>
						@endif

						<form id="edit-customer-form" method="POST" action="#">
							@csrf
							@method('PUT')
							<input type="hidden" name="_form" value="edit-customer">
							<input type="hidden" name="_id" id="edit-customer-id" value="{{ $editOld ? old('_id') : '' }}">

							<div class="row">
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Name <span class="required-star">*</span></label>
										<input type="text" name="name" id="edit-customer-name" class="form-control" maxlength="255" required>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Phone <span class="required-star">*</span></label>
										<input type="text" name="phone" id="edit-customer-phone" class="form-control" inputmode="tel" maxlength="20" required>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>City</label>
										<input type="text" name="city" id="edit-customer-city" class="form-control" maxlength="100">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Address</label>
										<input type="text" name="address" id="edit-customer-address" class="form-control" maxlength="500">
									</div>
								</div>
								@if($canSetLimit)
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Credit limit ({{ $symbol }})</label>
										<input type="number" name="credit_limit" id="edit-customer-limit" class="form-control" min="0" step="0.01" placeholder="No limit">
									</div>
								</div>
								@endif
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Status</label>
										<select class="select" name="is_active" id="edit-customer-status">
											<option value="1">Active</option>
											<option value="0">Inactive</option>
										</select>
										<span class="field-note">Inactive customers cannot take new credit.</span>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="input-blocks">
										<label>Notes</label>
										<input type="text" name="notes" id="edit-customer-notes" class="form-control" maxlength="500">
									</div>
								</div>
							</div>
							<div class="modal-footer-btn">
								<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-submit">Save changes</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		var baseUrl = "{{ url('/customers') }}/";

		function setValue(id, value) {
			var el = document.getElementById(id);
			if (el) el.value = value;
		}

		document.getElementById('add-customer').addEventListener('hidden.bs.modal', function () {
			this.querySelector('form').reset();
		});

		// Edit icons fill the modal (works after table search or pagination too).
		document.addEventListener('click', function (e) {
			var edit = e.target.closest('[data-customer-edit]');
			if (!edit) return;

			setValue('edit-customer-id', edit.dataset.id);
			document.getElementById('edit-customer-form').action = baseUrl + edit.dataset.id;
			setValue('edit-customer-name', edit.dataset.name);
			setValue('edit-customer-phone', edit.dataset.phone);
			setValue('edit-customer-city', edit.dataset.city);
			setValue('edit-customer-address', edit.dataset.address);
			setValue('edit-customer-limit', edit.dataset.limit);
			setValue('edit-customer-notes', edit.dataset.notes);
			setValue('edit-customer-status', edit.dataset.active);

			var status = document.getElementById('edit-customer-status');
			if (window.jQuery) window.jQuery(status).trigger('change');
		});

		// Reopen the same modal when validation fails.
		@if ($errors->any() && old('_form'))
		var failed = document.getElementById(@json(old('_form')));
		if (failed && window.bootstrap) {
			@if (old('_form') === 'edit-customer')
			document.getElementById('edit-customer-form').action = baseUrl + @json(old('_id'));
			setValue('edit-customer-name', @json(old('name')));
			setValue('edit-customer-phone', @json(old('phone')));
			setValue('edit-customer-city', @json(old('city')));
			setValue('edit-customer-address', @json(old('address')));
			setValue('edit-customer-limit', @json(old('credit_limit')));
			setValue('edit-customer-notes', @json(old('notes')));
			setValue('edit-customer-status', @json(old('is_active')));
			@endif
			new bootstrap.Modal(failed).show();
		}
		@endif
	});
</script>
@endpush
