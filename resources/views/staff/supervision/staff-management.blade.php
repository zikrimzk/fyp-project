@extends('staff.layouts.main')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Supervision</a></li>
                                <li class="breadcrumb-item" aria-current="page">Staff Management</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Staff Management</h2>
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
                @if (session()->has('skippedRows'))
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="alert-heading">
                                <i class="fas fa-info-circle"></i>
                                Error
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <ul>
                            @foreach (session('skippedRows') as $row)
                                <li>
                                    <strong>Staff ID:</strong> {{ $row['data']['staff_id'] }} -
                                    <strong>Staff Name:</strong> {{ $row['data']['staff_name'] }}
                                    <br>
                                    <strong>Errors:</strong>
                                    <ul>
                                        @foreach ($row['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <!-- [ Alert ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- [ Staff Management ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2 gap-md-3 d-md-flex flex-wrap">
                                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#addModal"><i class="ti ti-plus f-18"></i>
                                    Add Staff
                                </button>
                                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#importModal"><i
                                        class="ti ti-file-import f-18"></i>
                                    Import Staff
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Staff ID</th>
                                            <th scope="col">Role</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- [ Add Modal ] start -->
                <form action="{{ route('add-staff-post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal fade" id="addModal" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="mb-0">Add Staff</h5>
                                    <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default ms-auto"
                                        data-bs-dismiss="modal">
                                        <i class="ti ti-x f-20"></i>
                                    </a>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <!-- Photo Input -->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="d-grid justify-content-center align-items-center mb-3">
                                                <div class="user-upload avatar-s w-100">
                                                    <img src="{{ asset('assets/images/user/default-profile-1.jpg') }}"
                                                        alt="Profile Photo" width="150" height="150"
                                                        class="previewImage">
                                                    <label for="staff_photo" class="img-avtar-upload">
                                                        <i class="ti ti-camera f-24 mb-1"></i>
                                                        <span>Upload</span>
                                                    </label>
                                                    <input type="file" id="staff_photo" name="staff_photo"
                                                        class="d-none" accept="image/*" />
                                                    @error('staff_photo')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <label for="staff_photo"
                                                    class=" link-dark fw-semibold w-100 text-center mt-3"
                                                    style="cursor:pointer;">
                                                    Change Photo
                                                </label>
                                            </div>

                                        </div>

                                        <h5 class="mb-2">A. Personal Information</h5>

                                        <!-- Name Input -->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="staff_name" class="form-label">Staff Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('staff_name') is-invalid @enderror"
                                                    id="staff_name" name="staff_name" placeholder="Enter Staff Name"
                                                    value="{{ old('staff_name') }}" required>
                                                @error('staff_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Email Input -->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="staff_email" class="form-label">Email
                                                    <span class="text-danger">*</span></label>
                                                <input type="email"
                                                    class="form-control @error('staff_email') is-invalid @enderror"
                                                    id="staff_email" name="staff_email" placeholder="Enter Staff Email"
                                                    value="{{ old('staff_email') }}" required>
                                                @error('staff_email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
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
                                                        placeholder="Enter Phone Number" name="staff_phoneno"
                                                        value="{{ old('staff_phoneno') }}" maxlength="13" />
                                                    @error('staff_phoneno')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div id="phone-error-message" class="text-danger text-sm"
                                                        style="display: none;">
                                                        Phone number must be in a valid format (10 or 11 digits)!
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <h5 class="mb-2 mt-3">B. Work Information</h5>

                                        <!-- Staff ID Input -->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="staff_id" class="form-label">Staff ID
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('staff_id') is-invalid @enderror ids-input"
                                                    id="staff_id" name="staff_id" placeholder="Enter Staff ID"
                                                    value="{{ old('staff_id') }}" required>
                                                @error('staff_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Department Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="department_id" class="form-label">Department <span
                                                        class="text-danger">*</span></label>
                                                <select name="department_id" id="department_id"
                                                    class="form-select @error('department_id') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Department -</option>
                                                    @foreach ($deps as $dep)
                                                        @if (old('department_id') == $dep->id)
                                                            <option value="{{ $dep->id }}" selected>
                                                                {{ $dep->dep_name }}
                                                            </option>
                                                        @else
                                                            <option value="{{ $dep->id }}">
                                                                {{ $dep->dep_name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @error('department_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Role Input -->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="staff_role" class="form-label">
                                                    Role <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select @error('staff_role') is-invalid @enderror"
                                                    name="staff_role" required>
                                                    <option value ="" selected>- Select Role -</option>
                                                    @if (old('staff_role') == 1)
                                                        <option value ="1" selected>Committee</option>
                                                        <option value ="2">Lecturer</option>
                                                        <option value ="3">Timbalan Dekan Pendidikan</option>
                                                        <option value ="4">Dekan</option>
                                                    @elseif(old('staff_role') == 2)
                                                        <option value ="1">Committee</option>
                                                        <option value ="2" selected>Lecturer</option>
                                                        <option value ="3">Timbalan Dekan Pendidikan</option>
                                                        <option value ="4">Dekan</option>
                                                    @elseif(old('staff_role') == 3)
                                                        <option value ="1">Committee</option>
                                                        <option value ="2">Lecturer</option>
                                                        <option value ="3" selected>Timbalan Dekan Pendidikan</option>
                                                        <option value ="4">Dekan</option>
                                                    @elseif(old('staff_role') == 4)
                                                        <option value ="1">Committee</option>
                                                        <option value ="2">Lecturer</option>
                                                        <option value ="3">Timbalan Dekan Pendidikan</option>
                                                        <option value ="4" selected>Dekan</option>
                                                    @else
                                                        <option value ="1">Committee</option>
                                                        <option value ="2">Lecturer</option>
                                                        <option value ="3">Timbalan Dekan Pendidikan</option>
                                                        <option value ="4">Dekan</option>
                                                    @endif
                                                </select>
                                                @error('staff_role')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Status Input -->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Status <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select @error('staff_status') is-invalid @enderror"
                                                    name="staff_status" required>
                                                    <option value ="" selected>- Select Status -</option>
                                                    @if (old('staff_status') == 1)
                                                        <option value ="1" selected>Active</option>
                                                        <option value ="2">Inactive</option>
                                                    @elseif(old('staff_status') == 2)
                                                        <option value ="1">Active</option>
                                                        <option value ="2" selected>Inactive</option>
                                                    @else
                                                        <option value ="1">Active</option>
                                                        <option value ="2">Inactive</option>
                                                    @endif
                                                </select>
                                                @error('staff_status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-end">
                                    <div class="flex-grow-1 text-end">
                                        <button type="reset" class="btn btn-link-danger btn-pc-default"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Add Staff</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Add Modal ] end -->

                <!-- [ Import Modal ] start -->
                <form action="{{ route('import-staff-post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal fade" id="importModal" data-bs-keyboard="false" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="mb-0">Import Staff</h5>
                                    <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default ms-auto"
                                        data-bs-dismiss="modal">
                                        <i class="ti ti-x f-20"></i>
                                    </a>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <!-- File Input Section -->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <!-- Alert Note -->
                                                <div class="alert alert-warning d-flex align-items-center" role="alert">
                                                    <i class="ti ti-alert-circle me-2"></i>
                                                    <div>
                                                        <strong>Note:</strong> Please make sure to follow the template
                                                        provided.
                                                        <br> DO NOT CHANGE THE HEAD TITLE IN THE TEMPLATE.
                                                    </div>
                                                </div>
                                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                                    <i class="ti ti-file-text me-2"></i>
                                                    <div>
                                                        The supported file formats are <strong>CSV (*.csv)</strong> or
                                                        <strong>Excel (*.xlsx)</strong> only.
                                                    </div>
                                                </div>

                                                <!-- Custom File Upload -->
                                                <div class="mt-3">
                                                    <div class="input-group">
                                                        <input class="form-control d-none" type="file"
                                                            name="staff_file" id="file" accept=".csv, .xlsx"
                                                            required>
                                                        <input type="text" class="form-control" id="file-name"
                                                            placeholder="No file chosen" readonly>
                                                        <button class="btn btn-primary" type="button" id="browse-btn">
                                                            <i class="ti ti-upload"></i> Browse
                                                        </button>
                                                    </div>
                                                    <div class="fw-normal mt-2 text-muted">Click <a href=""
                                                            class="link-primary">here</a> to download the template</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-end">
                                    <div class="flex-grow-1 text-end">
                                        <button type="reset" class="btn btn-link-danger btn-pc-default"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary" id="import-btn" disabled>Import
                                            Staff</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Import Modal ] end -->

                @foreach ($staffs as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-staff-post', Crypt::encrypt($upd->id)) }}"
                        enctype="multipart/form-data" method="POST">
                        @csrf
                        <div class="modal fade" id="updateModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateModalLabel">Update Staff</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <!-- Photo Input -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="d-grid justify-content-center align-items-center mb-3">
                                                    <div class="user-upload avatar-s w-100">
                                                        <img src="{{ empty($upd->staff_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . $upd->staff_photo) }}"
                                                            alt="Profile Photo" width="150" height="150"
                                                            class="previewImage">
                                                        <label for="staff_photo_up_{{ $upd->id }}"
                                                            class="img-avtar-upload">
                                                            <i class="ti ti-camera f-24 mb-1"></i>
                                                            <span>Upload</span>
                                                        </label>
                                                        <input type="file" id="staff_photo_up_{{ $upd->id }}"
                                                            name="staff_photo_up" class="d-none staff_photo"
                                                            accept="image/*" />
                                                    </div>
                                                    <label for="staff_photo_up_{{ $upd->id }}"
                                                        class=" link-dark fw-semibold w-100 text-center mt-3"
                                                        style="cursor:pointer;">
                                                        Change Photo
                                                    </label>

                                                </div>
                                            </div>
                                            @error('staff_photo_up')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror

                                            <h5 class="mb-2">A. Personal Information</h5>

                                            <!-- Name Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="staff_name_up" class="form-label">Staff Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control @error('staff_name_up') is-invalid @enderror"
                                                        id="staff_name_up" name="staff_name_up"
                                                        placeholder="Enter Staff Name" value="{{ $upd->staff_name }}"
                                                        required>
                                                    @error('staff_name_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Email Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="staff_email_up" class="form-label">Email
                                                        <span class="text-danger">*</span></label>
                                                    <input type="email"
                                                        class="form-control @error('staff_email_up') is-invalid @enderror"
                                                        id="staff_email_up" name="staff_email_up"
                                                        placeholder="Enter Staff Email" value="{{ $upd->staff_email }}"
                                                        required>
                                                    @error('staff_email_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Phone No Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="staff_phoneno_up" class="form-label">
                                                        Phone Number
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">+60</span>
                                                        <input type="text"
                                                            class="form-control @error('staff_phoneno_up') is-invalid @enderror phonenum-input"
                                                            placeholder="Enter Phone Number" name="staff_phoneno_up"
                                                            value="{{ $upd->staff_phoneno }}" maxlength="13" />
                                                        @error('staff_phoneno_up')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <div id="phone-error-message" class="text-danger text-sm"
                                                            style="display: none;">
                                                            Phone number must be in a valid format (10 or 11 digits)!
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <h5 class="mb-2 mt-3">B. Academic Information</h5>

                                            <!-- Staff ID Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="staff_id_up" class="form-label">Staff ID
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('staff_id_up') is-invalid @enderror ids-input"
                                                        id="staff_id_up" name="staff_id_up" placeholder="Enter Staff ID"
                                                        value="{{ $upd->staff_id }}" required>
                                                    @error('staff_id_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Department Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="department_id_up" class="form-label">Department <span
                                                            class="text-danger">*</span></label>
                                                    <select name="department_id_up" id="department_id_up"
                                                        class="form-select @error('department_id_up') is-invalid @enderror"
                                                        required>
                                                        <option value="">- Select Department -</option>
                                                        @foreach ($deps as $dep)
                                                            @if ($upd->department_id == $dep->id)
                                                                <option value="{{ $dep->id }}" selected>
                                                                    {{ $dep->dep_name }}
                                                                </option>
                                                            @else
                                                                <option value="{{ $dep->id }}">
                                                                    {{ $dep->dep_name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @error('department_id_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Role Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="staff_role" class="form-label">
                                                        Role <span class="text-danger">*</span>
                                                    </label>
                                                    <select
                                                        class="form-select @error('staff_role_up') is-invalid @enderror"
                                                        name="staff_role_up" required>
                                                        <option value ="" selected>- Select Role -</option>
                                                        @if ($upd->staff_role == 1)
                                                            <option value ="1" selected>Committee</option>
                                                            <option value ="2">Lecturer</option>
                                                            <option value ="3">Timbalan Dekan Pendidikan</option>
                                                            <option value ="4">Dekan</option>
                                                        @elseif($upd->staff_role == 2)
                                                            <option value ="1">Committee</option>
                                                            <option value ="2" selected>Lecturer</option>
                                                            <option value ="3">Timbalan Dekan Pendidikan</option>
                                                            <option value ="4">Dekan</option>
                                                        @elseif($upd->staff_role == 3)
                                                            <option value ="1">Committee</option>
                                                            <option value ="2">Lecturer</option>
                                                            <option value ="3" selected>Timbalan Dekan Pendidikan
                                                            </option>
                                                            <option value ="4">Dekan</option>
                                                        @elseif($upd->staff_role == 4)
                                                            <option value ="1">Committee</option>
                                                            <option value ="2">Lecturer</option>
                                                            <option value ="3">Timbalan Dekan Pendidikan</option>
                                                            <option value ="4" selected>Dekan</option>
                                                        @else
                                                            <option value ="1">Committee</option>
                                                            <option value ="2">Lecturer</option>
                                                            <option value ="3">Timbalan Dekan Pendidikan</option>
                                                            <option value ="4">Dekan</option>
                                                        @endif
                                                    </select>
                                                    @error('staff_role_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Status Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="staff_status_up" class="form-label">
                                                        Status <span class="text-danger">*</span>
                                                    </label>
                                                    <select
                                                        class="form-select @error('staff_status') is-invalid @enderror"
                                                        name="staff_status_up" id="staff_status_up" required>
                                                        <option value ="" selected>- Select Status -</option>
                                                        @if ($upd->staff_status == 1)
                                                            <option value ="1" selected>Active</option>
                                                            <option value ="2">Inactive</option>
                                                        @elseif($upd->staff_status == 2)
                                                            <option value ="1">Active</option>
                                                            <option value ="2" selected>Inactive</option>
                                                        @else
                                                            <option value ="1">Active</option>
                                                            <option value ="2">Inactive</option>
                                                        @endif
                                                    </select>
                                                    @error('staff_status_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="modal-footer justify-content-end">
                                        <div class="flex-grow-1 text-end">
                                            <button type="reset" class="btn btn-link-danger btn-pc-default"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Update Modal ] end -->

                    <!-- [ Delete Modal ] start -->
                    <div class="modal fade" id="deleteModal-{{ $upd->id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 mb-4">
                                            <div class="d-flex justify-content-center align-items-center mb-3">
                                                <i class="ti ti-trash text-danger" style="font-size: 100px"></i>
                                            </div>

                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <h2>Are you sure ?</h2>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 mb-3">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <p class="fw-normal f-18 text-center">This action cannot be undone.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <a href="{{ route('delete-staff-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 1]) }}"
                                                    class="btn btn-danger w-100">Delete Anyways</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Delete Modal ] end -->

                    <!-- [ Disable Modal ] start -->
                    <div class="modal fade" id="disableModal-{{ $upd->id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 mb-4">
                                            <div class="d-flex justify-content-center align-items-center mb-3">
                                                <i class="ti ti-alert-circle text-warning" style="font-size: 100px"></i>
                                            </div>

                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <h2>Account Deletion</h2>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 mb-3">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <p class="fw-normal f-18 text-center">
                                                    Oops! You can't delete this staff.
                                                    However, you can inactive it instead. Would you like to proceed with
                                                    inactivating this staff?
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <a href="{{ route('delete-staff-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 2]) }}"
                                                    class="btn btn-warning w-100">Inactive</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Disable Modal ] end -->
                @endforeach

                <!-- [ Staff Management ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var modalToShow = "{{ session('modal') }}"; // Ambil modal yang perlu dibuka dari session
            if (modalToShow) {
                var modalElement = document.getElementById(modalToShow);
                if (modalElement) {
                    var modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }
        });

        $(document).ready(function() {

            $(function() {

                // DATATABLE : STUDENT
                var table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    autoWidth: true,
                    ajax: {
                        url: "{{ route('staff-management') }}",
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            searchable: false,
                            className: "text-start"
                        },
                        {
                            data: 'staff_photo',
                            name: 'staff_photo'
                        },
                        {
                            data: 'staff_id',
                            name: 'staff_id'
                        },
                        {
                            data: 'staff_role',
                            name: 'staff_role'
                        },
                        {
                            data: 'staff_status',
                            name: 'staff_status'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ]

                });

            });

            $('#staff_photo').on('change', function() {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        $('.previewImage').attr('src', e.target.result).show();
                    };

                    reader.readAsDataURL(file);
                }
            });

            $('.staff_photo').on('change', function() {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        $('.previewImage').attr('src', e.target.result).show();
                    };

                    reader.readAsDataURL(file);
                }
            });

            $('.phonenum-input').on('input', function() {
                let input = $(this).val().replace(/\D/g, '');
                let errorMessage = $('#phone-error-message');

                if (input.length <= 11) {
                    if (input.length === 10) {
                        // Format untuk 10 digit: ### ### ####
                        $(this).val(input.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3'));
                        errorMessage.hide();
                    } else if (input.length === 11) {
                        // Format untuk 11 digit: ### #### ####
                        $(this).val(input.replace(/(\d{3})(\d{4})(\d{4})/, '$1 $2 $3'));
                        errorMessage.hide();
                    } else {
                        $(this).val(input);
                        errorMessage.hide();
                    }
                } else {
                    errorMessage.show();
                }
            });

            $('.ids-input').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            $('#browse-btn').on('click', function() {
                $('#file').click();
            });

            $('#file').on('change', function() {
                let fileName = $(this).val().split("\\").pop();
                $('#file-name').val(fileName || "No file chosen");
                $('#import-btn').prop('disabled', false);
            });


        });
    </script>
@endsection
