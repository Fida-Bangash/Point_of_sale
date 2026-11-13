<?php

namespace App\Http\Controllers;

use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    public function index()
    {
        return view('pages.sync', [
            'pending' => DB::table('sync_queue')->where('tries', '<', 10)->count(),
            'failed'  => DB::table('sync_queue')->where('tries', '>=', 10)->count(),
            'last'    => SyncService::get('last_sync_at'),
            'url'     => SyncService::get('sync_url'),
        ]);
    }

    public function settings(Request $r, SyncService $s)
    {
        $d = $r->validate(['url' => ['required', 'url'], 'token' => ['required', 'string']]);
        $s->saveSettings($d['url'], $d['token']);

        return back()->with('status', 'Sync settings saved.');
    }

    public function run(SyncService $s)
    {
        return response()->json($s->run());
    }
}