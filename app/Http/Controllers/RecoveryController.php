<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class RecoveryController extends Controller
{
    public function show()
    {
        if(! session()->has('recovery_code')){
            return redirect()->route('signin');
        }

        return view('pages.recovery-code',['code' => session('recovery_code')]);
    }

    public function done()
    {
        session()->forget('recovery_code');

        return auth()->check()
        ? redirect()->route('index')
        : redirect()->route('signin');
    }

    public function showForgot()
    {
        return view('pages.forgot-password');
    }

    public function reset(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->email))]);

        $data = $request->validate([
            'email'         => ['required', 'email'],
            'recovery_code' => ['required', 'string', 'max:50'],
            'password'      => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $data['email'])->first();

        // Use the same message so the user cannot determine whether the email exists.
        if (! $user || ! $user->is_active || ! $user->checkRecoveryCode($data['recovery_code'])) {
            return back()
                ->withErrors(['recovery_code' => 'The email or recovery code is incorrect.'])
                ->onlyInput('email');
        }

        $user->password = $data['password'];                    // The model cast will automatically hash the password.
        session(['recovery_code' => $user->issueRecoveryCode()]); // The new code is saved here, and the previous code is invalidated.

        return redirect()->route('recovery.show')->with('status', 'Your password has been changed. Your new recovery code is shown below.');
    }

    public function regenerate(Request $request)
    {
        $request->validateWithBag('recovery', [
            'current_password' => ['required', 'current_password'],
        ]);

        session(['recovery_code' => $request->user()->issueRecoveryCode()]);

        return redirect()->route('recovery.show');
    }
}
