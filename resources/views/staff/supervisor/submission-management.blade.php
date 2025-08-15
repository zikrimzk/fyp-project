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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Supervisor</a></li>
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
                                            <option value="1">No Attempt</option>
                                            <option value="2">Locked</option>
                                            <option value="3">Submitted</option>
                                            <option value="4">Overdue</option>
                                            <option value="5">Archive</option>
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
                            <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-4"
                                    id="clearSelectionBtn">
                                    0 selected <i class="ti ti-x f-18"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-4"
                                    data-bs-toggle="modal" data-bs-target="#multipleSettingModal"
                                    id="updatemultipleModalBtn" title="Update Submission">
                                    <i class="ti ti-edit-circle f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Update Submission
                                    </span>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-4"
                                    data-bs-toggle="modal" data-bs-target="#archiveMultipleModal"
                                    id="archivemultipleModalBtn" title="Archive">
                                    <i class="ti ti-archive f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Archive
                                    </span>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-4"
                                    data-bs-toggle="modal" data-bs-target="#unarchiveMultipleModal"
                                    id="unarchivemultipleModalBtn" title="Unarchive">
                                    <i class="ti ti-history f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Unarchive
                                    </span>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-4"
                                    id="downloadmultipleModalBtn" title="Download (.zip)">
                                    <i class="ti ti-arrow-bar-to-down f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Download (.zip)
                                    </span>
                                </button>
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2 mb-4"
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
                            <!-- [ Datatable ] end -->
                        </div>
                    </div>
                </div>
                <!-- [ Main Content ] end -->


                <!-- [ Export Modal ] start -->
                <form action="{{ route('export-my-supervision-submission-data-get') }}" method="GET" id="exportForm">
                    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-md modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4">

                                <!-- Header -->
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold" id="exportModalLabel">
                                        Export Submission Data
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
                                            <label for="ex_submission_status" class="form-label fw-semibold">Submission
                                                Status</label>
                                            <select id="ex_submission_status" name="ex_submission_status"
                                                class="form-select">
                                                <option value="">-- All Status --</option>
                                                <option value="1">Not Submitted (No Attempt / Overdue)</option>
                                                <option value="2">Submitted</option>
                                            </select>
                                            <small class="text-muted">Filter records by their submission status.</small>
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

                <!-- [ Multiple Submission Update Modal ] start -->
                <div class="modal fade" id="multipleSettingModal" tabindex="-1" aria-labelledby="multipleSettingModal"
                    aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">

                            <div class="modal-header bg-light">
                                <h5 class="modal-title" id="multipleSettingModalLabel">Submission Setting</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <!-- Due Date Input -->
                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                        <div class="mb-3">
                                            <label for="submission_duedate_ups" class="form-label">
                                                Due Date
                                            </label>
                                            <input type="datetime-local"
                                                class="form-control @error('submission_duedate_ups') is-invalid @enderror"
                                                id="submission_duedate_ups" name="submission_duedate_ups">
                                            @error('submission_duedate_ups')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Status Input -->
                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                        <div class="mb-3">
                                            <label for="submission_status_ups" class="form-label">
                                                Status
                                            </label>
                                            <select
                                                class="form-select @error('submission_status_ups') is-invalid @enderror"
                                                name="submission_status_ups" id="submission_status_ups">
                                                <option value ="" selected>- Select Status -</option>
                                                <option value ="1">Open Submission</option>
                                                <option value ="2">Locked</option>
                                            </select>
                                            @error('submission_status_ups')
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
                                        <button type="submit" class="btn btn-primary w-100"
                                            id="multipleSubmissionUpdateBtn">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Multiple Submission Update Modal ] end -->

                <!-- [ Archive Multiple Submission Modal ] start -->
                <div class="modal fade" id="archiveMultipleModal" data-bs-keyboard="false" tabindex="-1"
                    aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4 shadow">
                            <div class="modal-body p-4">
                                <div class="text-center">
                                    <div class="mb-3">
                                        <i class="ti ti-archive text-danger" style="font-size: 80px;"></i>
                                    </div>
                                    <h4 class="fw-bold mb-2">Archive these submissions?</h4>
                                    <p class="text-muted mb-4">This action will archive the selected student submissions,
                                        but you can always revert it later.</p>
                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-danger w-100"
                                            id="multipleSubmissionArchiveBtn">
                                            Archive
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Archive Multiple Submission Modal ] end -->

                <!-- [ Unarchive Multiple Submission Modal ] start -->
                <div class="modal fade" id="unarchiveMultipleModal" data-bs-keyboard="false" tabindex="-1"
                    aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4 shadow">
                            <div class="modal-body p-4">
                                <div class="text-center">
                                    <div class="mb-3">
                                        <i class="ti ti-history text-primary" style="font-size: 80px;"></i>
                                    </div>
                                    <h4 class="fw-bold mb-2">Unarchive these submissions?</h4>
                                    <p class="text-muted mb-4">You can archive them again later if needed.</p>
                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary w-100"
                                            id="multipleSubmissionUnarchiveBtn">
                                            Unarchive
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Unarchive Multiple Submission Modal ] end -->

                @foreach ($subs as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-submission-post', Crypt::encrypt($upd->submission_id)) }}"
                        method="POST">
                        @csrf
                        <div class="modal fade" id="settingModal-{{ $upd->submission_id }}" tabindex="-1"
                            aria-labelledby="settingModal" aria-hidden="true">
                            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header bg-light">
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
                                                        class="form-select @error('submission_status_up') is-invalid @enderror"
                                                        name="submission_status_up" id="submission_status_up" required>
                                                        <option value ="" selected>- Select Status -</option>
                                                        @if ($upd->submission_status == 1 || $upd->submission_status == 2 || $upd->submission_status == 5)
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

                    <!-- [ Archive Modal ] start -->
                    <div class="modal fade" id="archiveModal-{{ $upd->submission_id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <i class="ti ti-archive text-danger" style="font-size: 80px;"></i>
                                        </div>
                                        <h4 class="fw-bold mb-2">Archive this submission?</h4>
                                        <p class="text-muted mb-4">This action will archive the submission, but you can
                                            always revert it later.</p>
                                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary w-100"
                                                data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                            <a href="{{ route('archive-submission-get', ['id' => Crypt::encrypt($upd->submission_id), 'opt' => 1]) }}"
                                                class="btn btn-danger w-100">
                                                Archive
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Archive Modal ] end -->

                    <!-- [ Unarchive Modal ] start -->
                    <div class="modal fade" id="unarchiveModal-{{ $upd->submission_id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <i class="ti ti-history text-primary" style="font-size: 80px;"></i>
                                        </div>
                                        <h4 class="fw-bold mb-2">Unarchive this submission?</h4>
                                        <p class="text-muted mb-4">You can archive it again later if needed.</p>
                                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary w-100"
                                                data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                            <a href="{{ route('archive-submission-get', ['id' => Crypt::encrypt($upd->submission_id), 'opt' => 2]) }}"
                                                class="btn btn-primary w-100">
                                                Unarchive
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Unarchive Modal ] end -->
                @endforeach

                <!-- [ Submission Management ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            $('#export_opt_id').on('change', function() {
                $('#exportBtn').prop('disabled', !$(this).val());
            });

            $('#export_opt_id').trigger('change');

            // DATATABLE : SUBMISSION
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('my-supervision-submission-management') }}",
                    data: function(d) {
                        d.faculty = $('#fil_faculty_id').val();
                        d.programme = $('#fil_programme_id').val();
                        d.semester = $('#fil_semester_id').val();
                        d.activity = $('#fil_activity_id').val();
                        d.document = $('#fil_document_id').val();
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

            // FILTER : ACTIVITY
            $('#fil_activity_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearActivityFilter').click(function() {
                $('#fil_activity_id').val('').change();
            });

            // FILTER : DOCUMENT
            $('#fil_document_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearDocumentFilter').click(function() {
                $('#fil_document_id').val('').change();
            });


            // FILTER : STATUS
            $('#fil_status').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
                clearBtn.trigger('click');

            });

            $('#clearStatusFilter').click(function() {
                $('#fil_status').val('').change();
            });

            /* SELECT : MULTIPLE STUDENT SELECT */
            const reassignBtn = $("#reassignBtn");
            const clearBtn = $("#clearSelectionBtn");
            const updatemultipleModalBtn = $("#updatemultipleModalBtn");
            const archivemultipleModalBtn = $('#archivemultipleModalBtn');
            const unarchivemultipleModalBtn = $('#unarchivemultipleModalBtn');
            const downloadmultipleModalBtn = $('#downloadmultipleModalBtn');
            const multipleSubmissionUpdateBtn = $("#multipleSubmissionUpdateBtn");
            const multipleSubmissionArchiveBtn = $("#multipleSubmissionArchiveBtn");
            const multipleSubmissionUnarchiveBtn = $("#multipleSubmissionUnarchiveBtn");

            let selectedIds = new Set();

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

            $(document).on("change", ".user-checkbox", function() {
                let id = $(this).val();
                if ($(this).prop("checked")) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
                toggleSelectButton();
            });

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
                let hasSubmitted = false;
                let hasArchived = false;

                $(".user-checkbox:checked").each(function() {
                    const row = $(this).closest("tr");
                    const table = $('.data-table').DataTable();
                    const rowIndex = table.row(row).index();

                    const submitted = table.cell(rowIndex, 5).data();
                    const archive = table.cell(rowIndex, 5).data();
                    const subDate = table.cell(rowIndex, 4).data();

                    if (submitted.trim().toLowerCase() !=
                        '<span class="badge bg-light-success">submitted</span>' && subDate.trim() == '-') {
                        hasSubmitted = false;
                    } else {
                        hasSubmitted = true;
                    }

                    if (archive.trim().toLowerCase() !=
                        '<span class="badge bg-secondary">archive</span>') {
                        hasArchived = false;
                    } else {
                        hasArchived = true;
                    }
                });

                reassignBtn.toggleClass("d-none", selectedCount !== 0);

                updatemultipleModalBtn.prop("disabled", hasArchived);
                updatemultipleModalBtn.toggleClass("d-none", selectedCount === 0 || hasArchived);

                archivemultipleModalBtn.prop("disabled", hasArchived);
                archivemultipleModalBtn.toggleClass("d-none", selectedCount === 0 || hasArchived);

                unarchivemultipleModalBtn.prop("disabled", !hasArchived);
                unarchivemultipleModalBtn.toggleClass("d-none", selectedCount === 0 || !hasArchived);

                downloadmultipleModalBtn.prop("disabled", !hasSubmitted);
                downloadmultipleModalBtn.toggleClass("d-none", selectedCount === 0);

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

            multipleSubmissionUpdateBtn.on('click', function() {
                const $button = $(this);
                const duedate = $('#submission_duedate_ups').val();
                const status = $('#submission_status_ups').val();

                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Saving...'
                    );

                    $.ajax({
                        url: "{{ route('update-multiple-submission-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedIds,
                            submission_status_ups: status,
                            submission_duedate_ups: duedate,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#submission_duedate_ups').val('');
                            $('#submission_status_ups').val('');
                            $('#multipleSettingModal').modal('hide');
                            clearBtn.trigger('click');
                            $('.data-table').DataTable().ajax
                                .reload();

                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert("Error: " + (xhr.responseJSON?.message || xhr.responseText));
                        },
                        complete: function() {
                            $button.prop('disabled', false).html('Save Changes');
                        }
                    });
                } else {
                    alert("No valid data selected for submission update.");
                }
            });

            multipleSubmissionArchiveBtn.on('click', function() {
                const $button = $(this);

                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Archiving...'
                    );

                    $.ajax({
                        url: "{{ route('archive-multiple-submission-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedIds,
                            option: 1,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#archiveMultipleModal').modal('hide');
                            clearBtn.trigger('click');
                            $('.data-table').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert("Error: " + (xhr.responseJSON?.message || xhr.responseText));
                        },
                        complete: function() {
                            $button.prop('disabled', false).html('Archive');
                        }
                    });
                } else {
                    alert("No valid data selected for submission archive.");
                }
            });

            multipleSubmissionUnarchiveBtn.on('click', function() {
                const $button = $(this);

                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Unarchiving...'
                    );

                    $.ajax({
                        url: "{{ route('archive-multiple-submission-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedIds,
                            option: 2,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#unarchiveMultipleModal').modal('hide');
                            clearBtn.trigger('click');
                            $('.data-table').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert("Error: " + (xhr.responseJSON?.message || xhr.responseText));
                        },
                        complete: function() {
                            $button.prop('disabled', false).html('Unarchive');
                        }
                    });
                } else {
                    alert("No valid data selected for submission unarchive.");
                }
            });

            downloadmultipleModalBtn.on('click', function() {
                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {

                    let idsParam = encodeURIComponent(JSON.stringify(selectedIds));
                    window.location.href = "{{ route('download-multiple-submission-get') }}?ids=" +
                        idsParam;
                    clearBtn.trigger('click');
                    $('.data-table').DataTable().ajax.reload();
                } else {
                    alert("No valid data selected for submission download.");
                }
            });
        });
    </script>
@endsection
