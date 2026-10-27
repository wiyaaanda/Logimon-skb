<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SKBtrans Monitoring</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="login-page-body">
        <div class="auth-shell">
            <div class="auth-card">
                <div class="auth-head">
                    <div class="auth-logo" aria-label="SKB Trans logo">
                        <img src="{{ asset('images/logo.png') }}" alt="SKB Trans" class="auth-logo-img" />
                    </div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger mb-3" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="auth-form">
                    @csrf
                    <div class="field-group">
                        <label class="form-label" for="username">Username</label>
                        <input id="username" type="text" class="form-control" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required autofocus autocomplete="username">
                    </div>

                    <div class="field-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-wrap">
                            <input id="password" type="password" class="form-control" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                            <button type="button" class="toggle-password" aria-label="Tampilkan password" aria-pressed="false">
                                <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn w-100 btn-submit"><span>Login</span></button>
                </form>

                <div class="auth-footer">
                    <a href="{{ route('welcome') }}" class="back-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const toggleButton = document.querySelector('.toggle-password');
                const passwordInput = document.getElementById('password');

                if (!toggleButton || !passwordInput) return;

                toggleButton.addEventListener('click', function () {
                    const isHidden = passwordInput.type === 'password';
                    passwordInput.type = isHidden ? 'text' : 'password';

                    toggleButton.setAttribute('aria-pressed', String(isHidden));
                    toggleButton.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');

                    // Ganti ikon SVG antara mata terbuka dan tertutup
                    toggleButton.innerHTML = isHidden
                        ? `<svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`
                        : `<svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
                });
            });
        </script>
    </body>
</html>
