<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\ShopSetting;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $sales = Sale::query()
            ->with('user:id,name')
            ->withCount('items')
            ->when($q !== '', fn ($w) => $w
                ->where('invoice_no', 'like', "%{$q}%")
                ->orWhere('customer_name', 'like', "%{$q}%"))
            ->latest('sold_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $today = Sale::whereDate('sold_at', today());

        return view('pages.sales-list', [
            'shop'       => ShopSetting::current(),
            'sales'      => $sales,
            'q'          => $q,
            'todayCount' => (clone $today)->count(),
            'todayTotal' => (clone $today)->sum('total'),
        ]);
    }

    /** Printable receipt: ?size=a4 for A4, default is 80mm thermal */
    public function receipt(Sale $sale, Request $request)
    {
        $sale->load(['items', 'user:id,name']);

        return view('pages.receipt', [
            'sale' => $sale,
            'shop' => ShopSetting::current(),
            'size' => $request->query('size') === 'a4' ? 'a4' : '80mm',
        ]);
    }
}