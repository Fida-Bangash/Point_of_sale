@extends('layouts.app')

@section('title', 'Products')

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

	/* ---- Products ---- */
	.prod-thumb { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
	.sku-text { color: #6b7280; font-size: 12px; }
	.stock-low { color: #ea5455; font-weight: 600; }
	.stock-ok { color: #1b2230; font-weight: 600; }
	.stock-unit { color: #6b7280; font-weight: 400; margin-left: 2px; }
</style>
@endpush

@section('content')
@php
$total = $products->count();
$active = $products->where('is_active', true)->count();
$low = $products->filter(fn ($p) => $p->isLowStock())->count();
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
					<h4>Products</h4>
					<h6>Manage your products and stock levels</h6>
				</div>
			</div>
			<ul class="table-top-head">
				<li>
					<a href="{{ url('/product-list') }}" data-bs-toggle="tooltip" data-bs-placement="top"
						title="Refresh"><i data-feather="rotate-ccw" class="feather-rotate-ccw"></i></a>
				</li>
				<li>
					<a href="javascript:void(0);" onclick="window.print()" data-bs-toggle="tooltip"
						data-bs-placement="top" title="Print"><i data-feather="printer"
							class="feather-rotate-ccw"></i></a>
				</li>
			</ul>
			<div class="page-btn">
				<a href="{{ url('/add-product') }}" class="btn btn-added">
					<i data-feather="plus-circle" class="me-2"></i>Add New Product
				</a>
			</div>
		</div>

		{{-- Summary cards --}}
		<div class="row g-3 mb-4">
			<div class="col-xl-4 col-sm-6">
				<div class="cat-stat">
					<div class="stat-icon stat-total"><i data-feather="package"></i></div>
					<div>
						<h3>{{ $total }}</h3>
						<p>Total products</p>
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
					<div class="stat-icon stat-inactive"><i data-feather="alert-triangle"></i></div>
					<div>
						<h3>{{ $low }}</h3>
						<p>Low stock</p>
					</div>
				</div>
			</div>
		</div>

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
								<th>Product</th>
								<th>Category</th>
								<th>Brand</th>
								<th>Cost</th>
								<th>Price</th>
								<th>Stock</th>
								<th>Status</th>
								<th class="no-sort text-center">Action</th>
							</tr>
						</thead>
						<tbody>
							@foreach($products as $product)
							<tr>
								<td>
									<div class="cat-cell">
										@if($product->imageUrl())
										<img src="{{ $product->imageUrl() }}" alt="" class="prod-thumb">
										@else
										<div class="cat-avatar cat-c{{ ($loop->index % 4) + 1 }}">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</div>
										@endif
										<div>
											<div class="cat-name">{{ $product->name }}</div>
											<div class="sku-text">{{ $product->sku }}</div>
										</div>
									</div>
								</td>
								<td>{{ $product->category->name }}</td>
								<td>{{ $product->brand?->name ?? '—' }}</td>
								<td data-order="{{ $product->purchase_price }}">Rs {{ number_format((float) $product->purchase_price, 2) }}</td>
								<td data-order="{{ $product->selling_price }}">Rs {{ number_format((float) $product->selling_price, 2) }}</td>
								<td data-order="{{ $product->quantity }}">
									<span class="{{ $product->isLowStock() ? 'stock-low' : 'stock-ok' }}">{{ $product->qty() }}</span><span class="stock-unit">{{ $product->unit->short_name }}</span>
									@if($product->isLowStock())
									<div class="sku-text">Low stock (alert at {{ $product->alertQty() }})</div>
									@endif
								</td>
								<td>
									@if($product->is_active)
									<span class="badge badge-linesuccess">Active</span>
									@else
									<span class="badge badge-linedanger">Inactive</span>
									@endif
								</td>
								<td class="action-table-data text-center">
									<div class="edit-delete-action cat-actions">
										<a href="{{ url('/product-details/'.$product->id) }}" title="View"><i data-feather="eye" class="feather-eye"></i></a>
										<a href="{{ url('/edit-product/'.$product->id) }}" title="Edit product"><i data-feather="edit" class="feather-edit"></i></a>
										<a data-bs-toggle="modal" data-bs-target="#delete-product" title="Delete product"
											data-prod-delete data-id="{{ $product->id }}" data-name="{{ $product->name }}">
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

{{-- ===================== Delete product modal ===================== --}}
<div class="modal fade" id="delete-product" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered custom-modal-two">
		<div class="modal-content">
			<div class="page-wrapper-new p-0">
				<div class="content">
					<div class="modal-header border-0 custom-modal-header">
						<div class="page-title">
							<h4>Delete Product</h4>
						</div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body custom-modal-body">
						<form id="delete-prod-form" action="{{ url('/products') }}" method="POST">
							@csrf
							@method('DELETE')

							<div class="delete-body">
								<div class="delete-icon"><i data-feather="trash-2"></i></div>
								<h5>Delete this product?</h5>
								<p><strong id="delete-prod-name">Product</strong> will be removed for good. This cannot
									be undone.</p>
							</div>
							<div class="modal-footer-btn justify-content-center">
								<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Keep
									product</button>
								<button type="submit" class="btn btn-delete-confirm">Delete product</button>
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
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert/sweetalerts.min.js') }}"></script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		var baseUrl = "{{ url('/products') }}/";

		// Delete icon fills the modal (works after table search/paging too)
		document.addEventListener('click', function (e) {
			var del = e.target.closest('[data-prod-delete]');
			if (del) {
				document.getElementById('delete-prod-form').action = baseUrl + del.dataset.id;
				document.getElementById('delete-prod-name').textContent = del.dataset.name;
			}
		});
	});
</script>
@endpush
