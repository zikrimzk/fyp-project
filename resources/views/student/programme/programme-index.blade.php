@php
    use Carbon\Carbon;
@endphp

@extends('student.layouts.main')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">{{ auth()->user()->programmes->prog_code }}</li>
                                <li class="breadcrumb-item" aria-current="page">Programme Overview</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Programme Overview</h2>
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

                <!-- [ Programme Overview ] start -->
                <div class="col-12">
                    @forelse($acts as $act)
                        <!-- [ Activity Details ] start -->
                        <div class="card mb-4 mt-3 border-2 shadow-md rounded-4">
                            <div class="card-body">

                                {{-- Activity Header --}}
                                <div
                                    class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                                    <div>
                                        <h5 class="fw-bold mb-1">{{ $act->act_name }}</h5>
                                        @if ($act->init_status == 1)
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif ($act->init_status == 2)
                                            <span class="badge bg-success">Approved by Supervisor</span>
                                        @elseif ($act->init_status == 3)
                                            <span class="badge bg-success">Approved by Committee / Deputy Dean / Dean</span>
                                        @elseif ($act->init_status == 10)
                                            <span class="badge bg-success badge-flash">Open for Submission</span>
                                        @elseif($act->init_status == 11)
                                            <span class="badge bg-danger">Locked</span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Flowchart Section --}}
                                <div class="mb-4">
                                    <h6 class="fw-semibold mb-2 text-dark">Flowchart / Material </h6>
                                    @if ($act->material)
                                        <a href="{{ URL::signedRoute('student-view-material-get', ['filename' => Crypt::encrypt($act->material)]) }}"
                                            target="_blank"
                                            class="text-decoration-none d-inline-flex align-items-center gap-2 text-primary">
                                            <i class="ti ti-download"></i> Download
                                        </a>
                                    @else
                                        <p class="text-muted fst-italic mb-0">No material uploaded</p>
                                    @endif
                                </div>

                                {{-- Final Document Section [UNFINISH] --}}
                                @if (isset($act->confirmed_document))
                                    <div class="mt-4 mb-4">
                                        <h6 class="fw-semibold mb-2">Final Document</h6>
                                        <a href="{{ route('student-view-material-get', ['filename' => Crypt::encrypt($act->confirmed_document)]) }}"
                                            class="text-decoration-none d-inline-flex align-items-center gap-2 text-primary">
                                            <i class="ti ti-file-check"></i> View Final Document
                                        </a>
                                    </div>
                                @endif

                                {{-- Document Submission Section --}}
                                <div class="mb-2">
                                    <h6 class="fw-semibold mb-3">Documents for Submission</h6>

                                    @php
                                        $activityDocs = $docs->get($act->id);
                                    @endphp

                                    @if ($activityDocs && optional($activityDocs->first())->document_name)
                                        <div class="row g-3">
                                            @foreach ($activityDocs as $item)
                                                <div class="col-12">
                                                    <div
                                                        class="bg-light p-3 rounded-3 shadow-sm border-start border-4 border-secondary">
                                                        <div
                                                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                                                            <div>
                                                                <h6 class="fw-bold mb-1 text-dark">
                                                                    {{ $item->document_name }}</h6>
                                                                <span
                                                                    class="badge {{ $item->isRequired == 1 ? 'bg-danger' : 'bg-secondary' }}">
                                                                    {{ $item->isRequired == 1 ? 'Required' : 'Optional' }}
                                                                </span>
                                                            </div>
                                                            {{-- Submission Status Condition --}}
                                                            @if ($item->submission_status == 1)
                                                                <span class="badge bg-warning mt-2 mt-md-0">No
                                                                    Attempt</span>
                                                            @elseif($item->submission_status == 2)
                                                                <span class="badge bg-danger mt-2 mt-md-0">Locked</span>
                                                            @elseif($item->submission_status == 3)
                                                                <span
                                                                    class="badge bg-light-success mt-2 mt-md-0">Submitted</span>
                                                            @elseif($item->submission_status == 4)
                                                                <span
                                                                    class="badge bg-light-danger mt-2 mt-md-0">Overdue</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-secondary mt-2 mt-md-0">Prohibited</span>
                                                            @endif
                                                        </div>

                                                        <div
                                                            class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mt-3">
                                                            <div>
                                                                <small class="text-muted">Submission Date</small>
                                                                <div class="fw-semibold">
                                                                    {{ Carbon::parse($item->submission_duedate)->format('d M Y , g:i a') }}
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <small class="text-muted">Time Remaining</small>
                                                                <div class="fw-semibold">
                                                                    {{ Carbon::parse($item->submission_duedate)->diffForHumans(Carbon::now(), [
                                                                        'parts' => 3,
                                                                        'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                                                                    ]) }}
                                                                </div>
                                                            </div>
                                                            <div class="text-md-end">
                                                                @if (in_array($act->init_status, [10, 11]))
                                                                    {{-- Allow submission based on status --}}
                                                                    @if ($item->submission_status == 1 || $item->submission_status == 4)
                                                                        <a href="{{ route('student-document-submission', Crypt::encrypt($item->submission_id)) }}"
                                                                            class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                                                            <i class="ti ti-upload"></i> Submit Document
                                                                        </a>
                                                                    @elseif($item->submission_status == 3)
                                                                        <a href="{{ route('student-document-submission', Crypt::encrypt($item->submission_id)) }}"
                                                                            class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                                                            <i class="ti ti-upload"></i> View Submission
                                                                        </a>
                                                                    @else
                                                                        <a href="javascript:void(0)"
                                                                            class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 disabled-a">
                                                                            <i class="ti ti-upload"></i> Submit Document
                                                                        </a>
                                                                    @endif
                                                                @else
                                                                    {{-- If confirmed, lock the button --}}
                                                                    <a href="javascript:void(0)"
                                                                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 disabled-a">
                                                                        <i class="ti ti-lock"></i> Locked
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted fst-italic">No documents visible to students.
                                        </p>
                                    @endif
                                </div>

                            </div>
                            <div class="card-footer d-flex justify-content-end align-items-center">
                                <button class="btn btn-sm btn-light-danger" data-bs-toggle="modal"
                                    data-bs-target="#confirmSubmissionModal-{{ $act->activity_id }}">
                                    <i class="fas fa-file-signature ms-2 me-2"></i>
                                    <span class="me-2">
                                        Confirm Submission
                                    </span>
                                </button>
                            </div>
                        </div>
                        <!-- [ Activity Details ] end -->


                        <!-- [ Confirm Submission Modal ] start -->
                        <form method="GET"
                            action="{{ route('student-confirm-submission-get', Crypt::encrypt($act->activity_id)) }}"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="modal fade" id="confirmSubmissionModal-{{ $act->activity_id }}" tabindex="-1"
                                aria-labelledby="confirmSubmissionModalLabel-{{ $act->activity_id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"
                                                id="confirmSubmissionModalLabel-{{ $act->activity_id }}">
                                                Confirm Submission</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <div class="alert alert-light" role="alert"
                                                    style="font-size: 0.95rem;">
                                                    <h5 class="mb-3">
                                                        <i class="ti ti-info-circle me-2"></i>
                                                        Submission Guidelines Before Confirmation
                                                    </h5>
                                                    <p class="mb-2"><strong>Please read the following carefully before
                                                            signing and confirming your submission:</strong></p>

                                                    <ul class="mb-3 ps-3">
                                                        <li class="mb-2">
                                                            <strong>Final Review:</strong><br>
                                                            Ensure that all uploaded documents are <u>correct</u>,
                                                            <u>complete</u>, and meet the required format.
                                                            Once confirmed, you <strong>cannot update or change</strong>
                                                            your submission.
                                                        </li>
                                                        <li class="mb-2">
                                                            <strong>Electronic Signature Declaration:</strong><br>
                                                            By providing your signature, you are certifying that:
                                                            <ul class="ps-4">
                                                                <li>The submission is <strong>100% your original
                                                                        work</strong>.</li>
                                                                <li>You understand that this signature has <strong>legal
                                                                        validity</strong> under applicable laws.</li>
                                                                <li>This electronic signature represents your
                                                                    <strong>handwritten signature</strong>.
                                                                </li>
                                                            </ul>
                                                        </li>
                                                        <li class="mb-2">
                                                            <strong>After Confirmation:</strong><br>
                                                            Your submission will be <strong>locked</strong> and sent for
                                                            review by the relevant committee.
                                                            You will be notified once your submission is either
                                                            <strong>approved</strong> or <strong>requires revision</strong>.
                                                        </li>
                                                    </ul>

                                                    <p class="mb-0 text-danger"><strong>By signing below, you acknowledge
                                                            and accept all the above terms.</strong></p>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <canvas id="signatureCanvas-{{ $act->activity_id }}"
                                                    style="border:1px solid #000000; border-radius:10px; width:100%; height:200px;"></canvas>
                                                <input type="hidden" name="signatureData"
                                                    id="signatureData-{{ $act->activity_id }}">
                                            </div>
                                            <div class="d-flex align-items-center text-muted mb-1">
                                                <div class="avtar avtar-s bg-light-primary flex-shrink-0 me-2">
                                                    <i class="fas fa-shield-alt text-primary f-20"></i>
                                                </div>
                                                <span class="text-muted text-sm w-100">
                                                    All signatures are securely stored within the system.
                                                </span>
                                            </div>
                                        </div>
                                        <div class="modal-footer justify-content-end">
                                            <div class="d-flex justify-content-between gap-3 align-items-center w-100">
                                                <button type="button" class="btn btn-light w-100"
                                                    data-clear="{{ $act->activity_id }}">
                                                    <i class="ti ti-eraser me-2"></i> Start over
                                                </button>
                                                <button type="submit" class="btn btn-danger w-100"
                                                    data-submit="{{ $act->activity_id }}">
                                                    <i class="ti ti-writing-sign me-2"></i> Confirm & Sign
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- [ Confirm Submission Modal ] end -->
                    @empty
                        <div class="alert alert-info">
                            No activities found for your programme.
                        </div>
                    @endforelse
                </div>
                <!-- [ Programme Overview ] end -->

            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>

    <script>
        const signaturePads = {};

        document.addEventListener('shown.bs.modal', function(event) {
            const modal = event.target;
            const activityId = modal.id.split('-')[1];
            const canvas = document.getElementById(`signatureCanvas-${activityId}`);

            if (canvas) {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);

                signaturePads[activityId] = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255,255,255,1)',
                    penColor: 'black'
                });
            }
        });

        document.addEventListener('click', function(e) {
            // Handle clear signature
            if (e.target.matches('[data-clear]')) {
                e.preventDefault();
                const activityId = e.target.getAttribute('data-clear');
                if (signaturePads[activityId]) signaturePads[activityId].clear();
            }

            // Handle submit signature
            if (e.target.matches('[data-submit]')) {
                const activityId = e.target.getAttribute('data-submit');
                if (signaturePads[activityId] && signaturePads[activityId].isEmpty()) {
                    e.preventDefault();
                    alert("Please provide a signature.");
                } else {
                    const dataURL = signaturePads[activityId].toDataURL('image/png');
                    document.getElementById(`signatureData-${activityId}`).value = dataURL;
                }
            }
        });
    </script>
@endsection
