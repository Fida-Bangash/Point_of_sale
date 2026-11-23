<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\ShopSetting;
use App\Services\ReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaleReturnController extends Controller
{
    /** List of all returns (the "Sales Returns" page). */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $returns = SaleReturn::query()
            ->with(['sale:id,invoice_no', 'user:id,name'])
            ->withCount('items')
            ->when($q !== '', fn ($w) => $w->where(function ($s) use ($q) {
                $s->where('return_no', 'like', "%{$q}%")
                    ->orWhereHas('sale', fn ($x) => $x->where('invoice_no', 'like', "%{$q}%"));
            }))
            ->latest('returned_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $today = SaleReturn::whereDate('returned_at', today());

        return view('pages.sales-returns', [
            'shop'        => ShopSetting::current(),
            'returns'     => $returns,
            'q'           => $q,
            'todayCount'  => (clone $today)->count(),
            'todayRefund' => (clone $today)->sum('refund_total'),
        ]);
    }

    /** The return form for one sale: pick items and quantities. */
    public function create(Sale $sale, ReturnService $returns)
    {
        if ($sale->status === Sale::STATUS_RETURNED) {
            return redirect()->route('sales-list')
                ->withErrors(['sale' => "Sale {$sale->invoice_no} has already been fully returned."]);
        }

        $sale->load('user:id,name');

        return view('pages.sale-return-create', [
            'shop'    => ShopSetting::current(),
            'sale'    => $sale,
            'states'  => $returns->lineStates($sale),
            'service' => $returns,
            'methods' => Sale::PAYMENT_METHODS,
        ]);
    }

    public function store(Request $request, Sale $sale, ReturnService $returns): RedirectResponse
    {
        $data = $request->validate([
            'items'                => ['required', 'array', 'min:1', 'max:200'],
            'items.*.sale_item_id' => ['required', 'integer'],
            'items.*.quantity'     => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'items.*.restock'      => ['nullable', 'boolean'],
            'refund_method'        => ['nullable', Rule::in(array_keys(Sale::PAYMENT_METHODS))],
            'reason'               => ['nullable', 'string', 'max:255'],
            'note'                 => ['nullable', 'string', 'max:500'],
        ]);

        $return = $returns->create($sale, $data, $request->user()->id);

        return redirect()
            ->route('sales-returns.receipt', $return)
            ->with('status', "Return {$return->return_no} saved. Stock updated and refund recorded.");
    }

    /** Printable refund slip: ?size=a4 for A4, default is 80mm thermal */
    public function receipt(SaleReturn $saleReturn, Request $request)
    {
        $saleReturn->load(['items', 'sale', 'user:id,name']);

        return view('pages.sale-return-receipt', [
            'return' => $saleReturn,
            'shop'   => ShopSetting::current(),
            'size'   => $request->query('size') === 'a4' ? 'a4' : '80mm',
        ]);
    }
}