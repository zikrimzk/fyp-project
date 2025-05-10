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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">My Supervision</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Submission</a></li>
                                <li class="breadcrumb-item" aria-current="page">Submission Approval</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Submission Approval</h2>
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

                <!-- [ Submission Approval ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">

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
                                                    <option value="{{ $fil->id }}" class="bg-light-success" selected>
                                                        {{ $fil->fac_code }} [Default]
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFacFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4 mb-3">
                                    <div class="input-group">
                                        <select id="fil_semester_id" class="form-select">
                                            <option value="">-- Select Semester --</option>
                                            @foreach ($sems as $fil)
                                                @if ($fil->sem_status == 1)
                                                    <option value="{{ $fil->id }}" class="bg-light-success" selected>
                                                        {{ $fil->sem_label }} [Current]
                                                    </option>
                                                @elseif($fil->sem_status == 0)
                                                    <option value="{{ $fil->id }}"> {{ $fil->sem_label }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSemFilter">
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
                                        <select id="fil_activity_id" class="form-select">
                                            <option value="">-- Select Activity --</option>
                                            @foreach ($acts as $fil)
                                                <option value="{{ $fil->id }}">{{ $fil->act_name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearActivityFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>


                                <div class="col-sm-12 col-md-4 mb-3">
                                    <div class="input-group">
                                        <select id="fil_status" class="form-select">
                                            <option value="">-- Select Status --</option>
                                            <option value="1">Pending</option>
                                            <option value="2">Approved (SV)</option>
                                            <option value="3">Approved (Comm/DD/Dean)</option>
                                            <option value="4">Rejected (SV)</option>
                                            <option value="5">Rejected (Comm/DD/Dean)</option>
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
                                            <th scope="col">Student</th>
                                            <th scope="col">Final Document</th>
                                            <th scope="col">Confirmation Date</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Activity</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- [ Submission Approval ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : STUDENT ACTIVITIES
            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('my-supervision-submission-approval') }}",
                    data: function(d) {
                        d.faculty = $('#fil_faculty_id').val();
                        d.programme = $('#fil_programme_id').val();
                        d.semester = $('#fil_semester_id').val();
                        d.activity = $('#fil_activity_id').val();
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
                        data: 'sa_final_submission',
                        name: 'sa_final_submission'
                    },
                    {
                        data: 'confirm_date',
                        name: 'confirm_date'
                    },
                    {
                        data: 'sa_status',
                        name: 'sa_status'
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
