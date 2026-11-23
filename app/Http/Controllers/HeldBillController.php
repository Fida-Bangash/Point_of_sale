<?php

namespace App\Http\Controllers;

use App\Models\HeldBill;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HeldBillController extends Controller
{
    /** Held bills the current user may see (also removes expired ones). */
    public function index(Request $request): JsonResponse
    {
        HeldBill::purgeExpired();

        $me = $request->user();

        $bills = HeldBill::visibleTo($me)
            ->with('user:id,name')
            ->orderBy('held_at')
            ->get()
            ->map(fn (HeldBill $b) => [
                'uuid'          => $b->uuid,
                'customer_name' => $b->customer_name,
                'total'         => (float) $b->total,
                'items_count'   => count($b->items),
                'held_at'       => $b->held_at->toIso8601String(),
                'mine'          => $b->user_id === $me->id,
                'by'            => $b->user?->name,
            ]);

        return response()->json($bills);
    }

    /** Put the current cart on hold. Prices come from the database, never from the browser. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items'              => ['required', 'array', 'min:1', 'max:200'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'numeric', 'gt:0', 'max:99999'],
            'discount'           => ['nullable', 'numeric', 'min:0'],
            'customer_name'      => ['nullable', 'string', 'max:255'],
            'replace_uuid'       => ['nullable', 'uuid'],
        ]);

        $user = $request->user();

        $bill = DB::transaction(function () use ($data, $user) {
            // A bill that is open on screen is replaced, not copied
            if (! empty($data['replace_uuid'])) {
                HeldBill::visibleTo($user)->where('uuid', $data['replace_uuid'])->delete();
            }

            $max = (int) config('pos.held_bills.max');
            if (HeldBill::where('user_id', $user->id)->count() >= $max) {
                throw ValidationException::withMessages([
                    'items' => "You can keep at most {$max} held bills. Finish or delete one first.",
                ]);
            }

            $lines = collect($data['items'])
                ->groupBy('product_id')
                ->map(fn ($group) => round((float) $group->sum('quantity'), 3));

            $products = Product::whereIn('id', $lines->keys())->get()->keyBy('id');

            $items    = [];
            $subtotal = 0.0;

            foreach ($lines as $productId => $qty) {
                $p = $products->get($productId);
                if (! $p) {
                    continue;
                }

                $items[] = [
                    'product_id'   => $p->id,
                    'product_uuid' => $p->uuid,
                    'barcode'      => $p->barcode,
                    'sku'          => $p->sku,
                    'name'         => $p->name,
                    'price'        => (float) $p->selling_price,
                    'quantity'     => $qty,
                ];
                $subtotal += round($qty * (float) $p->selling_price, 2);
            }

            $subtotal = round($subtotal, 2);
            $discount = min(round((float) ($data['discount'] ?? 0), 2), $subtotal);

            return HeldBill::create([
                'uuid'          => (string) Str::uuid(),
                'user_id'       => $user->id,
                'customer_name' => $data['customer_name'] ?? null,
                'discount'      => $discount,
                'total'         => round($subtotal - $discount, 2),
                'items'         => $items,
                'held_at'       => now(),
                'expires_at'    => now()->addDays((int) config('pos.held_bills.ttl_days')),
            ]);
        });

        return response()->json(['uuid' => $bill->uuid]);
    }

    /**
     * Open a held bill. Every line is checked again against today's price and
     * stock. The bill is NOT deleted here: it stays until the sale is completed.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $bill = HeldBill::visibleTo($request->user())->where('uuid', $uuid)->firstOrFail();

        $lines    = [];
        $warnings = [];

        foreach ($bill->items as $row) {
            $product = $this->findProduct($row);

            if (! $product || ! $product->is_active) {
                $warnings[] = "\"{$row['name']}\" is no longer available and was removed.";
                continue;
            }

            $stock = round((float) $product->quantity, 3);
            $qty   = round((float) $row['quantity'], 3);

            if ($stock <= 0) {
                $warnings[] = "\"{$product->name}\" is out of stock and was removed.";
                continue;
            }

            if ($qty > $stock) {
                $warnings[] = "\"{$product->name}\": only {$stock} in stock (was {$qty}).";
                $qty = $stock;
            }

            $price = (float) $product->selling_price;
            if (abs($price - (float) $row['price']) > 0.001) {
                $warnings[] = "\"{$product->name}\": price changed from " . (float) $row['price'] . " to {$price}.";
            }

            $lines[] = [
                'id'    => $product->id,
                'name'  => $product->name,
                'price' => $price,
                'stock' => $stock,
                'qty'   => $qty,
            ];
        }

        return response()->json([
            'uuid'          => $bill->uuid,
            'customer_name' => $bill->customer_name,
            'discount'      => (float) $bill->discount,
            'items'         => $lines,
            'warnings'      => $warnings,
        ]);
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        HeldBill::visibleTo($request->user())->where('uuid', $uuid)->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Match a snapshot line to a product: uuid first, then barcode, then sku.
     * The local id is used last, and only when the name still matches.
     * (Matching by uuid/barcode/sku is what lets a bill move between PCs later.)
     */
    private function findProduct(array $row): ?Product
    {
        if (filled($row['product_uuid'] ?? null) && $p = Product::where('uuid', $row['product_uuid'])->first()) {
            return $p;
        }
        if (filled($row['barcode'] ?? null) && $p = Product::where('barcode', $row['barcode'])->first()) {
            return $p;
        }
        if (filled($row['sku'] ?? null) && $p = Product::where('sku', $row['sku'])->first()) {
            return $p;
        }

        $byId = Product::find($row['product_id'] ?? 0);

        return ($byId && $byId->name === ($row['name'] ?? null)) ? $byId : null;
    }
}