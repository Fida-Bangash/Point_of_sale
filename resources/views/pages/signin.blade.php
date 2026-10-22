<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">
        <title>Sign in - {{ config('app.name') }}</title>

        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

        <style>
            /* Same layout as reset-password / register: form on the left half, picture on the right half
               (illustration is hidden on small screens by the template) */
            .login-wrapper.signin-wrap { background: #fff; }
            .login-wrapper.signin-wrap .login-content { background: #fff; padding: 32px 24px; }

            /* Illustration like the register page; uses the 3x HD version (2118x3222)
               so it stays sharp on a half-screen panel */
            .signin-wrap .login-img { background: #fec38c; overflow: hidden; }
            .signin-wrap .login-img img {
                width: 100%;
                height: 100vh;
                object-fit: cover;
                object-position: center;
            }

            .signin-wrap .login-userset {
                width: 100%;
                max-width: 440px;
                background: transparent;
                border: 0;
                box-shadow: none;
                padding: 0;
            }

            .signin-wrap .login-userheading h3 { font-weight: 700; letter-spacing: -0.01em; }
            .signin-wrap .login-userheading h4 { color: #6b7280; font-weight: 400; line-height: 1.5; }

            .signin-wrap .form-login { margin-bottom: 16px; }
            .signin-wrap .form-login > label { font-weight: 600; margin-bottom: 6px; }

            .signin-wrap .form-login input:not([type="checkbox"]),
            .signin-wrap .pass-input {
                height: 46px;
                border-radius: 8px;
                transition: border-color .15s, box-shadow .15s;
            }
            .signin-wrap .form-login input:focus,
            .signin-wrap .pass-input:focus {
                border-color: #ff9f43;
                box-shadow: 0 0 0 3px rgba(255, 159, 67, .2);
                outline: 0;
            }
            .signin-wrap .is-invalid { border-color: #dc3545 !important; }

            .field-hint { color: #6b7280; font-size: 13px; margin-top: 6px; display: block; min-height: 18px; }
            .field-hint.bad { color: #dc3545; }

            .signin-wrap .btn-login { height: 48px; border-radius: 8px; font-weight: 600; }
            .form-status { margin: 10px 0 0; text-align: center; font-size: 13px; color: #6b7280; min-height: 18px; }

            @media (max-width: 575.98px) {
                .login-wrapper.signin-wrap .login-content { padding: 24px 18px; }
            }
        </style>
    </head>
    <body class="account-page">

        <div id="global-loader">
            <div class="whirly-loader"></div>
        </div>

        <div class="main-wrapper">
            <div class="account-content">
                <div class="login-wrapper signin-wrap">
                    <div class="login-content">

                    @if (session('status'))
                    <div class="alert alert-info">
                         {{ session('status') }}
                    </div>
                    @endif

                       <form method="POST" action="{{ url('/signin') }}" id="signin-form" novalidate>
                            @csrf
                            <div class="login-userset">
                                <div class="login-logo logo-normal">
                                    <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}">
                                </div>
                                <a href="{{ url('/') }}" class="login-logo logo-white">
                                    <img src="{{ asset('assets/img/logo-white.png') }}" alt="{{ config('app.name') }}">
                                </a>

                                <div class="login-userheading">
                                    <h3>Sign in</h3>
                                    <h4>Use your email and password to open the POS.</h4>
                                </div>

                                {{-- Email --}}
                                <div class="form-login">
                                    <label for="email">Email address</label>
                                    <div class="form-addons">
                                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                               placeholder="you@example.com" autocomplete="email"
                                                value ="{{ old('email') }}">
                                        <img src="{{ asset('assets/img/icons/mail.svg') }}" alt="">
                                    </div>
                                   <span class="field-hint bad" data-for="email">@error('email'){{ $message }}@enderror</span>
                                </div>

                                {{-- Password --}}
                                <div class="form-login">
                                    <label for="password">Password</label>
                                    <div class="pass-group">
                                        <input type="password" id="password" name="password" class="pass-input @error('password') is-invalid @enderror"
                                               autocomplete="current-password">
                                        <span class="fas toggle-password fa-eye-slash"></span>
                                    </div>
                                   <span class="field-hint bad" data-for="password">@error('password'){{ $message }}@enderror</span>
                                </div>

                                {{-- Remember me / Forgot password --}}
                                <div class="form-login authentication-check">
                                    <div class="row">
                                        <div class="col-12 d-flex align-items-center justify-content-between">
                                            <div class="custom-control custom-checkbox">
                                                <label class="checkboxs ps-4 mb-0 pb-0 line-height-1">
                                                    <input type="checkbox" name="remember">
                                                    <span class="checkmarks"></span>Remember me
                                                </label>
                                            </div>
                                            <div class="text-end">
                                                <a class="forgot-link" href="{{ url('/forgot-password') }}">Forgot password?</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-login">
                                    <button type="submit" class="btn btn-login">Sign in</button>
                                    <p class="form-status" id="form-status" aria-live="polite"></p>
                                </div>

                                @if ($canRegister)
                                <div class="signinform text-center">
                                    <h4>New here?
                                    <a href="{{ url('/register') }}" class="hover-a">Create an account</a>
                                    </h4>
                                </div>    
                                @endif
                                

                                <!-- <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                                </div> -->
                            </div>
                        </form>

                    </div>

                    <div class="login-img">
                        <img src="{{ asset('assets/img/authentication/login02-hd.png') }}" alt="" decoding="async">
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('assets/js/feather.min.js') }}"></script>
        <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/js/theme-script.js') }}"></script>
        <script src="{{ asset('assets/js/script.js') }}"></script>

<script>
    (function () {
        var $ = function (id) {
            return document.getElementById(id);
        };

        var form = $('signin-form');
        var status = $('form-status');

        function setError(id, text) {
            var el = $(id);
            var hint = document.querySelector(
                '.field-hint[data-for="' + id + '"]'
            );

            if (el) {
                el.classList.toggle('is-invalid', !!text);
            }

            if (hint) {
                hint.textContent = text || '';
                hint.className = 'field-hint' + (text ? ' bad' : '');
            }
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                var valid = true;
                var email = $('email').value.trim();
                var password = $('password').value;

                // Validate email format.
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    setError('email', 'Enter a valid email address.');
                    valid = false;
                } else {
                    setError('email', '');
                }

                // Validate password.
                if (!password) {
                    setError('password', 'Enter your password.');
                    valid = false;
                } else {
                    setError('password', '');
                }

                // Stop submission only when validation fails.
                if (!valid) {
                    e.preventDefault();

                    if (status) {
                        status.textContent = '';
                    }
                }
            });
        }
    })();
</script>
    </body>
</html>