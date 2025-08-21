<!DOCTYPE html>
<html lang="en">

<head>
    <title>e-PostGrad | Login</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.9, user-scalable=no">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="e-PostGrad System - Postgraduate Document Management Portal" />
    <meta name="keywords" content="postgraduate, research, university, academic, document management" />
    <meta name="author" content="ZikriMzk" />

    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/images/logo-test-white.png" type="image/x-icon" />

    <!-- [Fonts] -->
    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/images/logo-test-white.png" type="image/x-icon" />
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
    <link rel="stylesheet" href="../assets/fonts/fontawesome.css" />
    <!-- [Material Icons] https://fonts.google.com/icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- [Icons] -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/1.35.0/tabler-icons.min.css">

    <style>
        :root {
            --primary-color: #1e40af;
            --primary-dark: #1e3a8a;
            --secondary-color: #374151;
            --success-color: #166534;
            --danger-color: #b91c1c;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --border-color: #d1d5db;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--gray-100);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            color: var(--gray-800);
        }

        .login-container {
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .login-header {
            background: var(--white);
            /* padding: 1.5rem 1rem 1rem; */
            text-align: center;
            /* border-bottom: 1px solid var(--gray-200); */
        }

        .university-logo {
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .university-logo img {
            width: 100px;
            height: auto;
        }

        .system-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }

        .system-subtitle {
            font-size: 1rem;
            color: var(--gray-600);
            font-weight: 500;
            line-height: 1.5;
        }

        .login-body {
            padding: 2.5rem 2rem;
        }

        .welcome-section {
            /* margin-bottom: 1rem; */
            text-align: center;
        }

        .welcome-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            color: var(--gray-600);
            font-size: 0.75rem;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.95rem;
            background: var(--white);
            color: var(--gray-900);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        .form-control::placeholder {
            color: var(--gray-400);
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 0.25rem;
            font-size: 1.1rem;
            transition: color 0.2s ease;

        }

        .password-toggle:hover {
            color: var(--gray-600);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-me input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            accent-color: var(--primary-color);
        }

        .remember-me label {
            font-size: 0.9rem;
            color: var(--gray-700);
            cursor: pointer;
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .forgot-password:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 1rem;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .login-button:hover {
            background: var(--primary-dark);
        }

        .login-button:active {
            transform: translateY(1px);
        }

        .login-button:disabled {
            background: var(--gray-400);
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            line-height: 1.5;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: var(--success-color);
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: var(--danger-color);
            border: 1px solid #fecaca;
        }

        .alert-close {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 1.2rem;
            margin-left: auto;
            opacity: 0.7;
            transition: opacity 0.2s ease;
            padding: 0;
            line-height: 1;
        }

        .alert-close:hover {
            opacity: 1;
        }

        .footer-info {
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
            padding: 1.5rem 2rem;
            text-align: center;
        }

        .footer-info p {
            color: var(--gray-500);
            font-size: 0.85rem;
            line-height: 1.5;
            margin: 0;
        }

        .institutional-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--gray-50);
            color: var(--gray-600);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 1rem;
            border: 1px solid var(--gray-200);
        }

        /* Loading state */
        .loading .login-button {
            background: var(--gray-400);
            cursor: not-allowed;
            position: relative;
        }

        .loading .login-button::after {
            content: '';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1rem;
            height: 1rem;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }

            .login-header,
            .login-body {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            .login-header {
                padding-top: 2rem;
                padding-bottom: 1.5rem;
            }

            .login-body {
                padding-top: 2rem;
                padding-bottom: 2rem;
            }

            /*
            .form-options {
                flex-direction: column;
                align-items:fl;
                gap: 0.75rem;
            } */

            .system-title {
                font-size: 1.75rem;
            }
        }

        /* Focus styles for accessibility */
        .form-control:focus,
        .login-button:focus,
        .forgot-password:focus,
        .password-toggle:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="university-logo">
                    <img src="../assets/images/logo-utem.PNG" alt="University Teknikal Malaysia Melaka" />
                </div>

                <div class="welcome-section">
                    <h2>e-PostGrad System</h2>
                    <p>Please sign in to your account to access the system.</p>
                </div>

            </div>

            <!-- Body -->
            <div class="login-body">
                <!-- Alerts -->
                <div id="alert-container">
                    @if (session()->has('success'))
                        <div class="alert alert-success d-flex align-items-center" id="success-alert">
                            <div>
                                <i class="fas fa-check-circle"></i>

                                <span id="success-message">{{ session('success') }}</span>
                            </div>
                            <button type="button" class="alert-close" onclick="closeAlert('success-alert')">×</button>
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert alert-danger d-flex align-items-center" id="error-alert">
                            <div>
                                <i class="fas fa-info-circle"></i>

                                <span id="error-message">{{ session('error') }}</span>
                            </div>
                            <button type="button" class="alert-close" onclick="closeAlert('error-alert')">×</button>
                        </div>
                    @endif
                </div>

                <!-- Login Form -->
                <form id="login-form" action="{{ route('user-authenticate') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="Enter your student / staff email" value="{{ old('email') }}"
                            autocomplete="email" required />
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Enter your password" autocomplete="current-password" required />
                            <button type="button" class="password-toggle" id="password-toggle"
                                aria-label="Toggle password visibility">
                                <i class="ti ti-eye" id="password-icon"></i>
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
            </div>

            <!-- Footer -->
            <div class="footer-info">
                <p>
                    <strong>University Teknikal Malaysia Melaka</strong><br>
                    For technical support, contact 
                    <a href="mailto:e-postgrad@appnest.my" style="text-decoration: none; color: var(--primary-color); display: block;">e-postgrad@appnest.my</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        document.getElementById('password-toggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'ti ti-eye-off';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'ti ti-eye';
            }
        });

        // Form submission handling
        document.getElementById('login-form').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('login-btn');
            const form = this;

            // Add loading state
            form.classList.add('loading');
            loginBtn.textContent = 'Authenticating...';
            loginBtn.disabled = true;
        });

        // Alert handling
        function showAlert(type, message) {
            const alertId = type + '-alert';
            const messageId = type + '-message';
            const alert = document.getElementById(alertId);
            const messageSpan = document.getElementById(messageId);

            if (alert && messageSpan) {
                messageSpan.textContent = message;
                alert.style.display = 'flex';

                setTimeout(() => {
                    closeAlert(alertId);
                }, 5000);
            }
        }

        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.display = 'none';
            }
        }

        // Prevent zoom on mobile
        document.addEventListener('gesturestart', function(e) {
            e.preventDefault();
        });

        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>
</body>

</html>
