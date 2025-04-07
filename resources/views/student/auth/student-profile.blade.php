@extends('student.layouts.main')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">My Profile</li>
                                <li class="breadcrumb-item" aria-current="page"><a href="javascript: void(0)"
                                        class="text-capitalize">{{ auth()->user()->student_name }}</a></li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">My Profile</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- [ Alert ] start -->
            <div>
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="alert-heading">
                                <i class="fas fa-check-circle"></i>
                                Success
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <p class="mb-0">{{ session('error') }}</p>
                    </div>
                @endif
            </div>
            <!-- [ Alert ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- [ Profile ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body py-0">
                            <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ session('active_tab', 'profile-1') == 'profile-1' ? 'active' : '' }}"
                                        id="profile-tab-1" data-bs-toggle="tab" href="#profile-1" role="tab">
                                        <i class="ti ti-user me-2"></i>Personal Details
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ session('active_tab') == 'profile-2' ? 'active' : '' }}"
                                        id="profile-tab-2" data-bs-toggle="tab" href="#profile-2" role="tab">
                                        <i class="ti ti-lock me-2"></i>Change Password
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="tab-content">

                        <!-- Personal Details Tab Start -->
                        <div class="tab-pane fade {{ session('active_tab', 'profile-1') == 'profile-1' ? 'show active' : '' }} "
                            id="profile-1" role="tabpanel" aria-labelledby="profile-tab-1">
                            <form action="{{ route('update-student-profile') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h5>Personal Details</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-4">
                                                    <!-- Photo Input -->
                                                    <div class="col-sm-12 col-md-4 col-lg-4">
                                                        <div class="d-grid justify-content-center align-items-center mb-3">
                                                            <div class="user-upload avatar-s w-100">

                                                                <img src="{{ empty(auth()->user()->student_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . auth()->user()->student_directory . '/photo/' . auth()->user()->student_photo) }}"
                                                                    alt="Profile Photo" width="150" height="150"
                                                                    class="previewImageAdd"
                                                                    data-default="{{ asset('assets/images/user/default-profile-1.jpg') }}">

                                                                <label for="student_photo" class="img-avtar-upload">
                                                                    <i class="ti ti-camera f-24 mb-1"></i>
                                                                    <span>Upload</span>
                                                                </label>

                                                                <input type="file" id="student_photo"
                                                                    name="student_photo" class="d-none" accept="image/*" />
                                                            </div>
                                                            <label for="student_photo"
                                                                class="btn btn-sm btn-secondary mt-2 mb-2">
                                                                Change Photo
                                                            </label>
                                                            <button type="button" id="resetPhoto"
                                                                class="btn btn-sm btn-light-danger">
                                                                Reset Photo
                                                            </button>
                                                            <input type="hidden" name="remove_photo" id="remove_photo"
                                                                value="0">
                                                        </div>
                                                        @error('student_photo')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <!-- Personal Information Section Start -->
                                                    <div class="col-md-8">
                                                        <div class="row">
                                                            <!-- Name Input -->
                                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                                <div class="mb-3">
                                                                    <label for="student_name" class="form-label">Student
                                                                        Name</label>
                                                                    <input type="text"
                                                                        class="form-control @error('student_name') is-invalid @enderror"
                                                                        id="student_name" name="student_name"
                                                                        placeholder="Enter Student Name"
                                                                        value="{{ auth()->user()->student_name }}"
                                                                        required>
                                                                    @error('student_name')
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <!-- Email Input -->
                                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="student_email"
                                                                        class="form-label">Email</label>
                                                                    <input type="email" class="form-control"
                                                                        id="student_email" name="student_email"
                                                                        placeholder="Enter Student Email"
                                                                        value="{{ auth()->user()->student_email }}"
                                                                        readonly>
                                                                </div>
                                                            </div>
                                                            <!-- Phone No Input -->
                                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="student_phoneno" class="form-label">
                                                                        Phone Number
                                                                    </label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-text">+60</span>
                                                                        <input type="text"
                                                                            class="form-control @error('student_phoneno') is-invalid @enderror phonenum-input"
                                                                            placeholder="Enter Phone Number"
                                                                            name="student_phoneno"
                                                                            value="{{ auth()->user()->student_phoneno }}"
                                                                            maxlength="11" />
                                                                        @error('student_phoneno')
                                                                            <div class="invalid-feedback">{{ $message }}
                                                                            </div>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Address Input -->
                                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                                <div class="mb-3">
                                                                    <label for="student_address_up" class="form-label">
                                                                        Address
                                                                    </label>
                                                                    <textarea name="student_address" id="student_address" placeholder="Enter Address" cols="10"
                                                                        rows="5" class="form-control @error('student_address') is-invalid @enderror">{{ auth()->user()->student_address }}</textarea>
                                                                    @error('student_address')
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <hr>

                                                            <!-- Matric No Input -->
                                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="student_matricno"
                                                                        class="form-label">Matric No
                                                                    </label>
                                                                    <input type="text" class="form-control"
                                                                        id="student_matricno" name="student_matricno"
                                                                        placeholder="Enter Matric No"
                                                                        value="{{ auth()->user()->student_matricno }}"
                                                                        readonly>
                                                                </div>
                                                            </div>
                                                            <!--Semester Input-->
                                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="semester_id" class="form-label">Semester
                                                                        Registered
                                                                    </label>
                                                                    <input type="text" class="form-control"
                                                                        id="semester_id" name="semester_id"
                                                                        placeholder="Semester Registered"
                                                                        value="{{ auth()->user()->semesters->sem_label }}"
                                                                        readonly>
                                                                </div>
                                                            </div>
                                                            <!--Programme Input-->
                                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="programme_id" class="form-label">Programme
                                                                    </label>
                                                                    <input type="text" class="form-control"
                                                                        id="programme_id" name="programme_id"
                                                                        placeholder="Student Programme"
                                                                        value="{{ auth()->user()->programmes->prog_code }} [{{ auth()->user()->programmes->prog_mode }}]"
                                                                        readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Personal Information Section End -->
                                                </div>
                                            </div>
                                            <div class="card-footer text-end btn-page">
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </form>
                        </div>
                        <!-- Personal Details Tab End -->

                        <!-- Update Password Tab Start -->
                        <div class="tab-pane fade {{ session('active_tab') == 'profile-2' ? 'show active' : '' }}"
                            id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">
                            <form action="{{ route('update-student-password') }}" method="POST">
                                @csrf
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Change Password</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Old Password
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group mb-3">
                                                        <input type="password"
                                                            class="form-control @error('oldPass') is-invalid @enderror"
                                                            name="oldPass" id="oldpassword"
                                                            placeholder="Enter Old Password" required />
                                                        <button class="btn btn-light border border-1 border-secondary"
                                                            type="button" id="show-old-password">
                                                            <i id="toggle-icon-old-password" class="ti ti-eye"></i>
                                                        </button>
                                                        @error('oldPass')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">New Password
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group mb-3">
                                                        <input type="password"
                                                            class="form-control @error('newPass') is-invalid @enderror"
                                                            id="passwords" name="newPass"
                                                            placeholder="Enter New Password" required />
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
                                                            name="renewPass" id="cpassword"
                                                            placeholder="Enter Confirm Password" required />
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
                                            <div class="col-sm-6">
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
                                    </div>
                                    <div class="card-footer text-end btn-page">
                                        <button type="submit" class="btn btn-primary disabled" id="submit-btn">Update
                                            Password</button>
                                    </div>
                                </div>

                            </form>

                        </div>
                        <!-- Update Password Tab End -->

                    </div>
                </div>
                <!-- [ Profile ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {

            var activeTab = "{{ session('active_tab', 'profile-1') }}";
            $('.nav-link[href="#' + activeTab + '"]').tab('show');

            // STAFF PHOTO FUNCTIONS
            var defaultImageAdd = $(".previewImageAdd").data("default");


            $('#student_photo').on('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        $('.previewImageAdd').attr('src', e.target.result).show();
                    };

                    reader.readAsDataURL(file);
                }
            });

            $("#resetPhoto").on("click", function() {
                $(".previewImageAdd").attr("src", defaultImageAdd);
                $("#student_photo").val("");
                $('#remove_photo').val("1");
            });

            // FORMATTING
            $('.phonenum-input').on('input', function() {
                let original = $(this).val();
                let numericOnly = original.replace(/\D/g, '');

                if (numericOnly.length > 11) {
                    numericOnly = numericOnly.substring(0, 11);
                }

                $(this).val(numericOnly);
            });

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
            showPassword('show-old-password', 'oldpassword', 'toggle-icon-old-password');
            showPassword('show-password', 'passwords', 'toggle-icon-password');
            showPassword('show-password-confirm', 'cpassword', 'toggle-icon-confirm-password');


        });
    </script>
@endsection
