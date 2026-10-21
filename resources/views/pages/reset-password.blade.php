<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">
        <title>Reset password - {{ config('app.name') }}</title>

        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

        <style>
            /* Left half is one full-height white panel, illustration fills the right half
               (illustration is hidden on small screens by the template) */
            .login-wrapper.reset-wrap { background: #fff; }
            .login-wrapper.reset-wrap .login-content { background: #fff; padding: 32px 24px; }

            .reset-wrap .login-img { background: #fec38c; }
            .reset-wrap .login-img img { height: 100vh; object-fit: cover; object-position: center; }

            .reset-wrap .login-userset {
                width: 100%;
                max-width: 440px;
                background: transparent;
                border: 0;
                box-shadow: none;
                padding: 0;
            }

            .reset-wrap .login-userheading h3 { font-weight: 700; letter-spacing: -0.01em; }
            .reset-wrap .login-userheading h4 { color: #6b7280; font-weight: 400; line-height: 1.5; }

            .reset-wrap .form-login { margin-bottom: 16px; }
            .reset-wrap .form-login > label { font-weight: 600; margin-bottom: 6px; }

            .reset-wrap .pass-input,
            .reset-wrap .pass-inputs {
                height: 46px;
                border-radius: 8px;
                transition: border-color .15s, box-shadow .15s;
            }
            .reset-wrap .pass-input:focus,
            .reset-wrap .pass-inputs:focus {
                border-color: #ff9f43;
                box-shadow: 0 0 0 3px rgba(255, 159, 67, .2);
                outline: 0;
            }
            .reset-wrap .is-invalid { border-color: #dc3545 !important; }

            .field-hint { color: #6b7280; font-size: 13px; margin-top: 6px; display: block; min-height: 18px; }
            .field-hint.ok { color: #198754; }
            .field-hint.bad { color: #dc3545; }

            /* Password strength */
            .strength { display: flex; gap: 4px; margin-top: 8px; }
            .strength span { flex: 1; height: 4px; border-radius: 2px; background: #e5e7eb; transition: background .2s; }
            .strength[data-level="1"] span:nth-child(-n+1) { background: #dc3545; }
            .strength[data-level="2"] span:nth-child(-n+2) { background: #fd7e14; }
            .strength[data-level="3"] span:nth-child(-n+3) { background: #ffc107; }
            .strength[data-level="4"] span:nth-child(-n+4) { background: #198754; }

            .reset-wrap .btn-login { height: 48px; border-radius: 8px; font-weight: 600; }
            .form-status { margin: 10px 0 0; text-align: center; font-size: 13px; color: #6b7280; min-height: 18px; }

            @media (max-width: 575.98px) {
                .login-wrapper.reset-wrap .login-content { padding: 24px 18px; }
            }
        </style>
    </head>
    <body class="account-page">

        <div id="global-loader">
            <div class="whirly-loader"></div>
        </div>

        <div class="main-wrapper">
            <div class="account-content">
                <div class="login-wrapper reset-wrap">
                    <div class="login-content">

                        <form action="#" id="reset-form" novalidate>
                            <div class="login-userset">
                                <div class="login-logo logo-normal">
                                    <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}">
                                </div>
                                <a href="{{ url('/') }}" class="login-logo logo-white">
                                    <img src="{{ asset('assets/img/logo-white.png') }}" alt="{{ config('app.name') }}">
                                </a>

                                <div class="login-userheading">
                                    <h3>Set a new password</h3>
                                    <h4>Choose a new password for your account. You will use it the next time you sign in.</h4>
                                </div>

                                {{-- New password --}}
                                <div class="form-login">
                                    <label for="password">New password</label>
                                    <div class="pass-group">
                                        <input type="password" id="password" name="password" class="pass-input"
                                               autocomplete="new-password">
                                        <span class="fas toggle-password fa-eye-slash"></span>
                                    </div>
                                    <div class="strength" id="strength" data-level="0" aria-hidden="true">
                                        <span></span><span></span><span></span><span></span>
                                    </div>
                                    <span class="field-hint" id="strength-text">Use at least 8 characters.</span>
                                </div>

                                {{-- Confirm --}}
                                <div class="form-login">
                                    <label for="password_confirmation">Confirm new password</label>
                                    <div class="pass-group">
                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                               class="pass-inputs" autocomplete="new-password">
                                        <span class="fas toggle-passwords fa-eye-slash"></span>
                                    </div>
                                    <span class="field-hint" id="match-text" aria-live="polite"></span>
                                </div>

                                <div class="form-login">
                                    <button type="submit" class="btn btn-login">Change password</button>
                                    <p class="form-status" id="form-status" aria-live="polite"></p>
                                </div>

                                <div class="signinform text-center">
                                    <h4>Back to <a href="{{ url('/signin') }}" class="hover-a">sign in</a></h4>
                                </div>

                                <!-- <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                                </div> -->
                            </div>
                        </form>

                    </div>

                    <div class="login-img">
                        <img src="{{ asset('assets/img/authentication/reset02.png') }}" alt="">
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
                var form = $('reset-form');
                var pass = $('password');
                var confirm = $('password_confirmation');
                var meter = $('strength');
                var meterText = $('strength-text');
                var matchText = $('match-text');
                var status = $('form-status');
                var labels = ['Use at least 8 characters.', 'Weak', 'Fair', 'Good', 'Strong'];

                function score(v) {
                    var s = 0;
                    if (v.length >= 8) s++;
                    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
                    if (/\d/.test(v)) s++;
                    if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) s++;
                    return v.length ? Math.max(s, 1) : 0;
                }

                function checkMatch() {
                    if (!confirm.value) { matchText.textContent = ''; matchText.className = 'field-hint'; return false; }
                    var ok = confirm.value === pass.value;
                    matchText.textContent = ok ? 'Passwords match.' : 'Passwords do not match.';
                    matchText.className = 'field-hint ' + (ok ? 'ok' : 'bad');
                    return ok;
                }

                pass.addEventListener('input', function () {
                    var s = score(pass.value);
                    meter.setAttribute('data-level', s);
                    meterText.textContent = labels[s];
                    meterText.className = 'field-hint';
                    pass.classList.remove('is-invalid');
                    checkMatch();
                });
                confirm.addEventListener('input', function () { confirm.classList.remove('is-invalid'); checkMatch(); });

                // Design preview only: checks the fields, does not change any password
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var valid = true;

                    if (pass.value.length < 8 || score(pass.value) < 2) {
                        pass.classList.add('is-invalid');
                        meterText.textContent = 'Use at least 8 characters with letters and numbers.';
                        meterText.className = 'field-hint bad';
                        valid = false;
                    }
                    if (!checkMatch()) {
                        confirm.classList.add('is-invalid');
                        if (!confirm.value) { matchText.textContent = 'Confirm your new password.'; matchText.className = 'field-hint bad'; }
                        valid = false;
                    }

                    status.textContent = valid ? 'Form looks good. Changing the password is not connected yet (design preview).' : '';
                });
            })();
        </script>
    </body>
</html>