@php
    use App\Models\Semester;
    use Carbon\Carbon;
@endphp

@extends('staff.layouts.main')

@section('content')
    <style>
        .timeline {
            position: relative;
        }

        .timeline-dot {
            z-index: 1;
        }
    </style>

    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
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

                            <!-- [ Option Section ] start -->
                            <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-5"
                                    id="clearSelectionBtn">
                                    0 selected <i class="ti ti-x f-18"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-5"
                                    id="downloadmultipleModalBtn" title="Download Document (.zip)">
                                    <i class="ti ti-arrow-bar-to-down f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Download Document (.zip)
                                    </span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

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
                                            <option value="1" selected>Pending</option>
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

                @foreach ($subs as $upd)
                    <!-- [ Approve Modal ] Start -->
                    <form method="GET"
                        action="{{ route('staff-submission-approval-post', ['stuActID' => Crypt::encrypt($upd->student_activity_id), 'option' => 1]) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal fade" id="approveModal-{{ $upd->student_activity_id }}"
                            data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <div class="row">

                                            <!-- Icon -->
                                            <div class="col-sm-12 mb-4 text-center">
                                                <i class="ti ti-circle-check text-success" style="font-size: 100px"></i>
                                            </div>

                                            <!-- Title -->
                                            <div class="col-sm-12 text-center">
                                                <h2 class="f-18">Approve Student Confirmation?</h2>
                                            </div>

                                            <!-- Instruction -->
                                            <div class="col-sm-12 mb-3">
                                                <div class="alert alert-light border text-start f-14">
                                                    <strong class="d-block mb-1">Instructions:</strong>
                                                    <ul class="mb-2 ps-3">
                                                        <li>Carefully review the student's submission document.</li>
                                                        <li>If necessary, provide comments or notes below.</li>
                                                        <li>Sign using the signature box to confirm your approval.</li>
                                                        <li>Click <strong>"Confirm & Sign"</strong> to finalize this action.
                                                        </li>
                                                        <li class="text-danger"><strong>This action is final and cannot be
                                                                undone.</strong></li>
                                                    </ul>
                                                    <div class="mt-2">
                                                        <strong class="text-muted fst-italic d-block">
                                                            By signing below, you confirm that the signature is your own
                                                            handwriting and that it is legally binding within the
                                                            institution’s authority and applicable regulations.
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Optional Comment -->
                                            <div class="col-sm-12 mb-3">
                                                <label for="comment_txt_{{ $upd->student_activity_id }}"
                                                    class="form-label">
                                                    Comment / Notes <span class="text-muted">(optional)</span>
                                                </label>
                                                <textarea name="comment" id="comment_txt_{{ $upd->student_activity_id }}" class="form-control" rows="4"
                                                    placeholder="Enter any remarks if needed..."></textarea>
                                            </div>

                                            <!-- Signature Canvas -->
                                            <div class="col-sm-12 mb-3">
                                                <label class="form-label">Signature <span
                                                        class="text-danger">*</span></label>
                                                <canvas id="signatureCanvas-{{ $upd->student_activity_id }}"
                                                    style="border:1px solid #000000; border-radius:10px; width:100%; height:200px;"></canvas>
                                                <input type="hidden" name="signatureData"
                                                    id="signatureData-{{ $upd->student_activity_id }}">
                                            </div>

                                            <!-- Signature Actions -->
                                            <div class="col-sm-12 mb-3 d-flex justify-content-between gap-3">
                                                <button type="button" class="btn btn-light w-100"
                                                    data-clear="{{ $upd->student_activity_id }}">
                                                    <i class="ti ti-eraser me-2"></i> Clear Signature
                                                </button>
                                            </div>

                                            <!-- Signature Notice -->
                                            <div class="col-sm-12 mb-3">
                                                <div class="d-flex align-items-center text-muted">
                                                    <div class="avtar avtar-s bg-light-primary flex-shrink-0 me-2">
                                                        <i class="fas fa-shield-alt text-primary f-20"></i>
                                                    </div>
                                                    <span class="text-sm">All signatures are securely stored within the
                                                        system.</span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <div class="d-flex justify-content-between gap-3 w-100">
                                            <button type="button" class="btn btn-light w-50"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success w-100"
                                                data-submit="{{ $upd->student_activity_id }}">
                                                <i class="ti ti-writing-sign me-2"></i> Confirm & Sign
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Approve Modal ] End -->

                    <!-- [ Reject Modal ] Start -->
                    <form method="GET"
                        action="{{ route('staff-submission-approval-post', ['stuActID' => Crypt::encrypt($upd->student_activity_id), 'option' => 2]) }}">
                        @csrf
                        <div class="modal fade" id="rejectModal-{{ $upd->student_activity_id }}"
                            data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered  modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <div class="row">

                                            <!-- Icon -->
                                            <div class="col-sm-12 mb-4">
                                                <div class="d-flex justify-content-center align-items-center mb-3">
                                                    <i class="ti ti-circle-x text-danger" style="font-size: 100px"></i>
                                                </div>
                                            </div>

                                            <!-- Title -->
                                            <div class="col-sm-12">
                                                <div class="d-flex justify-content-center align-items-center text-center">
                                                    <h2 class="f-18">Reject Student Confirmation?</h2>
                                                </div>
                                            </div>

                                            <!-- Message -->
                                            <div class="col-sm-12 mb-3">
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <p class="fw-normal f-14 text-center text-muted">
                                                        This action will mark the student's submission as
                                                        <strong>rejected</strong>.<br>
                                                        The student will need to review and <strong>resubmit</strong> their
                                                        confirmation for approval.
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Optional Comment -->
                                            <div class="col-sm-12 mb-3">
                                                <label for="comment_txt_{{ $upd->student_activity_id }}"
                                                    class="form-label">
                                                    Comment / Reason / Notes <span class="text-muted">(optional)</span>
                                                </label>
                                                <textarea name="comment" id="comment_txt_{{ $upd->student_activity_id }}" cols="30" rows="4"
                                                    class="form-control" placeholder="Enter any remarks if needed..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <div class="d-flex justify-content-between gap-3 align-items-center w-100">
                                            <button type="reset" class="btn btn-light w-50"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger w-50">Confirm Reject</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Reject Modal ] End -->

                    <!-- [ Revert Modal ] Start -->
                    <form method="GET"
                        action="{{ route('staff-submission-approval-post', ['stuActID' => Crypt::encrypt($upd->student_activity_id), 'option' => 3]) }}">
                        @csrf
                        <div class="modal fade" id="revertModal-{{ $upd->student_activity_id }}"
                            data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
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
                                            <div class="col-sm-12">
                                                <div class="d-flex justify-content-center align-items-center text-center">
                                                    <h2 class="f-18">Revert Student Confirmation?</h2>
                                                </div>
                                            </div>

                                            <!-- Message -->
                                            <div class="col-sm-12 mb-3">
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <p class="fw-normal f-14 text-center text-muted">
                                                        This action will <strong>cancel</strong> the student's current
                                                        confirmation.<br>
                                                        The student must log in and <strong>confirm again</strong> before
                                                        proceeding further.
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="col-sm-12">
                                                <div class="d-flex justify-content-between gap-3 align-items-center">
                                                    <button type="reset" class="btn btn-light w-50"
                                                        data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-warning w-50">Confirm
                                                        Revert</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Revert Modal ] End -->

                    <!-- [ Review Modal ] Start -->
                    <div class="modal fade" id="reviewModal-{{ $upd->student_activity_id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="multipleSettingModalLabel">Submission Review(s)</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12">
                                            @php
                                                $activityReviews = $reviews->where(
                                                    'student_activity_id',
                                                    $upd->student_activity_id,
                                                );
                                            @endphp

                                            @if ($activityReviews->isEmpty())
                                                <div class="alert alert-light text-center">
                                                    <i class="ti ti-info-circle"></i> There are no reviews for this
                                                    submission yet.
                                                </div>
                                            @else
                                                <div class="timeline">
                                                    @foreach ($activityReviews as $review)
                                                        <div
                                                            class="timeline-item mb-4 position-relative ps-4 border-start border-2 border-secondary">
                                                            <div class="card">
                                                                <div class="card-body p-3">
                                                                    <div class="d-flex justify-content-between mb-2">
                                                                        <small class="text-muted">
                                                                            {{ \Carbon\Carbon::parse($review->sr_date)->format('d M Y') }}
                                                                        </small>
                                                                        <small class="text-muted">
                                                                            — {{ $review->staff_name }}
                                                                        </small>
                                                                    </div>
                                                                    <p class="mb-0 text-dark">
                                                                        {{ $review->sr_comment }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <span
                                                                class="timeline-dot position-absolute top-0 start-0 translate-middle bg-secondary rounded-circle"
                                                                style="width: 12px; height: 12px;"></span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Review Modal ] End -->
                @endforeach
                
                <!-- [ Submission Approval ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>
    <script>
        const signaturePads = {};

        document.addEventListener('shown.bs.modal', function(event) {
            const modal = event.target;
            const studentActivityId = modal.id.split('-')[1];
            const canvas = document.getElementById(`signatureCanvas-${studentActivityId}`);

            if (canvas && !signaturePads[studentActivityId]) {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);

                signaturePads[studentActivityId] = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255,255,255,1)',
                    penColor: 'black'
                });
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target.matches('[data-clear]')) {
                const id = e.target.getAttribute('data-clear');
                if (signaturePads[id]) signaturePads[id].clear();
            }

            if (e.target.matches('[data-submit]')) {
                const confirmId = e.target.getAttribute('data-submit');
                if (signaturePads[confirmId] && signaturePads[confirmId].isEmpty()) {
                    e.preventDefault();
                    alert("Please provide a signature.");
                } else {
                    const dataURL = signaturePads[confirmId].toDataURL('image/png');
                    document.getElementById(`signatureData-${confirmId}`).value = dataURL;
                }
            }
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : STUDENT ACTIVITIES
            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('submission-approval') }}",
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
            const downloadmultipleModalBtn = $('#downloadmultipleModalBtn');

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

            downloadmultipleModalBtn.on('click', function() {
                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {

                    let idsParam = encodeURIComponent(JSON.stringify(selectedIds));
                    window.location.href = "{{ route('download-multiple-final-document-get') }}?ids=" +
                        idsParam;
                    clearBtn.trigger('click');
                    $('.data-table').DataTable().ajax.reload();
                } else {
                    alert("No valid data selected for document download.");
                }
            });

        });
    </script>
@endsection
