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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">[ Student Name ]</a></li>
                                <li class="breadcrumb-item" aria-current="page">My Profile</li>
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

                <!-- [ Dashboard ] start -->

                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body py-0">
                            <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ session('active_tab', 'profile-1') == 'profile-1' ? 'active' : '' }}"
                                        id="profile-tab-1" data-bs-toggle="tab" href="#profile-1" role="tab">
                                        <i class="ti ti-file-text me-2"></i>Personal Details
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
                            <form action="" method="POST" enctype="multipart/form-data">
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

                                                                <img src="{{ asset('assets/images/user/default-profile-1.jpg') }}"
                                                                    alt="Profile Photo" width="150" height="150"
                                                                    class="previewImageAdd"
                                                                    data-default="{{ asset('assets/images/user/default-profile-1.jpg') }}">

                                                                <label for="staff_photo" class="img-avtar-upload">
                                                                    <i class="ti ti-camera f-24 mb-1"></i>
                                                                    <span>Upload</span>
                                                                </label>

                                                                <input type="file" id="staff_photo" name="staff_photo"
                                                                    class="d-none" accept="image/*" />
                                                            </div>
                                                            <label for="staff_photo"
                                                                class="btn btn-sm btn-secondary mt-2 mb-2">
                                                                Change Photo
                                                            </label>
                                                            <button type="button" id="resetPhoto"
                                                                class="btn btn-sm btn-light-danger">
                                                                Reset Photo
                                                            </button>
                                                        </div>
                                                        @error('staff_photo')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <!-- Personal Information Section Start -->
                                                    <div class="col-md-8">
                                                        <div class="row">
                                                            <!-- Name Input -->
                                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="staff_name" class="form-label">Staff Name</label>
                                                                    <input type="text"
                                                                        class="form-control @error('staff_name') is-invalid @enderror"
                                                                        id="staff_name" name="staff_name"
                                                                        placeholder="Enter Staff Name"
                                                                        value="{{ old('staff_name') }}" required>
                                                                    @error('staff_name')
                                                                        <div class="invalid-feedback">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <!-- Email Input -->
                                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="staff_email"
                                                                        class="form-label">Email</label>
                                                                    <input type="email" class="form-control"
                                                                        id="staff_email" name="staff_email"
                                                                        placeholder="Enter Staff Email"
                                                                        value="{{ old('staff_email') }}" readonly>
                                                                </div>
                                                            </div>
                                                            <!-- Phone No Input -->
                                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="staff_phoneno" class="form-label">
                                                                        Phone Number
                                                                    </label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-text">+60</span>
                                                                        <input type="text"
                                                                            class="form-control @error('staff_phoneno') is-invalid @enderror phonenum-input"
                                                                            placeholder="Enter Phone Number"
                                                                            name="staff_phoneno"
                                                                            value="{{ old('staff_phoneno') }}"
                                                                            maxlength="11" />
                                                                        @error('staff_phoneno')
                                                                            <div class="invalid-feedback">{{ $message }}
                                                                            </div>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <!-- Staff ID Input -->
                                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="staff_id" class="form-label">Staff ID
                                                                    </label>
                                                                    <input type="text" class="form-control"
                                                                        id="staff_id" name="staff_id"
                                                                        placeholder="Enter Staff ID"
                                                                        value="{{ old('staff_id') }}" readonly>
                                                                </div>
                                                            </div>
                                                            <!--Department Input-->
                                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="department_id"
                                                                        class="form-label">Department
                                                                    </label>
                                                                    <input type="text" class="form-control"
                                                                        id="department_id" name="department_id"
                                                                        placeholder="Staff Department"
                                                                        value="{{ old('department_id') }}" readonly>
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
                            <form action="" method="POST">
                                @csrf
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Change Password</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Old Password</label>
                                                    <div class="input-group mb-3">
                                                        <input type="password"
                                                            class="form-control @error('oldPass') is-invalid @enderror"
                                                            name="oldPass" id="oldpassword" />
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
                                                    <label class="form-label">New Password</label>
                                                    <div class="input-group mb-3">
                                                        <input type="password"
                                                            class="form-control @error('newPass') is-invalid @enderror"
                                                            id="passwords" name="newPass" />
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
                                                    <label class="form-label">Confirm Password</label>
                                                    <div class="input-group mb-3">
                                                        <input type="password"
                                                            class="form-control @error('cpassword') is-invalid @enderror"
                                                            name="renewPass" id="cpassword" />
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
                                                <h5>New password must contain:</h5>
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item" id="min-char"><i></i> At least
                                                        8
                                                        characters</li>
                                                    <li class="list-group-item" id="lower-char"><i></i> At least
                                                        1
                                                        lower letter (a-z)</li>
                                                    <li class="list-group-item" id="upper-char"><i></i> At least
                                                        1
                                                        uppercase letter(A-Z)</li>
                                                    <li class="list-group-item" id="number-char"><i></i> At least
                                                        1
                                                        number (0-9)</li>
                                                    <li class="list-group-item" id="special-char"><i></i> At least
                                                        1
                                                        special characters</li>
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

                <!-- [ Dashboard ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
@endsection
