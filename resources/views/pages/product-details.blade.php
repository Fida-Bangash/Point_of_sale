@extends('layouts.app')

@section('title', $product->name)

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
	.detail-label { color: #6b7280; font-size: 13px; margin-bottom: 2px; }
	.detail-value { color: #1b2230; font-weight: 600; margin-bottom: 18px; }
	.detail-img { width: 100%; max-width: 260px; border-radius: 14px; object-fit: cover; }
</style>
@endpush

@section('content')
<div class="page-wrapper">
	<div class="content">
		<div class="page-header">
			<div class="add-item d-flex">
				<div class="page-title">
					<h4>{{ $product->name }}</h4>
					<h6>Product details</h6>
				</div>
			</div>
			<div class="page-btn d-flex gap-2">
				<a href="{{ url('/product-list') }}" class="btn btn-secondary"><i data-feather="arrow-left" class="me-2"></i>Back</a>
				<a href="{{ url('/edit-product/'.$product->id) }}" class="btn btn-added"><i data-feather="edit" class="me-2"></i>Edit</a>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<div class="row">
					<div class="col-lg-3 mb-3">
						@if($product->imageUrl())
						<img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="detail-img">
						@else
						<div class="cat-avatar cat-c1" style="width:120px;height:120px;font-size:44px;border-radius:18px;">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</div>
						@endif
					</div>
					<div class="col-lg-9">
						<div class="row">
							<div class="col-md-4"><div class="detail-label">SKU</div><div class="detail-value">{{ $product->sku }}</div></div>
							<div class="col-md-4"><div class="detail-label">Barcode</div><div class="detail-value">{{ $product->barcode ?: '—' }}</div></div>
							<div class="col-md-4"><div class="detail-label">Status</div>
								<div class="detail-value">
									@if($product->is_active)<span class="badge badge-linesuccess">Active</span>@else<span class="badge badge-linedanger">Inactive</span>@endif
								</div>
							</div>

							<div class="col-md-4"><div class="detail-label">Category</div><div class="detail-value">{{ $product->category->name }}</div></div>
							<div class="col-md-4"><div class="detail-label">Brand</div><div class="detail-value">{{ $product->brand?->name ?? '—' }}</div></div>
							<div class="col-md-4"><div class="detail-label">Unit</div><div class="detail-value">{{ $product->unit->name }} ({{ $product->unit->short_name }})</div></div>

							<div class="col-md-4"><div class="detail-label">Cost price</div><div class="detail-value">Rs {{ number_format((float) $product->purchase_price, 2) }}</div></div>
							<div class="col-md-4"><div class="detail-label">Selling price</div><div class="detail-value">Rs {{ number_format((float) $product->selling_price, 2) }}</div></div>
							<div class="col-md-4"><div class="detail-label">Profit per unit</div>
								@php $profit = (float) $product->selling_price - (float) $product->purchase_price; @endphp
								<div class="detail-value {{ $profit < 0 ? 'text-danger' : '' }}">Rs {{ number_format($profit, 2) }}</div>
							</div>

							<div class="col-md-4"><div class="detail-label">In stock</div>
								<div class="detail-value {{ $product->isLowStock() ? 'stock-low' : '' }}">{{ $product->qty() }} {{ $product->unit->short_name }}</div>
							</div>
							<div class="col-md-4"><div class="detail-label">Low-stock alert at</div><div class="detail-value">{{ $product->alertQty() }} {{ $product->unit->short_name }}</div></div>
							<div class="col-md-4"><div class="detail-label">Added on</div><div class="detail-value">{{ $product->created_at?->format('d M Y') }}</div></div>

							<div class="col-12"><div class="detail-label">Description</div><div class="detail-value" style="font-weight:400;">{{ $product->description ?: '—' }}</div></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
