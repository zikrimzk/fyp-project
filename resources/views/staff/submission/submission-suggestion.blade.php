@php
    use App\Models\Semester;
    use Carbon\Carbon;
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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Submission</a></li>
                                <li class="breadcrumb-item" aria-current="page">Submission Eligibility</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Submission Eligibility</h2>
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
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                <div id="toastContainer"></div>
            </div>
            <!-- [ Alert ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- [ Submission Approval ] start -->
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
                                    class="btn btn-outline-success d-flex align-items-center gap-2 d-none mb-4"
                                    id="approveMultipleModalBtn" title="Approve" data-bs-toggle="modal"
                                    data-bs-target="#approveMultipleModal">
                                    <i class="ti ti-circle-check me-2"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Approve
                                    </span>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-warning d-flex align-items-center gap-2 d-none mb-4"
                                    id="revertMultipleModalBtn" title="Revert" data-bs-toggle="modal"
                                    data-bs-target="#revertMultipleModal">
                                    <i class="ti ti-rotate me-2"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Revert
                                    </span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <!-- [ Filter Section ] Start -->
                            <div class="row g-3 align-items-center mb-3">

                                <div class="col-sm-12 col-md-6 mb-3">
                                    <label for="fil_activity_id" class="form-label">Activity</label>
                                    <div class="input-group">
                                        <select id="fil_activity_id" class="form-select">
                                            <option value="">All Activities</option>
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

                                <div class="col-sm-12 col-md-6 mb-3">
                                    <label for="fil_status" class="form-label">Status</label>

                                    <div class="input-group">
                                        <select id="fil_status" class="form-select">
                                            <option value="" selected>All Statuses</option>
                                            <option value="1">Eligible</option>
                                            <option value="2">Submission Opened</option>
                                            <option value="3">Prerequisite Pending</option>
                                            <option value="4">Under Review</option>
                                            <option value="5">Completed</option>
                                            <option value="6">Submission Archived</option>
                                            <option value="7">Semester Requirement Pending</option>
                                            <option value="8">Not Actively Enrolled</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearStatusFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <div class="row g-3 align-items-center mb-3 content">

                                <div class="col-sm-12 col-md-4">
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
                                                    <option value="{{ $fil->id }}" class="bg-light-success">
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

                                <div class="col-sm-12 col-md-4">
                                    <div class="input-group">
                                        <select id="fil_semester_id" class="form-select">
                                            <option value="">-- Select Semester --</option>
                                            @foreach ($sems as $fil)
                                                @if ($fil->sem_status == 1)
                                                    <option value="{{ $fil->id }}" class="bg-light-success"
                                                        selected>
                                                        {{ $fil->sem_label }} [Current]
                                                    </option>
                                                @elseif($fil->sem_status == 3)
                                                    <option value="{{ $fil->id }}"> {{ $fil->sem_label }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearSemFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4">
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

                            </div>
                            <!-- [ Filter Section ] End -->

                            <div class="dt-responsive table-responsive content">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all" class="form-check-input"></th>
                                            <th scope="col">Student</th>
                                            <th scope="col">Submission Status</th>
                                            <th scope="col">Activity</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>

                            <!-- [ Approve Multiple Modal ] Start -->
                            <div class="modal fade" id="approveMultipleModal" data-bs-keyboard="false" tabindex="-1"
                                aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">

                                        <!-- Approval Confirmation Modal -->
                                        <div class="modal-body">

                                            <!-- Header with icon -->
                                            <div class="col-sm-12 mb-4 text-center">
                                                <i class="ti ti-circle-check text-success" style="font-size: 100px"></i>
                                            </div>
                                            <div class="text-center mb-4">
                                                <h4 class="fw-bold">Approve Submission Opening?</h4>
                                            </div>

                                            <!-- Main message -->
                                            <div class="alert alert-success border-0">
                                                <div class="d-flex">
                                                    <i class="fas fa-info-circle mt-1 me-2"></i>
                                                    <div>
                                                        <p class="mb-2 fw-semibold">By approving this activity:</p>
                                                        <ul class="ps-3 mb-0">
                                                            <li>The student <span class="fw-bold">must
                                                                    submit</span> all required documents for this
                                                                activity</li>
                                                            <li>The system will <span class="fw-bold">automatically
                                                                    notify</span> the student</li>
                                                            <li>Submission deadline will be set based on activity
                                                                timeline</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Revert information -->
                                            <div class="alert alert-light border mt-3">
                                                <div class="d-flex">
                                                    <i class="fas fa-undo text-warning mt-1 me-2"></i>
                                                    <div>
                                                        <p class="mb-1"><span class="fw-semibold">Changed your
                                                                mind?</span></p>
                                                        <p class="small mb-0">You can <span class="fw-bold">revert
                                                                this decision</span> anytime before the student
                                                            confirms their submission. After confirmation, you'll
                                                            need to contact the student directly.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <div class="d-flex justify-content-between gap-3 w-100">
                                                <button type="button" class="btn btn-light w-50"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="button" class="btn btn-success w-100" id="approve-btn">
                                                    Confirm Approval
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ Approve Multiple Modal ] end -->

                            <!-- [ Revert Multiple Modal ] Start -->
                            <div class="modal fade" id="revertMultipleModal" data-bs-keyboard="false" tabindex="-1"
                                aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-body">
                                            <div class="row">

                                                <!-- Icon -->
                                                <div class="col-sm-12 mb-4">
                                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                                        <i class="ti ti-refresh-alert text-warning"
                                                            style="font-size: 100px"></i>
                                                    </div>
                                                </div>

                                                <!-- Title -->
                                                <div class="col-sm-12 mb-3">
                                                    <div
                                                        class="d-flex justify-content-center align-items-center text-center">
                                                        <h2 class="f-18">Revert Student Submission?</h2>
                                                    </div>
                                                </div>

                                                <!-- Message -->
                                                <div class="col-sm-12 mb-3">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <div class="alert alert-warning p-3 f-14">
                                                            <p class="fw-semibold mb-2">This action will:</p>
                                                            <ul class="list-unstyled ps-3">
                                                                <li class="mb-2">
                                                                    <i class="fas fa-lock me-2"></i>
                                                                    <strong>Lock</strong> the student's submission
                                                                </li>
                                                                <li class="mb-2">
                                                                    <i class="fas fa-undo me-2"></i>
                                                                    <strong>Preserve</strong> all uploaded documents
                                                                </li>
                                                                <li class="mb-2">
                                                                    <i class="fas fa-user-clock me-2"></i> Preserve existing review and nomination records
                                                                </li>
                                                                <li>
                                                                    <i class="fas fa-unlock-alt me-2"></i>
                                                                    Enrollment processing or committee approval can reopen
                                                                    for new submissions
                                                                </li>
                                                            </ul>
                                                            <p class="mt-2 mb-0 text-danger fw-semibold">
                                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                                Student cannot submit until eligibility processing or committee reopens this
                                                                activity!
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Confirmation -->
                                                <div class="col-sm-12">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <p class="f-14 text-muted text-center">
                                                            Are you sure you want to proceed with this action?
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Action Buttons -->
                                                <div class="col-sm-12">
                                                    <div class="d-flex justify-content-between gap-3 align-items-center">
                                                        <button type="reset" class="btn btn-light w-50"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                        <button type="button" id="revert-btn"
                                                            class="btn btn-warning w-100">
                                                            Confirm Revert
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ Revert Multiple Modal ] End -->

                            @foreach (['approve' => 'Approve Submission Opening', 'revert' => 'Lock Submission'] as $action => $label)
                                <div class="modal fade eligibility-action-modal" id="{{ $action }}EligibilityModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ $label }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="eligibility-selection fw-semibold"></p>
                                                @if ($action === 'approve')
                                                    <p>Open this eligible activity using its configured deadline and evaluation requirements?</p>
                                                @else
                                                    <p>Lock this activity? Uploaded documents and existing review and nomination records will be preserved.</p>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <a class="btn btn-primary eligibility-confirm">Confirm</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>

                <!-- [ Submission Approval ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>

    <script></script>

    <script type="text/javascript">
        $(document).ready(function() {

            $('.eligibility-action-modal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                $(this).find('.eligibility-confirm').attr('href', button.attr('data-action-url'));
                $(this).find('.eligibility-selection').text(button.attr('data-selection'));
            });

            /*********************************************************
             ***************GLOBAL FUNCTION & VARIABLES***************
             *********************************************************/

            function showToast(type, message) {
                const toastId = 'toast-' + Date.now();
                const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-info-circle';
                const bgClass = type === 'success' ? 'bg-light-success' : 'bg-light-danger';
                const txtClass = type === 'success' ? 'text-success' : 'text-danger';
                const colorClass = type === 'success' ? 'success' : 'danger';
                const title = type === 'success' ? 'Success' : 'Error';

                const toastHtml = `
                    <div id="${toastId}" class="toast border-0 shadow-sm mb-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                        <div class="toast-body text-white ${bgClass} rounded d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0 ${txtClass}">
                                    <i class="${iconClass} me-2"></i> ${title}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <p class="mb-0 ${txtClass}">${message}</p>
                        </div>
                    </div>
                `;

                $('#toastContainer').append(toastHtml);
                const toastEl = new bootstrap.Toast(document.getElementById(toastId));
                toastEl.show();
            }

            /*********************************************************/
            /*********DATATABLE : SUBMISSION SUGGESTION***************/
            /*********************************************************/
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('submission-eligibility') }}",
                    data: function(d) {
                        d.faculty = $('#fil_faculty_id').val();
                        d.programme = $('#fil_programme_id').val();
                        d.activity = $('#fil_activity_id').val();
                        d.status = $('#fil_status').val();
                        d.semester = $('#fil_semester_id').val();
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
                        data: 'suggestion_status',
                        name: 'suggestion_status'
                    },
                    {
                        data: 'activity_name',
                        name: 'activity_name',
                        visible: true
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                    },
                ],
                order: [[1, 'asc'], [3, 'asc']]
            });

            /*********************************************************/
            /********************DATATABLE : FILTERS******************/
            /*********************************************************/

            $('#fil_faculty_id, #fil_programme_id, #fil_semester_id').on('change', function() {
                $('#clearSelectionBtn').trigger('click');
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

            // FILTER : ACTIVITY
            $('#fil_activity_id').on('change', function() {
                table.ajax.reload();
                clearBtn.trigger('click');
            });
            $('#clearActivityFilter').click(function() {
                $('#fil_activity_id').val('').change();
            });

            // FILTER : SEMESTER
            $('#fil_semester_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearSemFilter').click(function() {
                $('#fil_semester_id').val(@json($sems->firstWhere('sem_status', 1)?->id)).change();
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




            /*********************************************************/
            /**********SELECT : MULTIPLE STUDENT SELECT***************/
            /*********************************************************/
            const clearBtn = $("#clearSelectionBtn");
            const approveMultipleModalBtn = $('#approveMultipleModalBtn');
            const revertMultipleModalBtn = $('#revertMultipleModalBtn');
            const approveBtn = $('#approve-btn');
            const revertBtn = $('#revert-btn');

            let selectedIds = new Set();
            let selectedStatuses = new Map();

            $("#select-all").on("change", function() {
                let isChecked = $(this).prop("checked");

                $(".user-checkbox").each(function() {
                    let id = $(this).val();
                    this.checked = isChecked;

                    if (isChecked) {
                        selectedIds.add(id);
                        selectedStatuses.set(id, Number($(this).data("status")));
                    } else {
                        selectedIds.delete(id);
                        selectedStatuses.delete(id);
                    }
                });
                toggleSelectButton();
            });

            $(document).on("change", ".user-checkbox", function() {
                let id = $(this).val();
                if ($(this).prop("checked")) {
                    selectedIds.add(id);
                        selectedStatuses.set(id, Number($(this).data("status")));
                } else {
                    selectedIds.delete(id);
                        selectedStatuses.delete(id);
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

                let hasEligible = false;
                let hasOpened = false;

                // Reset visibility
                approveMultipleModalBtn.addClass("d-none");
                revertMultipleModalBtn.addClass("d-none");

                if (selectedCount > 0) {
                    clearBtn.removeClass("d-none").html(
                        `<i class="ti ti-x f-18"></i> ${selectedCount} selected`
                    );

                    selectedIds.forEach(function(id) {
                        const status = selectedStatuses.get(id);
                        if (status === 1) hasEligible = true;
                        else if (status === 2) hasOpened = true;
                    });

                    if (hasEligible && !hasOpened) {
                        approveMultipleModalBtn.removeClass("d-none");
                    } else if (hasOpened && !hasEligible) {
                        revertMultipleModalBtn.removeClass("d-none");
                    }
                } else {
                    clearBtn.addClass("d-none");
                }
            }

            clearBtn.on("click", function() {
                $(".user-checkbox").prop("checked", false);
                $("#select-all").prop("checked", false);
                selectedIds.clear();
                selectedStatuses.clear();
                toggleSelectButton();
            });

            /*********************************************************/
            /**********SELECT : APPROVAL & REVERT ********************/
            /*********************************************************/

            approveBtn.on('click', function() {
                const $button = $(this);
                const selectedPairs = Array.from(selectedIds);


                if (selectedPairs.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span> Loading...'
                    );

                    $.ajax({
                        url: "{{ route('multiple-submission-eligibility-approval-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedPairs,
                            option: 1,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {

                            if (response.success) {
                                // Show success toast
                                $('#approveMultipleModal').modal('hide');
                                $('.data-table').DataTable().ajax.reload();
                                clearBtn.trigger('click');
                                showToast('success', response.message);
                            } else {
                                // Show error toast
                                showToast('error', response.message);
                            }
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message || 'Could not update eligibility. Refresh and retry.');
                        },
                        complete: function() {
                            $button.prop('disabled', false).html('Confirm Approval');
                        }
                    });
                } else {
                    showToast('error', "No valid data selected for approval.");
                }
            });


            revertBtn.on('click', function() {
                const $button = $(this);
                const selectedPairs = Array.from(selectedIds);


                if (selectedPairs.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span> Loading...'
                    );

                    $.ajax({
                        url: "{{ route('multiple-submission-eligibility-approval-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedPairs,
                            option: 2,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {

                            if (response.success) {
                                // Show success toast
                                $('#revertMultipleModal').modal('hide');
                                $('.data-table').DataTable().ajax.reload();
                                clearBtn.trigger('click');
                                showToast('success', response.message);
                            } else {
                                // Show error toast
                                showToast('error', response.message);
                            }
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message || 'Could not update eligibility. Refresh and retry.');
                        },
                        complete: function() {
                            $button.prop('disabled', false).html('Confirm Revert');
                        }
                    });
                } else {
                    showToast('error', "No valid data selected for revert.");
                }
            });

            /*********************************************************/
            /*******************EXTRA : LOADING INDICATOR*************/
            /*********************************************************/

            $('.confirm-btn').on('click', function() {
                const $btn = $(this);
                $btn.addClass('disabled-a', true);
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...'
                );
                $btn.closest('form').submit();
            });

        });
    </script>
@endsection
