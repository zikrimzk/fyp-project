{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <title>e-PostGrad | {{ $title }}</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=0.9, maximum-scale=1.0, user-scalable=no, minimal-ui">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="-" />
    <meta name="keywords" content="-" />
    <meta name="author" content="ZikriMzk" />

    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/images/logo-test-white.png" type="image/x-icon" />
    <!-- [Font] Family -->
    <link rel="stylesheet" href="../assets/fonts/inter/inter.css" id="main-font-link" />
    <!-- [phosphor Icons] https://phosphoricons.com/ -->
    <link rel="stylesheet" href="../assets/fonts/phosphor/duotone/style.css" />
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <!-- [Feather Icons] https://feathericons.com -->
    <link rel="stylesheet" href="../assets/fonts/feather.css" />
    <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
    <link rel="stylesheet" href="../assets/fonts/fontawesome.css" />
    <!-- [Material Icons] https://fonts.google.com/icons -->
    <link rel="stylesheet" href="../assets/fonts/material.css" />
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/style-preset.css" />
    <link rel="stylesheet" href="../assets/css/landing.css" />

</head>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
    data-pc-theme="light" class="landing-page">


    <div class="auth-main">
        <div class="auth-wrapper v1">
            <div class="auth-form">
                <div class="text-center">
                    <a href=""><img src="../assets/images/logo-utem.PNG" alt="img" class="img-fluid"
                            width="120" height="60" /></a>
                </div>

                <div class="card my-5 shadow shadow-lg">
                    <form action="{{ route('request-reset-password') }}" method="POST" autocomplete="off">
                        @csrf
                        <div class="card-body">
                            <div class="position-relative text-center mt-3 mb-5">
                                <a href="{{ route('main-login') }}"
                                    class="position-absolute start-0 top-50 translate-middle-y btn btn-sm f-16">
                                    <i class="ti ti-arrow-left text-primary fs-3"></i>
                                </a>
                                <h3 class="f-w-500 mb-1">Reset Password</h3>
                            </div>

                            <!-- Start Alert -->
                            <div>
                                @if (session()->has('success'))
                                    <div class="alert alert-success alert-dismissible" role="alert">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="alert-heading">
                                                <i class="fas fa-check-circle"></i>
                                                Success
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
                                        </div>
                                        <p class="mb-0">{{ session('success') }}</p>
                                    </div>
                                @endif
                                @if (session()->has('error'))
                                    <div class="alert alert-danger alert-dismissible" role="alert">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="alert-heading">
                                                <i class="fas fa-info-circle"></i>
                                                Error
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
                                        </div>
                                        <p class="mb-0">{{ session('error') }}</p>
                                    </div>
                                @endif
                            </div>
                            <!-- End Alert -->

                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" placeholder="Email"
                                    name="email" value="{{ old('email') }}" autocomplete="off"
                                    title="Staff or Student Email" />
                                <label for="email">Staff / Student Email</label>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg" title="Login">Request Reset
                                    Password</button>
                            </div>
                        </div>
                    </form>
                </div>


            </div>
        </div>
    </div>

    <!-- Required Js -->
    <script>
        function showpassword(buttonName, txtName, iconName) {
            document.getElementById(buttonName).addEventListener('click', function() {
                const passwordInput = document.getElementById(txtName);
                const icon = document.getElementById(iconName);

                // Toggle password visibility
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text'; // Change to text to show password
                    icon.classList.remove('ti-eye'); // Remove eye icon
                    icon.classList.add('ti-eye-off'); // Add eye-slash icon
                } else {
                    passwordInput.type = 'password'; // Change to password to hide it
                    icon.classList.remove('ti-eye-off'); // Remove eye-slash icon
                    icon.classList.add('ti-eye'); // Add eye icon
                }
            });
        }
        showpassword('show-password', 'password', 'toggle-icon-password');
    </script>
    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../assets/js/fonts/custom-font.js"></script>
    <script src="../assets/js/pcoded.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>

    <script>
        // Prevent pinch-to-zoom
        document.addEventListener('gesturestart', function(e) {
            e.preventDefault();
        });

        // Prevent double-tap zoom
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            let now = new Date().getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>

</body>

</html> --}}



<!DOCTYPE html>
<html lang="en">

<head>
    <title>e-PostGrad | Forgot Password</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="e-PostGrad System - Postgraduate Document Management Portal" />
    <meta name="author" content="ZikriMzk" />
    <link rel="icon" href="../assets/images/logo-test-white.png" type="image/x-icon" />

    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="../assets/fonts/fontawesome.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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
            position: relative;
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


        .top-nav {
            position: absolute;
            top: -3rem;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: start;
          
        }

        .top-nav a {
            background: #ffffff;
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 50px;
            font-size: 0.9rem;
            color: #1e40af;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: 0.2s;
        }

        .top-nav a:hover {
            background: #f9fafb;
            border-color: #9ca3af;
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

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

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
        <div class="top-nav">
            <a href="{{ route('main-login') }}"><i class="ti ti-arrow-left"></i> Back to Login</a>
        </div>
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="university-logo">
                    <img src="../assets/images/logo-utem.PNG" alt="University Teknikal Malaysia Melaka" />
                </div>

                <div class="welcome-section">
                    <h2>Reset Password</h2>
                    <p>Enter your email to request password reset.</p>
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

                <!-- Reset Password Form -->
                <form id="forgot-password-form" action="{{ route('request-reset-password') }}" method="POST"
                    autocomplete="off">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="form-label">Staff / Student Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="Enter your email" value="{{ old('email') }}" required />
                    </div>

                    <button type="submit" class="login-button" id="reset-btn">Request Password Reset</button>
                </form>
            </div>

            <!-- Footer -->
            <div class="footer-info">
                <p>
                    <strong>University Teknikal Malaysia Melaka</strong><br>
                    For technical support, contact b032320063@student.utem.edu.my
                </p>
            </div>
        </div>
    </div>

    <script>
        // Form submission loading state
        document.getElementById('forgot-password-form').addEventListener('submit', function(e) {
            const resetBtn = document.getElementById('reset-btn');
            this.classList.add('loading');
            resetBtn.textContent = 'Processing...';
            resetBtn.disabled = true;
        });

        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.display = 'none';
            }
        }

        // Prevent pinch zoom on mobile
        document.addEventListener('gesturestart', function(e) {
            e.preventDefault();
        });
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            let now = new Date().getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>
</body>

</html>
