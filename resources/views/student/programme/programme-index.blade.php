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
                        <div class="card mb-4 mt-3 border-2 shadow-md rounded-4">
                            <div class="card-body">

                                {{-- Activity Header --}}
                                <div
                                    class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                                    <div>
                                        <h5 class="fw-bold mb-1">{{ $act->act_name }}</h5>
                                        @if ($act->init_status == 1)
                                            <span class="badge bg-success badge-flash">Open for Submission</span>
                                        @elseif($act->init_status == 2)
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
                                {{-- <a href="{{ route('student-confirm-submission-get', Crypt::encrypt($act->activity_id)) }}"
                                    class="btn btn-sm btn-light-danger">Confirm Submission (Beta)</a> --}}
                                <a href="{{ route('student-confirm-submission-get', Crypt::encrypt($act->activity_id)) }}"
                                    class="btn btn-sm btn-light-danger" data-bs-toggle="modal"
                                    data-bs-target="#confirmSubmissionModal">
                                    <i class="fas fa-file-signature ms-2 me-2"></i>
                                    <span class="me-2">
                                        Confirm Submission
                                    </span>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">
                            No activities found for your programme.
                        </div>
                    @endforelse
                </div>

                <!-- [ Confirm Submission Modal ] start -->
                <form method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal fade" id="confirmSubmissionModal" tabindex="-1"
                        aria-labelledby="confirmSubmissionModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmSubmissionModalLabel">Confirm Submission</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <canvas id="signatureCanvas"
                                                    style="border:1px solid #000000; border-radius:10px; width:100%; height:200px;"></canvas>
                                                <input type="hidden" name="signature" id="signatureData">
                                            </div>
                                            <div class="d-flex align-items-center text-muted mb-1">
                                                <div class="avtar avtar-s bg-light-primary flex-shrink-0 me-2">
                                                    <i class="material-icons-two-tone text-primary f-20">security</i>
                                                </div>
                                                <span class="text-muted text-sm w-100">
                                                    By submitting this work electronically, I confirm that this submission
                                                    is my own original work and I understand that this electronic
                                                    submission holds the same validity as a physical signature under
                                                    applicable local law.
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-end">
                                    <div class="flex-grow-1 text-end">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="button"
                                                    class="btn btn-light btn-pc-default w-100 d-flex justify-content-center align-items-center"
                                                    id="clearSig">
                                                    <i class="ti ti-eraser me-2"></i>
                                                    Start over
                                                </button>
                                                <button type="submit"
                                                    class="btn btn-primary w-100 d-flex justify-content-center align-items-center"
                                                    id="submitSig">
                                                    <i class="ti ti-writing-sign me-2"></i>
                                                    Confirm & Sign
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Confirm Submission Modal ] end -->

                <!-- [ Programme Overview ] end -->

            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>

    <script>
        let signaturePad;

        const confirmModal = document.getElementById('confirmSubmissionModal');

        if (confirmModal) {
            confirmModal.addEventListener('shown.bs.modal', function() {
                const canvas = document.getElementById('signatureCanvas');
                if (!canvas) return;

                // Resize canvas properly for desktop and mobile
                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);
                    if (signaturePad) signaturePad.clear();
                }

                resizeCanvas(); // Resize on open

                signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 1)',
                    penColor: 'black',
                });
            });

            // Clear button
            let clearSigBtn = document.getElementById('clearSig');
            if (clearSigBtn) {
                clearSigBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (signaturePad) signaturePad.clear();
                });
            }

            // Optional: Submit Signature Button (to test only)
            let submitSigBtn = document.getElementById('submitSig');
            if (submitSigBtn) {
                submitSigBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!signaturePad || signaturePad.isEmpty()) {
                        alert("Please provide a signature.");
                        return;
                    }

                    const dataURL = signaturePad.toDataURL('image/png');
                    console.log("Captured Signature:", dataURL);
                    document.getElementById('signatureData').value = dataURL;
                    alert("Signature saved. Now click Confirm & Sign to submit.");
                });
            }
        }
    </script>
@endsection
