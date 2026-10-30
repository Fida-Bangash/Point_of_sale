<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index()
    {
        return view('pages.brand-list',[
            'brands' => Brand::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->prepare($request);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255', 'unique:brands,name'],
            'slug'      => ['required', 'string', 'max:255', 'unique:brands,slug'],
            'is_active' => ['required', 'boolean'],
        ]);

        Brand::Create($data);

        return back()->with('status', 'Brand created successfully.');
    }
    
    public function update(Request $request, Brand $brand)
    {
        $this->prepare($request);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brand->id)],
            'slug'      => ['required', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brand->id)],
            'is_active' => ['required', 'boolean'],
        ]);

        $brand->update($data);

        return back()->with('status', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        $count = $brand->products()->count();

        if($count > 0){
            return back()->with('error', "Cannot delete \"{$brand->name}\": {$count} product(s) use it. Change or delete those products first.");
        }

        $brand->delete();

        return back()->with('status', 'Brand deleted successfully.');
    }

    public function prepare(Request $request): void
    {
        $name = trim((string) $request->name);

        $request->merge([
            'name' => $name,
            'slug' => Str::slug($request->slug ?: $name),
        ]);
    }
}
