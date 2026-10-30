{{--
	Shared by add-product and edit-product.
	Needs: $product (null when creating), $categories, $brands, $units
--}}
@php
	$isEdit = ! empty($product);
@endphp

@if($errors->any())
<div class="alert alert-danger">
	<ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="row">
	{{-- ---- Basic ---- --}}
	<div class="col-lg-6">
		<div class="input-blocks">
			<label>Product name <span class="required-star">*</span></label>
			<input type="text" name="name" class="form-control @error('name') is-invalid @enderror" maxlength="255" required
				placeholder="e.g. Pepsi 1.5L" value="{{ old('name', $product?->name) }}">
		</div>
	</div>
	<div class="col-lg-3">
		<div class="input-blocks">
			<label>SKU</label>
			<input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" maxlength="100"
				placeholder="Auto" value="{{ old('sku', $product?->sku) }}">
			<span class="field-note">Leave blank to generate one.</span>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="input-blocks">
			<label>Barcode</label>
			<input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" maxlength="100"
				placeholder="Scan or type" value="{{ old('barcode', $product?->barcode) }}">
		</div>
	</div>

	{{-- ---- Classification ---- --}}
	<div class="col-lg-4">
		<div class="input-blocks">
			<label>Category <span class="required-star">*</span></label>
			<select class="select" name="category_id" required>
				<option value="">Choose category</option>
				@foreach($categories as $c)
				<option value="{{ $c->id }}" @selected((string) old('category_id', $product?->category_id) === (string) $c->id)>{{ $c->name }}</option>
				@endforeach
			</select>
			@if($categories->isEmpty())
			<span class="field-note text-danger">No categories yet. <a href="{{ url('/category-list') }}">Add one first</a>.</span>
			@endif
		</div>
	</div>
	<div class="col-lg-4">
		<div class="input-blocks">
			<label>Brand</label>
			<select class="select" name="brand_id">
				<option value="">No brand</option>
				@foreach($brands as $b)
				<option value="{{ $b->id }}" @selected((string) old('brand_id', $product?->brand_id) === (string) $b->id)>{{ $b->name }}</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="input-blocks">
			<label>Unit <span class="required-star">*</span></label>
			<select class="select" name="unit_id" required>
				<option value="">Choose unit</option>
				@foreach($units as $u)
				<option value="{{ $u->id }}" @selected((string) old('unit_id', $product?->unit_id) === (string) $u->id)>{{ $u->name }} ({{ $u->short_name }})</option>
				@endforeach
			</select>
		</div>
	</div>

	{{-- ---- Price & stock ---- --}}
	<div class="col-lg-3">
		<div class="input-blocks">
			<label>Cost price (Rs) <span class="required-star">*</span></label>
			<input type="number" id="pp" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror"
				min="0" step="0.01" required value="{{ old('purchase_price', $isEdit ? number_format((float) $product->purchase_price, 2, '.', '') : '0') }}">
		</div>
	</div>
	<div class="col-lg-3">
		<div class="input-blocks">
			<label>Selling price (Rs) <span class="required-star">*</span></label>
			<input type="number" id="sp" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror"
				min="0" step="0.01" required value="{{ old('selling_price', $isEdit ? number_format((float) $product->selling_price, 2, '.', '') : '0') }}">
			<span class="field-note" id="margin-note"></span>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="input-blocks">
			<label>{{ $isEdit ? 'Stock quantity' : 'Opening stock' }} <span class="required-star">*</span></label>
			<input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
				min="0" step="0.001" required value="{{ old('quantity', $isEdit ? $product->qty() : '0') }}">
			<span class="field-note">Purchases and sales will update this later.</span>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="input-blocks">
			<label>Low-stock alert at <span class="required-star">*</span></label>
			<input type="number" name="alert_quantity" class="form-control @error('alert_quantity') is-invalid @enderror"
				min="0" step="0.001" required value="{{ old('alert_quantity', $isEdit ? $product->alertQty() : '5') }}">
		</div>
	</div>

	{{-- ---- Extra ---- --}}
	<div class="col-lg-12">
		<div class="input-blocks">
			<label>Description</label>
			<textarea name="description" class="form-control" rows="3" maxlength="2000"
				placeholder="Optional">{{ old('description', $product?->description) }}</textarea>
		</div>
	</div>
	<div class="col-lg-6">
		<div class="input-blocks">
			<label>Product image</label>
			<input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
				accept="image/png,image/jpeg,image/webp">
			<span class="field-note">JPG, PNG or WEBP, up to 2 MB.</span>
			@if($isEdit && $product->imageUrl())
			<div class="d-flex align-items-center gap-3 mt-2">
				<img src="{{ $product->imageUrl() }}" alt="" class="prod-thumb" style="width:64px;height:64px;">
				<label class="mb-0"><input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))> Remove image</label>
			</div>
			@endif
		</div>
	</div>
	<div class="col-lg-3">
		<div class="input-blocks">
			<label>Status</label>
			<select class="select" name="is_active">
				<option value="1" @selected((string) old('is_active', ($product?->is_active ?? true) ? '1' : '0') === '1')>Active</option>
				<option value="0" @selected((string) old('is_active', ($product?->is_active ?? true) ? '1' : '0') === '0')>Inactive</option>
			</select>
			<span class="field-note">Inactive products are hidden from the POS.</span>
		</div>
	</div>
</div>

@push('scripts')
<script>
	document.addEventListener('DOMContentLoaded', function () {
		var pp = document.getElementById('pp'), sp = document.getElementById('sp'), note = document.getElementById('margin-note');
		function update() {
			var p = parseFloat(pp.value), s = parseFloat(sp.value);
			if (isNaN(p) || isNaN(s)) { note.textContent = ''; return; }
			var m = s - p;
			note.textContent = 'Profit per unit: Rs ' + m.toFixed(2) + (p > 0 ? ' (' + (m / p * 100).toFixed(1) + '%)' : '');
			note.className = 'field-note' + (m < 0 ? ' text-danger' : '');
		}
		pp.addEventListener('input', update);
		sp.addEventListener('input', update);
		update();
	});
</script>
@endpush
