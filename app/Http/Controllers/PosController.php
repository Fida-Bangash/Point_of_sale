<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\ShopSetting;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PosController extends Controller
{
    public function index()
    {
        return view('pages.pos', [
            'shop'    => ShopSetting::current(),
            'methods' => Sale::PAYMENT_METHODS,
        ]);
    }

    /** JSON list for the POS screen: ?barcode=... (exact) or ?q=... (search) */
    public function products(Request $request): JsonResponse
    {
        $barcode = trim((string) $request->query('barcode', ''));
        $q       = trim((string) $request->query('q', ''));

        $query = Product::query()->where('is_active', true)->with('unit:id,short_name');

        if ($barcode !== '') {
            $query->where('barcode', $barcode);
        } elseif ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('barcode', $q);
            });
        }

        $products = $query->orderBy('name')->limit(40)->get()->map(fn (Product $p) => [
            'id'      => $p->id,
            'name'    => $p->name,
            'sku'     => $p->sku,
            'barcode' => $p->barcode,
            'price'   => (float) $p->selling_price,
            'stock'   => (float) $p->quantity,
            'unit'    => $p->unit?->short_name,
        ]);

        return response()->json($products);
    }

    public function checkout(Request $request, SaleService $sales): JsonResponse
    {
        $data = $request->validate([
            'items'                => ['required', 'array', 'min:1', 'max:200'],
            'items.*.product_id'   => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'numeric', 'gt:0', 'max:99999'],
            'discount'             => ['nullable', 'numeric', 'min:0'],
            'paid'                 => ['required', 'numeric', 'min:0'],
            'payment_method'       => ['required', Rule::in(array_keys(Sale::PAYMENT_METHODS))],
            'customer_name'        => ['nullable', 'string', 'max:255'],
            'note'                 => ['nullable', 'string', 'max:500'],
        ]);

        $sale = $sales->create($data, $request->user()->id);

        return response()->json([
            'sale_id'     => $sale->id,
            'invoice_no'  => $sale->invoice_no,
            'receipt_url' => route('sales.receipt', $sale),
        ]);
    }
}