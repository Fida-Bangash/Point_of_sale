@extends('layouts.app')

@section('title', 'Updates')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Software Update</h4>
                <h6>Check for new versions and install them</h6>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <p class="mb-3">Current version: <strong>{{ $version }}</strong></p>

                @if ($status === 'checking')
                    <div class="alert alert-info">Checking for updates, please wait...</div>

                @elseif ($status === 'none')
                    <div class="alert alert-success">You are on the latest version.</div>

                @elseif ($status === 'available')
                    <div class="alert alert-warning">
                        A new version {{ $newVersion ? '(' . $newVersion . ')' : '' }} is available.
                    </div>
                    <form method="POST" action="{{ route('updates.download') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Download Update</button>
                    </form>

                @elseif ($status === 'downloading')
                    <div class="alert alert-info">Downloading the update, please wait...</div>

                @elseif ($status === 'downloaded')
                    <div class="alert alert-success">The update is ready. The app will close and reopen to finish installing.</div>
                    <form method="POST" action="{{ route('updates.install') }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Install Now</button>
                    </form>

                @elseif ($status === 'error')
                    <div class="alert alert-danger">Could not check for updates. Please check your internet connection and try again.</div>
                @endif

                @if (! in_array($status, ['checking', 'downloading', 'downloaded', 'available']))
                    <form method="POST" action="{{ route('updates.check') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">Check for Updates</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@if (in_array($status, ['checking', 'downloading']))
<script>
    // While checking or downloading, refresh the page every 4 seconds
    setTimeout(function () { window.location.reload(); }, 4000);
</script>
@endif
@endsection