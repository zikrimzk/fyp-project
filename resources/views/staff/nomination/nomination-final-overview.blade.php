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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Nomination</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Final Overview</a></li>
                                <li class="breadcrumb-item" aria-current="page">{{ $act->act_name }}
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">{{ $act->act_name }} - Final Overview</h2>
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

                <!-- [ Nomination - Final Overview ] start -->

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

                                {{-- Status --}}
                                <div>
                                    <label class="form-label fw-semibold text-muted small">Status</label>
                                    <div class="input-group input-group-sm">
                                        <select id="fil_status" class="form-select">
                                            <option value="">-- Select Status --</option>
                                            <option value="1">Pending</option>
                                            <option value="2">
                                                Nominated - SV</option>
                                            <option value="3">
                                                Reviewed - Committee</option>
                                            <option value="4">Approved & Active</option>
                                            <option value="5">Rejected</option>
                                            <option value="6">Approved & Inactive</option>
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

                <!-- [ Datatable ] Start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th scope="col">Student</th>
                                            <th scope="col">Nomination</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Semester</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Datatable ] End -->

                @foreach ($nomination as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-final-nomination-post', Crypt::encrypt($upd->nomination_id)) }}"
                        method="POST">
                        @csrf
                        <div class="modal fade" id="settingModal-{{ $upd->nomination_id }}" tabindex="-1"
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

                                            @foreach ($evaluator->where('nom_id', $upd->nomination_id) as $eva)
                                                @php
                                                    if ($eva->eva_role == 1) {
                                                        $role = 'Examiner/Panel';
                                                    } elseif ($eva->eva_role == 2) {
                                                        $role = 'Chairman';
                                                    } else {
                                                        $role = 'Evaluator';
                                                    }
                                                @endphp
                                                <div class="col-sm-12 col-md-12 col-lg-12">
                                                    <div class="mb-3">
                                                        <label for="eva_name_{{ $eva->id }}"
                                                            class="form-label">{{ $role }}
                                                        </label>
                                                        <input type="text" class="form-control"
                                                            id="eva_name_{{ $eva->id }}"
                                                            value="{{ $eva->staff_name }}" readonly>
                                                    </div>
                                                </div>
                                            @endforeach

                                            <!-- Nomination Status Input -->
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
                                                        student’s nomination process
                                                        and could lead to errors or data inconsistencies.
                                                    </div>

                                                    <select
                                                        class="form-select @error('nom_status_up') is-invalid @enderror"
                                                        name="nom_status_up" id="nom_status_up" required>
                                                        <option value="" selected>- Select Nomination Status -
                                                        </option>

                                                        <option value="1"
                                                            @if ($upd->nom_status == 1) selected @endif>Pending
                                                        </option>
                                                        <option value="2"
                                                            @if ($upd->nom_status == 2) selected @endif>Nominated -
                                                            SV</option>
                                                        <option value="3"
                                                            @if ($upd->nom_status == 3) selected @endif>Reviewed -
                                                            Committee</option>
                                                        <option value="4"
                                                            @if ($upd->nom_status == 4) selected @endif>Approved &
                                                            Active</option>
                                                        <option value="5"
                                                            @if ($upd->nom_status == 5) selected @endif>Rejected
                                                        </option>
                                                        <option value="6"
                                                            @if ($upd->nom_status == 6) selected @endif>Approve &
                                                            Inactive</option>
                                                    </select>
                                                    @error('nom_status_up')
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
                    <div class="modal fade" id="deleteModal-{{ $upd->nomination_id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <i class="ti ti-alert-octagon text-danger" style="font-size: 80px;"></i>
                                        </div>
                                        <h4 class="fw-bold mb-3 text-danger">⚠ Delete Nomination</h4>

                                        <div class="alert alert-danger p-3 text-start">
                                            <strong>Warning:</strong> Deleting a nomination without proper checking can
                                            cause serious issues.
                                            <ul class="mt-2 mb-0 ps-3">
                                                <li>Nominations are generated <strong>automatically during semester
                                                        enrollment</strong>.</li>
                                                <li>Deleting this record will disrupt the evaluation process since the final
                                                    evaluator cannot
                                                    be confirmed.</li>
                                                <li>This action may cause inconsistencies in evaluation tracking and student
                                                    records.</li>
                                            </ul>
                                        </div>

                                        <div class="alert alert-warning p-3 text-start">
                                            <strong>Acceptable reason for deletion:</strong>
                                            <ol class="mt-2 mb-0 ps-3">
                                                <li>Duplicate nomination for the <strong>same student, same semester, and
                                                        same activity</strong>.</li>
                                            </ol>
                                            <p class="mt-2 mb-0"><em>Any other reason is not valid — the nomination must
                                                    remain as it is.</em></p>
                                        </div>

                                        <p class="text-muted mt-3 mb-4">
                                            Please double-check the details for <strong>{{ $upd->student_name }}</strong>’s
                                            nomination before
                                            proceeding with deletion.
                                        </p>

                                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary w-100"
                                                data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                            <a href="{{ route('delete-final-nomination-get', ['id' => Crypt::encrypt($upd->nomination_id)]) }}"
                                                class="btn btn-danger w-100 fw-bold">
                                                Delete Permanently
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Delete Modal ] end -->

                    <!-- [ Renominate Modal ] Start -->
                    <div class="modal fade" id="renominateModal-{{ $upd->nomination_id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="row">

                                        <!-- Icon -->
                                        <div class="col-sm-12 mb-4 text-center">
                                            <i class="ti ti-info-circle text-warning" style="font-size: 100px"></i>
                                        </div>

                                        <!-- Title -->
                                        <div class="col-sm-12 text-center">
                                            <h2 class="f-18">Update / Re-nominate Nomination?</h2>
                                        </div>

                                        <!-- Instruction -->
                                        <div class="col-sm-12 mb-3">
                                            <div
                                                class="alert alert-warning border-0 shadow-sm p-4 rounded-3 text-start f-14">
                                                <h5 class="mb-3 text-dark">
                                                    <i class="ti ti-alert-triangle text-warning me-2"></i> Important Notice
                                                </h5>

                                                <p class="mb-3">
                                                    This action may be required for one of the following reasons:
                                                </p>

                                                <ul class="mb-4 ps-3">
                                                    <li><strong>Update nomination details</strong> for the current semester
                                                        (e.g., modifying examiner or panel information, correcting data).
                                                    </li>
                                                    <li><strong>Re-nominate the student</strong> because they need to
                                                        <strong>re-present or re-submit</strong> a previous activity.
                                                        Evaluators may remain the same, but a fresh nomination record is
                                                        required.</li>
                                                </ul>

                                                <div class="border-start border-3 border-dark ps-3 mb-4">
                                                    <p class="mb-2"><strong>By clicking "Confirmed", the system
                                                            will:</strong></p>
                                                    <ul class="ps-3 mb-0">
                                                        <li>Duplicate this nomination as a <strong>new record</strong> for
                                                            the current semester.</li>
                                                        <li>Change the old record status from <em>Approved & Active</em> →
                                                            <em>Approved & Inactive</em>.</li>
                                                        <li>Retain the original nomination as reference.</li>
                                                        <li>Require updated or reconfirmed nominee details.</li>
                                                    </ul>
                                                </div>

                                                <div class="text-danger mb-3">
                                                    <strong>
                                                        ⚠ All currently approved evaluators will lose access to evaluate
                                                        this student until the new nomination is fully completed and
                                                        approved.
                                                    </strong>
                                                </div>
                                                <div class="text-danger mb-3">
                                                    <strong>⚠ This action is irreversible. Please double-check all
                                                        information.</strong>
                                                </div>

                                                <em class="text-muted d-block">By proceeding, you acknowledge and accept
                                                    responsibility for this update.</em>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <div class="d-flex justify-content-between gap-3 w-100">
                                        <button type="button" class="btn btn-light w-50"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="{{ route('renomination-data-get', ['nominationId' => Crypt::encrypt($upd->nomination_id)]) }}"
                                            class="btn btn-warning w-50">
                                            Confirmed
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Renominate Modal ] End -->
                @endforeach

                <!-- [ Nomination - Final Overview  ] end -->

            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : NOMINATION
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('nomination-final-overview', strtolower(str_replace(' ', '-', $act->act_name))) }}",
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
                        data: 'nom_document',
                        name: 'nom_document'
                    },
                    {
                        data: 'nom_date',
                        name: 'nom_date'
                    },
                    {
                        data: 'nom_semester',
                        name: 'nom_semester'
                    },
                    {
                        data: 'nom_status',
                        name: 'nom_status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],


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
