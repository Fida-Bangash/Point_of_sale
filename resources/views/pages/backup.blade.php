@extends('layouts.app')

@section('title', 'Backup')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Backup</h4>
                <h6>Create a backup copy of your full data</h6>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @if ($canBackup)
                <p class="mb-3">
                  This button will generate and download a file containing all your products, sales, and users. 
                  Please save this file in a <strong>pendrive or Google Drive</strong>. 
                  Take a backup at least once a week.
                </p>

                    <form method="POST" action="{{ route('backup.download') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Download Backup</button>
                    </form>
                @else
                <div class="alert alert-warning mb-0">
                  The app is currently running on MySQL, so this backup button will not work. 
                  This feature only works in the installed desktop app (SQLite).
                </div>

                @endif
            </div>
        </div>
    </div>
</div>
@endsection