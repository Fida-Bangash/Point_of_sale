@extends('layouts.app')

@section('title', 'POS')

@push('styles')
<style>
	.pos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
	.pos-card { border: 1px solid #edf0f4; border-radius: 10px; padding: 10px; background: #fff; cursor: pointer; text-align: left; }
	.pos-card:hover { border-color: #ff9f43; }
	.pos-card.out { opacity: .5; }
	.pos-card .name { font-weight: 600; font-size: 13px; margin-bottom: 4px; }
	.pos-card .meta { font-size: 12px; color: #6b7280; }
	.pos-cart { max-height: 320px; overflow: auto; }
	.pos-cart input.qty { width: 80px; }
	.pos-total { font-size: 24px; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="page-wrapper">
	<div class="content">
		<div class="page-header">
			<div class="page-title">
				<h4>Point of Sale</h4>
				<h6>Scan a barcode or search a product (F2 = focus search)</h6>
			</div>
		</div>

		<div id="pos-error" class="alert alert-danger d-none" role="alert"></div>

		<div class="row">
			<div class="col-lg-7">
				<div class="card">
					<div class="card-body">
						<input type="text" id="pos-search" class="form-control mb-3" autocomplete="off" autofocus
							placeholder="Scan barcode or type a product name, then press Enter">
						<div id="pos-results" class="pos-grid"></div>
					</div>
				</div>
			</div>

			<div class="col-lg-5">
				<div class="card">
					<div class="card-body">
						<h5 class="mb-3">Cart</h5>
						<div class="pos-cart table-responsive">
							<table class="table table-sm align-middle">
								<thead>
									<tr><th>Item</th><th>Qty</th><th class="text-end">Total</th><th></th></tr>
								</thead>
								<tbody id="pos-cart-body"></tbody>
							</table>
						</div>

						<hr>
						<div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong id="pos-subtotal"></strong></div>
						<div class="d-flex justify-content-between align-items-center mb-2">
							<span>Discount</span>
							<input type="number" id="pos-discount" class="form-control form-control-sm text-end" style="width:120px" min="0" step="any" value="0">
						</div>
						<div class="d-flex justify-content-between align-items-center mb-3">
							<span>Total</span><span class="pos-total" id="pos-total"></span>
						</div>

						<div class="mb-2">
							<label class="form-label">Payment method</label>
							<select id="pos-method" class="form-select">
								@foreach($methods as $value => $label)
								<option value="{{ $value }}">{{ $label }}</option>
								@endforeach
							</select>
						</div>
						<div class="mb-2">
							<label class="form-label">Amount received</label>
							<div class="input-group">
								<input type="number" id="pos-paid" class="form-control" min="0" step="any">
								<button type="button" class="btn btn-outline-secondary" id="pos-exact">Exact</button>
							</div>
						</div>
						<div class="d-flex justify-content-between mb-2"><span>Change</span><strong id="pos-change"></strong></div>
						<div class="mb-3">
							<label class="form-label">Customer name (optional)</label>
							<input type="text" id="pos-customer" class="form-control" maxlength="255">
						</div>

						<button type="button" id="pos-pay" class="btn btn-submit w-100">Complete Sale</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts_after')
<script>
(function () {
	const CSRF = document.querySelector('meta[name=csrf-token]').content;
	const CURRENCY = @json($shop->currency_symbol);
	const PRODUCTS_URL = @json(route('pos.products'));
	const CHECKOUT_URL = @json(route('pos.checkout'));

	let cart = []; // { id, name, price, stock, qty }

	const $ = (id) => document.getElementById(id);
	const round2 = (n) => Math.round(n * 100) / 100;
	const qtyText = (n) => String(Math.round(n * 1000) / 1000);
	const money = (n) => CURRENCY + ' ' + Number(n).toLocaleString('en-US', {
		minimumFractionDigits: 2, maximumFractionDigits: 2,
	});

	function showError(msg) {
		const box = $('pos-error');
		box.textContent = msg || '';
		box.classList.toggle('d-none', !msg);
	}

	function fetchProducts(params) {
		return fetch(PRODUCTS_URL + '?' + new URLSearchParams(params), {
			headers: { 'Accept': 'application/json' },
		}).then((r) => r.json());
	}

	function renderResults(list) {
		const box = $('pos-results');
		box.innerHTML = '';
		if (!list.length) {
			box.textContent = 'No products found.';
			return;
		}
		list.forEach((p) => {
			const card = document.createElement('button');
			card.type = 'button';
			card.className = 'pos-card' + (p.stock <= 0 ? ' out' : '');

			const name = document.createElement('div');
			name.className = 'name';
			name.textContent = p.name;

			const meta = document.createElement('div');
			meta.className = 'meta';
			meta.textContent = money(p.price) + ' | Stock: ' + qtyText(p.stock) + (p.unit ? ' ' + p.unit : '');

			card.append(name, meta);
			card.addEventListener('click', () => addToCart(p));
			box.appendChild(card);
		});
	}

	function search(q) {
		return fetchProducts(q ? { q: q } : {}).then(renderResults);
	}

	function addToCart(p) {
		showError('');
		const line = cart.find((l) => l.id === p.id);
		const next = (line ? line.qty : 0) + 1;

		if (next > p.stock) {
			showError('Only ' + qtyText(p.stock) + ' of "' + p.name + '" in stock.');
			return;
		}
		if (line) {
			line.qty = next;
			line.stock = p.stock;
		} else {
			cart.push({ id: p.id, name: p.name, price: p.price, stock: p.stock, qty: 1 });
		}
		renderCart();
	}

	function totals() {
		const subtotal = round2(cart.reduce((s, l) => s + round2(l.price * l.qty), 0));
		const discount = Math.min(Math.max(parseFloat($('pos-discount').value) || 0, 0), subtotal);
		const total = round2(subtotal - discount);
		const paid = parseFloat($('pos-paid').value) || 0;
		return { subtotal, discount, total, paid, change: round2(Math.max(paid - total, 0)) };
	}

	function renderTotals() {
		const t = totals();
		$('pos-subtotal').textContent = money(t.subtotal);
		$('pos-total').textContent = money(t.total);
		$('pos-change').textContent = money(t.change);
	}

	function renderCart() {
		const body = $('pos-cart-body');
		body.innerHTML = '';

		cart.forEach((l, i) => {
			const tr = document.createElement('tr');

			const tdName = document.createElement('td');
			tdName.textContent = l.name;
			const price = document.createElement('div');
			price.className = 'text-muted small';
			price.textContent = money(l.price);
			tdName.appendChild(price);

			const tdQty = document.createElement('td');
			const input = document.createElement('input');
			input.type = 'number';
			input.className = 'form-control form-control-sm qty';
			input.min = '0.001';
			input.step = 'any';
			input.value = l.qty;
			input.addEventListener('change', () => {
				let v = parseFloat(input.value);
				if (!(v > 0)) v = 1;
				if (v > l.stock) {
					showError('Only ' + qtyText(l.stock) + ' of "' + l.name + '" in stock.');
					v = l.stock;
				}
				l.qty = v;
				renderCart();
			});
			tdQty.appendChild(input);

			const tdTotal = document.createElement('td');
			tdTotal.className = 'text-end';
			tdTotal.textContent = money(round2(l.price * l.qty));

			const tdDel = document.createElement('td');
			const del = document.createElement('button');
			del.type = 'button';
			del.className = 'btn btn-sm btn-outline-danger';
			del.textContent = 'x';
			del.addEventListener('click', () => { cart.splice(i, 1); renderCart(); });
			tdDel.appendChild(del);

			tr.append(tdName, tdQty, tdTotal, tdDel);
			body.appendChild(tr);
		});

		renderTotals();
	}

	async function checkout() {
		showError('');
		const t = totals();
		if (!cart.length) return showError('The cart is empty.');
		if (t.paid < t.total) return showError('Amount received is less than the total.');

		const btn = $('pos-pay');
		btn.disabled = true;

		try {
			const res = await fetch(CHECKOUT_URL, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					'X-CSRF-TOKEN': CSRF,
				},
				body: JSON.stringify({
					items: cart.map((l) => ({ product_id: l.id, quantity: l.qty })),
					discount: t.discount,
					paid: t.paid,
					payment_method: $('pos-method').value,
					customer_name: $('pos-customer').value,
				}),
			});
			const data = await res.json();

			if (!res.ok) {
				const first = data.errors ? Object.values(data.errors)[0][0] : data.message;
				showError(first || 'Could not complete the sale.');
				btn.disabled = false;
				return;
			}
			window.location.href = data.receipt_url;
		} catch (e) {
			showError('Something went wrong. Please try again.');
			btn.disabled = false;
		}
	}

	// ---- events ----
	let timer;
	$('pos-search').addEventListener('input', (e) => {
		clearTimeout(timer);
		timer = setTimeout(() => search(e.target.value.trim()), 250);
	});

	// Barcode scanners type the code and press Enter
	$('pos-search').addEventListener('keydown', async (e) => {
		if (e.key !== 'Enter') return;
		e.preventDefault();
		clearTimeout(timer);

		const text = e.target.value.trim();
		if (!text) return;

		const exact = await fetchProducts({ barcode: text });
		if (exact.length === 1) {
			addToCart(exact[0]);
			e.target.value = '';
			search('');
		} else {
			search(text);
		}
	});

	$('pos-discount').addEventListener('input', renderTotals);
	$('pos-paid').addEventListener('input', renderTotals);
	$('pos-exact').addEventListener('click', () => {
		$('pos-paid').value = totals().total;
		renderTotals();
	});
	$('pos-pay').addEventListener('click', checkout);

	document.addEventListener('keydown', (e) => {
		if (e.key === 'F2') { e.preventDefault(); $('pos-search').focus(); }
	});

	search('');
	renderCart();
})();
</script>
@endpush