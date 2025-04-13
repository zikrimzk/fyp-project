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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Submission</a></li>
                                <li class="breadcrumb-item" aria-current="page">Submission Management</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Submission Management</h2>
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

                <!-- [ Submission Management ] start -->
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
                                <a href="{{ route('assign-student-submission') }}"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2"
                                    title="Re-assign Submission">
                                    <i class="ti ti-refresh f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Re-assign Submission
                                    </span>
                                </a>
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
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearProgFilter">
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
                                                @elseif($fil->sem_status == 0)
                                                    <option value="{{ $fil->id }}"> {{ $fil->sem_label }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearSemFilter">
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
                                        <button type="button" class="btn btn-outline-danger btn-sm"
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
                                            <th scope="col">Student</th>
                                            <th scope="col">Document</th>
                                            <th scope="col">Due Date</th>
                                            <th scope="col">Submission Date</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Activity</th>
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
                                <div class="modal-header">
                                    <h5 class="mb-0">Add Student</h5>
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
                                                    <label for="student_photo" class="img-avtar-upload">
                                                        <i class="ti ti-camera f-24 mb-1"></i>
                                                        <span>Upload</span>
                                                    </label>
                                                    <input type="file" id="student_photo" name="student_photo"
                                                        class="d-none" accept="image/*" />
                                                </div>
                                                <label for="student_photo" class="btn btn-sm btn-secondary mt-2 mb-2">
                                                    Change Photo
                                                </label>
                                                <button type="button" id="resetPhoto"
                                                    class="btn btn-sm btn-light-danger">
                                                    Reset Photo
                                                </button>
                                            </div>
                                            @error('student_photo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <h5 class="mb-2">A. Personal Information</h5>

                                        <!-- Name Input -->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="student_name" class="form-label">Student Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('student_name') is-invalid @enderror"
                                                    id="student_name" name="student_name"
                                                    placeholder="Enter Student Name" value="{{ old('student_name') }}"
                                                    required>
                                                @error('student_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Gender Input -->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="student_gender" class="form-label">Gender
                                                    <span class="text-danger">*</span></label>
                                                <select name="student_gender" id="student_gender"
                                                    class="form-select @error('student_gender') is-invalid @enderror"
                                                    required>
                                                    <option value="" selected>- Select Gender -</option>
                                                    @if (old('student_gender') == 'male')
                                                        <option value="male" selected>Male</option>
                                                        <option value="female">Female</option>
                                                    @elseif(old('student_gender') == 'female')
                                                        <option value="">- Select Gender -</option>
                                                        <option value="male">Male</option>
                                                        <option value="female" selected>Female</option>
                                                    @else
                                                        <option value="male">Male</option>
                                                        <option value="female">Female</option>
                                                    @endif
                                                </select>
                                                @error('student_gender')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Email Input -->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="student_email" class="form-label">Email
                                                    <span class="text-danger">*</span></label>
                                                <input type="email"
                                                    class="form-control @error('student_email') is-invalid @enderror"
                                                    id="student_email" name="student_email"
                                                    placeholder="Enter Student Email" value="{{ old('student_email') }}"
                                                    required>
                                                @error('student_email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
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
                                                        placeholder="Enter Phone Number" name="student_phoneno"
                                                        value="{{ old('student_phoneno') }}" maxlength="11" />
                                                    @error('student_phoneno')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Address Input -->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="student_address" class="form-label">
                                                    Address
                                                </label>
                                                <textarea name="student_address" id="student_address" placeholder="Enter Address" cols="10" rows="5"
                                                    class="form-control @error('student_address') is-invalid @enderror">{{ old('student_address') }}</textarea>
                                                @error('student_address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <h5 class="mb-2 mt-3">B. Academic Information</h5>

                                        <!-- Matric No Input -->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="student_matricno" class="form-label">Matric Number
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('student_matricno') is-invalid @enderror matric-input"
                                                    id="student_matricno" name="student_matricno"
                                                    placeholder="Enter Matric Number"
                                                    value="{{ old('student_matricno') }}" required>
                                                @error('student_matricno')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Semester Input -->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="semester_id" class="form-label">Semester
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('semester_id') is-invalid @enderror"
                                                    id="semester_id" name="semester_id" placeholder="Current Semester"
                                                    value="{{ $current_sem }}" readonly>
                                                @error('semester_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Programme Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="programme_id" class="form-label">Programme <span
                                                        class="text-danger">*</span></label>
                                                <select name="programme_id" id="programme_id"
                                                    class="form-select @error('programme_id') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Programme -</option>
                                                    @foreach ($progs as $prog)
                                                        @if (old('programme_id') == $prog->id)
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
                                                @error('programme_id')
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
                                                <select class="form-select @error('student_status') is-invalid @enderror"
                                                    name="student_status" required>
                                                    <option value ="" selected>- Select Status -</option>
                                                    <option value ="1"
                                                        @if (old('student_status') == 1) selected @endif>Active</option>
                                                    <option value ="2"
                                                        @if (old('student_status') == 2) selected @endif>Inactive
                                                    </option>
                                                    <option value ="3"
                                                        @if (old('student_status') == 3) selected @endif>Extend</option>
                                                    <option value ="4"
                                                        @if (old('student_status') == 4) selected @endif>Terminate
                                                    </option>
                                                    <option value ="5"
                                                        @if (old('student_status') == 5) selected @endif>Withdraw
                                                    </option>
                                                </select>
                                                @error('student_status')
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
                                        <button type="submit" class="btn btn-primary">Add Student</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Add Modal ] end -->

                <!-- [ Change Status Modal ] start -->
                <div class="modal fade" id="changestatusModal" data-bs-keyboard="false" tabindex="-1"
                    aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="mb-0">Change Status</h5>
                                <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default ms-auto"
                                    data-bs-dismiss="modal">
                                    <i class="ti ti-x f-20"></i>
                                </a>
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
                            <div class="modal-footer justify-content-end">
                                <div class="flex-grow-1 text-end">
                                    <button type="reset" class="btn btn-link-danger btn-pc-default"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" id="updatestatusBtn" disabled>Update
                                        Status</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Change Status Modal ] end -->

                @foreach ($subs as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="" method="POST">
                        @csrf
                        <div class="modal fade" id="settingModal-{{ $upd->submission_id }}" tabindex="-1"
                            aria-labelledby="settingModal" aria-hidden="true">
                            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="settingModalLabel">Submission Setting</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">

                                            <!-- Student Name -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="student_name_up" class="form-label">Student Name </label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $upd->student_name }}" readonly>
                                                </div>
                                            </div>
                                            <!-- Document Name -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="student_email_up" class="form-label">Document</label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $upd->document_name }}" readonly>
                                                </div>
                                            </div>

                                            <!-- Due Date Input -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="submission_duedate_up" class="form-label">Due Date
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="datetime-local"
                                                        class="form-control @error('submission_duedate_up') is-invalid @enderror"
                                                        id="submission_duedate_up" name="submission_duedate_up"
                                                        placeholder="Enter Matric Number"
                                                        value="{{ $upd->submission_duedate }}" required>
                                                    @error('submission_duedate_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Status Input -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="submission_status_up" class="form-label">
                                                        Status <span class="text-danger">*</span>
                                                    </label>
                                                    <select
                                                        class="form-select @error('student_status') is-invalid @enderror"
                                                        name="submission_status_up" id="submission_status_up" required>
                                                        <option value ="" selected>- Select Status -</option>
                                                        @if ($upd->submission_status == 1 || $upd->submission_status == 2)
                                                            <option value ="1"
                                                                @if ($upd->submission_status == 1) selected @endif>Open
                                                                Submission
                                                            </option>
                                                            <option value ="2"
                                                                @if ($upd->submission_status == 2) selected @endif>Locked
                                                            </option>
                                                        @elseif($upd->submission_status == 3)
                                                            <option value ="3" selected>
                                                                Submitted
                                                            </option>
                                                        @elseif($upd->submission_status == 4)
                                                            <option value ="2">
                                                                Locked
                                                            </option>
                                                            <option value ="4"selected>
                                                                Overdue
                                                            </option>
                                                        @endif
                                                    </select>
                                                    @error('submission_status_up')
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
                    <div class="modal fade" id="deleteModal-{{ $upd->submission_id }}" data-bs-keyboard="false"
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
                                            <div class="d-flex justify-content-center align-items-center text-center">
                                                <h2>Are you sure to delete this submission ?</h2>
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
                                                <a href="{{ route('delete-student-get', ['id' => Crypt::encrypt($upd->submission_id), 'opt' => 1]) }}"
                                                    class="btn btn-danger w-100">Delete Anyways</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Delete Modal ] end -->
                @endforeach

                <!-- [ Submission Management ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : SUBMISSION
            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('submission-management') }}",
                    data: function(d) {
                        d.faculty = $('#fil_faculty_id').val();
                        d.programme = $('#fil_programme_id').val();
                        d.semester = $('#fil_semester_id').val();
                        d.status = $('#fil_status').val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'student_photo',
                        name: 'student_photo'
                    },
                    {
                        data: 'document_name',
                        name: 'document_name'
                    },
                    {
                        data: 'submission_duedate',
                        name: 'submission_duedate'
                    },
                    {
                        data: 'submission_date',
                        name: 'submission_date'
                    },
                    {
                        data: 'submission_status',
                        name: 'submission_status'
                    },
                    {
                        data: 'activity_name',
                        name: 'activity_name',
                        visible: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                rowGroup: {
                    dataSrc: 'activity_name',
                    startRender: function(rows, group) {
                        return $('<tr/>')
                            .append(
                                '<td colspan="7" class="bg-light text-center"> <span class="fw-semibold text-uppercase me-2">' +
                                group + '</span> <span class="badge bg-primary">' + rows.count() +
                                '</span></td>');
                    }
                }

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

                    // Disable the button and show loading text
                    // $button.prop('disabled', true).html(
                    //     '<span class="spinner-border spinner-border-sm me-2"></span>Saving...'
                    // );

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
                            $('.data-table').DataTable().ajax
                                .reload();
                            $('#student_status_change').val("");
                            updateStatusBtn.prop('disabled', true);


                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert("Error: " + xhr.responseText);
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
