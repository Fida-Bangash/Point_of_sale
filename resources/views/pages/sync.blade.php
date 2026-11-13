@extends('layouts.app')

@section('title', 'Cloud Sync')

@section('content')
<div class="page-wrapper">
    <div class="content">

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="page-header">
            <div class="page-title">
                <h4>Cloud Sync</h4>
                <h6>Back up your data to the server</h6>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <p class="mb-1">Pending: <strong>{{ $pending }}</strong> &nbsp;|&nbsp; Failed: <strong>{{ $failed }}</strong></p>
                <p class="mb-3">Last sync: <strong>{{ $last ?? 'Never' }}</strong></p>

                <form method="POST" action="{{ route('sync.settings') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Server URL</label>
                        <input type="url" name="url" class="form-control"
                               value="{{ old('url', $url) }}" placeholder="https://your-server.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Device token</label>
                        <input type="password" name="token" class="form-control"
                               placeholder="Enter the device token" autocomplete="off" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>

                <hr>

                <button type="button" class="btn btn-secondary" id="syncNow">Sync Now</button>
                <pre id="syncResult" class="mt-3 mb-0"></pre>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts_after')
<script>
    document.getElementById('syncNow').addEventListener('click', async function () {
        var btn = this, out = document.getElementById('syncResult');
        btn.disabled = true;
        out.textContent = 'Syncing...';
        try {
            var r = await fetch('{{ route('sync.run') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });
            out.textContent = JSON.stringify(await r.json(), null, 2);
        } catch (e) {
            out.textContent = 'Error: ' + e.message;
        }
        btn.disabled = false;
    });
</script>
@endpush