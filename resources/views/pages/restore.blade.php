<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Restore - {{ config('app.name') }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body class="account-page">
<div class="main-wrapper">
    <div class="account-content">
        <div class="login-wrapper signin-wrap">
            <div class="login-content">

                @if (session('status'))
                    <div class="alert alert-info">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('restore.store') }}" id="restoreForm">
                    @csrf
                    <div class="login-userset">
                        <div class="login-userheading">
                            <h3>Restore my data</h3>
                            <h4>Enter the admin email and password you used on your old computer.</h4>
                        </div>

                        @if ($needsUrl)
                            <div class="form-login mb-3">
                                <label class="form-label">Server URL</label>
                                <input type="url" name="url" class="form-control"
                                    value="{{ old('url') }}" placeholder="https://your-server.com" required>
                            </div>
                        @endif

                        <div class="form-login mb-3">
                            <label class="form-label">Admin email</label>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email') }}" autocomplete="username" required>
                        </div>

                        <div class="form-login mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control"
                                autocomplete="current-password" required>
                        </div>

                        @if (session('devices'))
                            <div class="form-login mb-3">
                                <label class="form-label">Which computer?</label>
                                <select name="device_id" class="form-control" required>
                                    @foreach (session('devices') as $dev)
                                        <option value="{{ $dev['id'] }}">
                                            {{ $dev['name'] }} — last backup: {{ $dev['last_sync_at'] ?? 'never' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-login">
                            <button type="submit" class="btn btn-login" id="restoreBtn">Restore</button>
                        </div>

                        <p class="text-muted text-center mt-3 mb-0" id="restoreWait" style="display:none">
                            Restoring... please wait, do not close the app.
                        </p>

                        <div class="signinform text-center mt-3">
                            <a href="{{ route('signin') }}" class="hover-a">Back to sign in</a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('restoreForm').addEventListener('submit', function () {
        var b = document.getElementById('restoreBtn');
        b.disabled = true;
        b.textContent = 'Restoring...';
        document.getElementById('restoreWait').style.display = 'block';
    });
</script>
</body>
</html>