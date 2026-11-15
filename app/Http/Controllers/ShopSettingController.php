<?php

namespace App\Http\Controllers;

use App\Models\ShopSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShopSettingController extends Controller
{
    public function edit()
    {
        return view('pages.company-settings', ['shop' => ShopSetting::current()]);
    }

    public function update(Request $request)
    {
        $this->prepare($request);

        $data = $request->validate([
            'shop_name'       => ['required', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'email'           => ['nullable', 'email', 'max:255'],
            'address'         => ['nullable', 'string', 'max:500'],
            'city'            => ['nullable', 'string', 'max:100'],
            'country'         => ['required', 'string', 'max:100'],
            'tax_number'      => ['nullable', 'string', 'max:50'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'currency_code'   => ['required', 'alpha', 'size:3'],
            'receipt_footer'  => ['nullable', 'string', 'max:500'],
            'logo'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo'     => ['nullable', 'boolean'],
        ]);

        $shop = ShopSetting::current();
        $data = $this->withLogo($request, $data, $shop);

        $shop->update($data);

        return back()->with('status', 'Shop settings saved successfully.');
    }

    private function prepare(Request $request): void
    {
        $request->merge([
            'currency_code' => strtoupper((string) $request->currency_code),
        ]);
    }

    private function withLogo(Request $request, array $data, ShopSetting $shop): array
    {
        unset($data['logo'], $data['remove_logo']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('shop', 'public');

            if ($shop->logo) {
                Storage::disk('public')->delete($shop->logo);
            }
        } elseif ($request->boolean('remove_logo')) {
            if ($shop->logo) {
                Storage::disk('public')->delete($shop->logo);
            }
            $data['logo'] = null;
        }

        return $data;
    }
}
