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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Supervision</a></li>
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
            </div>
            <!-- [ Alert ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- [ Student Management ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2 gap-md-3 d-md-flex flex-wrap">
                                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#addModal"><i class="ti ti-plus f-18"></i>
                                    Add Student
                                </button>
                                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#importModal"><i
                                        class="ti ti-file-import f-18"></i>
                                    Import Student
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
                                                        class="previewImage">
                                                    <label for="student_photo" class="img-avtar-upload">
                                                        <i class="ti ti-camera f-24 mb-1"></i>
                                                        <span>Upload</span>
                                                    </label>
                                                    <input type="file" id="student_photo" name="student_photo"
                                                        class="d-none" accept="image/*" />
                                                    @error('student_photo')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <label for="student_photo"
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
                                                        value="{{ old('student_phoneno') }}" maxlength="13" />
                                                    @error('student_phoneno')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div id="phone-error-message" class="text-danger text-sm"
                                                        style="display: none;">
                                                        Phone number must be in a valid format (10 or 11 digits)!
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Address Input -->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="student_matricno" class="form-label">
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
                                                    @if (old('student_status') == 1)
                                                        <option value ="1" selected>Active</option>
                                                        <option value ="2">Inactive</option>
                                                    @elseif(old('student_status') == 2)
                                                        <option value ="1">Active</option>
                                                        <option value ="2" selected>Inactive</option>
                                                    @else
                                                        <option value ="1">Active</option>
                                                        <option value ="2">Inactive</option>
                                                    @endif
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

                <!-- [ Import Modal ] start -->
                <form action="{{ route('import-student-post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal fade" id="importModal" data-bs-keyboard="false" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="mb-0">Import Student</h5>
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
                                                    <label for="file" class="form-label fw-bold">Upload File</label>
                                                    <div class="input-group">
                                                        <input class="form-control d-none" type="file" name="file"
                                                            id="file" accept=".csv, .xlsx" required>
                                                        <input type="text" class="form-control" id="file-name"
                                                            placeholder="No file chosen" readonly>
                                                        <button class="btn btn-primary" type="button" id="browse-btn">
                                                            <i class="ti ti-upload"></i> Browse
                                                        </button>
                                                    </div>
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
                                            Student</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Import Modal ] end -->

                @foreach ($studs as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-student-post', Crypt::encrypt($upd->id)) }}"
                        enctype="multipart/form-data" method="POST">
                        @csrf
                        <div class="modal fade" id="updateModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateModalLabel">Update Student</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <!-- Photo Input -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="d-grid justify-content-center align-items-center mb-3">
                                                    <div class="user-upload avatar-s w-100">
                                                        <img src="{{ empty($upd->student_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . $upd->student_directory . '/photo/' . $upd->student_photo) }}"
                                                            alt="Profile Photo" width="150" height="150"
                                                            class="previewImage">
                                                        <label for="student_photo_up_{{ $upd->id }}"
                                                            class="img-avtar-upload">
                                                            <i class="ti ti-camera f-24 mb-1"></i>
                                                            <span>Upload</span>
                                                        </label>
                                                        <input type="file" id="student_photo_up_{{ $upd->id }}"
                                                            name="student_photo_up" class="d-none student_photo"
                                                            accept="image/*" />
                                                        @error('student_photo_up')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <label for="student_photo_up_{{ $upd->id }}"
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
                                            </div>
                                            <!-- Gender Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
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
                                            </div>
                                            <!-- Email Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="student_email_up" class="form-label">Email
                                                        <span class="text-danger">*</span></label>
                                                    <input type="email"
                                                        class="form-control @error('student_email_up') is-invalid @enderror"
                                                        id="student_email_up" name="student_email_up"
                                                        placeholder="Enter Student Email"
                                                        value="{{ $upd->student_email }}" required>
                                                    @error('student_email')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Phone No Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="student_phoneno_up" class="form-label">
                                                        Phone Number
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">+60</span>
                                                        <input type="text"
                                                            class="form-control @error('student_phoneno_up') is-invalid @enderror phonenum-input"
                                                            placeholder="Enter Phone Number" name="student_phoneno_up"
                                                            value="{{ $upd->student_phoneno }}" maxlength="13" />
                                                        @error('student_phoneno_up')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <div id="phone-error-message" class="text-danger text-sm"
                                                            style="display: none;">
                                                            Phone number must be in a valid format (10 or 11 digits)!
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Address Input -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="student_address_up" class="form-label">
                                                        Address
                                                    </label>
                                                    <textarea name="student_address_up" id="student_address_up" placeholder="Enter Address" cols="10"
                                                        rows="5" class="form-control @error('student_address_up') is-invalid @enderror">{{ $upd->student_address }}</textarea>
                                                    @error('student_address_up')
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
                                                        class="form-control @error('student_matricno_up') is-invalid @enderror matric-input"
                                                        id="student_matricno_up" name="student_matricno_up"
                                                        placeholder="Enter Matric Number"
                                                        value="{{ $upd->student_matricno }}" required>
                                                    @error('student_matricno_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Semester Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="semester_id_up" class="form-label">Semester
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('semester_id_up') is-invalid @enderror"
                                                        id="semester_id_up" placeholder="Current Semester"
                                                        value="{{ Semester::where('id', $upd->semester_id)->first()->sem_label ?? '-' }}"
                                                        readonly>
                                                    @error('semester_id_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Programme Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
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
                                            </div>
                                            <!-- Status Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="student_status_up" class="form-label">
                                                        Status <span class="text-danger">*</span>
                                                    </label>
                                                    <select
                                                        class="form-select @error('student_status') is-invalid @enderror"
                                                        name="student_status_up" id="student_status_up" required>
                                                        <option value ="" selected>- Select Status -</option>
                                                        @if ($upd->student_status == 1)
                                                            <option value ="1" selected>Active</option>
                                                            <option value ="2">Inactive</option>
                                                        @elseif($upd->student_status == 2)
                                                            <option value ="1">Active</option>
                                                            <option value ="2" selected>Inactive</option>
                                                        @else
                                                            <option value ="1">Active</option>
                                                            <option value ="2">Inactive</option>
                                                        @endif
                                                    </select>
                                                    @error('student_status_up')
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
                                                <a href="{{ route('delete-student-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 1]) }}"
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
                                                <h2>Account Inactivation</h2>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 mb-3">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <p class="fw-normal f-18 text-center">
                                                    Oops! You can't delete this student.
                                                    However, you can inactive it instead. Would you like to proceed with
                                                    inactivating this student?
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <a href="{{ route('delete-student-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 2]) }}"
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

                <!-- [ Student Management ] end -->
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

            // DATATABLE : STUDENT
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('student-management') }}",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        className: "text-start"
                    },
                    {
                        data: 'student_photo',
                        name: 'student_photo'
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
                ]

            });

            $('#student_photo').on('change', function() {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        $('.previewImage').attr('src', e.target.result).show();
                    };

                    reader.readAsDataURL(file);
                }
            });

            $('.student_photo').on('change', function() {
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

            $('.matric-input').on('input', function() {
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
