<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
{
    return view('pages.signin', ['canRegister' => ! User::exists()]);
}

       public function store(Request $request)
{
      $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))){
        return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
    }
    
    $request->session()->regenerate();
    return redirect()->intended(route('index'));
}

public function destroy(Request $request){

Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();

return redirect()->route('signin');
}

}
