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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Examiner / Panel</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Evaluation</a></li>
                                <li class="breadcrumb-item" aria-current="page">{{ $act->act_name }} - Evaluation Management
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">{{ $act->act_name }} - Evaluation Management</h2>
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

                <!-- [ Examiner/Panel - Evaluation Management ] start -->

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
                                            <option value="7">Submitted [Draft]</option>
                                            <option value="9">Pending : Supervisor Approval</option>
                                            <option value="10">Pending : Committee/DD/Dean Approval</option>
                                            <option value="11">Rejected : Supervisor</option>
                                            <option value="12">Rejected : Committee/DD/Dean</option>
                                            <option value="15">Final & Confirmed</option>
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
                            <!-- [ Datatable ] Start -->
                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th scope="col">Student</th>
                                            <th scope="col">Evaluation</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Semester</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <!-- [ Datatable ] End -->
                        </div>
                    </div>
                </div>
                <!-- [ Datatable ] End -->

                <!-- [ Examiner/Panel - Evaluation Management ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : EVALUATION
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('examiner-panel-evaluation', strtolower(str_replace(' ', '-', $act->act_name))) }}",
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
                        data: 'evaluation_document',
                        name: 'evaluation_document'
                    },
                    {
                        data: 'evaluation_date',
                        name: 'evaluation_date'
                    },
                    {
                        data: 'evaluation_semester',
                        name: 'evaluation_semester'
                    },
                    {
                        data: 'evaluation_status',
                        name: 'evaluation_status'
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
