<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">
        <title>Forgot password - {{ config('app.name') }}</title>

        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

        <style>
            /* Same layout as reset-password / register / sign in: form on the left half,
               illustration on the right half (illustration is hidden on small screens by the template).
               Uses the 3x HD illustration (2124x3222) so it stays sharp on a half-screen panel */
            .login-wrapper.forgot-wrap { background: #fff; }
            .login-wrapper.forgot-wrap .login-content { background: #fff; padding: 32px 24px; }

            .forgot-wrap .login-img { background: #fec38c; overflow: hidden; }
            .forgot-wrap .login-img img {
                width: 100%;
                height: 100vh;
                object-fit: cover;
                object-position: center;
            }

            .forgot-wrap .login-userset {
                width: 100%;
                max-width: 440px;
                background: transparent;
                border: 0;
                box-shadow: none;
                padding: 0;
            }

            .forgot-wrap .login-userheading h3 { font-weight: 700; letter-spacing: -0.01em; }
            .forgot-wrap .login-userheading h4 { color: #6b7280; font-weight: 400; line-height: 1.5; }

            .forgot-wrap .form-login { margin-bottom: 16px; }
            .forgot-wrap .form-login > label { font-weight: 600; margin-bottom: 6px; }

            .forgot-wrap .form-login input {
                height: 46px;
                border-radius: 8px;
                transition: border-color .15s, box-shadow .15s;
            }
            .forgot-wrap .form-login input:focus {
                border-color: #ff9f43;
                box-shadow: 0 0 0 3px rgba(255, 159, 67, .2);
                outline: 0;
            }
            .forgot-wrap .is-invalid { border-color: #dc3545 !important; }

            .field-hint { color: #6b7280; font-size: 13px; margin-top: 6px; display: block; min-height: 18px; }
            .field-hint.bad { color: #dc3545; }

            .forgot-wrap .btn-login { height: 48px; border-radius: 8px; font-weight: 600; }
            .form-status { margin: 10px 0 0; text-align: center; font-size: 13px; color: #6b7280; min-height: 18px; }

            @media (max-width: 575.98px) {
                .login-wrapper.forgot-wrap .login-content { padding: 24px 18px; }
            }
        </style>
    </head>
    <body class="account-page">

        <div id="global-loader">
            <div class="whirly-loader"></div>
        </div>

        <div class="main-wrapper">
            <div class="account-content">
                <div class="login-wrapper forgot-wrap">
                    <div class="login-content">

                        <form action="#" id="forgot-form" novalidate>
                            <div class="login-userset">
                                <div class="login-logo logo-normal">
                                    <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}">
                                </div>
                                <a href="{{ url('/') }}" class="login-logo logo-white">
                                    <img src="{{ asset('assets/img/logo-white.png') }}" alt="{{ config('app.name') }}">
                                </a>

                                <div class="login-userheading">
                                    <h3>Forgot your password?</h3>
                                    <h4>Enter the email you signed up with and we will send you a link to reset your password.</h4>
                                </div>

                                {{-- Email --}}
                                <div class="form-login">
                                    <label for="email">Email address</label>
                                    <div class="form-addons">
                                        <input type="email" id="email" name="email" class="form-control"
                                               placeholder="you@example.com" autocomplete="email">
                                        <img src="{{ asset('assets/img/icons/mail.svg') }}" alt="">
                                    </div>
                                    <span class="field-hint" data-for="email"></span>
                                </div>

                                <div class="form-login">
                                    <button type="submit" class="btn btn-login">Send reset link</button>
                                    <p class="form-status" id="form-status" aria-live="polite"></p>
                                </div>

                                <div class="signinform text-center">
                                    <h4>Remembered it? <a href="{{ url('/signin') }}" class="hover-a">Back to sign in</a></h4>
                                </div>

                                <!-- <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                                </div> -->
                            </div>
                        </form>

                    </div>

                    <div class="login-img">
                        <img src="{{ asset('assets/img/authentication/forgot02-hd.png') }}" alt="" decoding="async">
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
                var $ = function (id) { return document.getElementById(id); };
                var form = $('forgot-form');
                var status = $('form-status');

                function setError(id, text) {
                    var el = $(id);
                    var hint = document.querySelector('.field-hint[data-for="' + id + '"]');
                    el.classList.toggle('is-invalid', !!text);
                    if (hint) { hint.textContent = text || ''; hint.className = 'field-hint' + (text ? ' bad' : ''); }
                }

                // Design preview only: checks the field, does not send any email
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($('email').value.trim());

                    setError('email', valid ? '' : 'Enter a valid email address.');
                    status.textContent = valid ? 'Form looks good. Email sending is not connected yet (design preview).' : '';
                });
            })();
        </script>
    </body>
</html>