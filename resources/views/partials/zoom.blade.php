<style>
/* The page can be zoomed (script below). Sizes written with "vh" are NOT zoomed with the page,
   so they come out too small (for example the loading screen covered only part of the window).
   Dividing by the zoom gives the real size. */
div#global-loader,
.searchpart,
.sidebar-settings,
.sidebar-content,
.login-wrapper,
.login-wrapper .login-content,
.login-wrapper .login-img img,
.login-wrapper.login-new,
.login-body {
    height: calc(100vh / var(--ui-zoom, 1));
}

.login-body {
    min-height: calc(100vh / var(--ui-zoom, 1));
}
</style>
<script>
(function () {
    var MIN = 0.5, MAX = 2.0, STEP = 0.1, COOKIE = 'ui_zoom';
    var zoom = read();
    apply();

    // Cookie se yaad rakhte hain
    function read() {
        var m = document.cookie.match(/(?:^|;\s*)ui_zoom=([^;]+)/);
        var v = m ? parseFloat(m[1]) : 1;
        return isFinite(v) ? Math.min(MAX, Math.max(MIN, v)) : 1;
    }

    function apply() {
        document.documentElement.style.zoom = zoom;
        document.documentElement.style.setProperty('--ui-zoom', zoom);
        fixLayout();
    }

    // The theme (script.js) sets the sidebar scroll height from the window height in screen pixels
    // minus the 60px header. With the page zoomed that must be divided by the zoom, otherwise the
    // sidebar stops at 70% of the window and the last links are cut off.
    function fixLayout() {
        var height = Math.max(0, Math.round(window.innerHeight / zoom - 60)) + 'px';
        var boxes = document.querySelectorAll('.slimscroll, .sidebar .slimScrollDiv');
        for (var i = 0; i < boxes.length; i++) {
            boxes[i].style.height = height;
        }
    }

    // Run after the theme has set its own heights (it does that on page ready and on resize)
    window.addEventListener('load', fixLayout);
    window.addEventListener('resize', function () { setTimeout(fixLayout, 0); });

    function save() {
        document.cookie = COOKIE + '=' + zoom + '; path=/; max-age=31536000; SameSite=Lax';
    }

    var badgeEl, badgeTimer;
    function badge() {
        if (!document.body) return;
        if (!badgeEl) {
            badgeEl = document.createElement('div');
            badgeEl.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:99999;' +
                'background:rgba(0,0,0,.75);color:#fff;padding:6px 12px;border-radius:6px;' +
                'font:600 13px sans-serif;pointer-events:none;transition:opacity .2s;';
            document.body.appendChild(badgeEl);
        }
        badgeEl.textContent = Math.round(zoom * 100) + '%';
        badgeEl.style.opacity = '1';
        clearTimeout(badgeTimer);
        badgeTimer = setTimeout(function () { badgeEl.style.opacity = '0'; }, 1200);
    }

    function set(v) {
        zoom = Math.round(Math.min(MAX, Math.max(MIN, v)) * 100) / 100;
        apply();
        save();
        badge();
    }

    document.addEventListener('keydown', function (e) {
        if (!(e.ctrlKey || e.metaKey) || e.altKey) return;

        var k = e.key, c = e.code;
        if (k === '+' || k === '=' || c === 'NumpadAdd') {
            e.preventDefault();
            set(zoom + STEP);
        } else if (k === '-' || k === '_' || c === 'NumpadSubtract') {
            e.preventDefault();
            set(zoom - STEP);
        } else if (k === '0' || c === 'Numpad0') {
            e.preventDefault();
            set(1);
        }
    }, true);

    window.addEventListener('wheel', function (e) {
        if (!(e.ctrlKey || e.metaKey)) return;
        e.preventDefault();
        set(zoom + (e.deltaY < 0 ? STEP : -STEP));
    }, { passive: false, capture: true });
})();
</script>