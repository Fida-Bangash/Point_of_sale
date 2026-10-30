<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        return view('pages.units', [
            'units' => Unit::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->prepare($request);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100', 'unique:units,name'],
            'short_name' => ['required', 'string', 'max:20', 'unique:units,short_name'],
            'is_active'  => ['required', 'boolean'],
        ]);

        Unit::create($data);

        return back()->with('status', 'Unit created successfully.');
    }

    public function update(Request $request, Unit $unit)
    {
        $this->prepare($request);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100', Rule::unique('units', 'name')->ignore($unit->id)],
            'short_name' => ['required', 'string', 'max:20', Rule::unique('units', 'short_name')->ignore($unit->id)],
            'is_active'  => ['required', 'boolean'],
        ]);

        $unit->update($data);

        return back()->with('status', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        $count = $unit->products()->count();

        if ($count > 0) {
            return back()->with('error', "Cannot delete \"{$unit->name}\": {$count} product(s) use it. Change or delete those products first.");
        }

        $unit->delete();

        return back()->with('status', 'Unit deleted successfully.');
    }

    private function prepare(Request $request): void
    {
        $request->merge([
            'name'       => trim((string) $request->name),
            'short_name' => trim((string) $request->short_name),
        ]);
    }
}