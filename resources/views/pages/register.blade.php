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

                        <form action="#" id="register-form" novalidate>
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
                                               placeholder="e.g. Ahmed Khan" autocomplete="name">
                                        <img src="{{ asset('assets/img/icons/user-icon.svg') }}" alt="">
                                    </div>
                                    <span class="field-hint" data-for="name"></span>
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

                                {{-- POS details --}}
                                <div class="form-login">
                                    <label for="shop_name">Shop / business name</label>
                                    <input type="text" id="shop_name" name="shop_name" class="form-control"
                                           placeholder="e.g. Khan General Store" autocomplete="organization">
                                    <span class="field-hint" data-for="shop_name"></span>
                                </div>

                                <div class="row-2">
                                    <div class="form-login">
                                        <label for="phone">Phone number</label>
                                        <input type="tel" id="phone" name="phone" class="form-control"
                                               placeholder="03XX XXXXXXX" autocomplete="tel">
                                        <span class="field-hint" data-for="phone"></span>
                                    </div>
                                    <div class="form-login">
                                        <label for="role">Your role</label>
                                        <select id="role" name="role">
                                            <option value="">Select role</option>
                                            <option value="owner">Owner</option>
                                            <option value="manager">Manager</option>
                                            <option value="cashier">Cashier</option>
                                            <option value="stock">Stock keeper</option>
                                        </select>
                                        <span class="field-hint" data-for="role"></span>
                                    </div>
                                </div>

                                <div class="row-2">
                                    <div class="form-login">
                                        <label for="store">Store / branch</label>
                                        <input type="text" id="store" name="store" class="form-control"
                                               placeholder="e.g. Main branch">
                                    </div>
                                    <div class="form-login">
                                        <label for="currency">Currency</label>
                                        <select id="currency" name="currency">
                                            <option value="PKR" selected>PKR - Rupee</option>
                                            <option value="USD">USD - Dollar</option>
                                            <option value="AED">AED - Dirham</option>
                                            <option value="SAR">SAR - Riyal</option>
                                        </select>
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
                                    <span class="field-hint" id="strength-text">Use at least 8 characters.</span>
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

        <script>
            (function () {
                var $ = function (id) { return document.getElementById(id); };
                var form = $('register-form');
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
                    checkMatch();
                });
                confirm.addEventListener('input', checkMatch);

                function setError(id, text) {
                    var el = $(id);
                    var hint = document.querySelector('.field-hint[data-for="' + id + '"]');
                    el.classList.toggle('is-invalid', !!text);
                    if (hint) { hint.textContent = text || ''; hint.className = 'field-hint' + (text ? ' bad' : ''); }
                }

                // Design preview only: checks the fields, does not save anything
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var valid = true;

                    if (!$('name').value.trim()) { setError('name', 'Enter your full name.'); valid = false; } else setError('name', '');
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($('email').value.trim())) { setError('email', 'Enter a valid email address.'); valid = false; } else setError('email', '');
                    if (!$('shop_name').value.trim()) { setError('shop_name', 'Enter your shop or business name.'); valid = false; } else setError('shop_name', '');
                    if ($('phone').value.replace(/\D/g, '').length < 10) { setError('phone', 'Enter a valid phone number.'); valid = false; } else setError('phone', '');
                    if (!$('role').value) { setError('role', 'Select your role.'); valid = false; } else setError('role', '');

                    if (score(pass.value) < 2 || pass.value.length < 8) { meterText.textContent = 'Use at least 8 characters with letters and numbers.'; meterText.className = 'field-hint bad'; valid = false; } else { meterText.className = 'field-hint'; meterText.textContent = labels[score(pass.value)]; }
                    if (!checkMatch()) { if (!confirm.value) { matchText.textContent = 'Confirm your password.'; matchText.className = 'field-hint bad'; } valid = false; }

                    if (!$('terms').checked) { status.textContent = 'Please accept the Terms & Privacy.'; valid = false; }
                    else if (valid) status.textContent = 'Form looks good. Saving is not connected yet (design preview).';
                    else status.textContent = '';
                });
            })();
        </script>
    </body>
</html>