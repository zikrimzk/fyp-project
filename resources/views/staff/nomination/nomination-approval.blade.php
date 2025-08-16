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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Approval</a></li>
                                <li class="breadcrumb-item" aria-current="page">{{ $act->act_name }}
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">{{ $act->act_name }} - Nomination Approval</h2>
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
                <!-- [ Activity Nomination ] start -->

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
                                            <option value="2" @if (auth()->user()->staff_role == 1) selected @endif>
                                                Nominated - SV</option>
                                            <option value="3" @if (auth()->user()->staff_role == 3 || auth()->user()->staff_role == 4) selected @endif>
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

                @foreach ($data as $nom)
                    <!-- [ Update Alert Modal ] Start -->
                    <div class="modal fade" id="updateNominationModal-{{ $nom->nomination_id }}-{{ $nom->semester_id }}"
                        data-bs-keyboard="false" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
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
                                            <div class="alert alert-warning border text-start f-14">
                                                <strong class="d-block mb-2 text-dark">Important Notice:</strong>

                                                <p class="mb-2">
                                                    This action may be required for one of the following reasons:
                                                </p>
                                                <ul class="mb-2 ps-3">
                                                    <li><strong>To update nomination details</strong> for the current
                                                        semester (e.g., modifying examiner or panel information, correcting
                                                        data).</li>
                                                    <li><strong>To re-nominate the student</strong> for the current semester
                                                        because they need to <strong>re-present or re-submit</strong> a
                                                        previous activity, requiring a new evaluation process. Evaluators
                                                        may remain the same, but a fresh nomination record is still required
                                                        to proceed.</li>
                                                </ul>

                                                <hr class="my-3">

                                                <ul class="mb-2 ps-3">
                                                    <li><strong>Only one nomination update is allowed per student per
                                                            semester.</strong></li>
                                                    <li>If this is the student’s first nomination for the current semester,
                                                        updates are only allowed in the <strong>next semester</strong>.</li>
                                                    <li>Please review the nomination details before confirming.</li>
                                                    <li>By clicking <strong>"Confirmed"</strong>, the system will:
                                                        <ul class="ps-3">
                                                            <li>Duplicate this nomination as a new record for the current
                                                                semester,</li>
                                                            <li>Retain the original nomination as reference,</li>
                                                            <li>Require you to provide updated or reconfirmed nominee
                                                                details.</li>
                                                        </ul>
                                                    </li>
                                                    <li class="text-danger"><strong>All currently approved evaluators will
                                                            lose access to evaluate this student until the new nomination is
                                                            fully completed.</strong></li>
                                                    <li class="text-danger"><strong>This action is irreversible. Please
                                                            double-check all information.</strong></li>
                                                </ul>

                                                <div class="mt-2">
                                                    <em class="text-muted d-block">By proceeding, you acknowledge and
                                                        accept responsibility for this update.</em>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <div class="d-flex justify-content-between gap-3 w-100">
                                        <button type="button" class="btn btn-light w-50"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="{{ route('renomination-data-get', ['nominationId' => Crypt::encrypt($nom->nomination_id)]) }}"
                                            class="btn btn-warning w-50">
                                            Confirmed
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Update Alert Modal ] End -->
                @endforeach

                <!-- [ Activity Nomination ] end -->
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
                    url: "{{ route('nomination-approval', strtolower(str_replace(' ', '-', $act->act_name))) }}",
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
