<!DOCTYPE html>
<html lang="en">
<head>
    <title>e-PostGrad | Login</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="e-PostGrad System - Postgraduate Document Management Portal" />
    <meta name="keywords" content="postgraduate, research, university, academic, document management" />
    <meta name="author" content="ZikriMzk" />

    <link rel="icon" href="https://example.com/assets/images/logo-test-white.png" type="image/x-icon" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com/">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-primary: rgba(52, 58, 64, 255);
            --color-primary-dark: #212529;
            --color-secondary: #6c757d;
            --color-success: #198754;
            --color-danger: #dc3545;
            --color-white: #ffffff;
            --color-light-gray: #f8f9fa;
            --color-medium-gray: #e9ecef;
            --color-dark-gray: #343a40;
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Basic Reset & Typography */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--color-dark-gray);
            background-color: var(--color-light-gray);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem 1rem;
            line-height: 1.6;
        }

        /* Login Card & Layout */
        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: var(--color-white);
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            text-align: center;
            border: 1px solid var(--color-medium-gray);
            animation: slideUpFadeIn 0.6s ease-out forwards;
        }

        /* Header */
        .login-header {
            padding: 2rem 2rem 1.5rem;
            border-bottom: 1px solid var(--color-medium-gray);
        }

        .university-logo {
            margin-bottom: 1rem;
        }

        .university-logo img {
            width: 120px;
            height: auto;
        }

        .system-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-primary);
            margin-bottom: 0.25rem;
        }

        .system-subtitle {
            font-size: 0.9rem;
            color: var(--color-secondary);
        }

        /* Login Body (Form) */
        .login-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--color-dark-gray);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--color-medium-gray);
            border-radius: 8px;
            font-size: 0.9rem;
            background: var(--color-white);
            color: var(--color-dark-gray);
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(52, 58, 64, 0.1);
        }

        .form-control::placeholder {
            color: var(--color-secondary);
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--color-secondary);
            cursor: pointer;
            padding: 0.25rem;
            font-size: 1rem;
            transition: color 0.2s ease-in-out;
        }

        .password-toggle:hover,
        .password-toggle:focus {
            color: var(--color-dark-gray);
        }

        /* Form Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.85rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-me input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            accent-color: var(--color-primary);
        }

        .remember-me label {
            color: var(--color-dark-gray);
            cursor: pointer;
        }

        .forgot-password {
            color: var(--color-dark-gray);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease-in-out;
        }

        .forgot-password:hover {
            color: var(--color-primary);
            text-decoration: underline;
        }

        /* Button */
        .login-button {
            width: 100%;
            padding: 0.875rem;
            background: var(--color-primary);
            color: var(--color-white);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }

        .login-button:hover {
            background: var(--color-primary-dark);
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .login-button:active {
            transform: scale(0.99);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .login-button:disabled {
            background: var(--color-secondary);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            opacity: 0;
            transform: translateY(-10px);
            animation: slideInFadeIn 0.3s ease-out forwards;
            font-weight: 500;
        }

        .alert-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 28px;
            height: 28px;
        }

        .alert-icon-wrapper .icon {
            width: 1.5em;
            height: 1.5em;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: var(--color-success);
            border: 1px solid #badbcc;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: var(--color-danger);
            border: 1px solid #f5c2c7;
        }

        .alert-close {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 1.2rem;
            margin-left: auto;
            opacity: 0.7;
            transition: opacity 0.2s ease-in-out;
        }

        .alert-close:hover {
            opacity: 1;
        }

        /* Footer */
        .footer-info {
            background: var(--color-light-gray);
            border-top: 1px solid var(--color-medium-gray);
            padding: 1.5rem 2rem;
            font-size: 0.8rem;
            color: var(--color-secondary);
            line-height: 1.5;
        }

        .footer-info a {
            color: var(--color-primary);
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 0.25rem;
            transition: color 0.2s ease-in-out;
        }

        .footer-info a:hover {
            text-decoration: underline;
            color: var(--color-primary-dark);
        }

        /* Loading State */
        .loading .login-button {
            background: var(--color-secondary);
            cursor: not-allowed;
            position: relative;
            pointer-events: none;
            transform: none !important;
        }

        .loading .login-button::after {
            content: '';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%) rotate(0deg);
            width: 1rem;
            height: 1rem;
            border: 2px solid transparent;
            border-top: 2px solid var(--color-white);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }

        /* Animations */
        @keyframes slideUpFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInFadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <header class="login-header">
                <div class="university-logo">
                    <img src="../assets/images/logo-utem.PNG" alt="University Teknikal Malaysia Melaka" />
                </div>
                <h1 class="system-title">e-PostGrad System</h1>
                <p class="system-subtitle">Please sign in to your account to access the system.</p>
            </header>

            <main class="login-body">
                <div id="alert-container">
                    @if (session()->has('success'))
                        <div class="alert alert-success" id="success-alert">
                            <span class="alert-icon-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M5 12l5 5l10 -10"></path>
                                </svg>
                            </span>
                            <span id="success-message">{{ session('success') }}</span>
                            <button type="button" class="alert-close" onclick="closeAlert('success-alert')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M18 6l-12 12"></path>
                                    <path d="M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert alert-danger" id="error-alert">
                            <span class="alert-icon-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M12 9v4"></path>
                                    <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.875h16.214a1.914 1.914 0 0 0 1.636 -2.875l-8.106 -13.534a1.914 1.914 0 0 0 -3.274 0z"></path>
                                    <path d="M12 16h.01"></path>
                                </svg>
                            </span>
                            <span id="error-message">{{ session('error') }}</span>
                            <button type="button" class="alert-close" onclick="closeAlert('error-alert')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M18 6l-12 12"></path>
                                    <path d="M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                <form id="login-form" action="{{ route('user-authenticate') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your student / staff email" value="{{ old('email') }}" required />
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required />
                            <button type="button" class="password-toggle" id="password-toggle" aria-label="Toggle password visibility">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" id="password-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                                    <path d="M21 12c-2.761 4.51 -6.828 7 -10.5 7c-4.145 0 -7.215 -2.31 -10.5 -7c2.761 -4.51 6.828 -7 10.5 -7c4.145 0 7.215 2.31 10.5 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember" />
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="{{ route('forgot-password') }}" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit" class="login-button" id="login-btn">
                        Sign in
                    </button>
                </form>
            </main>

            <footer class="footer-info">
                <p>
                    <strong>Universiti Teknikal Malaysia Melaka</strong><br>
                    For technical support, contact <a href="mailto:e-postgrad@appnest.my">e-postgrad@appnest.my</a>
                </p>
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const passwordToggle = document.getElementById('password-toggle');
            const passwordInput = document.getElementById('password');
            const loginForm = document.getElementById('login-form');
            const loginBtn = document.getElementById('login-btn');

            if (passwordToggle) {
                passwordToggle.addEventListener('click', function() {
                    const passwordIcon = this.querySelector('.icon');
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        passwordIcon.innerHTML = `<path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"></path><path d="M16.681 16.673a8.974 8.974 0 0 1 -4.681 1.327c-3.674 0 -7.215 -2.31 -10.5 -7c1.472 -2.215 3.593 -4.01 5.894 -5.461"></path><path d="M18.843 14.869c.808 -.816 1.411 -1.693 1.916 -2.869c-2.761 -4.51 -6.828 -7 -10.5 -7a8.983 8.983 0 0 0 -5.025 1.558"></path><path d="M3 3l18 18"></path>`;
                    } else {
                        passwordInput.type = 'password';
                        passwordIcon.innerHTML = `<path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M21 12c-2.761 4.51 -6.828 7 -10.5 7c-4.145 0 -7.215 -2.31 -10.5 -7c2.761 -4.51 6.828 -7 10.5 -7c4.145 0 7.215 2.31 10.5 7"></path>`;
                    }
                });
            }

            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    if (loginBtn) {
                        loginBtn.classList.add('loading');
                        loginBtn.textContent = 'Authenticating...';
                        loginBtn.disabled = true;
                    }
                });
            }

            window.closeAlert = function(alertId) {
                const alertElement = document.getElementById(alertId);
                if (alertElement) {
                    alertElement.style.animation = 'none';
                    alertElement.style.opacity = '0';
                    alertElement.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        alertElement.style.display = 'none';
                    }, 300);
                }
            };

            const successAlert = document.getElementById('success-alert');
            const errorAlert = document.getElementById('error-alert');

            if (successAlert) {
                setTimeout(() => closeAlert('success-alert'), 5000);
            }
            if (errorAlert) {
                setTimeout(() => closeAlert('error-alert'), 5000);
            }
        });
    </script>
</body>
</html>