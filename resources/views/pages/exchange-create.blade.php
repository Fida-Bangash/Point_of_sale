@extends('layouts.app')

@section('title', 'Exchange ' . $sale->invoice_no)

@section('content')
@php
	// Restore the cart if the form comes back with validation errors
	$oldCart = collect(old('new_items', []))->map(fn ($r) => [
		'id'    => (int) ($r['product_id'] ?? 0),
		'name'  => (string) ($r['name'] ?? ''),
		'price' => (float) ($r['price'] ?? 0),
		'qty'   => (float) ($r['quantity'] ?? 1),
	])->filter(fn ($r) => $r['id'] > 0)->values();
@endphp
<div class="page-wrapper">
	<div class="content">
		<div class="page-header">
			<div class="page-title">
				<h4>Exchange</h4>
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

		<form method="POST" action="{{ route('sales.exchange.store', $sale) }}" id="exchange-form">
			@csrf

			<div class="row">
				{{-- 1. Goods coming back --}}
				<div class="col-lg-6">
					<div class="card">
						<div class="card-body">
							<h5 class="mb-1">1. Goods coming back</h5>
							<p class="text-muted">Quantity the customer returns. Untick <strong>Restock</strong> for damaged goods.</p>

							<div class="table-responsive">
								<table class="table">
									<thead>
										<tr>
											<th>Product</th>
											<th class="text-end">Can return</th>
											<th style="width:110px">Return qty</th>
											<th class="text-center">Restock</th>
											<th class="text-end">Value</th>
										</tr>
									</thead>
									<tbody>
										@foreach($states->values() as $i => $s)
										@php
											$item = $s['item'];
											$unit = $service->netUnitPrice($s);
										@endphp
										<tr class="ex-return-row" data-max="{{ $s['available'] }}" data-unit="{{ $unit }}">
											<td>
												{{ $item->product_name }}
												<input type="hidden" name="return_items[{{ $i }}][sale_item_id]" value="{{ $item->id }}">
											</td>
											<td class="text-end">{{ \App\Models\Product::trimNumber($s['available']) }}</td>
											<td>
												@if($s['available'] > 0)
													<input type="number" class="form-control ex-return-qty"
														name="return_items[{{ $i }}][quantity]"
														value="{{ old('return_items.' . $i . '.quantity') }}"
														min="0" max="{{ $s['available'] }}" step="0.001" placeholder="0">
												@else
													<span class="text-muted">Returned</span>
												@endif
											</td>
											<td class="text-center">
												@if($s['available'] > 0)
													<input type="hidden" name="return_items[{{ $i }}][restock]" value="0">
													<input type="checkbox" name="return_items[{{ $i }}][restock]" value="1"
														{{ old('return_items.' . $i . '.restock', '1') ? 'checked' : '' }}>
												@endif
											</td>
											<td class="text-end ex-return-line">{{ $shop->money(0) }}</td>
										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

				{{-- 2. New goods --}}
				<div class="col-lg-6">
					<div class="card">
						<div class="card-body">
							<h5 class="mb-1">2. New goods</h5>
							<p class="text-muted">Search by name, SKU or barcode and click a product to add it.</p>

							<input type="text" id="ex-search" class="form-control" autocomplete="off"
								placeholder="Search product or scan barcode, then press Enter">
							<div id="ex-results" class="list-group mt-1" style="max-height:220px;overflow:auto"></div>

							<div class="table-responsive mt-3">
								<table class="table">
									<thead>
										<tr>
											<th>Product</th>
											<th class="text-end">Price</th>
											<th style="width:110px">Qty</th>
											<th class="text-end">Total</th>
											<th></th>
										</tr>
									</thead>
									<tbody id="ex-cart">
										<tr id="ex-cart-empty"><td colspan="5" class="text-center text-muted">No new items yet.</td></tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>

			{{-- 3. Difference and payment --}}
			<div class="card">
				<div class="card-body">
					<h5 class="mb-3">3. Difference</h5>

					<div class="row g-3">
						<div class="col-md-6">
							<table class="table table-borderless mb-0">
								<tr><td>Value of goods coming back</td><td class="text-end" id="ex-returned">{{ $shop->money(0) }}</td></tr>
								<tr><td>New goods (subtotal)</td><td class="text-end" id="ex-subtotal">{{ $shop->money(0) }}</td></tr>
								<tr>
									<td>Discount on new goods</td>
									<td class="text-end" style="width:160px">
										<input type="number" name="discount" id="ex-discount" class="form-control text-end"
											min="0" step="0.01" value="{{ old('discount') }}" placeholder="0">
									</td>
								</tr>
								<tr><td>New goods (total)</td><td class="text-end" id="ex-total">{{ $shop->money(0) }}</td></tr>
								<tr class="fw-bold"><td id="ex-result-label">Even exchange</td><td class="text-end" id="ex-result">{{ $shop->money(0) }}</td></tr>
							</table>
						</div>

						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label" for="payment_method">Payment / refund method</label>
								<select name="payment_method" id="payment_method" class="form-select">
									@foreach($methods as $key => $label)
									<option value="{{ $key }}" @selected(old('payment_method', $sale->payment_method) === $key)>{{ $label }}</option>
									@endforeach
								</select>
							</div>
							<div class="mb-3 d-none" id="ex-paid-group">
								<label class="form-label" for="paid">Amount received from customer</label>
								<input type="number" name="paid" id="paid" class="form-control" min="0" step="0.01"
									value="{{ old('paid') }}" placeholder="Leave empty if the exact amount was paid">
								@if($customer)
								<div class="form-text">If he pays less, the rest is added to {{ $customer->name }}'s account (udhaar).</div>
								@if(in_array(auth()->user()->role, ['admin', 'manager'], true))
								<div class="form-check mt-1">
									<input class="form-check-input" type="checkbox" name="override_credit_limit" id="override_credit_limit" value="1" @checked(old('override_credit_limit'))>
									<label class="form-check-label" for="override_credit_limit">Allow even if it passes his credit limit</label>
								</div>
								@endif
								@endif
							</div>
							@if($customer)
							<div class="mb-3 d-none" id="ex-refundto-group">
								<label class="form-label" for="refund_to">Pay the difference back as</label>
								<select name="refund_to" id="refund_to" class="form-select">
									<option value="cash" @selected(old('refund_to', 'cash') === 'cash')>Cash / selected method</option>
									<option value="ledger" @selected(old('refund_to') === 'ledger')>Credit to customer account (Khata)</option>
								</select>
								<div class="form-text">If this bill was bought on credit, the unpaid part always goes to his account.</div>
							</div>
							@endif
							<div class="mb-3">
								<label class="form-label" for="reason">Reason</label>
								<input type="text" name="reason" id="reason" class="form-control" maxlength="255"
									value="{{ old('reason') }}" placeholder="e.g. Wrong size, customer changed mind">
							</div>
							<div>
								<label class="form-label" for="note">Note (optional)</label>
								<input type="text" name="note" id="note" class="form-control" maxlength="500" value="{{ old('note') }}">
							</div>
						</div>
					</div>

					<div class="mt-3 text-end">
						<button type="submit" class="btn btn-danger">Confirm exchange</button>
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
	var symbol       = @json($shop->currency_symbol);
	var PRODUCTS_URL = @json(route('pos.products'));
	var cart         = @json($oldCart);

	var returnRows = document.querySelectorAll('.ex-return-row');
	var cartBody   = document.getElementById('ex-cart');
	var emptyRow   = document.getElementById('ex-cart-empty');
	var searchBox  = document.getElementById('ex-search');
	var results    = document.getElementById('ex-results');
	var timer      = null;

	function round2(n) { return Math.round(n * 100) / 100; }

	function money(n) {
		return symbol + ' ' + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function el(tag, attrs, text) {
		var node = document.createElement(tag);
		Object.keys(attrs || {}).forEach(function (k) { node.setAttribute(k, attrs[k]); });
		if (text !== undefined) { node.textContent = text; }
		return node;
	}

	/* ---------- cart ---------- */
	function renderCart() {
		cartBody.innerHTML = '';

		if (!cart.length) {
			cartBody.appendChild(emptyRow);
			recalc();
			return;
		}

		cart.forEach(function (item, i) {
			var tr = el('tr');

			var name = el('td', {}, item.name);
			name.appendChild(el('input', { type: 'hidden', name: 'new_items[' + i + '][product_id]', value: item.id }));
			name.appendChild(el('input', { type: 'hidden', name: 'new_items[' + i + '][name]', value: item.name }));
			name.appendChild(el('input', { type: 'hidden', name: 'new_items[' + i + '][price]', value: item.price }));
			tr.appendChild(name);

			tr.appendChild(el('td', { 'class': 'text-end' }, money(item.price)));

			var qtyCell = el('td');
			var qty = el('input', {
				type: 'number', 'class': 'form-control', name: 'new_items[' + i + '][quantity]',
				min: '0.001', step: '0.001', value: item.qty
			});
			qty.addEventListener('input', function () {
				item.qty = parseFloat(qty.value) || 0;
				recalc();
			});
			qtyCell.appendChild(qty);
			tr.appendChild(qtyCell);

			tr.appendChild(el('td', { 'class': 'text-end ex-line-total' }, money(0)));

			var removeCell = el('td', { 'class': 'text-end' });
			var remove = el('button', { type: 'button', 'class': 'btn btn-sm btn-outline-danger' }, 'x');
			remove.addEventListener('click', function () {
				cart.splice(i, 1);
				renderCart();
			});
			removeCell.appendChild(remove);
			tr.appendChild(removeCell);

			cartBody.appendChild(tr);
		});

		recalc();
	}

	function addToCart(p) {
		var found = cart.find(function (c) { return c.id === p.id; });
		if (found) {
			found.qty = round2(found.qty + 1);
		} else {
			cart.push({ id: p.id, name: p.name, price: p.price, qty: 1 });
		}
		renderCart();
		results.innerHTML = '';
		searchBox.value = '';
		searchBox.focus();
	}

	/* ---------- product search (same JSON endpoint as the POS screen) ---------- */
	function search(text, pickSingle) {
		if (text.length < 1) { results.innerHTML = ''; return; }

		fetch(PRODUCTS_URL + '?' + new URLSearchParams({ q: text }), { headers: { 'Accept': 'application/json' } })
			.then(function (res) { return res.json(); })
			.then(function (list) {
				results.innerHTML = '';

				if (pickSingle && list.length === 1) { addToCart(list[0]); return; }

				list.slice(0, 8).forEach(function (p) {
					var label = p.name + '  |  ' + money(p.price) + '  |  stock ' + p.stock;
					var btn = el('button', { type: 'button', 'class': 'list-group-item list-group-item-action' }, label);
					btn.addEventListener('click', function () { addToCart(p); });
					results.appendChild(btn);
				});

				if (!list.length) {
					results.appendChild(el('div', { 'class': 'list-group-item text-muted' }, 'No product found.'));
				}
			})
			.catch(function () {
				results.innerHTML = '';
				results.appendChild(el('div', { 'class': 'list-group-item text-danger' }, 'Search failed. Try again.'));
			});
	}

	searchBox.addEventListener('input', function () {
		clearTimeout(timer);
		timer = setTimeout(function () { search(searchBox.value.trim(), false); }, 250);
	});

	searchBox.addEventListener('keydown', function (e) {
		if (e.key === 'Enter') {
			e.preventDefault(); // Enter must not submit the exchange
			search(searchBox.value.trim(), true);
		}
	});

	/* ---------- totals ---------- */
	function recalc() {
		var returned = 0;
		returnRows.forEach(function (row) {
			var input = row.querySelector('.ex-return-qty');
			var max   = parseFloat(row.dataset.max) || 0;
			var unit  = parseFloat(row.dataset.unit) || 0;
			var qty   = input ? (parseFloat(input.value) || 0) : 0;
			qty = Math.min(Math.max(qty, 0), max);

			var line = round2(qty * unit);
			row.querySelector('.ex-return-line').textContent = money(line);
			returned += line;
		});
		returned = round2(returned);

		var subtotal = 0;
		var lineCells = cartBody.querySelectorAll('.ex-line-total');
		cart.forEach(function (item, i) {
			var line = round2((item.qty || 0) * item.price);
			if (lineCells[i]) { lineCells[i].textContent = money(line); }
			subtotal += line;
		});
		subtotal = round2(subtotal);

		var discount = Math.min(Math.max(parseFloat(document.getElementById('ex-discount').value) || 0, 0), subtotal);
		var total    = round2(subtotal - discount);

		var credit = Math.min(returned, total);
		var due    = round2(total - credit);
		var refund = round2(returned - credit);

		document.getElementById('ex-returned').textContent = money(returned);
		document.getElementById('ex-subtotal').textContent = money(subtotal);
		document.getElementById('ex-total').textContent    = money(total);

		var label  = document.getElementById('ex-result-label');
		var result = document.getElementById('ex-result');

		if (due > 0) {
			label.textContent  = 'Customer pays';
			result.textContent = money(due);
		} else if (refund > 0) {
			label.textContent  = 'Refund to customer';
			result.textContent = money(refund);
		} else {
			label.textContent  = 'Even exchange, nothing to pay';
			result.textContent = money(0);
		}

		// Only ask for the received amount when the customer has to pay something
		document.getElementById('ex-paid-group').classList.toggle('d-none', !(due > 0));

		// A registered customer can have the refund put on his account instead of paid out
		var refundGroup = document.getElementById('ex-refundto-group');
		if (refundGroup) { refundGroup.classList.toggle('d-none', !(refund > 0)); }
	}

	['input', 'change', 'keyup'].forEach(function (evt) {
		document.querySelectorAll('.ex-return-qty').forEach(function (input) { input.addEventListener(evt, recalc); });
		document.getElementById('ex-discount').addEventListener(evt, recalc);
	});

	renderCart();
})();
</script>
@endpush