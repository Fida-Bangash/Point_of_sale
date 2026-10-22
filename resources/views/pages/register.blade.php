<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">
        <title>Create account - {{ config('app.name') }}</title>

        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

        <style>
            /* Same layout as reset-password: form on the left half, illustration on the right half
               (illustration is hidden on small screens by the template) */
            .login-wrapper.register-wrap { background: #fff; }
            .login-wrapper.register-wrap .login-content { background: #fff; padding: 32px 24px; }

            .register-wrap .login-img { background: #fec38c; }
            .register-wrap .login-img img {
                height: 100vh;
                object-fit: cover;
                object-position: center;
                position: sticky;   /* stays in view while the longer form scrolls */
                top: 0;
            }

            .register-wrap .login-userset {
                width: 100%;
                max-width: 480px;
                background: transparent;
                border: 0;
                box-shadow: none;
                padding: 0;
            }

            .register-wrap .login-userheading h3 { font-weight: 700; letter-spacing: -0.01em; }
            .register-wrap .login-userheading h4 { color: #6b7280; font-weight: 400; line-height: 1.5; }

            .register-wrap .form-login { margin-bottom: 16px; }
            .register-wrap .form-login > label { font-weight: 600; margin-bottom: 6px; }

            .register-wrap .form-login input:not([type="checkbox"]),
            .register-wrap .form-login select,
            .register-wrap .pass-input,
            .register-wrap .pass-inputs {
                height: 46px;
                border-radius: 8px;
                transition: border-color .15s, box-shadow .15s;
            }
            .register-wrap .form-login select {
                width: 100%;
                padding: 0 12px;
                color: #1b2230;
                background-color: #fff;
                border: 1px solid rgba(145, 158, 171, 0.32);
            }
            .register-wrap .form-login input:focus,
            .register-wrap .form-login select:focus,
            .register-wrap .pass-input:focus,
            .register-wrap .pass-inputs:focus {
                border-color: #ff9f43;
                box-shadow: 0 0 0 3px rgba(255, 159, 67, .2);
                outline: 0;
            }
            .register-wrap .is-invalid { border-color: #dc3545 !important; }

            .register-wrap .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
            @media (max-width: 575.98px) {
                .login-wrapper.register-wrap .login-content { padding: 24px 18px; }
                .register-wrap .row-2 { grid-template-columns: 1fr; gap: 0; }
            }

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

            .register-wrap .btn-login { height: 48px; border-radius: 8px; font-weight: 600; }
            .form-status { margin: 10px 0 0; text-align: center; font-size: 13px; color: #6b7280; min-height: 18px; }
        </style>
    </head>
    <body class="account-page">

        <div id="global-loader">
            <div class="whirly-loader"></div>
        </div>

        <div class="main-wrapper">
            <div class="account-content">
                <div class="login-wrapper register-wrap">
                    <div class="login-content">

                       <form method="POST" action="{{ url('/register') }}" id="register-form" novalidate>
                            @csrf
                            <div class="login-userset">
                                <div class="login-logo logo-normal">
                                    <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}">
                                </div>
                                <a href="{{ url('/') }}" class="login-logo logo-white">
                                    <img src="{{ asset('assets/img/logo-white.png') }}" alt="{{ config('app.name') }}">
                                </a>

                                <div class="login-userheading">
                                    <h3>Create your account</h3>
                                    <h4>Add your details to start using the POS.</h4>
                                </div>

                                {{-- Full name --}}
                                <div class="form-login">
                                    <label for="name">Full name</label>
                                    <div class="form-addons">
                                        <input type="text" id="name" name="name" class="form-control"
                                               placeholder="e.g. Ahmed Khan" autocomplete="name" value="{{ old('name') }}">
                                        <img src="{{ asset('assets/img/icons/user-icon.svg') }}" alt="">
                                    </div>
                                    <span class="field-hint bad" data-for="name">@error('name'){{ $message }}@enderror</span>
                                </div>

                                {{-- Email --}}
                                <div class="form-login">
                                    <label for="email">Email address</label>
                                    <div class="form-addons">
                                        <input type="email" id="email" name="email" class="form-control"
                                               placeholder="you@example.com" autocomplete="email" value="{{ old('email') }}">
                                        <img src="{{ asset('assets/img/icons/mail.svg') }}" alt="">
                                    </div>
                             <span class="field-hint bad" data-for="email">@error('email'){{ $message }}@enderror</span>
                                </div>

                                <div class="row-2">
                                    <div class="form-login">
                                        <label for="phone">Phone number</label>
                                        <input type="tel" id="phone" name="phone" class="form-control"
                                               placeholder="03XX XXXXXXX" autocomplete="tel" value="{{ old('phone') }}">
                                        <span class="field-hint bad" data-for="phone">@error('phone'){{ $message }}@enderror</span>
                                    </div>
                                </div>

                                {{-- Password --}}
                                <div class="form-login">
                                    <label for="password">Password</label>
                                    <div class="pass-group">
                                        <input type="password" id="password" name="password" class="pass-input"
                                               autocomplete="new-password">
                                        <span class="fas toggle-password fa-eye-slash"></span>
                                    </div>
                                    <div class="strength" id="strength" data-level="0" aria-hidden="true">
                                        <span></span><span></span><span></span><span></span>
                                    </div>
                                    <span class="field-hint bad" data-for="password">@error('password'){{ $message }}@enderror</span>
                                </div>

                                <div class="form-login">
                                    <label for="password_confirmation">Confirm password</label>
                                    <div class="pass-group">
                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                               class="pass-inputs" autocomplete="new-password">
                                        <span class="fas toggle-passwords fa-eye-slash"></span>
                                    </div>
                                    <span class="field-hint" id="match-text" aria-live="polite"></span>
                                </div>

                                <div class="form-login authentication-check">
                                    <div class="custom-control custom-checkbox justify-content-start">
                                        <label class="checkboxs ps-4 mb-0 pb-0 line-height-1">
                                            <input type="checkbox" id="terms" name="terms">
                                            <span class="checkmarks"></span>I agree to the <a href="#" class="hover-a">Terms &amp; Privacy</a>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-login">
                                    <button type="submit" class="btn btn-login">Create account</button>
                                    <p class="form-status" id="form-status" aria-live="polite"></p>
                                </div>

                                <div class="signinform text-center">
                                    <h4>Already have an account? <a href="{{ url('/signin') }}" class="hover-a">Sign in</a></h4>
                                </div>

                                <!-- <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                                </div> -->
                            </div>
                        </form>

                    </div>

                    <div class="login-img">
                        <img src="{{ asset('assets/img/authentication/register02.png') }}" alt="">
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('assets/js/feather.min.js') }}"></script>
        <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/js/theme-script.js') }}"></script>
        <script src="{{ asset('assets/js/script.js') }}"></script>

        ```html
<script>
    (function () {
        'use strict';

        var $ = function (id) {
            return document.getElementById(id);
        };

        var form = $('register-form');
        var pass = $('password');
        var confirmPassword = $('password_confirmation');
        var meter = $('strength');
        var meterText = $('strength-text');
        var matchText = $('match-text');
        var status = $('form-status');

        var labels = [
            'Use at least 8 characters.',
            'Weak',
            'Fair',
            'Good',
            'Strong'
        ];

        // Stop if the registration form is not on this page.
        if (!form) {
            return;
        }

        // Calculate password strength.
        function score(value) {
            var points = 0;

            if (value.length >= 8) points++;
            if (/[a-z]/.test(value) && /[A-Z]/.test(value)) points++;
            if (/\d/.test(value)) points++;
            if (/[^A-Za-z0-9]/.test(value) || value.length >= 12) points++;

            return value.length ? Math.max(points, 1) : 0;
        }

        // Show validation errors beside fields.
        function setError(id, message) {
            var element = $(id);
            var hint = document.querySelector(
                '.field-hint[data-for="' + id + '"]'
            );

            if (element) {
                element.classList.toggle('is-invalid', !!message);
                element.setAttribute('aria-invalid', message ? 'true' : 'false');
            }

            if (hint) {
                hint.textContent = message || '';
                hint.className = 'field-hint' + (message ? ' bad' : '');
            }
        }

        // Update password strength indicator.
        function updateStrength() {
            if (!pass) return 0;

            var value = pass.value;
            var strength = score(value);

            if (meter) {
                meter.setAttribute('data-level', strength);
            }

            if (meterText) {
                meterText.textContent = value
                    ? labels[strength]
                    : labels[0];

                meterText.className = 'field-hint';
            }

            return strength;
        }

        // Check whether both passwords match.
        function checkMatch() {
            if (!pass || !confirmPassword || !matchText) {
                return false;
            }

            if (!confirmPassword.value) {
                matchText.textContent = '';
                matchText.className = 'field-hint';
                return false;
            }

            var matches = confirmPassword.value === pass.value;

            matchText.textContent = matches
                ? 'Passwords match.'
                : 'Passwords do not match.';

            matchText.className =
                'field-hint ' + (matches ? 'ok' : 'bad');

            return matches;
        }

        // Validate fields while the user types.
        if (pass) {
            pass.addEventListener('input', function () {
                updateStrength();

                if (confirmPassword && confirmPassword.value) {
                    checkMatch();
                }
            });
        }

        if (confirmPassword) {
            confirmPassword.addEventListener('input', checkMatch);
        }

        // Validate and submit the registration form.
        form.addEventListener('submit', function (event) {
            var valid = true;

            var name = $('name');
            var email = $('email');
            var phone = $('phone');
            var terms = $('terms');

            // Validate full name.
            if (!name || !name.value.trim()) {
                setError('name', 'Enter your full name.');
                valid = false;
            } else {
                setError('name', '');
            }

            // Validate email address.
            if (
                !email ||
                !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())
            ) {
                setError('email', 'Enter a valid email address.');
                valid = false;
            } else {
                setError('email', '');
            }

            // Validate phone number.
            if (!phone || phone.value.replace(/\D/g, '').length < 10) {
                setError('phone', 'Enter a valid phone number.');
                valid = false;
            } else {
                setError('phone', '');
            }

            // Validate password strength.
            var strength = updateStrength();

            if (!pass || pass.value.length < 8 || strength < 2) {
                if (meterText) {
                    meterText.textContent =
                        'Use at least 8 characters with letters and numbers.';
                    meterText.className = 'field-hint bad';
                }

                valid = false;
            }

            // Validate password confirmation.
            if (!checkMatch()) {
                if (confirmPassword && !confirmPassword.value && matchText) {
                    matchText.textContent = 'Confirm your password.';
                    matchText.className = 'field-hint bad';
                }

                valid = false;
            }

            // Validate terms and privacy agreement.
            if (!terms || !terms.checked) {
                if (status) {
                    status.textContent =
                        'Please accept the Terms & Privacy.';
                    status.className = 'form-status bad';
                }

                valid = false;
            } else if (status) {
                status.textContent = '';
                status.className = 'form-status';
            }

            // Block submission only if validation fails.
            if (!valid) {
                event.preventDefault();
            }
            // If valid, the browser submits the form to Laravel.
        });
    })();
</script>
```
    </body>
</html>