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

        // Validate registration form data.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
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