@php
    use App\Models\Semester;
    use App\Models\Student;
    use Illuminate\Support\Facades\Crypt;
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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Student</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('semester-enrollment') }}">
                                        Semester Enrollment</a></li>
                                <li class="breadcrumb-item" aria-current="page">Student List ({{ $sems->sem_label }})</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h3 class="mb-0 d-flex align-items-center ">
                                    <a href="{{ route('semester-enrollment') }}" class="btn me-2">
                                        <span class="f-18">
                                            <i class="ti ti-arrow-left"></i>
                                        </span>
                                    </a>
                                    Student List ({{ $sems->sem_label }})
                                </h3>
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
                                    <strong>Student Matric No:</strong>
                                    {{ Student::where('id', $row['data']['student_matricno'])->first()->student_matricno ?? 'Not Found' }}
                                    -
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

                <!-- [ Semester Student List ] start -->
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
                                @if ($sems->sem_status == 1)
                                    <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                        data-bs-toggle="modal" data-bs-target="#assignModal" title="Enroll Student"
                                        id="assignStudentBtn">
                                        <i class="ti ti-plus f-18"></i> <span class="d-none d-sm-inline me-2">Enroll
                                            Student</span>
                                    </button>
                                    <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                        data-bs-toggle="modal" data-bs-target="#import-assign-Modal" id="importStudentBtn"
                                        title="Import Student">
                                        <i class="ti ti-file-import f-18"></i>
                                        <span class="d-none d-sm-inline me-2">Import Student</span>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2"
                                    id="excelExportBtn" title="Export Data">
                                    <i class="ti ti-file-export f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Export Data
                                    </span>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none"
                                    data-bs-toggle="modal" data-bs-target="#updateStatusModal" id="updateStatusModalBtn"
                                    title=" Update Semester Status">
                                    <i class="ti ti-edit f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Update Semester Status
                                    </span>
                                </button>

                                @if ($sems->sem_status == 1)
                                    <button type="button"
                                        class="btn btn-outline-danger d-flex align-items-center gap-2 d-none"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal" id="deleteModalBtn"
                                        title=" Delete Student">
                                        <i class="ti ti-trash f-18"></i>
                                        <span class="d-none d-sm-inline me-2">
                                            Delete Student
                                        </span>
                                    </button>
                                @endif
                            </div>
                            <!-- [ Option Section ] end -->

                            <!-- [ Filter Section ] Start -->
                            <div class="row g-3 align-items-end">

                                <div class="col-sm-12 col-md-4 mb-3">
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
                                                    <option value="{{ $fil->id }}" class="bg-light-success"
                                                        selected>
                                                        {{ $fil->fac_code }} [Default]
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearFacFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4 mb-3">
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

                                <div class="col-sm-12 col-md-4 mb-3">
                                    <div class="input-group">
                                        <select id="fil_status" class="form-select">
                                            <option value="">-- Select Status --</option>
                                            <option value="1">Active</option>
                                            <option value="2">Inactive</option>
                                            <option value="3">Barred</option>
                                            <option value="4">Completed</option>
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
                                            <th scope="col">Semester Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- [ Assign Modal ] start -->
                <form action="{{ route('assign-student-post', Crypt::encrypt($sem_id)) }}" method="POST">
                    @csrf
                    <div class="modal fade" id="assignModal" data-bs-keyboard="false" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content border-0 shadow-lg rounded-3">
                                <div class="modal-header bg-light">
                                    <h5 class="mb-0"><i class="ti ti-user-plus me-2"></i>Enroll Student</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body px-4 py-3">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="student_matricno" class="form-label">Student Matric No</label>
                                            <div class="input-group">
                                                <input type="text"
                                                    class="form-control @error('student_matricno') is-invalid @enderror"
                                                    id="student_matricno" name="student_matricno"
                                                    placeholder="Enter Matric Number" required>
                                                <button type="button" class="btn btn-outline-primary"
                                                    id="studentFindBtn">
                                                    <i class="ti ti-search me-1"></i> Find
                                                </button>
                                            </div>
                                            @error('student_matricno')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Student Name</label>
                                            <input type="text" class="form-control bg-light" id="student_name"
                                                readonly>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Student Programme</label>
                                            <input type="text" class="form-control bg-light" id="student_programme"
                                                readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="modal-footer pt-2 bg-light">
                                    <div class="row w-100 g-2">
                                        <div class="col-12 col-md-6">
                                            <button type="reset" class="btn btn-outline-secondary w-100"
                                                data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <button type="submit" class="btn btn-primary w-100" id="assignBtn" disabled>
                                                Enroll Student
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Assign Modal ] end -->

                <!-- [ Import Student Modal ] start -->
                <form action="{{ route('import-student-semester-post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal fade" id="import-assign-Modal" data-bs-keyboard="false" tabindex="-1"
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
                                                        <li>Ensure the <strong>current semester</strong> is set before
                                                            continuing the assignment process.</li>
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
                                                        name="semester_file" accept=".csv, .xlsx" required>
                                                    <input type="text" class="form-control" id="file-name"
                                                        placeholder="No file chosen" readonly>
                                                    <button class="btn btn-outline-primary" type="button"
                                                        id="browse-btn">
                                                        <i class="ti ti-folder-up"></i> Browse
                                                    </button>
                                                </div>
                                                <div class="form-text mt-2">
                                                    <a href="{{ asset('assets/excel-template/e-PGS_ASSIGN_STUDENT_SEMESTER_TEMPLATE.xlsx') }}"
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
                <!-- [ Import Student Modal ] end -->

                <!-- [ Multiple Update Status Modal ] start -->
                <div class="modal fade" id="updateStatusModal" data-bs-keyboard="false" tabindex="-1"
                    aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content shadow-sm rounded-4 border-0">

                            <!-- Modal Header -->
                            <div class="modal-header bg-light border-bottom">
                                <h5 class="modal-title mb-0">Update Semester Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <!-- Modal Body -->
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="student_semester_status_change" class="form-label">
                                                Semester Status <span class="text-danger">*</span>
                                            </label>
                                            <select
                                                class="form-select @error('student_semester_status_change') is-invalid @enderror"
                                                name="student_semester_status_change" id="student_semester_status_change"
                                                required>
                                                <option value="" selected>- Select Semester Status -</option>
                                                <option value="1" @if (old('student_semester_status_change') == 1) selected @endif>
                                                    Active</option>
                                                <option value="2" @if (old('student_semester_status_change') == 2) selected @endif>
                                                    Inactive</option>
                                                <option value="3" @if (old('student_semester_status_change') == 3) selected @endif>
                                                    Barred</option>
                                                <option value="4" @if (old('student_semester_status_change') == 4) selected @endif>
                                                    Completed</option>
                                            </select>
                                            @error('student_semester_status_change')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Footer -->
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
                                            disabled>
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- [ Multiple Update Status Modal ] end -->

                <!-- [ Multiple Delete Modal ] start -->
                <div class="modal fade" id="deleteModal" data-bs-keyboard="false" tabindex="-1" aria-hidden="true"
                    data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg rounded-4 border-0">

                            <div class="modal-body p-4">
                                <!-- Trash Icon -->
                                <div class="d-flex justify-content-center mb-3">
                                    <i class="ti ti-trash text-danger" style="font-size: 80px;"></i>
                                </div>

                                <!-- Confirmation Title -->
                                <h4 class="text-center fw-semibold mb-2">Are you sure?</h4>

                                <!-- Description -->
                                <p class="text-center text-muted mb-4">The selected student will be removed from this
                                    semester.
                                    This action is reversible, and you can add the student back anytime.</p>

                                <!-- Action Buttons -->
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <button type="reset" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <a href="javascript:void(0)" class="btn btn-danger w-100" id="deleteStudentBtn">
                                            Delete Anyways
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- [ Multiple Delete Modal ] end -->

                @foreach ($studs as $upd)
                    <!-- [ Update Status Modal ] start -->
                    <form
                        action="{{ route('update-student-semester-post', ['studentID' => Crypt::encrypt($upd->student_id), 'semID' => Crypt::encrypt($sem_id)]) }}"
                        method="POST">
                        @csrf
                        <div class="modal fade" id="updateStatusModal-{{ $upd->student_id }}" tabindex="-1"
                            aria-labelledby="updateModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content shadow-lg rounded-4 border-0">

                                    <!-- Modal Header -->
                                    <div class="modal-header bg-light rounded-top-4">
                                        <h5 class="modal-title fw-semibold" id="updateModalLabel">Update Semester Status
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <!-- Modal Body -->
                                    <div class="modal-body px-4 py-3">
                                        <div class="mb-3">
                                            <label for="student_semester_status_change" class="form-label fw-semibold">
                                                Semester Status <span class="text-danger">*</span>
                                            </label>
                                            <select id="student_semester_status_change"
                                                class="form-select @error('student_semester_status_change') is-invalid @enderror"
                                                name="student_semester_status_change" required>
                                                <option value="" selected disabled>- Select Semester Status -
                                                </option>
                                                <option value="1" @if ($upd->ss_status == 1) selected @endif>
                                                    Active</option>
                                                <option value="2" @if ($upd->ss_status == 2) selected @endif>
                                                    Inactive</option>
                                                <option value="3" @if ($upd->ss_status == 3) selected @endif>
                                                    Barred</option>
                                                <option value="4" @if ($upd->ss_status == 4) selected @endif>
                                                    Completed</option>
                                            </select>
                                            @error('student_semester_status_change')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Modal Footer -->
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
                    <!-- [ Update Status Modal ] end -->

                    <!-- [ Delete Modal ] start -->
                    <div class="modal fade" id="deleteModal-{{ $upd->id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow-lg rounded-4 border-0">

                                <div class="modal-body p-4">
                                    <!-- Trash Icon -->
                                    <div class="d-flex justify-content-center mb-3">
                                        <i class="ti ti-trash text-danger" style="font-size: 80px;"></i>
                                    </div>

                                    <!-- Confirmation Title -->
                                    <h4 class="text-center fw-semibold mb-2">Are you sure?</h4>

                                    <!-- Description -->
                                    <p class="text-center text-muted mb-4">The student will be removed from this semester.
                                        This action is reversible, and you can add the student back anytime.</p>

                                    <!-- Action Buttons -->
                                    <div class="row g-2">
                                        <div class="col-12 col-md-6">
                                            <button type="reset" class="btn btn-outline-secondary w-100"
                                                data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <a href="{{ route('delete-student-semester-get', ['studentID' => Crypt::encrypt($upd->student_id), 'semID' => Crypt::encrypt($sem_id)]) }}"
                                                class="btn btn-danger w-100">
                                                Delete Anyways
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- [ Delete Modal ] end -->
                @endforeach

                <!-- [ Semester Student List ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : STUDENT LIST
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('semester-student-list', Crypt::encrypt($sem_id)) }}",
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
                        data: 'ss_status',
                        name: 'ss_status'
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

            // FILTER : STATUS
            $('#fil_status').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearStatusFilter').click(function() {
                $('#fil_status').val('').change();
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

            /* ASSIGN STUDENT : FETCHING STUDENT DATA */
            const studentMatricTxt = $('#student_matricno');
            const studentFindBtn = $('#studentFindBtn');
            const studentNameTxt = $('#student_name');
            const studentProgrammeTxt = $('#student_programme');
            const assignBtn = $('#assignBtn');

            function getStudentData(matricno) {
                $.ajax({
                    url: "{{ route('get-student-data-post') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        matricNo: matricno
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            studentNameTxt.val(response.student.student_name);
                            const programme = response.student.prog_code + " (" + response.student
                                .prog_mode + ")";
                            studentProgrammeTxt.val(programme);
                            assignBtn.prop('disabled', false);
                        } else {
                            studentNameTxt.val("Not Found");
                            studentProgrammeTxt.val("Not Found");
                            assignBtn.prop('disabled', true);
                        }
                    },
                    error: function(xhr) {
                        studentNameTxt.val("Not Found");
                        studentProgrammeTxt.val("Not Found");
                        assignBtn.prop('disabled', true);
                    }
                });
            }

            studentFindBtn.on('click', function() {
                const matricno = studentMatricTxt.val();
                getStudentData(matricno);
            });


            /* SELECT : MULTIPLE STUDENT SELECT */
            const assignStudentBtn = $("#assignStudentBtn");
            const importStudentBtn = $("#importStudentBtn");
            const excelExportBtn = $("#excelExportBtn");
            const clearBtn = $("#clearSelectionBtn");
            const cstatusBtn = $("#updateStatusModalBtn");
            const updateStatusBtn = $("#updatestatusBtn");
            const deleteModalBtn = $("#deleteModalBtn");
            const deleteStudentBtn = $("#deleteStudentBtn");


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

                assignStudentBtn.toggleClass("d-none", selectedIds.size !== 0);
                importStudentBtn.toggleClass("d-none", selectedIds.size !== 0);
                deleteModalBtn.toggleClass("d-none", selectedIds.size === 0);
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

                let url = "{{ route('export-student-semester-enrollment-get') }}";

                if (selectedIds.length > 0) {
                    url += "?ids=" + selectedIds.join(",");
                    url += "&semester_id=" + "{{ $sem_id }}";
                } else {
                    url += "?semester_id=" + "{{ $sem_id }}";
                }
                window.location.href = url;
            });

            $('#student_semester_status_change').on('change', function() {
                let status = $(this).val();
                if (status != '') {
                    updateStatusBtn.prop('disabled', false);
                } else {
                    updateStatusBtn.prop('disabled', true);
                }
            })

            updateStatusBtn.on('click', function() {
                const $button = $(this);
                const status = $('#student_semester_status_change').val();

                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();


                if (selectedIds.length > 0) {

                    // Disable the button and show loading text
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Saving...'
                    );

                    $.ajax({
                        url: "{{ route('update-multiple-student-semester-post') }}",
                        type: "POST",
                        data: {
                            student_ids: selectedIds,
                            semester_id: "{{ $sem_id }}",
                            status: status,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#updateStatusModal').modal('hide');
                            $('#student_semester_status_change').val("");
                            $('.data-table').DataTable().ajax
                                .reload();
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

            deleteStudentBtn.on('click', function() {
                const $button = $(this);

                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();


                if (selectedIds.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...'
                    );

                    $.ajax({
                        url: "{{ route('delete-multiple-student-semester-post') }}",
                        type: "POST",
                        data: {
                            student_ids: selectedIds,
                            semester_id: "{{ $sem_id }}",
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#deleteModal').modal('hide');
                            $('.data-table').DataTable().ajax
                                .reload();
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
                        "No valid data selected for delete."
                    );
                }
            });

        });
    </script>
@endsection
