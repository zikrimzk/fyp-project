@php
    use App\Models\Semester;
@endphp
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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Administrator</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Supervision</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Student</a></li>
                                <li class="breadcrumb-item" aria-current="page">Student Management</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Student Management</h2>
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
                                    <strong>Student Matric No:</strong> {{ $row['data']['matricno'] }} -
                                    <strong>Student Name:</strong> {{ $row['data']['student_name'] }}
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

                <!-- [ Student Management ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- [ Option Section ] start -->
                            <div class="mb-4 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none"
                                    id="clearSelectionBtn">
                                    0 selected <i class="ti ti-x f-18"></i>
                                </button>
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#addModal" title="Add Student"
                                    id="addStudentBtn">
                                    <i class="ti ti-plus f-18"></i> <span class="d-none d-sm-inline me-2">Add Student</span>
                                </button>
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#importModal" id="importBtn" title="Import Data">
                                    <i class="ti ti-file-import f-18"></i>
                                    <span class="d-none d-sm-inline me-2">Import Student</span>
                                </button>
                                <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2"
                                    id="excelExportBtn" title="Export Data">
                                    <i class="ti ti-file-export f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Export Data
                                    </span>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none"
                                    data-bs-toggle="modal" data-bs-target="#changestatusModal" id="changestatusBtn"
                                    title="Change Status">
                                    <i class="ti ti-user f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Change Status
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
                                                @elseif($fil->fac_status == 3)
                                                    <option value="{{ $fil->id }}" class="bg-light-success" selected>
                                                        {{ $fil->fac_code }} [Default]
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFacFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_semester_id" class="form-select">
                                            <option value="">-- Select Semester --</option>
                                            @foreach ($sems as $fil)
                                                @if ($fil->sem_status == 1)
                                                    <option value="{{ $fil->id }}" class="bg-light-success">
                                                        {{ $fil->sem_label }} [Current]
                                                    </option>
                                                @elseif($fil->sem_status == 3)
                                                    <option value="{{ $fil->id }}"> {{ $fil->sem_label }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearSemFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_programme_id" class="form-select">
                                            <option value="">-- Select Programme --</option>
                                            @foreach ($progs as $fil)
                                                @if ($fil->prog_status == 1)
                                                    <option value="{{ $fil->id }}"> {{ $fil->prog_code }}
                                                        ({{ $fil->prog_mode }})
                                                    </option>
                                                @elseif($fil->prog_status == 2)
                                                    <option value="{{ $fil->id }}" class="bg-light-danger">
                                                        {{ $fil->prog_code }}
                                                        ({{ $fil->prog_mode }}) [Inactive]</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearProgFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_status" class="form-select">
                                            <option value="">-- Select Status --</option>
                                            <option value="1" selected>Active</option>
                                            <option value="2">Inactive</option>
                                            <option value="3">Extend</option>
                                            <option value="4">Terminate</option>
                                            <option value="5">Withdraw</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
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
                                            <th scope="col">Matric No</th>
                                            <th scope="col">Programme</th>
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
                <form action="{{ route('add-student-post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal fade" id="addModal" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header bg-light">
                                    <h5 class="mb-0">Add Student</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row g-3">

                                        <!-- Photo Upload -->
                                        <div class="col-12">
                                            <div class="d-flex flex-column align-items-center mb-3">
                                                <div class="user-upload avatar-s text-center">
                                                    <img src="{{ asset('assets/images/user/default-profile-1.jpg') }}"
                                                        alt="Profile Photo" width="150" height="150"
                                                        class="previewImageAdd"
                                                        data-default="{{ asset('assets/images/user/default-profile-1.jpg') }}">
                                                    <label for="student_photo" class="img-avtar-upload">
                                                        <i class="ti ti-camera f-24 mb-1"></i>
                                                        <span>Upload</span>
                                                    </label>
                                                    <input type="file" id="student_photo" name="student_photo"
                                                        class="d-none" accept="image/*" />
                                                </div>
                                                <div class="d-flex gap-2 mt-2">
                                                    <label for="student_photo" class="btn btn-sm btn-secondary">
                                                        Change Photo
                                                    </label>
                                                    <button type="button" id="resetPhoto"
                                                        class="btn btn-sm btn-light-danger">
                                                        Reset Photo
                                                    </button>
                                                </div>
                                                @error('student_photo')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Personal Info Section -->
                                        <div class="col-12 border-bottom pb-2 mb-3">
                                            <h5 class="mb-0">A. Personal Information</h5>
                                        </div>

                                        <!-- Student Name -->
                                        <div class="col-md-6">
                                            <label for="student_name" class="form-label">Student Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" id="student_name" name="student_name"
                                                class="form-control @error('student_name') is-invalid @enderror"
                                                placeholder="Enter Student Name" value="{{ old('student_name') }}"
                                                required>
                                            @error('student_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Gender -->
                                        <div class="col-md-6">
                                            <label for="student_gender" class="form-label">Gender <span
                                                    class="text-danger">*</span></label>
                                            <select id="student_gender" name="student_gender"
                                                class="form-select @error('student_gender') is-invalid @enderror" required>
                                                <option value="" disabled
                                                    {{ old('student_gender') ? '' : 'selected' }}>- Select Gender -
                                                </option>
                                                <option value="male"
                                                    {{ old('student_gender') == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female"
                                                    {{ old('student_gender') == 'female' ? 'selected' : '' }}>Female
                                                </option>
                                            </select>
                                            @error('student_gender')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Email -->
                                        <div class="col-md-6">
                                            <label for="student_email" class="form-label">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" id="student_email" name="student_email"
                                                class="form-control @error('student_email') is-invalid @enderror"
                                                placeholder="Enter Student Email" value="{{ old('student_email') }}"
                                                required>
                                            @error('student_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Phone Number -->
                                        <div class="col-md-6">
                                            <label for="student_phoneno" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text">+60</span>
                                                <input type="text" name="student_phoneno"
                                                    class="form-control @error('student_phoneno') is-invalid @enderror phonenum-input"
                                                    placeholder="Enter Phone Number" value="{{ old('student_phoneno') }}"
                                                    maxlength="11">
                                                @error('student_phoneno')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Address -->
                                        <div class="col-12">
                                            <label for="student_address" class="form-label">Address</label>
                                            <textarea id="student_address" name="student_address"
                                                class="form-control @error('student_address') is-invalid @enderror" placeholder="Enter Address" rows="4">{{ old('student_address') }}</textarea>
                                            @error('student_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Academic Info Section -->
                                        <div class="col-12 border-bottom pb-2 mt-3 mb-3">
                                            <h5 class="mb-0">B. Academic Information</h5>
                                        </div>

                                        <!-- Matric No -->
                                        <div class="col-md-6">
                                            <label for="student_matricno" class="form-label">Matric Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" id="student_matricno" name="student_matricno"
                                                class="form-control @error('student_matricno') is-invalid @enderror matric-input"
                                                placeholder="Enter Matric Number" value="{{ old('student_matricno') }}"
                                                required>
                                            @error('student_matricno')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Programme -->
                                        <div class="col-md-6">
                                            <label for="programme_id" class="form-label">Programme <span
                                                    class="text-danger">*</span></label>
                                            <select id="programme_id" name="programme_id"
                                                class="form-select @error('programme_id') is-invalid @enderror" required>
                                                <option value="">- Select Programme -</option>
                                                @foreach ($progs as $prog)
                                                    <option value="{{ $prog->id }}"
                                                        {{ old('programme_id') == $prog->id ? 'selected' : '' }}>
                                                        {{ $prog->prog_code }} ({{ $prog->prog_mode }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('programme_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Status -->
                                        <div class="col-md-6">
                                            <label for="student_status" class="form-label">Status <span
                                                    class="text-danger">*</span></label>
                                            <select id="student_status" name="student_status"
                                                class="form-select @error('student_status') is-invalid @enderror" required>
                                                <option value="">- Select Status -</option>
                                                <option value="1" {{ old('student_status') == 1 ? 'selected' : '' }}>
                                                    Active</option>
                                                <option value="2" {{ old('student_status') == 2 ? 'selected' : '' }}>
                                                    Inactive</option>
                                                <option value="3" {{ old('student_status') == 3 ? 'selected' : '' }}>
                                                    Extend</option>
                                                <option value="4" {{ old('student_status') == 4 ? 'selected' : '' }}>
                                                    Terminate</option>
                                                <option value="5" {{ old('student_status') == 5 ? 'selected' : '' }}>
                                                    Withdraw</option>
                                            </select>
                                            @error('student_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer pt-2 bg-light">
                                    <div class="row w-100 g-2">
                                        <div class="col-12 col-md-6">
                                            <button type="reset" class="btn btn-outline-secondary w-100"
                                                data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <button type="submit" class="btn btn-primary w-100">
                                                Add Student
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Add Modal ] end -->

                <!-- [ Import Modal ] start -->
                <form action="{{ route('import-student-post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal fade" id="importModal" data-bs-keyboard="false" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header bg-light">
                                    <h5 class="mb-0"><i class="ti ti-upload me-2"></i> Import Student (Excel)
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <!-- File Input Section -->
                                        <div class="col-12">
                                            <!-- Alert Note -->
                                            <div class="alert alert-light d-flex align-items-start gap-2" role="alert">
                                                <i class="ti ti-alert-circle mt-1"></i>
                                                <div>
                                                    <strong>Important:</strong>
                                                    <ul class="mb-0 ps-3">
                                                        <li>Please make sure to follow the template provided.</li>
                                                        <li>Do not change the column headers in the template.</li>
                                                        <li> Supported file formats are <strong>CSV (*.csv)</strong> and
                                                            <strong>Excel (*.xlsx)</strong>.
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <!-- Custom File Upload -->
                                            <div class="mt-3">
                                                <label for="file" class="form-label fw-semibold">Upload File</label>
                                                <div class="input-group">
                                                    <input type="file" class="form-control d-none" id="file"
                                                        name="student_file" accept=".csv, .xlsx" required>
                                                    <input type="text" class="form-control" id="file-name"
                                                        placeholder="No file chosen" readonly>
                                                    <button class="btn btn-outline-primary" type="button"
                                                        id="browse-btn">
                                                        <i class="ti ti-folder-up"></i> Browse
                                                    </button>
                                                </div>
                                                <div class="form-text mt-2">
                                                    <a href="{{ asset('assets/excel-template/e-PGS_STUDENT_REGISTRATION_TEMPLATE.xlsx') }}"
                                                        class="link-primary" target="_blank" download>Download the
                                                        template here</a>.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="modal-footer justify-content-end bg-light">
                                    <div class="flex-grow-1 text-end">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-outline-secondary w-100"
                                                    data-bs-dismiss="modal">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="btn btn-primary w-100" id="import-btn"
                                                    disabled>
                                                    Import Student
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Import Modal ] end -->

                <!-- [ Change Status Modal ] start -->
                <div class="modal fade" id="changestatusModal" data-bs-keyboard="false" tabindex="-1"
                    aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-light">
                                <h5 class="mb-0">Change Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <!-- Status Input Section -->
                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                        <!-- Custom File Upload -->
                                        <div class="mb-3">
                                            <label class="form-label">
                                                Status <span class="text-danger">*</span>
                                            </label>
                                            <select
                                                class="form-select @error('student_status_change') is-invalid @enderror"
                                                name="student_status_change" id="student_status_change" required>
                                                <option value ="" selected>- Select Status -</option>
                                                <option value ="1" @if (old('student_status_change') == 1) selected @endif>
                                                    Active
                                                </option>
                                                <option value ="2" @if (old('student_status_change') == 2) selected @endif>
                                                    Inactive
                                                </option>
                                                <option value ="3" @if (old('student_status_change') == 3) selected @endif>
                                                    Extend
                                                </option>
                                                <option value ="4" @if (old('student_status_change') == 4) selected @endif>
                                                    Terminate
                                                </option>
                                                <option value ="5" @if (old('student_status_change') == 5) selected @endif>
                                                    Withdraw
                                                </option>
                                            </select>
                                            @error('student_status_change')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer pt-2 bg-light">
                                <div class="row w-100 g-2">
                                    <div class="col-12 col-md-6">
                                        <button type="reset" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <button type="submit" class="btn btn-primary w-100" id="updatestatusBtn"
                                            disabled>Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Change Status Modal ] end -->

                @foreach ($studs as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-student-post', Crypt::encrypt($upd->id)) }}"
                        enctype="multipart/form-data" method="POST">
                        @csrf
                        <div class="modal fade" id="updateModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title" id="updateModalLabel">Update Student</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <!-- Photo Upload -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="d-flex flex-column align-items-center mb-3 profile-container"
                                                    data-id="{{ $upd->id }}">
                                                    <div class="user-upload avatar-s text-center">
                                                        <img src="{{ empty($upd->student_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . $upd->student_directory . '/photo/' . $upd->student_photo) }}"
                                                            alt="Profile Photo" width="150" height="150"
                                                            class="previewImage"
                                                            data-default="{{ asset('assets/images/user/default-profile-1.jpg') }}">
                                                        <label for="student_photo_up_{{ $upd->id }}"
                                                            class="img-avtar-upload">
                                                            <i class="ti ti-camera f-24 mb-1"></i>
                                                            <span>Upload</span>
                                                        </label>
                                                        <input type="file" id="student_photo_up_{{ $upd->id }}"
                                                            name="student_photo_up" class="d-none student_photo"
                                                            accept="image/*" />
                                                    </div>
                                                    <div class="d-flex gap-2 mt-2">
                                                        <label for="student_photo_up_{{ $upd->id }}"
                                                            class="btn btn-sm btn-secondary">
                                                            Change Photo
                                                        </label>
                                                        <button type="button"
                                                            class="btn btn-sm btn-light-danger resetPhoto">
                                                            Reset Photo
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="remove_photo" class="remove_photo"
                                                        value="0">
                                                </div>
                                                @error('student_photo_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Personal Info Section -->
                                            <div class="col-12 border-bottom pb-2 mb-3">
                                                <h5 class="mb-0">A. Personal Information</h5>
                                            </div>

                                            <!-- Name -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <label for="student_name_up" class="form-label">Student Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('student_name_up') is-invalid @enderror"
                                                    id="student_name_up" name="student_name_up"
                                                    placeholder="Enter Student Name" value="{{ $upd->student_name }}"
                                                    required>
                                                @error('student_name_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Gender -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <label for="student_gender_up" class="form-label">Gender
                                                    <span class="text-danger">*</span></label>
                                                <select name="student_gender_up" id="student_gender_up"
                                                    class="form-select @error('student_gender_up') is-invalid @enderror"
                                                    required>
                                                    <option value="" selected>- Select Gender -</option>
                                                    @if ($upd->student_gender == 'male')
                                                        <option value="male" selected>Male</option>
                                                        <option value="female">Female</option>
                                                    @elseif($upd->student_gender == 'female')
                                                        <option value="male">Male</option>
                                                        <option value="female" selected>Female</option>
                                                    @else
                                                        <option value="male">Male</option>
                                                        <option value="female">Female</option>
                                                    @endif
                                                </select>
                                                @error('student_gender_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Email -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <label for="student_email_up" class="form-label">Email
                                                    <span class="text-danger">*</span></label>
                                                <input type="email"
                                                    class="form-control @error('student_email_up') is-invalid @enderror"
                                                    id="student_email_up" name="student_email_up"
                                                    placeholder="Enter Student Email" value="{{ $upd->student_email }}"
                                                    required>
                                                @error('student_email_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Phone No -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <label for="student_phoneno_up" class="form-label">
                                                    Phone Number
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">+60</span>
                                                    <input type="text"
                                                        class="form-control @error('student_phoneno_up') is-invalid @enderror phonenum-input"
                                                        placeholder="Enter Phone Number" name="student_phoneno_up"
                                                        value="{{ $upd->student_phoneno }}" maxlength="11" />
                                                    @error('student_phoneno_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Address -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <label for="student_address_up" class="form-label">
                                                    Address
                                                </label>
                                                <textarea name="student_address_up" id="student_address_up" placeholder="Enter Address" cols="10"
                                                    rows="5" class="form-control @error('student_address_up') is-invalid @enderror">{{ $upd->student_address }}</textarea>
                                                @error('student_address_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Academic Info Section -->
                                            <div class="col-12 border-bottom pb-2 mt-3 mb-3">
                                                <h5 class="mb-0">B. Academic Information</h5>
                                            </div>

                                            <!-- Matric No -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <label for="student_matricno" class="form-label">Matric Number
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('student_matricno_up') is-invalid @enderror matric-input"
                                                    id="student_matricno_up" name="student_matricno_up"
                                                    placeholder="Enter Matric Number"
                                                    value="{{ $upd->student_matricno }}" required>
                                                @error('student_matricno_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!--Programme -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <label for="programme_id_up" class="form-label">Programme <span
                                                        class="text-danger">*</span></label>
                                                <select name="programme_id_up" id="programme_id_up"
                                                    class="form-select @error('programme_id_up') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Programme -</option>
                                                    @foreach ($progs as $prog)
                                                        @if ($upd->programme_id == $prog->id)
                                                            <option value="{{ $prog->id }}" selected>
                                                                {{ $prog->prog_code }} ({{ $prog->prog_mode }})
                                                            </option>
                                                        @else
                                                            <option value="{{ $prog->id }}">
                                                                {{ $prog->prog_code }} ({{ $prog->prog_mode }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @error('programme_id_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror

                                            </div>

                                            <!-- Status -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <label for="student_status_up" class="form-label">
                                                    Status <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select @error('student_status') is-invalid @enderror"
                                                    name="student_status_up" id="student_status_up" required>
                                                    <option value ="" selected>- Select Status -</option>
                                                    <option value ="1"
                                                        @if ($upd->student_status == 1) selected @endif>Active
                                                    </option>
                                                    <option value ="2"
                                                        @if ($upd->student_status == 2) selected @endif>Inactive
                                                    </option>
                                                    <option value ="3"
                                                        @if ($upd->student_status == 3) selected @endif>Extend
                                                    </option>
                                                    <option value ="4"
                                                        @if ($upd->student_status == 4) selected @endif>Terminate
                                                    </option>
                                                    <option value ="5"
                                                        @if ($upd->student_status == 5) selected @endif>Withdraw
                                                    </option>
                                                </select>
                                                @error('student_status_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer pt-2 bg-light">
                                        <div class="row w-100 g-2">
                                            <div class="col-12 col-md-6">
                                                <button type="reset" class="btn btn-outline-secondary w-100"
                                                    data-bs-dismiss="modal">
                                                    Cancel
                                                </button>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    Save Changes
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Update Modal ] end -->

                    <!-- [ Delete Modal ] start -->
                    <div class="modal fade" id="deleteModal-{{ $upd->id }}" data-bs-backdrop="static"
                        data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $upd->id }}"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-body p-4">
                                    <div class="text-center mb-3">
                                        <i class="ti ti-trash text-danger" style="font-size: 80px;"></i>
                                    </div>
                                    <h4 class="text-center mb-2" id="deleteModalLabel-{{ $upd->id }}">Are you sure?
                                    </h4>
                                    <p class="text-center text-muted mb-4">This action cannot be undone.</p>

                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="{{ route('delete-student-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 1]) }}"
                                            class="btn btn-danger w-100">Delete Anyway</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Delete Modal ] end -->

                    <!-- [ Disable Modal ] start -->
                    <div class="modal fade" id="disableModal-{{ $upd->id }}" data-bs-backdrop="static"
                        data-bs-keyboard="false" tabindex="-1" aria-labelledby="disableModalLabel-{{ $upd->id }}"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-body p-4">
                                    <div class="text-center mb-3">
                                        <i class="ti ti-alert-circle text-warning" style="font-size: 80px;"></i>
                                    </div>
                                    <h4 class="text-center mb-2" id="disableModalLabel-{{ $upd->id }}">Account
                                        Inactivation</h4>
                                    <p class="text-center text-muted mb-4">
                                        Oops! You can't delete this student.<br>
                                        However, you can inactivate them instead. Would you like to proceed?
                                    </p>

                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="{{ route('delete-student-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 2]) }}"
                                            class="btn btn-warning w-100">Inactivate</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Disable Modal ] end -->
                @endforeach

                <!-- [ Student Management ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : STUDENT
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('student-management') }}",
                    data: function(d) {
                        d.faculty = $('#fil_faculty_id')
                            .val();
                        d.programme = $('#fil_programme_id')
                            .val();
                        d.semester = $('#fil_semester_id')
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
                        data: 'student_photo',
                        name: 'student_photo',
                    },
                    {
                        data: 'student_matricno',
                        name: 'student_matricno'
                    },
                    {
                        data: 'student_programme',
                        name: 'student_programme'
                    },
                    {
                        data: 'student_status',
                        name: 'student_status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],


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

            // FILTER : PROGRAMME
            $('#fil_programme_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearProgFilter').click(function() {
                $('#fil_programme_id').val('').change();
            });

            // FILTER : SEMESTER
            $('#fil_semester_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearSemFilter').click(function() {
                $('#fil_semester_id').val('').change();
            });

            // FILTER : STATUS
            $('#fil_status').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearStatusFilter').click(function() {
                $('#fil_status').val('').change();
            });

            // STUDENT PHOTO FUNCTIONS
            var defaultImageAdd = $(".previewImageAdd").data("default");
            var defaultImage = $(".previewImage").data("default");

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
            });

            $(document).on('change', '.student_photo', function(event) {
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

                container.find('.student_photo').val(null); // Reset input file
                container.find('.remove_photo').val("1"); // Tandakan gambar perlu dipadam
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

            $('.matric-input').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            // IMPORT : STUDENT
            $('#browse-btn').on('click', function() {
                $('#file').click();
            });

            $('#file').on('change', function() {
                let fileName = $(this).val().split("\\").pop();
                $('#file-name').val(fileName || "No file chosen");
                $('#import-btn').prop('disabled', false);
            });

            /* SELECT : MULTIPLE STUDENT SELECT */
            const addBtn = $("#addStudentBtn");
            const importBtn = $("#importBtn");
            const excelExportBtn = $("#excelExportBtn");
            const clearBtn = $("#clearSelectionBtn");
            const cstatusBtn = $("#changestatusBtn");
            const updateStatusBtn = $("#updatestatusBtn");

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
                cstatusBtn.toggleClass("d-none", selectedIds.size === 0);

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

                let url = "{{ route('export-student-get') }}";

                if (selectedIds.length > 0) {
                    url += "?ids=" + selectedIds.join(",");
                }
                window.location.href = url;
                clearBtn.trigger('click');
            });

            $('#student_status_change').on('change', function() {
                let status = $(this).val();
                if (status != '') {
                    updateStatusBtn.prop('disabled', false);
                } else {
                    updateStatusBtn.prop('disabled', true);
                }
            })

            updateStatusBtn.on('click', function() {
                const $button = $(this);
                const status = $('#student_status_change').val();

                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();


                if (selectedIds.length > 0) {

                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Saving...'
                    );

                    $.ajax({
                        url: "{{ route('update-student-status-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedIds,
                            status: status,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#changestatusModal').modal('hide');
                            $('.data-table').DataTable().ajax.reload();
                            $('#student_status_change').val("");
                            clearBtn.trigger('click');
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert("Error: " + xhr.responseText);
                        },
                        complete: function() {
                            $button.prop('disabled', true).html('Save Changes');
                        }
                    });
                } else {
                    alert(
                        "No valid data selected for status change."
                    );
                }
            });


        });
    </script>
@endsection
