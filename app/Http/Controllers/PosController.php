<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\HeldBill;
use App\Models\Product;
use App\Models\Sale;
use App\Models\ShopSetting;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PosController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.pos', [
            'shop'         => ShopSetting::current(),
            'methods'      => Sale::checkoutMethods(),
            'canOverrideCreditLimit' => $this->canOverrideCreditLimit($request),
            'categories'   => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'canSeeMargin' => $this->canSeeMargin($request),
        ]);
    }

    /** JSON for the POS screen: ?barcode= (exact), ?q= (search), ?category= (filter) */
    public function products(Request $request): JsonResponse
    {
        $barcode    = trim((string) $request->query('barcode', ''));
        $q          = trim((string) $request->query('q', ''));
        $categoryId = (int) $request->query('category', 0);
        $margin     = $this->canSeeMargin($request);

        $query = Product::query()
            ->where('is_active', true)
            ->with(['unit:id,short_name', 'category:id,name']);

        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        if ($barcode !== '') {
            $query->where('barcode', $barcode);
        } elseif ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('barcode', $q);
            });
        }

        $products = $query
            ->orderByRaw('quantity <= 0')   // sold-out items go last
            ->orderBy('name')
            ->limit(200)
            ->get()
            ->map(function (Product $p) use ($margin) {
                $row = [
                    'id'       => $p->id,
                    'name'     => $p->name,
                    'sku'      => $p->sku,
                    'barcode'  => $p->barcode,
                    'category' => $p->category?->name,
                    'price'    => (float) $p->selling_price,
                    'stock'    => (float) $p->quantity,
                    'alert'    => (float) $p->alert_quantity,
                    'unit'     => $p->unit?->short_name,
                    'image'    => $p->imageUrl(),
                ];

                // Cost-based numbers are only sent to admin/manager
                if ($margin) {
                    $profit = round((float) $p->selling_price - (float) $p->purchase_price, 2);
                    $row['profit']     = $profit;
                    $row['margin_pct'] = (float) $p->selling_price > 0
                        ? (int) round($profit / (float) $p->selling_price * 100)
                        : 0;
                }

                return $row;
            });

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
            'payment_method'       => ['required', Rule::in(array_keys(Sale::checkoutMethods()))],
            'customer_id'          => ['nullable', 'integer', 'exists:customers,id'],
            'customer_name'        => ['nullable', 'string', 'max:255'],
            'override_credit_limit' => ['nullable', 'boolean'],
            'note'                 => ['nullable', 'string', 'max:500'],
            'held_uuid'            => ['nullable', 'uuid'],
        ]);

        $user = $request->user();

        // Only admin/manager may push a customer past the credit limit. A cashier who sends the
        // flag anyway simply gets the normal "limit exceeded" error.
        $data['allow_over_limit'] = $request->boolean('override_credit_limit')
            && $this->canOverrideCreditLimit($request);

        // The sale and the removal of its held bill succeed or fail together
        $sale = DB::transaction(function () use ($sales, $data, $user) {
            $sale = $sales->create($data, $user->id);

            if (! empty($data['held_uuid'])) {
                HeldBill::visibleTo($user)->where('uuid', $data['held_uuid'])->delete();
            }

            return $sale;
        });

        return response()->json([
            'sale_id'     => $sale->id,
            'invoice_no'  => $sale->invoice_no,
            'receipt_url' => route('sales.receipt', $sale),
        ]);
    }

    /** JSON for the customer picker on the POS screen: ?q= matches name or phone (active customers only). */
    public function customers(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $query = Customer::query()
            ->where('is_active', true)
            ->withSum('ledger as balance', 'amount');

        if ($q !== '') {
            $phone = Customer::normalizePhone($q);

            $query->where(function ($w) use ($q, $phone) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$phone}%");
            });
        }

        $customers = $query->orderBy('name')->limit(15)->get()
            ->map(fn (Customer $c) => $c->toPosArray());

        return response()->json($customers);
    }

    /**
     * "New customer" shortcut at the checkout: only the fields needed to start selling.
     * Everything else (address, credit limit, opening balance) is edited on the Customers page.
     */
    public function storeCustomer(Request $request): JsonResponse
    {
        $request->merge([
            'name'  => trim((string) $request->input('name')),
            'phone' => Customer::normalizePhone((string) $request->input('phone')),
        ]);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:' . Customer::PHONE_REGEX, Rule::unique('customers', 'phone')],
        ], [
            'phone.regex'  => 'Phone number must be 11 digits and start with 03 (e.g. 03001234567).',
            'phone.unique' => 'A customer with this phone number already exists.',
        ]);

        $customer = Customer::create($data + ['is_active' => true]);

        return response()->json($customer->toPosArray(), 201);
    }

    private function canOverrideCreditLimit(Request $request): bool
    {
        return $request->user()->hasRole('admin', 'manager');
    }

    private function canSeeMargin(Request $request): bool
    {
        return in_array($request->user()->role, ['admin', 'manager'], true);
    }
}