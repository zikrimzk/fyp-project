<!DOCTYPE html>
<html lang="en">

<head>
    <title>e-PostGrad | {{ $title }}</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


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
                    <form
                        action="{{ route('reset-password', ['token' => $token, 'email' => Crypt::encrypt($email), 'userType' => $userType]) }}"
                        method="POST" autocomplete="off">
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

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="mb-3">
                                        <label class="form-label">New Password
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group mb-3">
                                            <input type="password"
                                                class="form-control @error('newPass') is-invalid @enderror"
                                                id="passwords" name="newPass" placeholder="Enter New Password"
                                                required />
                                            <button class="btn btn-light border border-1 border-secondary"
                                                type="button" id="show-password">
                                                <i id="toggle-icon-password" class="ti ti-eye"></i>
                                            </button>
                                            @error('newPass')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Confirm Password
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group mb-3">
                                            <input type="password"
                                                class="form-control @error('cpassword') is-invalid @enderror"
                                                name="renewPass" id="cpassword" placeholder="Enter Confirm Password"
                                                required />
                                            <button class="btn btn-light border border-1 border-secondary"
                                                type="button" id="show-password-confirm">
                                                <i id="toggle-icon-confirm-password" class="ti ti-eye"></i>
                                            </button>
                                            @error('cpassword')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                    </div>

                                </div>

                                <div class="col-sm-12">
                                    <h5 class="mb-3">New password must contain:</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex align-items-center justify-content-between"
                                            id="min-char">
                                            <span><i class="me-2"></i> At least <strong>8
                                                    characters</strong></span>
                                            <span class="badge bg-secondary">Required</span>
                                        </li>
                                        <li class="list-group-item d-flex align-items-center justify-content-between"
                                            id="lower-char">
                                            <span><i class="me-2"></i> At least <strong>1 lowercase
                                                    letter</strong> (a-z)</span>
                                            <span class="badge bg-secondary">Required</span>
                                        </li>
                                        <li class="list-group-item d-flex align-items-center justify-content-between"
                                            id="upper-char">
                                            <span><i class="me-2"></i> At least <strong>1 uppercase
                                                    letter</strong> (A-Z)</span>
                                            <span class="badge bg-secondary">Required</span>
                                        </li>
                                        <li class="list-group-item d-flex align-items-center justify-content-between"
                                            id="number-char">
                                            <span><i class="me-2"></i> At least <strong>1 number</strong>
                                                (0-9)</span>
                                            <span class="badge bg-secondary">Required</span>
                                        </li>
                                        <li class="list-group-item d-flex align-items-center justify-content-between"
                                            id="special-char">
                                            <span><i class="me-2"></i> At least <strong>1 special
                                                    character</strong> (!@#$...)</span>
                                            <span class="badge bg-secondary">Required</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit"
                                    class="btn btn-primary btn-lg d-flex align-items-center justify-content-center disabled"
                                    title="Login" id="submit-btn">
                                    Update Password
                                    <i class="ti ti-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>


            </div>
        </div>
    </div>

    <!-- Required Js -->
    <script type="text/javascript">
        $(document).ready(function() {
            // UPDATE PASSWORD FUNCTIONS
            $('#passwords').on('input', function() {
                const password = $(this).val();
                const confirmPasswordInput = $('#cpassword');
                const submitBtn = $('#submit-btn');

                // Requirements
                const minChar = /.{8,}/;
                const lowerChar = /[a-z]/;
                const upperChar = /[A-Z]/;
                const numberChar = /[0-9]/;
                const specialChar = /[!@#$%^&*(),.?":{}|<>]/;

                // Validate each requirement
                function validateRequirement(regex, elementId) {
                    const $el = $('#' + elementId);
                    const $icon = $el.find('i');
                    const $badge = $el.find('.badge');

                    if (regex.test(password)) {
                        $icon.removeClass().addClass('ti ti-circle-check text-success f-16');
                        $badge.removeClass('bg-secondary').addClass('bg-success').text('Valid');
                    } else {
                        $icon.removeClass().addClass('ti ti-circle-x text-danger f-16');
                        $badge.removeClass('bg-success').addClass('bg-secondary').text('Required');
                    }
                }

                validateRequirement(minChar, 'min-char');
                validateRequirement(lowerChar, 'lower-char');
                validateRequirement(upperChar, 'upper-char');
                validateRequirement(numberChar, 'number-char');
                validateRequirement(specialChar, 'special-char');

                // All requirements met
                const allValid = minChar.test(password) &&
                    lowerChar.test(password) &&
                    upperChar.test(password) &&
                    numberChar.test(password) &&
                    specialChar.test(password);

                if (allValid) {
                    confirmPasswordInput.prop('disabled', false);
                    checkPasswordsMatch();
                } else {
                    submitBtn.addClass('disabled');
                    confirmPasswordInput.prop('disabled', true);
                }

                // Check passwords match
                function checkPasswordsMatch() {
                    const confirmPassword = confirmPasswordInput.val();
                    if (password === confirmPassword) {
                        submitBtn.removeClass('disabled');
                    } else {
                        submitBtn.addClass('disabled');
                    }
                }
            });

            // Confirm password input event
            $('#cpassword').on('input', function() {
                const password = $('#passwords').val();
                const confirmPassword = $(this).val();
                const submitBtn = $('#submit-btn');

                if (password === confirmPassword) {
                    submitBtn.removeClass('disabled');
                } else {
                    submitBtn.addClass('disabled');
                }
            });

            // Toggle password visibility
            function showPassword(buttonId, inputId, iconId) {
                $('#' + buttonId).on('click', function() {
                    const input = $('#' + inputId);
                    const icon = $('#' + iconId);

                    if (input.attr('type') === 'password') {
                        input.attr('type', 'text');
                        icon.removeClass('ti-eye').addClass('ti-eye-off');
                    } else {
                        input.attr('type', 'password');
                        icon.removeClass('ti-eye-off').addClass('ti-eye');
                    }
                });
            }

            // Apply showPassword toggles
            showPassword('show-password', 'passwords', 'toggle-icon-password');
            showPassword('show-password-confirm', 'cpassword', 'toggle-icon-confirm-password');
        });
    </script>
    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../assets/js/fonts/custom-font.js"></script>
    <script src="../assets/js/pcoded.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>

</body>

</html>
