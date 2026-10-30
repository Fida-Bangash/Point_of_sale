<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        return view('pages.product-list', [
            'products' => Product::with(['category', 'brand', 'unit'])->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('pages.add-product', ['product' => null] + $this->formData());
    }

    public function store(Request $request)
    {
        $this->prepare($request);

        $data = $request->validate($this->rules());
        $data = $this->withImage($request, $data);

        $product = Product::create($data);

        if (! $product->sku) {
            $product->update(['sku' => 'SKU-'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT)]);
        }

        return redirect()->route('product-list')->with('status', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'unit']);

        return view('pages.product-details', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('pages.edit-product', ['product' => $product] + $this->formData($product));
    }

    public function update(Request $request, Product $product)
    {
        $this->prepare($request);

        $data = $request->validate($this->rules($product));
        $data = $this->withImage($request, $data, $product);

        $product->update($data);

        return redirect()->route('product-list')->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('product-list')->with('status', 'Product deleted successfully.');
    }

    private function rules(?Product $product = null): array
    {
        $money = ['required', 'numeric', 'min:0', 'max:9999999999'];
        $stock = ['required', 'numeric', 'min:0', 'max:999999999'];

        return [
            'name'           => ['required', 'string', 'max:255'],
            'sku'            => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product?->id)],
            'barcode'        => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($product?->id)],
            'category_id'    => ['required', 'integer', 'exists:categories,id'],
            'brand_id'       => ['nullable', 'integer', 'exists:brands,id'],
            'unit_id'        => ['required', 'integer', 'exists:units,id'],
            'purchase_price' => $money,
            'selling_price'  => $money,
            'quantity'       => $stock,
            'alert_quantity' => $stock,
            'description'    => ['nullable', 'string', 'max:2000'],
            'image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image'   => ['nullable', 'boolean'],
            'is_active'      => ['required', 'boolean'],
        ];
    }

    private function prepare(Request $request): void
    {
        $sku = trim((string) $request->sku);
        $barcode = trim((string) $request->barcode);

        $request->merge([
            'name'    => trim((string) $request->name),
            'sku'     => $sku === '' ? null : strtoupper($sku),
            'barcode' => $barcode === '' ? null : $barcode,
        ]);
    }

    private function withImage(Request $request, array $data, ?Product $product = null): array
    {
        unset($data['image'], $data['remove_image']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');

            if ($product?->image) {
                Storage::disk('public')->delete($product->image);
            }
        } elseif ($product && $request->boolean('remove_image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = null;
        }

        return $data;
    }

    private function formData(?Product $product = null): array
    {
        $pick = fn (string $model, ?int $currentId) => $model::query()
            ->where('is_active', true)
            ->when($currentId, fn ($q) => $q->orWhere('id', $currentId))
            ->orderBy('name')
            ->get();

        return [
            'categories' => $pick(Category::class, $product?->category_id),
            'brands'     => $pick(Brand::class, $product?->brand_id),
            'units'      => $pick(Unit::class, $product?->unit_id),
        ];
    }
}