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
                                <li class="breadcrumb-item" aria-current="page">Student Enrollment</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Student Enrollment</h2>
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

                <!-- [ Student Semester Assignment ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- [ Enrollment Guideline ] start -->
                            <div class="alert alert-light d-flex align-items-start gap-3 p-4" role="alert">
                                <i class="ti ti-info-circle fs-3"></i>
                                <div class="w-100">
                                    <h4 class="mb-3 fw-semibold">Enrollment Guideline</h4>
                                    <ul class="mb-0 ps-3 small">
                                        <li class="mb-2">
                                            The system only allows <strong>Enroll</strong> and <strong>Unenroll</strong>
                                            actions for students in the <strong>current active semester</strong>.
                                        </li>
                                        <li class="mb-2">
                                            For <strong>past semesters</strong>, Committee users can only update the
                                            <strong>student's semester status</strong>. Enrollment actions are disabled.
                                        </li>
                                        <li class="mb-2">
                                            Only students with an <strong>Active</strong> semester status will have their
                                            semester counted. If the status is set to <strong>Inactive</strong> or
                                            <strong>Barred</strong>, their semester count will be excluded.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- [ Enrollment Guideline ] end -->
                            
                            <!-- [ Filter Section ] Start -->
                            <div class="row g-3 align-items-end">
                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="dateRangeFilter"
                                            placeholder="-- Select date range --" readonly />
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearDateRangeFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_status" class="form-select">
                                            <option value="">-- Select Status --</option>
                                            <option value="1">Active</option>
                                            <option value="3">Past</option>
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
                                            <th>#</th>
                                            <th scope="col">Semester</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Total Student(s)</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Student Semester Assignment ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : SEMESTER
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('student-semester-assignment') }}",
                    data: function(d) {
                        d.date_range = $('#dateRangeFilter')
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
                        data: 'semester',
                        name: 'semester',
                    },
                    {
                        data: 'sem_status',
                        name: 'sem_status'
                    },
                    {
                        data: 'total_student',
                        name: 'total_student'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],


            });

            /* Date Range Picker Filter */
            let datePicker = flatpickr("#dateRangeFilter", {
                mode: "range",
                dateFormat: "d M Y",
                allowInput: true,
                locale: {
                    rangeSeparator: " to "
                },
                onClose: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        $('.data-table').DataTable().ajax
                            .reload();
                    }
                }
            });

            $("#clearDateRangeFilter").click(function() {
                datePicker.clear();
                $('.data-table').DataTable().ajax.reload();
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
