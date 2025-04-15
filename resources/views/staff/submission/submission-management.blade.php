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
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- [ Option Section ] start -->
                            <div class="mb-5 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none"
                                    id="clearSelectionBtn">
                                    0 selected <i class="ti ti-x f-18"></i>
                                </button>
                                <a href="{{ route('assign-student-submission') }}"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2"
                                    title="Re-assign Submission" id="reassignBtn">
                                    <i class="ti ti-refresh f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Re-assign Submission
                                    </span>
                                </a>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none"
                                    data-bs-toggle="modal" data-bs-target="#multipleSettingModal"
                                    id="updatemultipleModalBtn" title="Update Submission">
                                    <i class="ti ti-edit-circle f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Update Submission
                                    </span>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none"
                                    data-bs-toggle="modal" data-bs-target="#deleteMultipleModal" id="deletemultipleModalBtn"
                                    title="Delete Submission">
                                    <i class="ti ti-archive f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Archive Submission
                                    </span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <!-- [ Filter Section ] Start -->
                            <div class="row g-3 align-items-end">

                                <div class="col-sm-12 col-md-3 mb-3">
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
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearFacFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
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
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearProgFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_semester_id" class="form-select">
                                            <option value="">-- Select Semester --</option>
                                            @foreach ($sems as $fil)
                                                @if ($fil->sem_status == 1)
                                                    <option value="{{ $fil->id }}" class="bg-light-success">
                                                        {{ $fil->sem_label }} [Current]
                                                    </option>
                                                @elseif($fil->sem_status == 0)
                                                    <option value="{{ $fil->id }}"> {{ $fil->sem_label }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearSemFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_status" class="form-select">
                                            <option value="">-- Select Status --</option>
                                            <option value="1">No Attempt</option>
                                            <option value="2">Locked</option>
                                            <option value="3">Submitted</option>
                                            <option value="4">Overdue</option>
                                            <option value="5">Archive</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
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
                        </div>
                    </div>
                </div>

                <!-- [ Multiple Submission Update Modal ] start -->
                <div class="modal fade" id="multipleSettingModal" tabindex="-1" aria-labelledby="multipleSettingModal"
                    aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">

                            <div class="modal-header">
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
                                                <option value ="5">Archive</option>
                                            </select>
                                            @error('submission_status_ups')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer justify-content-end">
                                <div class="flex-grow-1 text-end">
                                    <button type="reset" class="btn btn-link-danger btn-pc-default"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" id="multipleSubmissionUpdateBtn">Save
                                        Changes</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- [ Multiple Submission Update Modal ] end -->

                <!-- [ Archive Multiple Submission Modal ] start -->
                <div class="modal fade" id="deleteMultipleModal" data-bs-keyboard="false" tabindex="-1"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12 mb-4">
                                        <div class="d-flex justify-content-center align-items-center mb-3">
                                            <i class="ti ti-archive text-secondary" style="font-size: 100px"></i>
                                        </div>

                                    </div>
                                    <div class="col-sm-12">
                                        <div class="d-flex justify-content-center align-items-center text-center">
                                            <h2>Are you sure to archive this submission ?</h2>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <p class="fw-normal f-18 text-center">You can revert this action.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="d-flex justify-content-between gap-3 align-items-center">
                                            <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-secondary w-100"
                                                id="multipleSubmissionDeleteBtn">
                                                Archive
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Archive Multiple Submission Modal ] end -->

                @foreach ($subs as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-submission-post', Crypt::encrypt($upd->submission_id)) }}"
                        method="POST">
                        @csrf
                        <div class="modal fade" id="settingModal-{{ $upd->submission_id }}" tabindex="-1"
                            aria-labelledby="settingModal" aria-hidden="true">
                            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
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
                                                        <option value ="5" @if ($upd->submission_status == 5) selected @endif>
                                                            Archive
                                                        </option>
                                                    </select>
                                                    @error('submission_status_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="modal-footer justify-content-end">
                                        <div class="flex-grow-1 text-end">
                                            <button type="reset" class="btn btn-link-danger btn-pc-default"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Update Modal ] end -->

                    <!-- [ Archive Modal ] start -->
                    <div class="modal fade" id="deleteModal-{{ $upd->submission_id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 mb-4">
                                            <div class="d-flex justify-content-center align-items-center mb-3">
                                                <i class="ti ti-archive text-secondary" style="font-size: 100px"></i>
                                            </div>

                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-center align-items-center text-center">
                                                <h2>Are you sure to archive this submission ?</h2>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 mb-3">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <p class="fw-normal f-18 text-center">You can revert this action.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <a href="{{ route('delete-submission-get', ['id' => Crypt::encrypt($upd->submission_id)]) }}"
                                                    class="btn btn-secondary w-100">Archive</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Archive Modal ] end -->
                @endforeach

                <!-- [ Submission Management ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : SUBMISSION
            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('submission-management') }}",
                    data: function(d) {
                        d.faculty = $('#fil_faculty_id').val();
                        d.programme = $('#fil_programme_id').val();
                        d.semester = $('#fil_semester_id').val();
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

            // FILTER : STATUS
            $('#fil_status').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearStatusFilter').click(function() {
                $('#fil_status').val('').change();
            });

            /* SELECT : MULTIPLE STUDENT SELECT */
            const reassignBtn = $("#reassignBtn");
            const clearBtn = $("#clearSelectionBtn");
            const updatemultipleModalBtn = $("#updatemultipleModalBtn");
            const deletemultipleModalBtn = $('#deletemultipleModalBtn');
            const multipleSubmissionUpdateBtn = $("#multipleSubmissionUpdateBtn");
            const multipleSubmissionDeleteBtn = $("#multipleSubmissionDeleteBtn");

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

                reassignBtn.toggleClass("d-none", selectedIds.size !== 0);
                updatemultipleModalBtn.toggleClass("d-none", selectedIds.size === 0);
                deletemultipleModalBtn.toggleClass("d-none", selectedIds.size === 0);

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

            multipleSubmissionDeleteBtn.on('click', function() {
                const $button = $(this);

                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...'
                    );

                    $.ajax({
                        url: "{{ route('delete-multiple-submission-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedIds,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#deleteMultipleModal').modal('hide');
                            clearBtn.trigger('click');
                            $('.data-table').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert("Error: " + (xhr.responseJSON?.message || xhr.responseText));
                        },
                        complete: function() {
                            $button.prop('disabled', false).html('Delete Anyways');
                        }
                    });
                } else {
                    alert("No valid data selected for submission update.");
                }
            });


        });
    </script>
@endsection
