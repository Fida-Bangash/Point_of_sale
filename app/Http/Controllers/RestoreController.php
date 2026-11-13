<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SyncService;
use Illuminate\Http\Request;

class RestoreController extends Controller
{
    public function show()
    {
        abort_if(User::exists(), 403);

        return view('pages.restore', ['needsUrl' => ! config('sync.url')]);
    }

    public function store(Request $r, SyncService $s)
    {
        abort_if(User::exists(), 403);

        $d = $r->validate([
            'url'       => ['nullable', 'url'],
            'email'     => ['required', 'email'],
            'password'  => ['required', 'string'],
            'device_id' => ['nullable', 'integer'],
        ]);

        if (! empty($d['url'])) {
            SyncService::put('sync_url', rtrim($d['url'], '/'));
        }

        try {
            $res = $s->restoreWithLogin(strtolower(trim($d['email'])), $d['password'], $d['device_id'] ?? null);
        } catch (\Throwable $e) {
            return back()->withErrors(['email' => $e->getMessage()])->onlyInput('email', 'url');
        }

        if ($res['status'] === 'choose') {
            return back()
                ->with('devices', $res['devices'])
                ->with('status', 'More than one PC uses this account. Choose the one to restore and enter your password again.')
                ->onlyInput('email', 'url');
        }

        return redirect()->route('signin')->with('status', 'Data restored. Sign in with your old email and password.');
    }
}