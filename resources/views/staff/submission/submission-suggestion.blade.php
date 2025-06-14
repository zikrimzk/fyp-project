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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Submission</a></li>
                                <li class="breadcrumb-item" aria-current="page">Submission Suggestion</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Submission Suggestion</h2>
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
                                            <option value="-1000">-- Select Activity --</option>
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
                                            <option value="1" selected>Eligible</option>
                                            <option value="2">Submission Opened</option>
                                            <option value="3">Prerequisite Pending</option>
                                            <option value="4">Under Review</option>
                                            <option value="5">Completed</option>
                                            <option value="6">Submission Archived</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearStatusFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 actAlert">

                                    <!-- System-Generated Eligibility Suggestions Explanation -->
                                    <div class="alert alert-light d-flex align-items-start gap-3 p-4" role="alert">
                                        <i class="ti ti-info-circle fs-3"></i>
                                        <div class="w-100">
                                            <h4 class="mb-3 fw-semibold">How Eligibility is Determined</h4>
                                            <p class="mb-3">The system automatically suggests students who are eligible to
                                                proceed with their next activity. Below is an overview of how this decision
                                                is made based on student records:</p>

                                            <div class="mb-3">
                                                <h5 class="fw-semibold mb-2">Eligibility Criteria</h5>
                                                <ul class="mb-1 ps-3">
                                                    <li class="mb-2">
                                                        <strong>Active Student Status</strong>
                                                        <ul class="mt-1">
                                                            <li>Student must have an <span
                                                                    class="text-decoration-underline">active system
                                                                    account</span></li>
                                                            <li>Student must be <span
                                                                    class="text-decoration-underline">enrolled in the
                                                                    current semester</span></li>
                                                        </ul>
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>First-Time Submission (New Students)</strong>
                                                        <ul class="mt-1">
                                                            <li>Must have reached the <span
                                                                    class="text-decoration-underline">minimum semester
                                                                    requirement</span> set by the program</li>
                                                            <li>Must fulfill all <span
                                                                    class="text-decoration-underline">prerequisite
                                                                    requirements</span> for the first activity</li>
                                                        </ul>
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>Continuing Submission (Returning Students)</strong>
                                                        <ul class="mt-1">
                                                            <li>Must have <span class="text-decoration-underline">completed
                                                                    the previous activity</span> in full</li>
                                                            <li>Must have received <span
                                                                    class="text-decoration-underline">official
                                                                    confirmation</span> of completion before proceeding</li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="mb-3">
                                                <h5 class="fw-semibold mb-2">Role of Committee</h5>
                                                <ol class="mb-1 ps-3">
                                                    <li class="mb-2">Review the list of suggested students</li>
                                                    <li class="mb-2">Verify eligibility if necessary</li>
                                                    <li class="mb-2">Approve eligible students to open their submission
                                                    </li>
                                                </ol>
                                            </div>

                                            <div class="alert alert-light bg-light border mt-3 p-3">
                                                <h6 class="fw-semibold text-danger mb-2">Important Notes:</h6>
                                                <ul class="mb-0 ps-3 small">
                                                    <li class="mb-1">If a student's previous submission is archived, they
                                                        must be <strong>unarchived</strong> before being approved. Check the
                                                        status in the <a href="#fil_status" class="fw-semibold"
                                                            onclick="$('#fil_status').val('6').trigger('click')">Submission
                                                            Archive</a>.</li>
                                                    <li class="mb-1">Eligibility status is generated based on student
                                                        submission records and approval confirmations.</li>
                                                    <li class="mb-1">If a student does not appear in the suggestion list,
                                                        it could be due to <strong>unmet requirements</strong> or
                                                        <strong>incorrect procedure settings</strong>.
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="row g-3 align-items-center mb-3 content d-none">

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

                            <div class="dt-responsive table-responsive content d-none">
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
                                                                    notify</span> the student and supervisors</li>
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
                                                                    <strong>Reset</strong> all submission documents
                                                                </li>
                                                                <li class="mb-2">
                                                                    <i class="fas fa-user-clock me-2"></i> Require
                                                                    student to <strong>resubmit</strong> all
                                                                    documents
                                                                </li>
                                                                <li>
                                                                    <i class="fas fa-unlock-alt me-2"></i>
                                                                    Committee must <strong>reapprove</strong>
                                                                    for new submissions
                                                                </li>
                                                            </ul>
                                                            <p class="mt-2 mb-0 text-danger fw-semibold">
                                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                                Student cannot submit until committee reopens this
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

                            @foreach ($data as $upd)
                                <!-- [ Approve Modal ] Start -->
                                <div class="modal fade" id="approveModal-{{ $upd->student_id . $upd->activity_id }}"
                                    data-bs-keyboard="false" tabindex="-1" aria-hidden="true"
                                    data-bs-backdrop="static">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content">
                                            {{-- <div class="modal-body">
                                                    <div class="row">

                                                        <!-- Icon -->
                                                        <div class="col-sm-12 mb-4 text-center">
                                                            <i class="ti ti-circle-check text-success"
                                                                style="font-size: 100px"></i>
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
                                                                    <li>Carefully review the student's submission document.
                                                                    </li>
                                                                    <li>If necessary, provide comments or notes below.</li>
                                                                    <li>Sign using the signature box to confirm your
                                                                        approval.</li>
                                                                    <li>Click <strong>"Confirm & Sign"</strong> to finalize
                                                                        this action.
                                                                    </li>
                                                                    <li class="text-danger"><strong>This action is final
                                                                            and cannot be
                                                                            undone.</strong></li>
                                                                </ul>
                                                                <div class="mt-2">
                                                                    <strong class="text-muted fst-italic d-block">
                                                                        By signing below, you confirm that the signature is
                                                                        your own
                                                                        handwriting and that it is legally binding within
                                                                        the
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
                                                                <div
                                                                    class="avtar avtar-s bg-light-primary flex-shrink-0 me-2">
                                                                    <i class="fas fa-shield-alt text-primary f-20"></i>
                                                                </div>
                                                                <span class="text-sm">All signatures are securely stored
                                                                    within the
                                                                    system.</span>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div> --}}

                                            <!-- Approval Confirmation Modal -->
                                            <div class="modal-body">

                                                <!-- Header with icon -->
                                                <div class="col-sm-12 mb-4 text-center">
                                                    <i class="ti ti-circle-check text-success"
                                                        style="font-size: 100px"></i>
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
                                                                        notify</span> the student and supervisors</li>
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
                                                    <a href="{{ route('submission-eligibility-approval-get', ['studentID' => Crypt::encrypt($upd->student_id), 'activityID' => Crypt::encrypt($upd->activity_id), 'opt' => 1]) }}"
                                                        class="btn btn-success w-100 confirm-btn">
                                                        Confirm Approval
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- [ Approve Modal ] end -->

                                <!-- [ Revert Modal ] Start -->
                                <div class="modal fade" id="revertModal-{{ $upd->student_id . $upd->activity_id }}"
                                    data-bs-keyboard="false" tabindex="-1" aria-hidden="true"
                                    data-bs-backdrop="static">
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
                                                                        <strong>Reset</strong> all submission documents
                                                                    </li>
                                                                    <li class="mb-2">
                                                                        <i class="fas fa-user-clock me-2"></i> Require
                                                                        student to <strong>resubmit</strong> all
                                                                        documents
                                                                    </li>
                                                                    <li>
                                                                        <i class="fas fa-unlock-alt me-2"></i>
                                                                        Committee must <strong>reapprove</strong>
                                                                        for new submissions
                                                                    </li>
                                                                </ul>
                                                                <p class="mt-2 mb-0 text-danger fw-semibold">
                                                                    <i class="fas fa-exclamation-circle me-1"></i>
                                                                    Student cannot submit until committee reopens this
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
                                                        <div
                                                            class="d-flex justify-content-between gap-3 align-items-center">
                                                            <button type="reset" class="btn btn-light w-50"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <a href="{{ route('submission-eligibility-approval-get', ['studentID' => Crypt::encrypt($upd->student_id), 'activityID' => Crypt::encrypt($upd->activity_id), 'opt' => 2]) }}"
                                                                class="btn btn-warning w-100 confirm-btn">
                                                                Confirm Revert
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- [ Revert Modal ] End -->
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
                    url: "{{ route('submission-suggestion') }}",
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

            // FILTER : ACTIVITY
            $('#fil_activity_id').on('change', function() {
                if ($(this).val() == '-1000') {
                    $('.content').addClass('d-none');
                    $('.actAlert').removeClass('d-none m-0');
                } else {
                    $('.actAlert').addClass('d-none m-0');
                    $('.data-table').DataTable().ajax.reload();
                    $('.content').removeClass('d-none');
                }
                clearBtn.trigger('click');
            });

            $('#clearActivityFilter').click(function() {
                $('#fil_activity_id').val('-1000').change();
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
                clearBtn.trigger('click');

            });

            $('#clearStatusFilter').click(function() {
                $('#fil_status').val('1').change();
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
                        let checkbox = $(`.user-checkbox[value="${id}"]`);
                        let row = checkbox.closest('tr');
                        let status = row.find('td:eq(2)').text()
                            .trim();

                        if (status === "Eligible") {
                            hasEligible = true;
                        } else if (status === "Submission Opened") {
                            hasOpened = true;
                        }
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
                toggleSelectButton();
            });

            /*********************************************************/
            /**********SELECT : APPROVAL & REVERT ********************/
            /*********************************************************/

            approveBtn.on('click', function() {
                const $button = $(this);
                let activityId = $('#fil_activity_id').val();
                let selectedCheckboxes = $(".user-checkbox:checked");
                let selectedIds = selectedCheckboxes.map(function() {
                    return $(this).val();
                }).get();


                if (selectedIds.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span> Loading...'
                    );

                    $.ajax({
                        url: "{{ route('multiple-submission-eligibility-approval-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedIds,
                            activityId: activityId,
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
                            showToast('error', xhr.responseText);
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
                let activityId = $('#fil_activity_id').val();
                let selectedCheckboxes = $(".user-checkbox:checked");
                let selectedIds = selectedCheckboxes.map(function() {
                    return $(this).val();
                }).get();


                if (selectedIds.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span> Loading...'
                    );

                    $.ajax({
                        url: "{{ route('multiple-submission-eligibility-approval-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedIds,
                            activityId: activityId,
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
                            showToast('error', xhr.responseText);
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
