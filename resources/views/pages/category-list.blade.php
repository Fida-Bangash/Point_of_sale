@extends('layouts.app')

@section('title', 'Categories')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">
<style>
	/* ---- Summary cards ---- */
	.cat-stat {
		display: flex;
		align-items: center;
		gap: 14px;
		padding: 18px 20px;
		border: 1px solid #edf0f4;
		border-radius: 12px;
		background: #fff;
		height: 100%;
	}

	.cat-stat .stat-icon {
		width: 46px;
		height: 46px;
		border-radius: 12px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	.cat-stat .stat-icon svg {
		width: 22px;
		height: 22px;
	}

	.cat-stat h3 {
		margin: 0;
		font-size: 24px;
		font-weight: 700;
		line-height: 1.1;
	}

	.cat-stat p {
		margin: 2px 0 0;
		color: #6b7280;
		font-size: 13px;
	}

	.stat-total {
		background: #fff4e6;
		color: #ff9f43;
	}

	.stat-active {
		background: #e8f7ee;
		color: #28c76f;
	}

	.stat-inactive {
		background: #fdeaea;
		color: #ea5455;
	}

	/* ---- Table ---- */
	.cat-cell {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	.cat-avatar {
		width: 40px;
		height: 40px;
		border-radius: 10px;
		font-weight: 700;
		font-size: 16px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	.cat-c1 {
		background: #fff4e6;
		color: #ff9f43;
	}

	.cat-c2 {
		background: #e8f7ee;
		color: #28c76f;
	}

	.cat-c3 {
		background: #eceafd;
		color: #7367f0;
	}

	.cat-c4 {
		background: #e1f3fc;
		color: #00a2e8;
	}

	.cat-cell .cat-name {
		font-weight: 600;
		color: #1b2230;
		line-height: 1.2;
	}

	.slug-chip {
		display: inline-block;
		padding: 3px 10px;
		border-radius: 6px;
		background: #f8f9fb;
		color: #6b7280;
		font-size: 13px;
	}

	.product-count {
		color: #1b2230;
		font-weight: 600;
	}

	.product-count small {
		color: #6b7280;
		font-weight: 400;
		margin-left: 2px;
	}

	/* Edit / delete icons centred under the "Action" header */
	.cat-actions {
		display: flex;
		justify-content: center;
		align-items: center;
		gap: 14px;
		width: 100%;
	}

	.cat-actions a {
		padding: 0;
		margin: 0;
		cursor: pointer;
	}

	/* ---- Alerts ---- */
	.page-alert {
		border-radius: 10px;
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.page-alert svg {
		width: 18px;
		height: 18px;
		flex-shrink: 0;
	}

	/* ---- Modals ---- */
	.field-note {
		color: #6b7280;
		font-size: 12px;
		margin-top: 4px;
		display: block;
	}

	.required-star {
		color: #ea5455;
	}

	.modal-body .alert {
		border-radius: 10px;
		font-size: 14px;
	}

	.delete-body {
		text-align: center;
		padding: 8px 4px 0;
	}

	.delete-icon {
		width: 56px;
		height: 56px;
		border-radius: 50%;
		background: #fdeaea;
		color: #ea5455;
		display: flex;
		align-items: center;
		justify-content: center;
		margin: 0 auto 14px;
	}

	.delete-icon svg {
		width: 26px;
		height: 26px;
	}

	.delete-body h5 {
		font-weight: 700;
		color: #1b2230;
		margin-bottom: 6px;
	}

	.delete-body p {
		color: #6b7280;
		margin-bottom: 0;
	}

	.delete-body p strong {
		color: #1b2230;
	}

	.btn-delete-confirm {
		background: #ea5455;
		border: 1px solid #ea5455;
		color: #fff;
		border-radius: 8px;
		font-weight: 600;
		padding: 10px 20px;
	}

	.btn-delete-confirm:hover {
		background: #d94546;
		border-color: #d94546;
		color: #fff;
	}
</style>
@endpush

@section('content')
@php
$total = $categories->count();
$active = $categories->where('is_active', true)->count();
$inactive = $total - $active;

$addOld = old('_form') === 'add-category';
$editOld = old('_form') === 'edit-category';
@endphp

<div class="page-wrapper">
	<div class="content">

		{{-- Flash message (success) --}}
		@if(session('status'))
		<div class="alert alert-success alert-dismissible fade show page-alert" role="alert">
			<i data-feather="check-circle"></i>
			<span>{{ session('status') }}</span>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		@endif

		{{-- Flash message (error), e.g. delete blocked --}}
		@if(session('error'))
		<div class="alert alert-danger alert-dismissible fade show page-alert" role="alert">
			<i data-feather="alert-circle"></i>
			<span>{{ session('error') }}</span>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		@endif

		<div class="page-header">
			<div class="add-item d-flex">
				<div class="page-title">
					<h4>Categories</h4>
					<h6>Group your products so they are easy to find and sell</h6>
				</div>
			</div>
			<ul class="table-top-head">
				<li>
					<a href="{{ url('/category-list') }}" data-bs-toggle="tooltip" data-bs-placement="top"
						title="Refresh"><i data-feather="rotate-ccw" class="feather-rotate-ccw"></i></a>
				</li>
				<li>
					<a href="javascript:void(0);" onclick="window.print()" data-bs-toggle="tooltip"
						data-bs-placement="top" title="Print"><i data-feather="printer"
							class="feather-rotate-ccw"></i></a>
				</li>
				<li>
					<a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i
							data-feather="chevron-up" class="feather-chevron-up"></i></a>
				</li>
			</ul>
			<div class="page-btn">
				<a href="#" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#add-category">
					<i data-feather="plus-circle" class="me-2"></i>Add New Category
				</a>
			</div>
		</div>

		{{-- Summary cards --}}
		<div class="row g-3 mb-4">
			<div class="col-xl-4 col-sm-6">
				<div class="cat-stat">
					<div class="stat-icon stat-total"><i data-feather="grid"></i></div>
					<div>
						<h3>{{ $total }}</h3>
						<p>Total categories</p>
					</div>
				</div>
			</div>
			<div class="col-xl-4 col-sm-6">
				<div class="cat-stat">
					<div class="stat-icon stat-active"><i data-feather="check-circle"></i></div>
					<div>
						<h3>{{ $active }}</h3>
						<p>Active</p>
					</div>
				</div>
			</div>
			<div class="col-xl-4 col-sm-6">
				<div class="cat-stat">
					<div class="stat-icon stat-inactive"><i data-feather="x-circle"></i></div>
					<div>
						<h3>{{ $inactive }}</h3>
						<p>Inactive</p>
					</div>
				</div>
			</div>
		</div>

		{{-- Categories table --}}
		<div class="card table-list-card">
			<div class="card-body">
				<div class="table-top">
					<div class="search-set">
						<div class="search-input">
							<a href="" class="btn btn-searchset"><i data-feather="search"
									class="feather-search"></i></a>
						</div>
					</div>
				</div>

				<div class="table-responsive">
					<table class="table datanew">
						<thead>
							<tr>
								<th>Category</th>
								<th>Slug</th>
								<th>Products</th>
								<th>Created On</th>
								<th>Status</th>
								<th class="no-sort text-center">Action</th>
							</tr>
						</thead>
						<tbody>
							@foreach($categories as $category)
							<tr>
								<td>
									<div class="cat-cell">
										<div class="cat-avatar cat-c{{ ($loop->index % 4) + 1 }}">{{
											mb_strtoupper(mb_substr($category->name, 0, 1)) }}</div>
										<div class="cat-name">{{ $category->name }}</div>
									</div>
								</td>
								<td><span class="slug-chip">{{ $category->slug }}</span></td>
								<td><span class="product-count">{{ $category->products_count }}<small>items</small></span></td>
								<td>{{ $category->created_at?->format('d M Y') }}</td>
								<td>
									@if($category->is_active)
									<span class="badge badge-linesuccess">Active</span>
									@else
									<span class="badge badge-linedanger">Inactive</span>
									@endif
								</td>
								<td class="action-table-data text-center">
									<div class="edit-delete-action cat-actions">
										<a data-bs-toggle="modal" data-bs-target="#edit-category" title="Edit category"
											data-cat-edit data-id="{{ $category->id }}"
											data-name="{{ $category->name }}" data-slug="{{ $category->slug }}"
											data-active="{{ $category->is_active ? 1 : 0 }}">
											<i data-feather="edit" class="feather-edit"></i>
										</a>
										<a data-bs-toggle="modal" data-bs-target="#delete-category"
											title="Delete category" data-cat-delete data-id="{{ $category->id }}"
											data-name="{{ $category->name }}">
											<i data-feather="trash-2" class="feather-trash-2"></i>
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

{{-- ===================== Add category modal ===================== --}}
<div class="modal fade" id="add-category" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered custom-modal-two">
		<div class="modal-content">
			<div class="page-wrapper-new p-0">
				<div class="content">
					<div class="modal-header border-0 custom-modal-header">
						<div class="page-title">
							<h4>Add Category</h4>
						</div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body custom-modal-body">

						@if($addOld && $errors->any())
						<div class="alert alert-danger">
							<ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
						</div>
						@endif

						<form action="{{ route('categories.store') }}" method="POST">
							@csrf
							<input type="hidden" name="_form" value="add-category">

							<div class="row">
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Category name <span class="required-star">*</span></label>
										<input type="text" id="add-cat-name" name="name" class="form-control"
											placeholder="e.g. Laptops" maxlength="255" required
											value="{{ $addOld ? old('name') : '' }}">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Slug <span class="required-star">*</span></label>
										<input type="text" id="add-cat-slug" name="slug" class="form-control"
											placeholder="laptops" maxlength="255" required
											value="{{ $addOld ? old('slug') : '' }}">
										<span class="field-note">Filled in for you. Lowercase letters, numbers and
											dashes.</span>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Status</label>
										<select class="select" name="is_active">
											<option value="1" @selected(! $addOld || old('is_active')==='1' )>Active
											</option>
											<option value="0" @selected($addOld && old('is_active')==='0' )>Inactive
											</option>
										</select>
										<span class="field-note">Inactive categories are hidden from the POS.</span>
									</div>
								</div>
							</div>
							<div class="modal-footer-btn">
								<button type="button" class="btn btn-cancel me-2"
									data-bs-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-submit">Create category</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

{{-- ===================== Edit category modal ===================== --}}
<div class="modal fade" id="edit-category" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered custom-modal-two">
		<div class="modal-content">
			<div class="page-wrapper-new p-0">
				<div class="content">
	                <div class="modal-header border-0 custom-modal-header">
						<div class="page-title">
							<h4>Edit Category</h4>
						</div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body custom-modal-body">

						@if($editOld && $errors->any())
						<div class="alert alert-danger">
							<ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
						</div>
						@endif

						{{-- JavaScript sets the action ID when the edit icon is clicked. --}}
						<form id="edit-cat-form" action="{{ url('/category-list') }}" method="POST">
							@csrf
							@method('PUT')
							<input type="hidden" name="_form" value="edit-category">
							<input type="hidden" name="_id" id="edit-cat-id" value="{{ $editOld ? old('_id') : '' }}">

							<div class="row">
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Category name <span class="required-star">*</span></label>
										<input type="text" id="edit-cat-name" name="name" class="form-control"
											maxlength="255" required value="{{ $editOld ? old('name') : '' }}">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Slug <span class="required-star">*</span></label>
										<input type="text" id="edit-cat-slug" name="slug" class="form-control"
											maxlength="255" required value="{{ $editOld ? old('slug') : '' }}">
										<span class="field-note">Lowercase letters, numbers and dashes.</span>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="input-blocks">
										<label>Status</label>
										<select class="select" id="edit-cat-status" name="is_active">
											<option value="1" @selected(! $editOld || old('is_active')==='1' )>Active
											</option>
											<option value="0" @selected($editOld && old('is_active')==='0' )>Inactive
											</option>
										</select>
										<span class="field-note">Inactive categories are hidden from the POS.</span>
									</div>
								</div>
							</div>
							<div class="modal-footer-btn">
								<button type="button" class="btn btn-cancel me-2"
									data-bs-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-submit">Save changes</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

{{-- ===================== Delete category modal ===================== --}}
<div class="modal fade" id="delete-category" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered custom-modal-two">
		<div class="modal-content">
			<div class="page-wrapper-new p-0">
				<div class="content">
					<div class="modal-header border-0 custom-modal-header">
						<div class="page-title">
							<h4>Delete Category</h4>
						</div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body custom-modal-body">
				{{-- JavaScript sets the action ID when the delete icon is clicked. --}}
						<form id="delete-cat-form" action="{{ url('/category-list') }}" method="POST">
							@csrf
							@method('DELETE')

							<div class="delete-body">
								<div class="delete-icon"><i data-feather="trash-2"></i></div>
								<h5>Delete this category?</h5>
								<p><strong id="delete-cat-name">Category</strong> will be removed for good. This cannot
									be undone.</p>
							</div>
							<div class="modal-footer-btn justify-content-center">
								<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Keep
									category</button>
								<button type="submit" class="btn btn-delete-confirm">Delete category</button>
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
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalerts.min.js') }}"></script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		var baseUrl = "{{ url('/category-list') }}/";

		function slugify(text) {
			return text.toLowerCase().trim()
				.replace(/&/g, ' and ')
				.replace(/[^a-z0-9]+/g, '-')
				.replace(/^-+|-+$/g, '');
		}

		// Add modal: The slug is automatically generated from the name until manually edited.
		var addName = document.getElementById('add-cat-name');
		var addSlug = document.getElementById('add-cat-slug');
		var slugEdited = false;

		addSlug.addEventListener('input', function () {
			slugEdited = addSlug.value !== '';
		});
		addName.addEventListener('input', function () {
			if (!slugEdited) addSlug.value = slugify(addName.value);
		});
		document.getElementById('add-category').addEventListener('hidden.bs.modal', function () {
			this.querySelector('form').reset();
			slugEdited = false;
		});
		// Edit/delete icons populate their modals (works even after table search or pagination).
		document.addEventListener('click', function (e) {
			var edit = e.target.closest('[data-cat-edit]');
			if (edit) {
				document.getElementById('edit-cat-id').value = edit.dataset.id;
				document.getElementById('edit-cat-form').action = baseUrl + edit.dataset.id;
				document.getElementById('edit-cat-name').value = edit.dataset.name;
				document.getElementById('edit-cat-slug').value = edit.dataset.slug;
				var status = document.getElementById('edit-cat-status');
				status.value = edit.dataset.active;
				if (window.jQuery) window.jQuery(status).trigger('change');
			}

			var del = e.target.closest('[data-cat-delete]');
			if (del) {
				document.getElementById('delete-cat-form').action = baseUrl + del.dataset.id;
				document.getElementById('delete-cat-name').textContent = del.dataset.name;
			}
		});

		// Reopen the same modal when validation fails.
		@if ($errors -> any() && old('_form'))
			var failed = document.getElementById(@json(old('_form')));
		if (failed && window.bootstrap) {
			@if (old('_form') === 'edit-category')
				document.getElementById('edit-cat-form').action = baseUrl + @json(old('_id'));
			@endif
			new bootstrap.Modal(failed).show();
		}
		@endif
	});
</script>
@endpush