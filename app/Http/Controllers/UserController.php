<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
  public function index()
{
    return view('pages.users', ['users' => User::orderBy('name')->get()]);
}

public function store(Request $request)
{
    $request->merge(['email' => strtolower(trim((string) $request->email))]);
    $data = $request->validate([
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
        'phone' => ['nullable', 'regex:/^03\d{9}$/'],
        'role'     => ['required', Rule::in(User::ROLES)],
        'password' => ['required', 'confirmed', Password::defaults()],
    ]);

    $user = new User(Arr::only($data, ['name', 'email', 'phone', 'password']));
    $user->role = $data['role'];
    $user->save();

    return back()->with('status', 'User created successfully.');
}

public function update(Request $request, User $user)
{
    $request->merge(['email' => strtolower(trim((string) $request->email))]);
    $data = $request->validate([
        'name'      => ['required', 'string', 'max:255'],
        'email'     => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        'phone'     => ['nullable', 'regex:/^03\d{9}$/', 'string', 'max:20'],
        'role'      => ['required', Rule::in(User::ROLES)],
        'is_active' => ['required', 'boolean'],
        'password'  => ['nullable', 'confirmed', Password::defaults()],
    ]);

// Admin cannot demote or deactivate their own account (to ensure at least one admin remains).
    if ($user->is($request->user()) && ($data['role'] !== 'admin' || ! $data['is_active'])) {
        return back()->withErrors(['role' => 'You cannot change your own role or deactivate your own account.']);
    }

    $user->fill(Arr::only($data, ['name', 'email', 'phone']));
    $user->role = $data['role'];
    $user->is_active = (bool) $data['is_active'];
    if (! empty($data['password'])) {
        $user->password = $data['password'];   // The model cast will automatically hash the password.
    }
    $user->save();

    return back()->with('status', 'User updated successfully.');
}

}