<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function index()
    {
        return view('pages.backup',[
            'canBackup' => DB::connection()->getDriverName() === 'sqlite',
        ]);
    }

    public function download(): BinaryFileResponse
    {
        $connection = DB::connection();

        if($connection->getDriverName() !== 'sqlite')
            {
                abort(422, 'The backup is only for the local (SQLite) database.');
            }

            $name = 'pos-backup-' .now()->format('Y-m-d_H-i-s') . '.sqlite';
            $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name;

            $connection->statement("VACUUM INTO '" . str_replace("'", "''", $path) . "'");

            return response()->download($path,$name)->deleteFileAfterSend(true);
    }
}
