<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
// use PhpParser\Node\Expr\FuncCall;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view ('pages.profile', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $request->merge([
            'phone' => preg_replace('/[\s\-]/', '', (string) $request->phone),
        ]);
        
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'regex:/^03\d{9}$/'],
        ], [
            'phone.regex' => 'phone number must be 11 digits and start with 03 (e.g. 03XXXXXXXXX).', 
        ]);


        $user = $request->user();
        $user->name = $data['name'];
        $user->phone = $data['phone'] ?: null;
        $user->save();

        return back()->with('status', 'Profile updated successfully.');
        }


        public function updatePassword(Request $request)
        {
            $data = $request->validateWithBag('password',[
                'current_password' => ['required', 'current_password'],
                'password'        => ['required', 'confirmed', Password::defaults(), 'different:current_password'],
            ]);

            $request->user()->update(['password' => $data['password']]);
            return back()->with('status', 'password changed successfully.');
        }
}
