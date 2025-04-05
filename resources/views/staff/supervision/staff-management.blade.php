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
                            <!-- [ Option Section ] start -->
                            <div class="mb-5 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none"
                                    id="clearSelectionBtn">
                                    0 selected <i class="ti ti-x f-18"></i>
                                </button>
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#addModal" title="Add Staff" id="addStaffBtn">
                                    <i class="ti ti-plus f-18"></i> <span class="d-none d-sm-inline me-2">Add Staff</span>
                                </button>
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#importModal" id="importBtn" title="Import Data">
                                    <i class="ti ti-file-import f-18"></i>
                                    <span class="d-none d-sm-inline me-2">Import Data</span>
                                </button>
                                <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2"
                                    id="excelExportBtn" title="Export Data">
                                    <i class="ti ti-file-export f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Export Data
                                    </span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <!-- [ Filter Section ] Start -->
                            <div class="row g-3 align-items-end">

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_faculty_id" class="form-select">
                                            <option value="">-- Select Faculty --</option>
                                            @foreach ($facs as $fil)
                                                @if ($fil->fac_status == 1)
                                                    <option value="{{ $fil->id }}">{{ $fil->fac_code }}</option>
                                                @elseif($fil->fac_status == 2)
                                                    <option value="{{ $fil->id }}" class="bg-light-danger">
                                                        {{ $fil->fac_code }} [Inactive]
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearFacFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_department_id" class="form-select">
                                            <option value="">-- Select Department --</option>
                                            @foreach ($deps as $fil)
                                                @if ($fil->dep_status == 1)
                                                    <option value="{{ $fil->id }}"> {{ $fil->dep_name }}</option>
                                                @elseif($fil->dep_status == 2)
                                                    <option value="{{ $fil->id }}" class="bg-light-danger">
                                                        {{ $fil->dep_name }} [Inactive]
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearDepFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_role" class="form-select">
                                            <option value="">-- Select Role --</option>
                                            <option value="1">Committee</option>
                                            <option value="2">Lecturer</option>
                                            <option value="3">Timbalan Dekan Pendidikan</option>
                                            <option value="4">Dekan</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            id="clearRoleFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_status" class="form-select">
                                            <option value="">-- Select Status --</option>
                                            <option value="1">Active</option>
                                            <option value="2">Inactive</option>
                                        </select>
                                        <button type="button" class="btn  btn-outline-danger btn-sm"
                                            id="clearStatusFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>
                            <!-- [ Filter Section ] End -->

                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all" class="form-check-input"></th>
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
                                                        class="previewImageAdd"
                                                        data-default="{{ asset('assets/images/user/default-profile-1.jpg') }}">

                                                    <label for="staff_photo" class="img-avtar-upload">
                                                        <i class="ti ti-camera f-24 mb-1"></i>
                                                        <span>Upload</span>
                                                    </label>

                                                    <input type="file" id="staff_photo" name="staff_photo"
                                                        class="d-none" accept="image/*" />
                                                </div>
                                                <label for="staff_photo" class="btn btn-sm btn-secondary mt-2 mb-2">
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
                                                        value="{{ old('staff_phoneno') }}" maxlength="11" />
                                                    @error('staff_phoneno')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
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
                                                    @foreach ($deps->where('dep_status', 1) as $dep)
                                                        @if (old('department_id') == $dep->id)
                                                            <option value="{{ $dep->id }}" selected>
                                                                {{ $dep->fac_code }} - {{ $dep->dep_name }}
                                                            </option>
                                                        @else
                                                            <option value="{{ $dep->id }}">
                                                                {{ $dep->fac_code }} - {{ $dep->dep_name }}
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
                                    <h5 class="mb-0">Import Data</h5>
                                    <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default ms-auto"
                                        data-bs-dismiss="modal">
                                        <i class="ti ti-x f-20"></i>
                                    </a>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <!-- File Input Section -->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-1">
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
                                                    <div class="fw-normal mt-2 text-muted">Click <a
                                                            href="{{ asset('assets/excel-template/e-PGS_STAFF_REGISTRATION_TEMPLATE.xlsx') }}"
                                                            class="link-primary" target="_blank"
                                                            download="e-PGS_STAFF_REGISTRATION_TEMPLATE.xlsx">here</a> to
                                                        download the template</div>
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
                                            Data</button>
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
                                                <div class="d-grid justify-content-center align-items-center mb-3 profile-container"
                                                    data-id="{{ $upd->id }}">
                                                    <div class="user-upload avatar-s w-100">
                                                        <img src="{{ empty($upd->staff_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . $upd->staff_photo) }}"
                                                            alt="Profile Photo" width="150" height="150"
                                                            class="previewImage"
                                                            data-default="{{ asset('assets/images/user/default-profile-1.jpg') }}">
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
                                                        class="btn btn-sm btn-secondary mt-2 mb-2">
                                                        Change Photo
                                                    </label>
                                                    <button type="button" class="btn btn-sm btn-light-danger resetPhoto">
                                                        Reset Photo
                                                    </button>
                                                    <input type="hidden" name="remove_photo" class="remove_photo"
                                                        value="0">
                                                </div>
                                                @error('staff_photo_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

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
                                                            value="{{ $upd->staff_phoneno }}" maxlength="11" />
                                                        @error('staff_phoneno_up')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
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
                                                                    {{ $dep->fac_code }} - {{ $dep->dep_name }}
                                                                    @if ($dep->dep_status == 2)
                                                                        [Inactive]
                                                                    @endif
                                                                </option>
                                                            @else
                                                                @if ($dep->dep_status == 1)
                                                                    <option value="{{ $dep->id }}">
                                                                        {{ $dep->fac_code }} - {{ $dep->dep_name }}
                                                                    </option>
                                                                @endif
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
        $(document).ready(function() {

            // DATATABLE : STAFF
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('staff-management') }}",
                    data: function(d) {
                        d.faculty = $('#fil_faculty_id')
                            .val();
                        d.department = $('#fil_department_id')
                            .val();
                        d.role = $('#fil_role')
                            .val();
                        d.status = $('#fil_status')
                            .val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,

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

            var modalToShow = "{{ session('modal') }}";
            if (modalToShow) {
                var modalElement = $("#" + modalToShow);
                if (modalElement.length) {
                    var modal = new bootstrap.Modal(modalElement[0]);
                    modal.show();
                }
            }

            // FILTER : FACULTY
            $('#fil_faculty_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearFacFilter').click(function() {
                $('#fil_faculty_id').val('').change();
            });

            // FILTER : DEPARTMENT
            $('#fil_department_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearDepFilter').click(function() {
                $('#fil_department_id').val('').change();
            });

            // FILTER : ROLE 
            $('#fil_role').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearRoleFilter').click(function() {
                $('#fil_role').val('').change();
            });

            // FILTER : STATUS
            $('#fil_status').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearStatusFilter').click(function() {
                $('#fil_status').val('').change();
            });

            // STAFF PHOTO FUNCTIONS
            var defaultImageAdd = $(".previewImageAdd").data("default");
            var defaultImage = $(".previewImage").data("default");

            $('#staff_photo').on('change', function(event) {
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
                $("#staff_photo").val("");
            });

            $(document).on('change', '.staff_photo', function(event) {
                var file = event.target.files[0];
                var reader = new FileReader();
                var container = $(this).closest('.profile-container');
                var userId = container.data('id');

                if (file) {
                    reader.onload = function(e) {
                        $('.profile-container[data-id="' + userId + '"] .previewImage').attr('src', e
                            .target
                            .result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            $(document).on("click", ".resetPhoto", function() {
                var container = $(this).closest('.profile-container');
                var defaultImage = container.find('.previewImage').data("default");

                console.log("Reset button clicked for user:", container.data('id')); // Debugging

                if (defaultImage) {
                    container.find('.previewImage').attr('src', defaultImage);
                } else {
                    console.error("Default image not found for user " + container.data('id'));
                }

                container.find('.staff_photo').val(null); // Reset input file
                container.find('.remove_photo').val("1"); // Tandakan gambar perlu dipadam
            });

            // FORMATTING
            $('.phonenum-input').on('input', function() {
                let input = $(this).val().replace(/\D/g, ''); // Remove non-numeric characters
                let errorMessage = $('.phone-error-message');

                if (input.length > 11) {
                    input = input.substring(0, 11); // Limit to 11 digits
                }
            });

            $('.ids-input').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            // IMPORT : STAFF
            $('#browse-btn').on('click', function() {
                $('#file').click();
            });

            $('#file').on('change', function() {
                let fileName = $(this).val().split("\\").pop();
                $('#file-name').val(fileName || "No file chosen");
                $('#import-btn').prop('disabled', false);
            });

            /* SELECT : MULTIPLE STAFF SELECT */
            const addBtn = $("#addStaffBtn");
            const importBtn = $("#importBtn");
            const excelExportBtn = $("#excelExportBtn");
            const clearBtn = $("#clearSelectionBtn");

            let selectedIds = new Set();

            // Handle "Select All" checkbox
            $("#select-all").on("change", function() {
                let isChecked = $(this).prop("checked");

                $(".user-checkbox").each(function() {
                    let id = $(this).val();
                    this.checked = isChecked;

                    if (isChecked) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                });
                toggleSelectButton();
            });

            // Handle individual checkbox selection
            $(document).on("change", ".user-checkbox", function() {
                let id = $(this).val();
                if ($(this).prop("checked")) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
                toggleSelectButton();
            });

            // Restore checkbox states after DataTables refresh
            $('.data-table').on("draw.dt", function() {
                $(".user-checkbox").each(function() {
                    let id = $(this).val();
                    this.checked = selectedIds.has(id);
                });

                // If all checkboxes are selected, keep "Select All" checked
                $("#select-all").prop(
                    "checked",
                    $(".user-checkbox").length === $(".user-checkbox:checked").length
                );

                toggleSelectButton();
            });

            function toggleSelectButton() {
                let selectedCount = selectedIds.size;

                addBtn.toggleClass("d-none", selectedIds.size !== 0);
                importBtn.toggleClass("d-none", selectedIds.size !== 0);

                if (selectedCount > 0) {
                    clearBtn.removeClass("d-none").html(
                        `<i class="ti ti-x f-18"></i> ${selectedCount} selected`);
                } else {
                    clearBtn.addClass("d-none");
                }
            }

            clearBtn.on("click", function() {
                $(".user-checkbox").prop("checked", false);
                $("#select-all").prop("checked", false);
                selectedIds.clear();
                toggleSelectButton();
            });

            excelExportBtn.click(function(e) {
                e.preventDefault();
                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                let url = "{{ route('export-staff-get') }}";

                if (selectedIds.length > 0) {
                    url += "?ids=" + selectedIds.join(",");
                }
                window.location.href = url;
            });


        });
    </script>
@endsection
