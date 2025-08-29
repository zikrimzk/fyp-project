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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Evaluation</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Final Overview</a></li>
                                <li class="breadcrumb-item" aria-current="page">Correction</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Correction - Final Overview</h2>
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

                <!-- [ Correction - Final Overview ] start -->

                <!-- [ Filter Section ] Start -->
                <div class="col-sm-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header fw-semibold table-color text-white py-2">
                            <i class="ti ti-filter me-1"></i> FILTERS
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3 row-cols-1 row-cols-md-3 row-cols-lg-4 align-items-end">

                                {{-- Faculty --}}
                                <div>
                                    <label class="form-label fw-semibold text-muted small">Faculty</label>
                                    <div class="input-group input-group-sm">
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
                                        <button type="button" class="btn btn-outline-secondary" id="clearFacFilter"
                                            title="Clear">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Semester --}}
                                <div>
                                    <label class="form-label fw-semibold text-muted small">Semester</label>
                                    <div class="input-group input-group-sm">
                                        <select id="fil_semester_id" class="form-select">
                                            <option value="">-- Select Semester --</option>
                                            @foreach ($sems as $fil)
                                                @if ($fil->sem_status == 1)
                                                    <option value="{{ $fil->id }}" class="bg-light-success" selected>
                                                        {{ $fil->sem_label }} [Current]
                                                    </option>
                                                @elseif($fil->sem_status == 3)
                                                    <option value="{{ $fil->id }}">{{ $fil->sem_label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" id="clearSemFilter"
                                            title="Clear">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Programme --}}
                                <div>
                                    <label class="form-label fw-semibold text-muted small">Programme</label>
                                    <div class="input-group input-group-sm">
                                        <select id="fil_programme_id" class="form-select">
                                            <option value="">-- Select Programme --</option>
                                            @foreach ($progs as $fil)
                                                @if ($fil->prog_status == 1)
                                                    <option value="{{ $fil->id }}">{{ $fil->prog_code }}
                                                        ({{ $fil->prog_mode }})
                                                    </option>
                                                @elseif($fil->prog_status == 2)
                                                    <option value="{{ $fil->id }}" class="bg-light-danger">
                                                        {{ $fil->prog_code }} ({{ $fil->prog_mode }}) [Inactive]
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" id="clearProgFilter"
                                            title="Clear">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Activity --}}
                                <div>
                                    <label class="form-label fw-semibold text-muted small">Activity</label>
                                    <div class="input-group input-group-sm">
                                        <select id="fil_activity_id" class="form-select">
                                            <option value="">-- Select Activity --</option>
                                            @foreach ($acts as $fil)
                                                <option value="{{ $fil->id }}">{{ $fil->act_name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" id="clearActivityFilter"
                                            title="Clear">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div>
                                    <label class="form-label fw-semibold text-muted small">Status</label>
                                    <div class="input-group input-group-sm">
                                        <select id="fil_status" class="form-select">
                                            <option value="">-- Select Status --</option>
                                            <option value="1">Correction : Pending Student Action</option>
                                            <option value="2">Correction : Pending Supervisor Approval</option>
                                            <option value="3">Correction : Pending Examiners/Panels Approval</option>
                                            <option value="4">Correction : Pending Committee / Deputy Dean / Dean
                                                Approval</option>
                                            <option value="5">Correction : Approve & Completed</option>
                                            <option value="6">Correction : Rejected by Supervisor</option>
                                            <option value="7">Correction : Rejected by Examiners/Panels</option>
                                            <option value="8">Correction : Rejected by Committee / Deputy Dean / Dean
                                            </option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" id="clearStatusFilter"
                                            title="Clear">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Filter Section ] End -->

                <!-- [ Datatable & Option ] Start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">

                            <!-- [ Option Section ] start -->
                            <div class="mb-4 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#exportModal" id="exportModalBtn"
                                    title="Export Data">
                                    <i class="ti ti-file-export f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Export Data
                                    </span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <!-- [ Datatable ] start -->
                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th scope="col">Student</th>
                                            <th scope="col">Final Correction</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Semester</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <!-- [ Datatable ] end -->

                        </div>
                    </div>
                </div>
                <!-- [ Datatable & Option ] End -->


                @foreach ($correction as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-final-correction-post', Crypt::encrypt($upd->ac_id)) }}"
                        method="POST">
                        @csrf
                        <div class="modal fade" id="settingModal-{{ $upd->ac_id }}" tabindex="-1"
                            aria-labelledby="settingModal" aria-hidden="true">
                            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title" id="settingModalLabel">Setting</h5>
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

                                            <!-- Activity Name -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="student_name_up" class="form-label">Activity </label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $upd->activity_name }}" readonly>
                                                </div>
                                            </div>

                                            <!-- AC Start Date Input -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="ac_startdate_up" class="form-label">
                                                        Start Date <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="datetime-local"
                                                        class="form-control @error('ac_startdate_up') is-invalid @enderror"
                                                        name="ac_startdate_up" id="ac_startdate_up"
                                                        value="{{ old('ac_startdate_up', $upd->ac_startdate ? \Carbon\Carbon::parse($upd->ac_startdate)->format('Y-m-d\TH:i') : '') }}"
                                                        required>
                                                    @error('ac_startdate_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- AC Due Date Input -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="ac_duedate_up" class="form-label">
                                                        Due Date <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="datetime-local"
                                                        class="form-control @error('ac_duedate_up') is-invalid @enderror"
                                                        name="ac_duedate_up" id="ac_duedate_up"
                                                        value="{{ old('ac_duedate_up', $upd->ac_duedate ? \Carbon\Carbon::parse($upd->ac_duedate)->format('Y-m-d\TH:i') : '') }}"
                                                        required>
                                                    @error('ac_duedate_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- AC Status Input -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="ac_status_up" class="form-label">
                                                        Status <span class="text-danger">*</span>
                                                    </label>

                                                    <!-- Important Warning -->
                                                    <div class="alert alert-warning p-2 mb-2">
                                                        <strong>⚠ Important Notice:</strong> This status is managed by the
                                                        system workflow.
                                                        Updating it manually without a proper check may disrupt the
                                                        student’s correction process
                                                        and could lead to errors or data inconsistencies.
                                                    </div>

                                                    <select class="form-select @error('ac_status_up') is-invalid @enderror"
                                                        name="ac_status_up" id="ac_status_up" required>
                                                        <option value="" selected>- Select Status -</option>

                                                        <option value="1"
                                                            @if ($upd->ac_status == 1) selected @endif>
                                                            Correction : Pending Student Action
                                                        </option>
                                                        <option value="2"
                                                            @if ($upd->ac_status == 2) selected @endif>
                                                            Correction : Pending Supervisor Approval
                                                        </option>
                                                        <option value="3"
                                                            @if ($upd->ac_status == 3) selected @endif>
                                                            Correction : Pending Examiners/Panels Approval
                                                        </option>
                                                        <option value="4"
                                                            @if ($upd->ac_status == 4) selected @endif>
                                                            Correction : Pending Committee / Deputy Dean / Dean Approval
                                                        </option>
                                                        <option value="5"
                                                            @if ($upd->ac_status == 5) selected @endif>
                                                            Correction : Approve & Completed
                                                        </option>
                                                        <option value="6"
                                                            @if ($upd->ac_status == 6) selected @endif>
                                                            Correction : Rejected by Supervisor
                                                        </option>
                                                        <option value="7"
                                                            @if ($upd->ac_status == 7) selected @endif>
                                                            Correction : Rejected by Examiners/Panels
                                                        </option>
                                                        <option value="8"
                                                            @if ($upd->ac_status == 8) selected @endif>
                                                            Correction : Rejected by Committee / Deputy Dean / Dean
                                                        </option>
                                                    </select>
                                                    @error('ac_status_up')
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
                    <div class="modal fade" id="deleteModal-{{ $upd->ac_id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content border-0 rounded-4 shadow">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <i class="ti ti-trash text-danger" style="font-size: 80px;"></i>
                                        </div>
                                        <h4 class="fw-bold mb-2 text-danger">Delete Correction</h4>
                                        <div class="alert alert-warning p-2">
                                            <strong>⚠ Warning:</strong> Deleting this correction will affect the student’s
                                            confirmation status.
                                            The student will need to <strong>reconfirm</strong> their correction before
                                            proceeding.
                                        </div>
                                        <p class="text-muted mb-4">
                                            Please make sure you are absolutely certain before deleting
                                            <strong>{{ $upd->student_name }}</strong>’s correction for
                                            <strong>{{ $upd->activity_name }}</strong>.
                                        </p>
                                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary w-100"
                                                data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                            <a href="{{ route('delete-final-correction-get', ['id' => Crypt::encrypt($upd->ac_id)]) }}"
                                                class="btn btn-danger w-100">
                                                Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Delete Modal ] end -->
                @endforeach

                <!-- [ Export Modal ] start -->
                <form action="{{ route('export-final-correction-data-get') }}" method="GET" id="exportForm">
                    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-md modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4">

                                <!-- Header -->
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold" id="exportModalLabel">
                                        Export Final Correction
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <!-- Body -->
                                <div class="modal-body">
                                    <div class="row g-3">

                                        <!-- Semester Input -->
                                        <div class="col-12">
                                            <label for="ex_semester_id" class="form-label fw-semibold">Semester *</label>
                                            <select id="ex_semester_id" name="ex_semester_id" class="form-select"
                                                required>
                                                <option value="">-- Select Semester --</option>
                                                @foreach ($sems->whereIn('sem_status', [1, 3]) as $fil)
                                                    <option value="{{ $fil->id }}"
                                                        class="{{ $fil->sem_status == 1 ? 'bg-light-success' : '' }}"
                                                        {{ $fil->sem_status == 1 ? 'selected' : '' }}>
                                                        {{ $fil->sem_label }}
                                                        {{ $fil->sem_status == 1 ? '[Current]' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Choose the semester you want to export data
                                                for.</small>
                                        </div>

                                        <!-- Status Input -->
                                        <div class="col-12">
                                            <label for="ex_correction_status" class="form-label fw-semibold">Correction
                                                Status</label>
                                            <select id="ex_correction_status" name="ex_correction_status"
                                                class="form-select">
                                                <option value="">-- All Status --</option>
                                                <option value="1">Correction : Pending Student Action</option>
                                                <option value="2">Correction : Pending Supervisor Approval</option>
                                                <option value="3">Correction : Pending Examiners/Panels Approval
                                                </option>
                                                <option value="4">Correction : Pending Committee / Deputy Dean / Dean
                                                    Approval</option>
                                                <option value="5">Correction : Approve & Completed</option>
                                                <option value="6">Correction : Rejected by Supervisor</option>
                                                <option value="7">Correction : Rejected by Examiners/Panels</option>
                                                <option value="8">Correction : Rejected by Committee / Deputy Dean /
                                                    Dean
                                                </option>
                                            </select>
                                            <small class="text-muted">Filter records by their correction status.</small>
                                        </div>

                                        <!-- Export Format Input -->
                                        <div class="col-12">
                                            <label for="export_opt_id" class="form-label fw-semibold">Export Format
                                                *</label>
                                            <select id="export_opt_id" name="export_opt_id" class="form-select" required>
                                                <option value="">-- Select Format --</option>
                                                <option value="1" selected>PDF (.pdf)</option>
                                                <option value="2" disabled>Excel (.xlsx) <small>(Coming
                                                        Soon)</small></option>
                                            </select>
                                            <small class="text-muted">Choose the file format for export.</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="modal-footer bg-light">
                                    <div class="row w-100 g-2">
                                        <div class="col-6">
                                            <button type="button" class="btn btn-outline-secondary w-100"
                                                data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="submit" class="btn btn-primary w-100 " id="exportBtn"
                                                disabled>
                                                Export Data
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Export Modal ] end -->

                <!-- [ Correction - Final Overview ] end -->

            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var modalToShow = "{{ session('modal') }}";
            if (modalToShow) {
                var modalElement = document.getElementById(modalToShow);
                if (modalElement) {
                    var modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }
        });

        $(document).ready(function() {

            // EXPORT : FINAL CORRECTION
            $('#export_opt_id').on('change', function() {
                $('#exportBtn').prop('disabled', !$(this).val());
            });

            $('#export_opt_id').trigger('change');

            // DATATABLE : STUDENT ACTIVITY
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('correction-final-overview') }}",
                    data: function(d) {
                        d.faculty = $('#fil_faculty_id').val();
                        d.programme = $('#fil_programme_id').val();
                        d.semester = $('#fil_semester_id').val();
                        d.activity = $('#fil_activity_id').val();
                        d.status = $('#fil_status').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        className: "text-start"
                    },
                    {
                        data: 'student_photo',
                        name: 'student_photo',
                    },
                    {
                        data: 'ac_final_submission',
                        name: 'ac_final_submission'
                    },
                    {
                        data: 'ac_date',
                        name: 'ac_date'
                    },
                    {
                        data: 'ac_semester',
                        name: 'ac_semester'
                    },
                    {
                        data: 'ac_status',
                        name: 'ac_status'
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
                                '<td colspan="9" class="bg-light text-center"> <span class="fw-semibold text-uppercase me-2">' +
                                group + '</span> <span class="badge bg-primary">' + rows.count() +
                                '</span></td>');
                    }
                }


            });

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

            // FILTER : ACTIVITY
            $('#fil_activity_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearActivityFilter').click(function() {
                $('#fil_activity_id').val('').change();
            });

            // FILTER : STATUS
            $('#fil_status').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearStatusFilter').click(function() {
                $('#fil_status').val('').change();
            });

        });
    </script>
@endsection
