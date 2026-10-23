<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function show()
    {
        if (User::query()->exists()) {
            return redirect()
            ->route('signin')
            ->with('status','Registration is disabled. Please contact the administrator to create your account.');
        }

        return view('pages.register');
    }

    public function store(Request $request)
    {
        // Prevent public registration after the first user exists.
        abort_if(User::query()->exists(), 403);

        $request->merge([
        'email' => strtolower(trim((string) $request->email)),
        'phone' => preg_replace('/[\s\-]/', '', (string) $request->phone),
        ]);

        // Validate registration form data.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email:rfc,dns', 'max:255', 'unique:users,email',
                function ($attribute, $value, $fail) {
            $domain = strtolower(substr(strrchr($value, '@'), 1));
            $typos = [
                'gmil.com', 'gmial.com', 'gamil.com', 'gmai.com', 'gmaill.com',
                'gnail.com', 'gmail.co', 'gmail.con', 'gmail.cm',
                'yahooo.com', 'yaho.com', 'hotmial.com', 'hotmal.com', 'outlok.com',
                ];
            if (in_array($domain, $typos, true)) {
                $fail('This email domain looks misspelled. Please check it and try again.');
               }   
             },
            ],
            'phone' => ['required', 'regex:/^03\d{9}$/', 'string', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ],[
            'email.email' => 'Enter a valid email address (e.g. name@gmail.com).',
            'phone.regex' => 'Phone number must be 11 digits and start with 03 (e.g. 03001234567).',
        ]);

        // Create the first user.
        $user = new User(Arr::only($data, ['name', 'email', 'phone', 'password'])
        );

        $user->role = 'admin';
        $user->is_active = true;
        $user->save();

        // Log in the newly created admin.
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('index');
    }
}