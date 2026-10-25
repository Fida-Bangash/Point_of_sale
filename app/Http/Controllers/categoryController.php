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
        'categories' => category::orderBy('name')->get(),
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

        category::create($data);

        return back()->with('status','category created successfully.');
    }

    public function update(Request $request, category $category)
    {
        $this->prepare($request);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'slug'      => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
            'is_active' => ['required', 'boolean'],
        ]);

        $category->update($data);

        return back()->with('status','category updated successfully.');
    }

    public function destroy(category $category)
    {
        $category->delete();

        return back()->with('status','category deleted successfully.');
    }

    private function prepare(Request $request): void
    {
        $name = trim((string) $request->name);

        $request->merge([
            'name' => $name,
            'slug' => str::slug($request->slug ?: $name),
        ]);
    }

}
