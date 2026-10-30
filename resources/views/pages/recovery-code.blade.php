<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery code - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:520px">
    <div class="card shadow-sm">
        <div class="card-body p-4 text-center">
            <h4 class="mb-2">Your Recovery Code</h4>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <p class="text-muted">
                This code will help you recover your account if you forget your password.
                Write it down on paper or take a photo, and keep it in a safe place.
            </p>

            <div class="my-4 p-3 border rounded bg-white"
                style="font-size:28px;letter-spacing:3px;font-family:monospace;font-weight:700">
                {{ $code }}
            </div>

            <form method="POST" action="{{ route('recovery.done') }}">
                @csrf
                <button type="submit" class="btn btn-warning w-100">I've Saved It</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>