<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('pages.category-list',[
        'categories' => Category::withCount('products')->orderBy('name')->get(),
        ]);
    }


    public function store(Request $request)
    {
        $this->prepare($request);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255','unique:categories,name'],
            'slug'       => ['required', 'string', 'max:255','unique:categories,slug'],
            'is_active'  => ['required', 'boolean'],
        ]);

        Category::create($data);

        return back()->with('status','category created successfully.');
    }

    public function update(Request $request, Category $Category)
    {
        $this->prepare($request);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($Category->id)],
            'slug'      => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($Category->id)],
            'is_active' => ['required', 'boolean'],
        ]);

        $Category->update($data);

        return back()->with('status','category updated successfully.');
    }

    public function destroy(Category $Category)
    {

    $count = $Category->products()->count();
    if($count > 0) {
        return back()->with('error', "cannot delete \"{$Category->name}\": {$count} product(s) use it.");
    }
        $Category->delete();

        return back()->with('status','category deleted successfully.');
    }


    private function prepare(Request $request): void
    {
        $name = trim((string) $request->name);

        $request->merge([
            'name' => $name,
            'slug' => Str::slug($request->slug ?: $name),
        ]);
    }

}
