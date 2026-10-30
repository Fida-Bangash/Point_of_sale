<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Native\Desktop\Facades\AutoUpdater;

class UpdateController extends Controller
{
    public function index()
    {
        return view('pages.updates', [
            'version'    => config('nativephp.version'),
            'status'     => Cache::get('update.status'),
            'newVersion' => Cache::get('update.version'),
        ]);
    }

    public function check()
    {
        cache::forget('update.status');
        cache::forget('update.version');
        Cache::forever('update.status', 'checking');

        AutoUpdater::checkForUpdates();

        return back();
    }

    public function download()
    {
        cache::forever('update.status', 'downloading');

        AutoUpdater::downloadUpdate();
        return back();

    }

    public function install()
    {
        AutoUpdater::quitAndInstall();
        return back();
    }
}
