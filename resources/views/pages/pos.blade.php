@extends('layouts.app')

@section('title', 'POS')

{{-- pos-screen: full-height POS page. mini-sidebar: gives the POS more room (remove it to keep the full sidebar) --}}
@section('body_class', 'pos-screen mini-sidebar')

@push('styles')
<style>
	html,
	body {
		height: 100%;
	}

	body.pos-screen {
		overflow: hidden;
	}

	body.pos-screen .main-wrapper {
		height: 100%;
	}

	body.pos-screen .page-wrapper {
		height: 100%;
		box-sizing: border-box;
	}

	/* Theme colors: primary orange #FF9F43, secondary navy #092C4C (same as the rest of the app) */
	.pos-app,
	.pos-mask {
		--pg: #ff9f43;
		--pgh: #f08a24;
		--pgd: #092c4c;
		--pgl: #fff1e0;
		--pgt: #d97706;
		--po: #b45309;
		--pbg: #f5f6f8;
		--pline: #e6e8ec;
		--pmuted: #6b7280;
		--pred: #dc2626;
		--pink: #1f2937;
	}

	.pos-app {
		height: 100%;
		display: grid;
		grid-template-columns: 190px minmax(0, 1fr) 370px;
		background: var(--pbg);
		color: var(--pink);
		font-size: 14px;
	}

	.pos-app *,
	.pos-mask * {
		box-sizing: border-box;
	}

	.pos-app button,
	.pos-mask button {
		font: inherit;
		cursor: pointer;
	}

	.pos-app svg,
	.pos-mask svg {
		display: block;
	}

	/* ---- Categories ---- */
	.pos-cats {
		background: #fff;
		border-right: 1px solid var(--pline);
		display: flex;
		flex-direction: column;
		min-height: 0;
	}

	.pos-cats-title {
		padding: 14px 14px 8px;
		font-weight: 800;
		font-size: 13px;
		color: var(--pmuted);
		text-transform: uppercase;
		letter-spacing: .04em;
	}

	.pos-cat-list {
		overflow: auto;
		padding: 0 8px 8px;
		flex: 1;
	}

	.pos-cat {
		display: block;
		width: 100%;
		text-align: left;
		border: 0;
		background: transparent;
		padding: 10px 12px;
		border-radius: 10px;
		font-weight: 700;
		margin-bottom: 2px;
		color: var(--pink);
	}

	.pos-cat:hover {
		background: var(--pgl);
	}

	.pos-cat.pos-on {
		background: var(--pg);
		color: #fff;
	}

	/* ---- Catalog ---- */
	.pos-catalog {
		display: flex;
		flex-direction: column;
		min-width: 0;
		min-height: 0;
		padding: 12px 14px;
	}

	.pos-search-row {
		display: flex;
		gap: 10px;
		align-items: center;
	}

	.pos-search {
		flex: 1;
		display: flex;
		align-items: center;
		gap: 10px;
		background: #fff;
		border: 1.5px solid var(--pline);
		border-radius: 14px;
		padding: 0 14px;
		height: 48px;
		color: var(--pmuted);
		margin: 0;
	}

	.pos-search:focus-within {
		border-color: var(--pg);
	}

	.pos-search input {
		flex: 1;
		border: 0;
		outline: 0;
		box-shadow: none;
		background: transparent;
		font-size: 15px;
		height: 100%;
		color: var(--pink);
		padding: 0;
	}

	.pos-round {
		width: 46px;
		height: 46px;
		border-radius: 50%;
		border: 0;
		background: var(--pgl);
		color: var(--pgd);
		display: inline-flex;
		align-items: center;
		justify-content: center;
		text-decoration: none;
	}

	.pos-round:hover {
		color: var(--pgd);
		background: #ffe1c2;
	}

	.pos-meta {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin: 10px 2px 8px;
		color: var(--pmuted);
		font-size: 13px;
	}

	.pos-seg button {
		border: 0;
		background: transparent;
		color: var(--pmuted);
		padding: 6px 8px;
		border-radius: 8px;
	}

	.pos-seg button.pos-on {
		background: var(--pgl);
		color: var(--pgd);
	}

	.pos-err {
		background: #fee2e2;
		color: #991b1b;
		border-radius: 10px;
		padding: 10px 14px;
		margin-top: 10px;
		font-weight: 700;
		display: none;
	}

	.pos-err.pos-on {
		display: block;
	}

	.pos-items {
		flex: 1;
		overflow: auto;
		min-height: 0;
		padding: 2px;
	}

	.pos-items.pos-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
		gap: 12px;
		align-content: start;
	}

	.pos-items.pos-list {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.pos-card {
		position: relative;
		background: #fff;
		border: 1.5px solid var(--pline);
		border-radius: 14px;
		padding: 12px;
		cursor: pointer;
		display: flex;
		flex-direction: column;
		gap: 6px;
		min-height: 118px;
	}

	.pos-card:hover {
		border-color: #ffc88f;
	}

	.pos-card.pos-sel {
		border-color: var(--pg);
		box-shadow: 0 0 0 1px var(--pg);
	}

	.pos-card.pos-out {
		opacity: .55;
		cursor: not-allowed;
		background: #f8fafc;
	}

	.pos-top {
		display: flex;
		gap: 8px;
		align-items: flex-start;
		padding-right: 30px;
	}

	.pos-tile {
		width: 28px;
		height: 28px;
		border-radius: 8px;
		flex: 0 0 28px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-weight: 700;
		font-size: 13px;
		overflow: hidden;
	}

	.pos-tile img {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.pos-name {
		font-weight: 800;
		font-size: 15px;
		line-height: 1.2;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
		overflow: hidden;
		color: var(--pink);
	}

	.pos-pcat {
		color: var(--pmuted);
		font-size: 12px;
	}

	.pos-row {
		display: flex;
		justify-content: space-between;
		align-items: baseline;
		gap: 6px;
		margin-top: auto;
	}

	.pos-price {
		color: var(--pgt);
		font-weight: 800;
		font-size: 15px;
	}

	.pos-price small {
		font-weight: 600;
		color: var(--pmuted);
	}

	.pos-stock {
		font-size: 12px;
		font-weight: 700;
		color: #475569;
	}

	.pos-stock.pos-low {
		color: var(--pred);
	}

	.pos-soldout {
		color: #d6525f;
		font-weight: 800;
		font-size: 12px;
	}

	.pos-margin {
		font-size: 11px;
		font-weight: 800;
		color: #15803d;
	}

	.pos-badge {
		position: absolute;
		top: 8px;
		right: 8px;
		background: var(--pgd);
		color: #fff;
		min-width: 28px;
		height: 28px;
		padding: 0 6px;
		border-radius: 14px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-weight: 800;
	}

	.pos-minus {
		position: absolute;
		right: 8px;
		bottom: 8px;
		width: 34px;
		height: 34px;
		border-radius: 50%;
		border: 0;
		background: var(--pgl);
		color: var(--pgd);
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.pos-items.pos-list .pos-card {
		flex-direction: row;
		align-items: center;
		min-height: 0;
		padding: 10px 14px;
	}

	.pos-items.pos-list .pos-top {
		flex: 1;
		padding-right: 0;
	}

	.pos-items.pos-list .pos-row {
		margin-top: 0;
		gap: 24px;
		min-width: 260px;
		justify-content: flex-end;
	}

	.pos-items.pos-list .pos-badge {
		position: static;
	}

	.pos-items.pos-list .pos-minus {
		position: static;
	}

	.pos-empty {
		color: var(--pmuted);
		text-align: center;
		margin-top: 40px;
	}

	/* ---- Cart ---- */
	.pos-cart {
		background: #fff;
		border-left: 1px solid var(--pline);
		display: flex;
		flex-direction: column;
		min-height: 0;
	}

	.pos-held {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 10px 12px;
		background: #fff8ee;
		border-bottom: 1px solid #ffd9a8;
		overflow-x: auto;
		min-height: 48px;
	}

	.pos-held .pos-lbl {
		color: var(--po);
		font-weight: 800;
		font-size: 13px;
		white-space: nowrap;
	}

	.pos-held .pos-none {
		color: var(--pmuted);
		font-size: 12px;
	}

	.pos-chip {
		border: 1.5px solid var(--pg);
		background: #fff;
		border-radius: 999px;
		padding: 5px 12px;
		font-size: 12px;
		font-weight: 700;
		white-space: nowrap;
		display: inline-flex;
		gap: 8px;
		align-items: center;
		color: var(--pink);
	}

	.pos-chip.pos-active {
		background: var(--pgl);
		border-color: var(--pgh);
	}

	.pos-chip i {
		font-style: normal;
		color: var(--pmuted);
	}

	.pos-lines {
		flex: 1;
		overflow: auto;
		min-height: 0;
		padding: 6px 14px;
	}

	.pos-line {
		display: grid;
		grid-template-columns: 1fr auto auto auto;
		gap: 10px;
		align-items: center;
		padding: 12px 0;
		border-bottom: 1px solid #f1f5f9;
	}

	.pos-ln-name {
		font-weight: 800;
		color: var(--pink);
	}

	.pos-ln-sub {
		color: var(--pmuted);
		font-size: 12px;
	}

	.pos-stepper {
		display: flex;
		align-items: center;
		gap: 6px;
	}

	.pos-stepper button {
		width: 34px;
		height: 34px;
		border-radius: 50%;
		border: 0;
		background: var(--pgl);
		color: var(--pgd);
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.pos-stepper input {
		width: 58px;
		text-align: center;
		border: 0;
		border-bottom: 2px solid var(--pgl);
		border-radius: 0;
		box-shadow: none;
		font-weight: 800;
		font-size: 16px;
		outline: 0;
		padding: 4px 0;
		background: transparent;
		color: var(--pink);
		height: auto;
	}

	.pos-stepper input:focus {
		border-bottom-color: var(--pg);
	}

	.pos-ln-total {
		font-weight: 800;
		min-width: 70px;
		text-align: right;
		color: var(--pink);
	}

	.pos-ln-x {
		border: 0;
		background: transparent;
		color: var(--pmuted);
	}

	.pos-foot {
		border-top: 1px solid var(--pline);
		padding: 12px 14px;
		display: flex;
		flex-direction: column;
		gap: 10px;
	}

	.pos-foot-top {
		display: flex;
		justify-content: space-between;
		align-items: center;
		color: var(--pmuted);
	}

	.pos-link {
		border: 0;
		background: transparent;
		font-weight: 800;
		display: inline-flex;
		gap: 6px;
		align-items: center;
		padding: 4px 6px;
	}

	.pos-link.pos-green {
		color: var(--pgt);
	}

	.pos-link.pos-red {
		color: var(--pred);
	}

	.pos-cust {
		display: none;
	}

	.pos-cust.pos-open {
		display: block;
	}

	.pos-cust input {
		width: 100%;
		border: 1.5px solid var(--pline);
		border-radius: 10px;
		padding: 9px 12px;
		font-size: 14px;
		height: auto;
	}

	.pos-disc-row {
		display: none;
		justify-content: space-between;
		font-size: 13px;
		color: var(--pmuted);
	}

	.pos-total {
		background: var(--pgd);
		color: #fff;
		border-radius: 12px;
		padding: 14px 16px;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.pos-total b {
		font-size: 22px;
	}

	.pos-paygrid {
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		gap: 8px;
		position: relative;
	}

	.pos-pbtn {
		border: 1.5px solid #cbd2db;
		background: #fff;
		border-radius: 12px;
		padding: 12px 0;
		font-weight: 800;
		color: var(--pink);
	}

	.pos-pbtn:hover:not(:disabled) {
		border-color: var(--pg);
		background: var(--pgl);
	}

	.pos-pbtn:disabled {
		opacity: .45;
		cursor: not-allowed;
	}

	.pos-bigrow {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 8px;
	}

	.pos-big {
		border: 0;
		border-radius: 12px;
		padding: 16px 0;
		color: #fff;
		font-weight: 800;
		font-size: 16px;
		display: flex;
		gap: 8px;
		align-items: center;
		justify-content: center;
	}

	.pos-big.pos-cash {
		background: var(--pg);
	}

	.pos-big.pos-cash:hover:not(:disabled) {
		background: var(--pgh);
	}

	.pos-big.pos-print {
		background: var(--pgd);
	}

	.pos-big:disabled {
		opacity: .5;
		cursor: not-allowed;
	}

	.pos-wallet-pop {
		position: absolute;
		bottom: 52px;
		left: 25%;
		background: #fff;
		border: 1px solid var(--pline);
		border-radius: 12px;
		box-shadow: 0 8px 24px rgba(15, 23, 42, .18);
		padding: 6px;
		display: none;
		z-index: 5;
		min-width: 150px;
	}

	.pos-wallet-pop.pos-open {
		display: block;
	}

	.pos-wallet-pop button {
		display: block;
		width: 100%;
		border: 0;
		background: transparent;
		padding: 10px 18px;
		text-align: left;
		font-weight: 700;
		border-radius: 8px;
		color: var(--pink);
	}

	.pos-wallet-pop button:hover {
		background: var(--pgl);
	}

	/* ---- "More" popup ---- */
	.pos-mask {
		position: fixed;
		inset: 0;
		background: rgba(9, 44, 76, .45);
		display: none;
		align-items: center;
		justify-content: center;
		z-index: 2000;
	}

	.pos-mask.pos-open {
		display: flex;
	}

	.pos-modal {
		background: #fff;
		border-radius: 16px;
		width: 420px;
		max-width: 92vw;
		padding: 20px;
		display: flex;
		flex-direction: column;
		gap: 12px;
		color: var(--pink);
	}

	.pos-modal h3 {
		margin: 0;
		font-size: 18px;
	}

	.pos-modal label {
		font-weight: 700;
		font-size: 13px;
		display: block;
		margin-bottom: 4px;
	}

	.pos-modal input,
	.pos-modal select {
		width: 100%;
		border: 1.5px solid var(--pline);
		border-radius: 10px;
		padding: 10px 12px;
		font-size: 15px;
		background: #fff;
		height: auto;
	}

	.pos-modal input:focus,
	.pos-modal select:focus {
		border-color: var(--pg);
		outline: 0;
	}

	.pos-two {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 10px;
	}

	.pos-sum {
		background: #f1f5f9;
		border-radius: 10px;
		padding: 10px 12px;
		display: flex;
		justify-content: space-between;
		font-weight: 800;
	}

	.pos-btns {
		display: flex;
		gap: 10px;
		justify-content: flex-end;
	}

	.pos-btns button {
		padding: 11px 18px;
		border-radius: 10px;
		border: 0;
		font-weight: 800;
	}

	.pos-btns .pos-ok {
		background: var(--pg);
		color: #fff;
	}

	.pos-btns .pos-ok:hover:not(:disabled) {
		background: var(--pgh);
	}

	.pos-btns .pos-no {
		background: #e2e8f0;
		color: var(--pink);
	}

	@media (max-width: 1500px) {
		.pos-app {
			grid-template-columns: 170px minmax(0, 1fr) 330px;
		}
	}

	@media (max-width: 1200px) {
		.pos-app {
			grid-template-columns: 140px minmax(0, 1fr) 300px;
		}
	}
</style>
@endpush

@section('content')
<div class="page-wrapper">
	<div class="pos-app" id="pos">

		{{-- ===== Left: categories ===== --}}
		<aside class="pos-cats">
			<div class="pos-cats-title">Categories</div>
			<div class="pos-cat-list" id="pos-cat-list">
				<button type="button" class="pos-cat pos-on" data-id="">All</button>
				@foreach($categories as $c)
				<button type="button" class="pos-cat" data-id="{{ $c->id }}">{{ $c->name }}</button>
				@endforeach
			</div>
		</aside>

		{{-- ===== Middle: products ===== --}}
		<main class="pos-catalog">
			<div class="pos-search-row">
				<label class="pos-search">
					<i data-icon="search"></i>
					<input type="text" id="pos-search" autocomplete="off" autofocus
						placeholder="Barcode or product name (F2)">
				</label>
				<a class="pos-round" href="{{ route('sales-list') }}" title="Sales list"><i data-icon="file"></i></a>
				<button type="button" class="pos-round" id="pos-refresh" title="Refresh products"><i
						data-icon="refresh"></i></button>
			</div>

			<div class="pos-err" id="pos-err"></div>

			<div class="pos-meta">
				<span id="pos-count">0 items</span>
				<span class="pos-seg">
					<button type="button" id="pos-v-grid" class="pos-on" title="Grid"><i data-icon="grid"></i></button>
					<button type="button" id="pos-v-list" title="List"><i data-icon="list"></i></button>
				</span>
			</div>

			<div class="pos-items pos-grid" id="pos-items"></div>
		</main>

		{{-- ===== Right: cart ===== --}}
		<section class="pos-cart">
			<div class="pos-held" id="pos-held"></div>
			<div class="pos-lines" id="pos-lines"></div>

			<div class="pos-foot">
				<div class="pos-foot-top">
					<span id="pos-summary">0 items · 0 qty</span>
					<span>
						<button type="button" class="pos-link pos-green" id="pos-hold"><i data-icon="pause"></i>
							Hold</button>
						<button type="button" class="pos-link pos-red" id="pos-cancel"><i data-icon="x"></i>
							Cancel</button>
					</span>
				</div>

				<button type="button" class="pos-link pos-green" id="pos-cust-toggle" style="align-self:flex-start"><i
						data-icon="user"></i> Customer name</button>
				<div class="pos-cust" id="pos-cust"><input type="text" id="pos-customer" maxlength="255"
						placeholder="Walk-in customer"></div>

				<div class="pos-disc-row" id="pos-disc-row"><span>Discount</span><span id="pos-disc-val"></span></div>
				<div class="pos-total"><span>Total</span><b id="pos-total"></b></div>

				<div class="pos-paygrid">
					<button type="button" class="pos-pbtn" id="pos-pay-card">Card</button>
					<button type="button" class="pos-pbtn" id="pos-pay-wallet">Wallet</button>
					<button type="button" class="pos-pbtn" id="pos-pay-more">More</button>
					<button type="button" class="pos-pbtn" disabled
						title="Needs the customers module (coming later)">Credit</button>
					<div class="pos-wallet-pop" id="pos-wallet-pop">
						<button type="button" data-m="easypaisa">Easypaisa</button>
						<button type="button" data-m="jazzcash">JazzCash</button>
					</div>
				</div>

				<div class="pos-bigrow">
					<button type="button" class="pos-big pos-cash" id="pos-pay-cash"><i data-icon="cash"></i>
						Cash</button>
					<button type="button" class="pos-big pos-print" id="pos-pay-print"><i data-icon="zap"></i> Instant
						print</button>
				</div>
			</div>
		</section>
	</div>
</div>

{{-- ===== "More" popup ===== --}}
<div class="pos-mask" id="pos-mask">
	<div class="pos-modal">
		<h3>Payment details</h3>
		<div class="pos-two">
			<div><label for="pos-m-method">Payment method</label><select id="pos-m-method"></select></div>
			<div><label for="pos-m-discount">Discount</label><input type="number" id="pos-m-discount" min="0"
					step="any"></div>
		</div>
		<div class="pos-two">
			<div><label for="pos-m-received">Amount received</label><input type="number" id="pos-m-received" min="0"
					step="any" placeholder="Exact"></div>
			<div><label for="pos-m-customer">Customer name</label><input type="text" id="pos-m-customer"
					maxlength="255"></div>
		</div>
		<div class="pos-sum"><span>Total</span><span id="pos-m-total"></span></div>
		<div class="pos-sum"><span>Change</span><span id="pos-m-change"></span></div>
		<div class="pos-btns">
			<button type="button" class="pos-no" id="pos-m-cancel">Close</button>
			<button type="button" class="pos-ok" id="pos-m-ok">Complete sale</button>
		</div>
	</div>
</div>
@endsection

@push('scripts_after')
<script>
	(function () {
		const CSRF = document.querySelector('meta[name=csrf-token]').content;
		const CURRENCY = @json($shop->currency_symbol);
		const METHODS = @json($methods);
		const PRODUCTS_URL = @json(route('pos.products'));
		const CHECKOUT_URL = @json(route('pos.checkout'));
		const HELD_URL = @json(route('pos.held'));
		const HELD_BASE = @json(url('/pos/held'));

		const ICONS = {
			search: '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
			plus: '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
			minus: '<line x1="5" y1="12" x2="19" y2="12"/>',
			x: '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
			pause: '<circle cx="12" cy="12" r="10"/><line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/>',
			refresh: '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
			file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
			zap: '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
			grid: '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
			list: '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
			user: '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
			cash: '<rect x="1" y="4" width="22" height="16" rx="2"/><circle cx="12" cy="12" r="3"/>',
		};
		const svg = (name, size) => '<svg viewBox="0 0 24 24" width="' + (size || 18) + '" height="' + (size || 18) +
			'" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + ICONS[name] + '</svg>';

		const TILE_COLORS = [['#fde8e8', '#b42318'], ['#e0f2e9', '#15803d'], ['#e0ecff', '#1d4ed8'], ['#fff1d6', '#b45309'], ['#efe6ff', '#6d28d9'], ['#e0f7fa', '#0e7490']];

		// $('search') -> element with id "pos-search" (all ids on this page start with "pos-")
		const $ = (id) => document.getElementById('pos-' + id);
		const h = (tag, cls, text) => {
			const e = document.createElement(tag);
			if (cls) e.className = cls;
			if (text != null) e.textContent = text;
			return e;
		};
		const round2 = (n) => Math.round(n * 100) / 100;
		const qtyText = (n) => String(Math.round(n * 1000) / 1000);
		const money = (n) => CURRENCY + ' ' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

		// ---- state ----
		let products = [];
		let category = '';
		let query = '';
		let view = 'grid';
		let cart = [];        // { id, name, price, stock, qty }
		let customer = '';
		let discount = 0;
		let held = [];          // held bills from the server
		let activeHeld = null;  // uuid of the held bill that is open in the cart
		let loadToken = 0;

		document.querySelectorAll('#pos [data-icon]').forEach((el) => { el.innerHTML = svg(el.dataset.icon); });

		// ---- helpers ----
		function showErr(msg) {
			const box = $('err');
			box.textContent = msg || '';
			box.classList.toggle('pos-on', !!msg);
		}

		// Small JSON helper: throws an Error with a readable message when the server says no
		async function api(method, url, body) {
			const res = await fetch(url, {
				method: method,
				headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
				body: body ? JSON.stringify(body) : undefined,
			});
			let data = null;
			try { data = await res.json(); } catch (e) { }
			if (!res.ok) {
				const msg = data && data.errors ? Object.values(data.errors)[0][0] : (data && data.message);
				throw new Error(msg || 'Request failed.');
			}
			return data;
		}

		function totals() {
			const subtotal = round2(cart.reduce((s, l) => s + round2(l.price * l.qty), 0));
			const disc = Math.min(Math.max(discount || 0, 0), subtotal);
			return { subtotal: subtotal, discount: disc, total: round2(subtotal - disc) };
		}

		// ---- products ----
		async function load() {
			const token = ++loadToken;
			const params = {};
			if (query) params.q = query;
			if (category) params.category = category;
			try {
				const res = await fetch(PRODUCTS_URL + '?' + new URLSearchParams(params), { headers: { 'Accept': 'application/json' } });
				const data = await res.json();
				if (token !== loadToken) return;
				products = data;
				renderProducts();
			} catch (e) {
				showErr('Could not load products.');
			}
		}

		function renderProducts() {
			const box = $('items');
			box.className = 'pos-items pos-' + view;
			box.innerHTML = '';
			$('count').textContent = products.length + ' items';

			if (!products.length) {
				box.appendChild(h('div', 'pos-empty', 'No products found.'));
				return;
			}

			products.forEach((p) => {
				const line = cart.find((l) => l.id === p.id);
				const out = p.stock <= 0;
				const card = h('div', 'pos-card' + (out ? ' pos-out' : '') + (line ? ' pos-sel' : ''));

				const top = h('div', 'pos-top');
				const color = TILE_COLORS[(p.category || p.name).length % TILE_COLORS.length];
				const tile = h('div', 'pos-tile');
				tile.style.background = color[0];
				tile.style.color = color[1];
				if (p.image) {
					const img = document.createElement('img');
					img.src = p.image;
					img.alt = '';
					tile.appendChild(img);
				} else {
					tile.textContent = p.name.charAt(0).toUpperCase();
				}
				const names = h('div');
				names.appendChild(h('div', 'pos-name', p.name));
				if (p.category) names.appendChild(h('div', 'pos-pcat', p.category));
				top.append(tile, names);
				card.appendChild(top);

				const row = h('div', 'pos-row');
				const price = h('span', 'pos-price', money(p.price));
				if (p.unit) price.appendChild(h('small', null, ' / ' + p.unit));
				row.appendChild(price);

				if (out) {
					row.appendChild(h('span', 'pos-soldout', 'Sold out'));
				} else {
					row.appendChild(h('span', 'pos-stock' + (p.stock <= p.alert ? ' pos-low' : ''), qtyText(p.stock) + ' left'));
				}
				card.appendChild(row);

				if (p.profit !== undefined && !out) {
					card.appendChild(h('div', 'pos-margin', '+' + qtyText(p.profit) + ' · ' + p.margin_pct + '%'));
				}

				if (line) {
					card.appendChild(h('div', 'pos-badge', qtyText(line.qty)));
					const minus = h('button', 'pos-minus');
					minus.type = 'button';
					minus.innerHTML = svg('minus');
					minus.addEventListener('click', (e) => { e.stopPropagation(); changeQty(p.id, -1); });
					card.appendChild(minus);
				}

				if (!out) card.addEventListener('click', () => addToCart(p));
				box.appendChild(card);
			});
		}

		// ---- cart ----
		function addToCart(p) {
			showErr('');
			const line = cart.find((l) => l.id === p.id);
			const next = (line ? line.qty : 0) + 1;
			if (next > p.stock) {
				showErr('Only ' + qtyText(p.stock) + ' of "' + p.name + '" in stock.');
				return;
			}
			if (line) {
				line.qty = next;
				line.stock = p.stock;
			} else {
				cart.push({ id: p.id, name: p.name, price: p.price, stock: p.stock, qty: 1 });
			}
			renderAll();
		}

		function changeQty(id, delta) {
			const i = cart.findIndex((l) => l.id === id);
			if (i < 0) return;
			cart[i].qty = round2(cart[i].qty + delta);
			if (cart[i].qty <= 0) cart.splice(i, 1);
			renderAll();
		}

		function renderCart() {
			const box = $('lines');
			box.innerHTML = '';

			if (!cart.length) {
				box.appendChild(h('div', 'pos-empty', 'Cart is empty. Scan or tap a product.'));
			}

			cart.forEach((l, i) => {
				const row = h('div', 'pos-line');

				const info = h('div');
				info.appendChild(h('div', 'pos-ln-name', l.name));
				info.appendChild(h('div', 'pos-ln-sub', '× ' + money(l.price)));

				const stepper = h('div', 'pos-stepper');
				const dec = h('button');
				dec.type = 'button';
				dec.innerHTML = svg('minus');
				dec.addEventListener('click', () => changeQty(l.id, -1));
				const input = document.createElement('input');
				input.type = 'number';
				input.min = '0.001';
				input.step = 'any';
				input.value = l.qty;
				input.addEventListener('change', () => {
					let v = parseFloat(input.value);
					if (!(v > 0)) v = 1;
					if (v > l.stock) {
						showErr('Only ' + qtyText(l.stock) + ' of "' + l.name + '" in stock.');
						v = l.stock;
					}
					l.qty = v;
					renderAll();
				});
				const inc = h('button');
				inc.type = 'button';
				inc.innerHTML = svg('plus');
				inc.addEventListener('click', () => {
					if (l.qty + 1 > l.stock) { showErr('Only ' + qtyText(l.stock) + ' of "' + l.name + '" in stock.'); return; }
					changeQty(l.id, 1);
				});
				stepper.append(dec, input, inc);

				const total = h('div', 'pos-ln-total', money(round2(l.price * l.qty)));

				const del = h('button', 'pos-ln-x');
				del.type = 'button';
				del.innerHTML = svg('x');
				del.addEventListener('click', () => { cart.splice(i, 1); renderAll(); });

				row.append(info, stepper, total, del);
				box.appendChild(row);
			});

			const t = totals();
			const qty = cart.reduce((s, l) => s + l.qty, 0);
			$('summary').textContent = cart.length + ' items · ' + qtyText(qty) + ' qty';
			$('total').textContent = money(t.total);
			$('disc-row').style.display = t.discount > 0 ? 'flex' : 'none';
			$('disc-val').textContent = '- ' + money(t.discount);
		}

		function renderHeld() {
			const box = $('held');
			box.innerHTML = '';
			box.appendChild(h('span', 'pos-lbl', 'Held bills' + (held.length ? ' (' + held.length + ')' : '')));
			if (!held.length) {
				box.appendChild(h('span', 'pos-none', 'none'));
				return;
			}
			held.forEach((b, i) => {
				const chip = h('button', 'pos-chip' + (b.uuid === activeHeld ? ' pos-active' : ''));
				chip.type = 'button';
				const time = new Date(b.held_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
				const name = b.customer_name ? b.customer_name : 'Bill ' + (i + 1);
				const who = b.mine ? '' : ' · ' + (b.by || '');
				chip.appendChild(document.createTextNode(name + ' · ' + money(b.total) + ' · ' + time + who));
				const x = h('i', null, '×');
				x.addEventListener('click', async (e) => {
					e.stopPropagation();
					if (!confirm('Delete this held bill?')) return;
					try {
						await api('DELETE', HELD_BASE + '/' + b.uuid);
						if (activeHeld === b.uuid) activeHeld = null;
					} catch (err) { showErr(err.message); }
					loadHeld();
				});
				chip.appendChild(x);
				chip.addEventListener('click', () => resumeBill(b.uuid));
				box.appendChild(chip);
			});
		}

		async function loadHeld() {
			try { held = await api('GET', HELD_URL); } catch (e) { held = []; }
			renderHeld();
		}

		function renderAll() {
			renderProducts();
			renderCart();
		}

		// Put the cart on hold. Returns true when it was saved (the cart is cleared only then).
		async function holdBill() {
			if (!cart.length) { showErr('The cart is empty.'); return false; }
			try {
				await api('POST', HELD_URL, {
					items: cart.map((l) => ({ product_id: l.id, quantity: l.qty })),
					discount: totals().discount,
					customer_name: customer || null,
					replace_uuid: activeHeld,
				});
			} catch (e) {
				showErr(e.message);
				return false;
			}
			resetSale();
			await loadHeld();
			return true;
		}

		// Open a held bill. The server re-checks price and stock for every line.
		async function resumeBill(uuid) {
			if (uuid === activeHeld) return;
			showErr('');
			if (cart.length) {
				if (!confirm('Put the current cart on hold and open this bill?')) return;
				if (!(await holdBill())) return;
			}
			try {
				const bill = await api('GET', HELD_BASE + '/' + uuid);
				cart = bill.items.map((l) => ({ id: l.id, name: l.name, price: l.price, stock: l.stock, qty: l.qty }));
				customer = bill.customer_name || '';
				discount = bill.discount || 0;
				activeHeld = bill.uuid;
				$('customer').value = customer;
				renderHeld();
				renderAll();
				if (bill.warnings.length) showErr('Please check: ' + bill.warnings.join(' '));
			} catch (e) {
				showErr(e.message);
				loadHeld();
			}
		}

		function resetSale() {
			cart = [];
			activeHeld = null;
			customer = '';
			discount = 0;
			$('customer').value = '';
			showErr('');
			renderAll();
		}

		// ---- payment ----
		function setBusy(on) {
			['pay-card', 'pay-wallet', 'pay-more', 'pay-cash', 'pay-print', 'm-ok'].forEach((id) => { $(id).disabled = on; });
		}

		async function pay(method, opts) {
			opts = opts || {};
			showErr('');
			const t = totals();
			if (!cart.length) { closeMore(); showErr('The cart is empty.'); return; }
			const received = opts.received != null ? opts.received : t.total;
			if (received < t.total) { showErr('Amount received is less than the total.'); return; }

			setBusy(true);
			try {
				const res = await fetch(CHECKOUT_URL, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
					body: JSON.stringify({
						items: cart.map((l) => ({ product_id: l.id, quantity: l.qty })),
						discount: t.discount,
						paid: received,
						payment_method: method,
						customer_name: customer || null,
						held_uuid: activeHeld,
					}),
				});
				const data = await res.json();
				if (!res.ok) {
					const first = data.errors ? Object.values(data.errors)[0][0] : data.message;
					closeMore();
					showErr(first || 'Could not complete the sale.');
					setBusy(false);
					load(); // stock may have changed
					return;
				}
				window.location.href = data.receipt_url + (opts.print ? '?print=1' : '');
			} catch (e) {
				showErr('Something went wrong. Please try again.');
				setBusy(false);
			}
		}

		// ---- "More" popup ----
		function openMore() {
			if (!cart.length) { showErr('The cart is empty.'); return; }
			const sel = $('m-method');
			sel.innerHTML = '';
			Object.keys(METHODS).forEach((k) => {
				const o = document.createElement('option');
				o.value = k;
				o.textContent = METHODS[k];
				sel.appendChild(o);
			});
			$('m-discount').value = discount || '';
			$('m-received').value = '';
			$('m-customer').value = customer;
			updateMore();
			$('mask').classList.add('pos-open');
			$('m-received').focus();
		}
		function closeMore() { $('mask').classList.remove('pos-open'); }
		function updateMore() {
			const sub = totals().subtotal;
			const disc = Math.min(Math.max(parseFloat($('m-discount').value) || 0, 0), sub);
			const total = round2(sub - disc);
			const recv = parseFloat($('m-received').value);
			$('m-total').textContent = money(total);
			$('m-change').textContent = money(isNaN(recv) ? 0 : Math.max(round2(recv - total), 0));
		}

		// ---- events ----
		$('cat-list').addEventListener('click', (e) => {
			const btn = e.target.closest('.pos-cat');
			if (!btn) return;
			document.querySelectorAll('#pos .pos-cat').forEach((c) => c.classList.remove('pos-on'));
			btn.classList.add('pos-on');
			category = btn.dataset.id;
			load();
		});

		let timer;
		$('search').addEventListener('input', (e) => {
			clearTimeout(timer);
			timer = setTimeout(() => { query = e.target.value.trim(); load(); }, 250);
		});

		// Barcode scanners type the code and press Enter
		$('search').addEventListener('keydown', async (e) => {
			if (e.key !== 'Enter') return;
			e.preventDefault();
			clearTimeout(timer);
			const text = e.target.value.trim();
			if (!text) return;
			try {
				const res = await fetch(PRODUCTS_URL + '?' + new URLSearchParams({ barcode: text }), { headers: { 'Accept': 'application/json' } });
				const exact = await res.json();
				if (exact.length === 1) {
					addToCart(exact[0]);
					e.target.value = '';
					query = '';
					load();
					return;
				}
			} catch (err) { }
			query = text;
			load();
		});

		$('refresh').addEventListener('click', load);
		$('v-grid').addEventListener('click', () => { view = 'grid'; $('v-grid').classList.add('pos-on'); $('v-list').classList.remove('pos-on'); renderProducts(); });
		$('v-list').addEventListener('click', () => { view = 'list'; $('v-list').classList.add('pos-on'); $('v-grid').classList.remove('pos-on'); renderProducts(); });

		$('hold').addEventListener('click', holdBill);
		$('cancel').addEventListener('click', () => {
			if (cart.length && !confirm('Clear the whole cart?')) return;
			resetSale();
		});
		$('cust-toggle').addEventListener('click', () => { $('cust').classList.toggle('pos-open'); $('customer').focus(); });
		$('customer').addEventListener('input', (e) => { customer = e.target.value.trim(); });

		$('pay-cash').addEventListener('click', () => pay('cash'));
		$('pay-card').addEventListener('click', () => pay('card'));
		$('pay-print').addEventListener('click', () => pay('cash', { print: true }));
		$('pay-wallet').addEventListener('click', (e) => { e.stopPropagation(); $('wallet-pop').classList.toggle('pos-open'); });
		$('wallet-pop').addEventListener('click', (e) => {
			const b = e.target.closest('button[data-m]');
			if (b) { $('wallet-pop').classList.remove('pos-open'); pay(b.dataset.m); }
		});
		document.addEventListener('click', () => $('wallet-pop').classList.remove('pos-open'));

		$('pay-more').addEventListener('click', openMore);
		$('m-cancel').addEventListener('click', closeMore);
		$('m-discount').addEventListener('input', updateMore);
		$('m-received').addEventListener('input', updateMore);
		$('m-ok').addEventListener('click', () => {
			discount = Math.max(parseFloat($('m-discount').value) || 0, 0);
			customer = $('m-customer').value.trim();
			$('customer').value = customer;
			renderCart();
			const recv = parseFloat($('m-received').value);
			pay($('m-method').value, { received: isNaN(recv) ? null : recv });
		});

		document.addEventListener('keydown', (e) => {
			if (e.key === 'F2') { e.preventDefault(); $('search').focus(); $('search').select(); }
			else if (e.key === 'F9') { e.preventDefault(); pay('cash'); }
			else if (e.key === 'Escape') { closeMore(); }
		});

		loadHeld();
		renderCart();
		load();
		window.addEventListener('focus', loadHeld);
	})();
</script>
@endpush