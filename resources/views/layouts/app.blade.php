<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="POS - Admin Panel">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $shop?->shop_name ?? config('app.name') }}</title>

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
    <script>jQuery.fx.off = true;</script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
            <script>
    (function ($) {
        var KEY = 'sidebarScroll';

        
        var saved = null;
        try { saved = JSON.parse(sessionStorage.getItem(KEY)); } catch (e) {}

        function boxes() { return $('.sidebar .slimscroll'); }

        function save() {
            try {
                var v = boxes().map(function () { return $(this).scrollTop(); }).get();
                sessionStorage.setItem(KEY, JSON.stringify(v));
            } catch (e) {}
        }

        $(function () {
            boxes().each(function (i) {
                if (saved && saved[i] != null) {
                    $(this).slimScroll({ scrollTo: saved[i] + 'px' });
                } else {
                    var a = $(this).find('a.active').first();
                    if (a.length) {
                        var top = a.offset().top - $(this).offset().top + $(this).scrollTop() - 120;
                        $(this).slimScroll({ scrollTo: Math.max(top, 0) + 'px' });
                    }
                }
            });

            $.fx.off = false;
        });

        
        $(document).on('click', '.sidebar-menu a', function (e) {
            if (e.originalEvent) save();
        });
        $(window).on('pagehide beforeunload', save);
    })(jQuery);
    </script>
    @stack('scripts_after')

    <script>
    (function () {
        var meta = document.querySelector('meta[name=csrf-token]');
        if (!meta) return;
        var busy = false;

                async function sync() {
            if (busy || !navigator.onLine) return;
            busy = true;
            try {
                // Keep going while there is still data to send (max 20 rounds)
                for (var i = 0; i < 20; i++) {
                    var r = await fetch('{{ route('sync.run') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': meta.content, 'Accept': 'application/json' }
                    });
                    var j = await r.json();
                    if (j.status !== 'ok' || !j.sent) break;
                }
            } catch (e) {}
            busy = false;
        }

        // Sync when the connection comes back
        window.addEventListener('online', sync);
        // Sync every 5 minutes
        setInterval(sync, 5 * 60 * 1000);
        // Sync 15 seconds after the app opens
        setTimeout(sync, 15000);
    })();
    </script>
</body>

</html>
