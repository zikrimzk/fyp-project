@php
    use App\Models\Semester;
    use App\Models\Student;
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
                                <li class="breadcrumb-item" aria-current="page">Student Semester Assignment</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Student Semester Assignment</h2>
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

                <!-- [ Student Semester Assignment ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">

                            <!-- [ Option Section ] start -->
                            <div class="mb-5 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#import-assign-Modal" id="importBtn"
                                    title="Assign Student Semester (Excel)">
                                    <i class="ti ti-file-import f-18"></i>
                                    <span class="d-none d-sm-inline me-2">Assign Student Semester (Excel)</span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

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

                <!-- [ Assign Modal ] start -->
                <form action="{{ route('import-student-semester-post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal fade" id="import-assign-Modal" data-bs-keyboard="false" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header bg-light">
                                    <h5 class="mb-0"><i class="ti ti-upload me-2"></i> Assign Student Semester (Excel)
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
                                <div class="modal-footer justify-content-end">
                                    <div class="flex-grow-1 text-end">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-light-danger w-100"
                                                    data-bs-dismiss="modal">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="btn btn-primary w-100" id="import-btn"
                                                    disabled>
                                                    Confirm Assignment
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Assign Modal ] end -->

                <!-- [ Student Semester Assignment ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : STUDENT
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

            var modalToShow = "{{ session('modal') }}";
            if (modalToShow) {
                var modalElement = $("#" + modalToShow);
                if (modalElement.length) {
                    var modal = new bootstrap.Modal(modalElement[0]);
                    modal.show();
                }
            }

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


            // IMPORT : STUDENT
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
