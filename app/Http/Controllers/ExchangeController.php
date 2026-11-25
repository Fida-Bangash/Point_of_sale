<?php

namespace App\Http\Controllers;

use App\Models\Exchange;
use App\Models\Sale;
use App\Models\ShopSetting;
use App\Services\ExchangeService;
use App\Services\ReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExchangeController extends Controller
{
    /** List of all exchanges. */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $exchanges = Exchange::query()
            ->with(['originalSale:id,invoice_no', 'newSale:id,invoice_no', 'user:id,name'])
            ->when($q !== '', fn ($w) => $w->where(function ($s) use ($q) {
                $s->where('exchange_no', 'like', "%{$q}%")
                    ->orWhereHas('originalSale', fn ($x) => $x->where('invoice_no', 'like', "%{$q}%"))
                    ->orWhereHas('newSale', fn ($x) => $x->where('invoice_no', 'like', "%{$q}%"));
            }))
            ->latest('exchanged_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('pages.exchanges', [
            'shop'      => ShopSetting::current(),
            'exchanges' => $exchanges,
            'q'         => $q,
        ]);
    }

    /** The exchange screen for one sale: goods coming back on the left, new goods on the right. */
    public function create(Sale $sale, ReturnService $returns)
    {
        if ($sale->status === Sale::STATUS_RETURNED) {
            return redirect()->route('sales-list')
                ->withErrors(['sale' => "Sale {$sale->invoice_no} has already been fully returned."]);
        }

        return view('pages.exchange-create', [
            'shop'    => ShopSetting::current(),
            'sale'    => $sale,
            'states'  => $returns->lineStates($sale),
            'service' => $returns,
            'methods' => Sale::PAYMENT_METHODS,
            'customer' => $sale->customer_id ? $sale->customer()->first() : null,
        ]);
    }

    public function store(Request $request, Sale $sale, ExchangeService $exchanges): RedirectResponse
    {
        $data = $request->validate([
            'return_items'                => ['required', 'array', 'min:1', 'max:200'],
            'return_items.*.sale_item_id' => ['required', 'integer'],
            'return_items.*.quantity'     => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'return_items.*.restock'      => ['nullable', 'boolean'],
            'new_items'                   => ['required', 'array', 'min:1', 'max:100'],
            'new_items.*.product_id'      => ['required', 'integer', 'exists:products,id'],
            'new_items.*.quantity'        => ['required', 'numeric', 'gt:0', 'max:99999'],
            'discount'                    => ['nullable', 'numeric', 'min:0'],
            'paid'                        => ['nullable', 'numeric', 'min:0'],
            'payment_method'              => ['required', Rule::in(array_keys(Sale::PAYMENT_METHODS))],
            'refund_to'                   => ['nullable', Rule::in(['cash', ReturnService::REFUND_LEDGER])],
            'override_credit_limit'       => ['nullable', 'boolean'],
            'reason'                      => ['nullable', 'string', 'max:255'],
            'note'                        => ['nullable', 'string', 'max:500'],
        ]);

        // Only admin/manager may push a customer past the credit limit (same rule as the POS checkout)
        $data['allow_over_limit'] = $request->boolean('override_credit_limit')
            && in_array($request->user()->role, ['admin', 'manager'], true);

        $exchange = $exchanges->create($sale, $data, $request->user()->id);

        return redirect()
            ->route('exchanges.receipt', $exchange)
            ->with('status', "Exchange {$exchange->exchange_no} saved. Stock and payment updated.");
    }

    /** Printable exchange slip: ?size=a4 for A4, default is 80mm thermal */
    public function receipt(Exchange $exchange, Request $request)
    {
        $exchange->load(['originalSale', 'newSale.items', 'saleReturn.items', 'user:id,name']);

        return view('pages.exchange-receipt', [
            'exchange' => $exchange,
            'shop'     => ShopSetting::current(),
            'size'     => $request->query('size') === 'a4' ? 'a4' : '80mm',
        ]);
    }
}