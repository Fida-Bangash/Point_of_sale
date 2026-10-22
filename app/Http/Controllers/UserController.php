<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
  public function index()
{
    return view('pages.users', ['users' => User::orderBy('name')->get()]);
}

public function store(Request $request)
{
    $data = $request->validate([
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
        'phone'    => ['nullable', 'string', 'max:20'],
        'role'     => ['required', Rule::in(User::ROLES)],
        'password' => ['required', 'confirmed', 'min:8'],
    ]);

    $user = new User(Arr::only($data, ['name', 'email', 'phone', 'password']));
    $user->role = $data['role'];
    $user->save();

    return back()->with('status', 'User ban gaya.');
}

public function update(Request $request, User $user)
{
    $data = $request->validate([
        'name'      => ['required', 'string', 'max:255'],
        'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        'phone'     => ['nullable', 'string', 'max:20'],
        'role'      => ['required', Rule::in(User::ROLES)],
        'is_active' => ['required', 'boolean'],
        'password'  => ['nullable', 'confirmed', 'min:8'],
    ]);

    // Admin apne aap ko demote ya deactivate na kar sake (warna koi admin hi na bache)
    if ($user->is($request->user()) && ($data['role'] !== 'admin' || ! $data['is_active'])) {
        return back()->withErrors(['role' => 'Apna admin role ya status nahi badal sakte.']);
    }

    $user->fill(Arr::only($data, ['name', 'email', 'phone']));
    $user->role = $data['role'];
    $user->is_active = (bool) $data['is_active'];
    if (! empty($data['password'])) {
        $user->password = $data['password'];   // cast khud hash karega
    }
    $user->save();

    return back()->with('status', 'User update ho gaya.');
}

}
