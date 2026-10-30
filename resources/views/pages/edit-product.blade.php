@extends('layouts.app')

@section('title', 'Edit Product')

@push('styles')
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
<div class="page-wrapper">
	<div class="content">
		<div class="page-header">
			<div class="add-item d-flex">
				<div class="page-title">
					<h4>Edit Product</h4>
					<h6>Update product details</h6>
				</div>
			</div>
			<div class="page-btn">
				<a href="{{ url('/product-list') }}" class="btn btn-secondary"><i data-feather="arrow-left" class="me-2"></i>Back to products</a>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
					@csrf
					@method('PUT')

					@include('partials.product-form')

					<div class="modal-footer-btn mt-3">
						<a href="{{ url('/product-list') }}" class="btn btn-cancel me-2">Cancel</a>
						<button type="submit" class="btn btn-submit">Save changes</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
@endpush
