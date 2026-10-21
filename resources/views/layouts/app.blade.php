<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="POS - Admin Panel">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', config('app.name'))</title>

    @include('partials.head')
    @stack('styles')
</head>

<body class="@yield('body_class')">
    <div id="global-loader">
        <div class="whirly-loader"> </div>
    </div>

    <div class="main-wrapper">
        @include('partials.header')
        @include('partials.sidebar')

        @yield('content')

        @include('partials.customizer')
    </div>

    @include('partials.scripts')
    @stack('scripts')

    <script src="{{ asset('assets/js/theme-script.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    @stack('scripts_after')
</body>

</html>
