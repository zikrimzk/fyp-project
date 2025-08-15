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
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                <div id="toastContainer"></div>
            </div>
            <!-- [ Alert ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- [ Submission Approval ] start -->

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
                                            <option value="1">Pending Approval: Supervisor</option>
                                            <option value="2" selected>Pending Approval: (Comm/DD/Dean)</option>
                                            <option value="7">Pending: Evaluation</option>
                                            <option value="3">Approved & Completed</option>
                                            <option value="4">Rejected: Supervisor</option>
                                            <option value="5">Rejected: (Comm/DD/Dean)</option>
                                            <option value="8">Evaluation: Major/Minor Correction</option>
                                            <option value="9">Evaluation: Resubmit/Represent</option>
                                            <option value="12">Evaluation: Failed</option>
                                            <option value="13">Continue Next Semester</option>
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
                            <!-- Submission Approval Guidelines -->
                            <div class="alert alert-light d-flex align-items-start gap-3 p-4" role="alert">
                                <i class="ti ti-info-circle fs-3"></i>
                                <div class="w-100">
                                    <h4 class="mb-3 fw-semibold">Submission Approval Guidelines</h4>
                                    <ul class="mb-0 ps-3 small">
                                        <li class="mb-2">
                                            Approval actions are only shown to roles configured in the <strong>Form Settings
                                                signature flow</strong>.
                                        </li>
                                        <li class="mb-2">
                                            Each role (Committee, Deputy Dean, Dean) can only approve submissions if their
                                            signature is required.
                                        </li>
                                        <li class="mb-2">
                                            <strong>Cross Approvals</strong>
                                            <ul class="mb-0 ps-3">
                                                <li>
                                                    Committee/Deputy Dean/Dean can approve on
                                                    behalf of the Supervisor.
                                                    No signature is required—just use the signature pad to scramble a mark.
                                                    This applies <strong>only to Supervisor approvals</strong>.
                                                </li>
                                                <li>
                                                    For roles that are officially assigned to approve, <strong>cross
                                                        approvals are
                                                        ignored</strong>.
                                                    The approval process will skip the cross approval and mark the activity
                                                    as
                                                    completed if the assigned role is the last required approver.
                                                </li>

                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>

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
                                        Download (.zip)
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
                                            <th scope="col">Final Document</th>
                                            <th scope="col">Confirmation Date</th>
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
                <!-- [ Datatable & Option ] End -->

                @foreach ($subs as $upd)
                    <!-- [ Approve Modal ] Start -->
                    <form method="POST"
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
                    <form method="POST"
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
                                            <button type="submit" class="btn btn-danger w-50 confirm-btn">Confirm
                                                Reject</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Reject Modal ] End -->
                @endforeach

                <!-- [ Review Modal ] Start -->
                <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                        <div class="modal-content rounded-3 shadow-sm">
                            <div class="modal-header bg-light border-bottom">
                                <h5 class="modal-title fw-semibold">Submission Review(s)</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body p-4">
                                <div id="reviewList" class="row gy-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Review Modal ] End -->

                <!-- [ Submission Approval ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>

    <script></script>

    <script type="text/javascript">
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

        var modalToShow = "{{ session('modal') }}";
        if (modalToShow) {
            var modalElement = $("#" + modalToShow);
            if (modalElement.length) {
                var modal = new bootstrap.Modal(modalElement[0]);
                modal.show();
            }
        }

        /*********************************************************
         ***************SIGNATURE PADS INITIALIZATION*************
         *********************************************************/
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
                    showToast('danger', 'Please provide a signature.');
                } else {
                    const $btn = $(e.target);
                    $btn.prop('disabled', true);
                    $btn.html(
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...'
                    );

                    const dataURL = signaturePads[confirmId].toDataURL('image/png');
                    document.getElementById(`signatureData-${confirmId}`).value = dataURL;

                    $btn.closest('form').submit();
                }
            }
        });

        /*********************************************************
         *********************REVIEW FUNCTIONS********************
         *********************************************************/
        function loadReviews(sa_id) {
            $.ajax({
                url: "{{ route('get-review-data-post') }}",
                type: "POST",
                data: {
                    sa_id: sa_id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        const reviewList = $('#reviewList');
                        reviewList.empty();

                        if (response.review.length === 0) {
                            reviewList.html(`
                        <div class="alert alert-light text-center">
                            <i class="fas fa-info-circle me-2"></i> No reviews found.
                        </div>
                    `);
                        } else {
                            const currUser = "{{ auth()->user()->id }}";
                            response.review.forEach(review => {
                                const isOwner = review.staff_id == currUser;

                                const reviewCard = `
                                    <div class="col-12 mb-3">
                                        <div class="card border-0 shadow-sm rounded-3">
                                            <div class="card-body">
                                                <div class="d-flex flex-column flex-md-row justify-content-between mb-2 small text-muted">
                                                    <div><i class="ti ti-calendar-event me-1"></i> ${new Date(review.sr_date).toLocaleDateString()}</div>
                                                    <div><i class="ti ti-user me-1"></i> ${review.staff_name}</div>
                                                </div>

                                                <textarea id="sr_comment-${review.review_id}" class="form-control mb-3" rows="3" ${isOwner ? '' : 'readonly'}>${review.sr_comment}</textarea>

                                                ${isOwner ? `
                                                                                                                <div class="d-flex flex-column flex-md-row justify-content-end gap-2">
                                                                                                                    <button class="btn btn-sm btn-light-danger" onclick="deleteReview(${review.review_id}, ${review.student_activity_id})">
                                                                                                                        <i class="ti ti-trash me-2"></i>
                                                                                                                        <span class="me-2">Delete</span>
                                                                                                                    </button>
                                                                                                                    <button class="btn btn-sm btn-light-primary" onclick="updateReview(${review.review_id})">
                                                                                                                        <i class="ti ti-edit-circle me-2"></i>
                                                                                                                        <span class="me-2">Update</span>
                                                                                                                    </button>
                                                                                                                </div>
                                                                                                            ` : ''}
                                            </div>
                                        </div>
                                    </div>
                                `;

                                reviewList.append(reviewCard);
                            });
                        }

                        $('#reviewModal').modal('show');
                    } else {
                        showToast('error', 'Failed to load reviews.');
                    }
                },
                error: function(xhr) {
                    showToast('error', 'Something went wrong while fetching reviews.');
                }
            });
        }

        function updateReview(review_id) {
            const comment = $(`#sr_comment-${review_id}`).val();

            $.ajax({
                url: "{{ route('update-review-post') }}",
                type: "POST",
                data: {
                    review_id: review_id,
                    sr_comment: comment,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        loadReviews(response.review.student_activity_id);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        Object.values(errors).forEach(msgs => showToast('error', msgs[0]));
                    } else {
                        showToast('error', 'Unexpected error occurred during update.');
                    }
                }
            });
        }

        function deleteReview(review_id, student_activity_id) {
            if (!confirm("Are you sure you want to delete this review?")) return;

            $.ajax({
                url: "{{ route('delete-review-post') }}",
                type: "POST",
                data: {
                    review_id: review_id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        loadReviews(student_activity_id);
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', 'Something went wrong while deleting.');
                }
            });
        }

        $(document).ready(function() {

            /*********************************************************/
            /************DATATABLE : STUDENT ACTIVITIES***************/
            /*********************************************************/
            var table = $('.data-table').DataTable({
                processing: true,
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
                        d.role = $('#fil_role').val();
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
                        searchable: false,
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


            /*********************************************************/
            /********************DATATABLE : FILTERS******************/
            /*********************************************************/

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

            // FILTER : ROLE
            $('#fil_role').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
                clearBtn.trigger('click');

            });

            $('#clearRoleFilter').click(function() {
                $('#fil_role').val('').change();
            });


            /*********************************************************/
            /**********SELECT : MULTIPLE STUDENT SELECT***************/
            /*********************************************************/
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

            /*********************************************************/
            /**********SELECT : DOWNLOAD STUDENT DOCUMENT*************/
            /*********************************************************/

            downloadmultipleModalBtn.on('click', function() {
                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {

                    let idsParam = encodeURIComponent(JSON.stringify(selectedIds));
                    window.location.href = "{{ route('download-multiple-final-document-get') }}?ids=" +
                        idsParam + "&option=1";
                    clearBtn.trigger('click');
                    $('.data-table').DataTable().ajax.reload();
                } else {
                    alert("No valid data selected for document download.");
                }
            });

            /*********************************************************/
            /*******************EXTRA : LOADING INDICATOR*************/
            /*********************************************************/

            $('.confirm-btn').on('click', function() {
                const $btn = $(this);
                $btn.prop('disabled', true);
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...'
                );
                $btn.closest('form').submit();
            });

        });
    </script>
@endsection
