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
                                            <th scope="col">Matric No</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Programme</th>
                                            <th scope="col">Mode</th>
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
                <form action="{{ route('add-activity-post') }}" method="POST">
                    @csrf
                    <div class="modal fade" id="addModal" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
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
                                        <div class="col-sm-4">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-center align-items-center mb-3">
                                                    <img src="{{ asset('assets/images/user/avatar-1.jpg') }}"
                                                        alt="Profile Photo" width="150" height="150"
                                                        class="user-avtar rounded-circle">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-8">
                                            <div class="row">
                                                <h5 class="mb-2">A. Personal Information</h5>

                                                <!-- Name Input -->
                                                <div class="col-sm-12 col-md-6 col-lg-6">
                                                    <div class="mb-3">
                                                        <label for="student_name" class="form-label">Student Name <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text"
                                                            class="form-control @error('student_name') is-invalid @enderror"
                                                            id="student_name" name="student_name"
                                                            placeholder="Enter Student Name"
                                                            value="{{ old('student_name') }}" required>
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
                                                            placeholder="Enter Student Email"
                                                            value="{{ old('student_email') }}" required>
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
                                                                class="form-control @error('student_phoneno') is-invalid @enderror tasker-phoneno"
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

                                                <h5 class="mb-2">B. Academic Information</h5>

                                                <!-- Matric No Input -->
                                                <div class="col-sm-12 col-md-6 col-lg-6">
                                                    <div class="mb-3">
                                                        <label for="student_matricno" class="form-label">Matric Number
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text"
                                                            class="form-control @error('student_matricno') is-invalid @enderror"
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
                                                            id="semester_id" name="semester_id"
                                                            placeholder="Enter Matric Number" value="{{ $current_sem }}"
                                                            readonly>
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
                                                        <select
                                                            class="form-select @error('student_status') is-invalid @enderror"
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
                    <div class="modal fade" id="addModals" tabindex="-1" aria-labelledby="addModal"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addModalLabel">Add Student</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">

                                        <!-- Name Input -->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
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
                                        <!-- Matric No Input -->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="student_matricno" class="form-label">Matric Number <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('student_matricno') is-invalid @enderror"
                                                    id="student_matricno" name="student_matricno"
                                                    placeholder="Enter Matric Number"
                                                    value="{{ old('student_matricno') }}" required>
                                                @error('student_matricno')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Matric No Input -->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="student_matricno" class="form-label">Matric Number <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('student_matricno') is-invalid @enderror"
                                                    id="student_matricno" name="student_matricno"
                                                    placeholder="Enter Matric Number"
                                                    value="{{ old('student_matricno') }}" required>
                                                @error('student_matricno')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="modal-footer justify-content-end">
                                    <div class="flex-grow-1 text-end">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="button" class="btn btn-light btn-pc-default w-100"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary w-100"
                                                    id="addApplicationBtn">
                                                    Add Activity
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Add Modal ] end -->

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

            $(function() {

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
                            data: 'act_name',
                            name: 'act_name'
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

        });
    </script>
@endsection
