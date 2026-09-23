@php
    use Carbon\Carbon;

    $dueDate = Carbon::parse($doc->submission_duedate);
    $hasSubmission = $doc->submission_document !== '-' && !empty($doc->submission_date);
    $submissionDate = $hasSubmission ? Carbon::parse($doc->submission_date) : null;
    $isOverdue = in_array((int) $doc->submission_status, [1, 4]) && $dueDate->isPast();
    $isEarly = $hasSubmission ? $submissionDate->lessThanOrEqualTo($dueDate) : false;
    $humanDiff = $hasSubmission
        ? $submissionDate->diffForHumans($dueDate, [
            'parts' => 3,
            'short' => false,
            'syntax' => Carbon::DIFF_ABSOLUTE,
        ])
        : null;

    $statusDetails = match ((int) $doc->submission_status) {
        1 => ['Not Submitted', 'bg-light-warning text-warning', 'ti-clock'],
        2 => ['Locked', 'bg-light-danger text-danger', 'ti-lock'],
        3 => ['Submitted', 'bg-light-success text-success', 'ti-circle-check'],
        4 => ['Overdue', 'bg-light-danger text-danger', 'ti-alert-triangle'],
        default => ['Unavailable', 'bg-light text-muted', 'ti-ban'],
    };

    $deadlineText = $isOverdue
        ? 'Overdue by ' . $dueDate->diffForHumans(now(), ['parts' => 3, 'syntax' => Carbon::DIFF_ABSOLUTE])
        : $dueDate->diffForHumans(now(), ['parts' => 3, 'syntax' => Carbon::DIFF_RELATIVE_TO_NOW]);
@endphp
@extends('student.layouts.main')

@section('content')
    <style>
        .submission-summary-card {
            border: 1px solid #e3e9f0;
            box-shadow: 0 5px 18px rgba(15, 23, 42, .055);
        }

        .submission-status-icon {
            width: 2.75rem;
            height: 2.75rem;
            display: inline-grid;
            place-items: center;
            border-radius: .75rem;
            font-size: 1.25rem;
        }

        .submission-details-table th {
            width: 42%;
            color: #475569;
            background: #f8fafc;
        }

        .submission-details-table th,
        .submission-details-table td {
            padding: 1rem;
            vertical-align: middle;
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
                                <li class="breadcrumb-item">{{ auth()->user()->programmes->prog_code }}</li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('student-programme-overview') }}">
                                        Programme Overview
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('student-programme-overview') }}">
                                        {{ $doc->activity_name }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">{{ $doc->document_name }}</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0 d-flex align-items-center">
                                    <a href="{{ route('student-programme-overview') }}" class="btn me-2">
                                        <span class="f-18">
                                            <i class="ti ti-arrow-left"></i>
                                        </span>
                                    </a>
                                    {{ $doc->document_name }}
                                </h2>
                                <p class="text-muted mb-0 ms-md-5 ps-md-2">
                                    {{ $doc->activity_name }} · {{ $doc->isRequired ? 'Required document' : 'Optional document' }}
                                </p>
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

                <!-- [ Submission Document ] start -->

                <div class="col-12">
                    <div class="card submission-summary-card">
                        <div class="card-body p-4">

                            <div id="submission_status">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <span class="submission-status-icon {{ $statusDetails[1] }}">
                                        <i class="ti {{ $statusDetails[2] }}" aria-hidden="true"></i>
                                    </span>
                                    <div>
                                        <h4 class="mb-1">Submission Status</h4>
                                        <span class="badge {{ $statusDetails[1] }}">{{ $statusDetails[0] }}</span>
                                    </div>
                                </div>

                                @if ($isOverdue && !$hasSubmission)
                                    <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                                        <i class="ti ti-alert-triangle fs-5 mt-1" aria-hidden="true"></i>
                                        <div>
                                            <strong>This document is overdue.</strong>
                                            Submit it as soon as possible. The system will record the actual submission date.
                                        </div>
                                    </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-bordered submission-details-table mb-0">
                                        <tbody>
                                            {{-- Submission Status --}}
                                            <tr>
                                                <th scope="row" class="fw-bold">Submission Status</th>
                                                <td><span class="badge {{ $statusDetails[1] }}">{{ $statusDetails[0] }}</span></td>
                                            </tr>

                                            {{-- Appear when only have file --}}
                                            @if ($hasSubmission)
                                                <tr>
                                                    <th scope="row" class="fw-bold">Submission Date</th>
                                                    <td>
                                                        {{ $submissionDate->format('d M Y, g:i a') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" class="fw-bold">Submission Timing</th>
                                                    <td class="{{ $isEarly ? 'text-success' : 'text-danger' }} fw-semibold">
                                                        Submitted {{ $humanDiff }} {{ $isEarly ? 'before the deadline' : 'after the deadline' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" class="fw-bold">File Submission</th>
                                                    <td>
                                                        <a href="{{ route('student-view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $doc->submission_document)]) }}"
                                                            class="d-flex align-items-center" target="_blank">
                                                            <i class="fas fa-file-pdf f-18 text-danger me-2"></i>
                                                            {{ $doc->submission_document }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <th scope="row" class="fw-bold">Submission Due Date</th>
                                                    <td> {{ $dueDate->format('d M Y, g:i a') }}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th scope="row" class="fw-bold">Deadline</th>
                                                    <td class="fw-semibold {{ $isOverdue ? 'text-danger' : '' }}">
                                                        {{ $deadlineText }}
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <!-- [ Option Section ] start -->
                                <div class="mt-4 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                    @if ($hasSubmission)
                                        <button type="button"
                                            class="btn btn-outline-primary d-flex align-items-center gap-2"
                                            id="updateSubmissionBtn" title="Update Submission">
                                            <i class="ti ti-edit-circle f-18"></i>
                                            <span class="d-none d-sm-inline me-2">
                                                Update Submission
                                            </span>
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-danger d-flex align-items-center gap-2"
                                            id="removeSubmissionBtn" title="remove Submission" data-bs-toggle="modal"
                                            data-bs-target="#removeSubmissionModal">
                                            <i class="ti ti-trash f-18"></i>
                                            <span class="d-none d-sm-inline me-2">
                                                Remove Submission
                                            </span>
                                        </button>
                                    @else
                                        <button type="button"
                                            class="btn {{ $isOverdue ? 'btn-danger' : 'btn-primary' }} d-flex align-items-center gap-2"
                                            id="addSubmissionBtn" title="Add Submission">
                                            <i class="ti ti-upload f-18"></i>
                                            <span class="d-none d-sm-inline me-2">
                                                Submit Document
                                            </span>
                                        </button>
                                    @endif
                                </div>
                                <!-- [ Option Section ] end -->
                            </div>

                            <div id="submission_area" class="d-none">

                                <!-- Header -->
                                <div
                                    class="d-flex d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
                                    <h4 class="mb-1 text-start">Add Submission</h4>
                                    <small class="text-muted text-start">Please follow the requirements before
                                        uploading</small>
                                </div>

                                <hr>

                                <!-- File Info Line -->
                                <div
                                    class="mb-3 d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 text-muted small px-2">
                                    <div>
                                        File format: <strong>PDF</strong>
                                    </div>
                                    <div>
                                        Maximum file size: <strong>100 MB</strong>, Maximum number of files:
                                        <strong>1</strong>
                                    </div>
                                </div>

                                <!-- Uppy Uploader -->
                                <div class="uppy-container-wrapper mb-4 border rounded shadow-sm p-3 bg-light">
                                    <div id="pc-uppy-1"></div>
                                </div>

                                <!-- PDF Preview -->
                                <div class="d-none d-md-block">
                                    <iframe id="pdfPreview" width="100%" height="1000px"
                                        style="display: none; border: 1px solid #ccc;"></iframe>
                                </div>

                                <hr>

                                <!-- Action Buttons -->
                                <div class="mb-3 d-flex justify-content-start align-items-center gap-2">
                                    <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                        id="saveChangesBtn">
                                        <span>Save Changes</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger d-flex align-items-center gap-2"
                                        id="cancelSubmissionAreaBtn">
                                        <span>Cancel</span>
                                    </button>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <!-- [ Delete Confirmation Modal ] start -->
                <div class="modal fade" id="removeSubmissionModal" data-bs-keyboard="false" tabindex="-1"
                    aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-body p-5">
                                <div class="text-center">
                                    <i class="ti ti-alert-circle text-danger" style="font-size: 80px;"></i>
                                    <h4 class="mt-4 fw-semibold">Confirm Submission Deletion</h4>
                                    <p class="text-muted mt-2 mb-4">
                                        Are you sure you want to delete this submission? This action cannot be
                                        undone.
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between gap-3">
                                    <button type="button" class="btn btn-outline-secondary w-50"
                                        data-bs-dismiss="modal">
                                        Cancel
                                    </button>
                                    <x-mutation-button :action="route('student-remove-document-get', ['id' => Crypt::encrypt($doc->submission_id), 'filename' => Crypt::encrypt($submission_dir . '/' . $doc->submission_document)])"
                                        class="btn btn-danger w-50">
                                        Delete Anyways
                                    </x-mutation-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Delete Confirmation Modal ] end -->

                <!-- [ Submission Document ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {

            /* INTERFACE NAVIGATION */
            $('#addSubmissionBtn').on('click', function() {
                $('#submission_status').fadeOut(150, function() {
                    $('#submission_area').fadeIn(300).removeClass('d-none');
                });
            });

            $('#cancelSubmissionAreaBtn').on('click', function() {
                uppy.cancelAll();
                $('#submission_area').fadeOut(150, function() {
                    $('#submission_area').addClass('d-none');
                    $('#submission_status').fadeIn(300);
                });
            });

            /* ADD SUBMISSION */
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#saveChangesBtn').on('click', function() {
                const uploadedFiles = uppy.getFiles();
                const $btn = $(this);

                if (uploadedFiles.length === 0) {
                    alert('No file selected. Please upload your document before submitting.');
                    return;
                }

                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
                );

                const file = uploadedFiles[0].data;
                const formData = new FormData();
                formData.append('file', file);
                formData.append('document_id', "{{ $doc->document_id }}");
                formData.append('activity_id', "{{ $doc->activity_id }}");
                formData.append('submission_id', "{{ $doc->submission_id }}");

                $.ajax({
                    url: "{{ route('student-submit-document-post') }}",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            alert("Validation error: " + JSON.stringify(xhr.responseJSON
                                .errors));
                        } else {
                            alert('Oops! Something went wrong. Please try again.');
                        }

                        $btn.prop('disabled', false).html('Save Changes');
                    }
                });
            });


            /* UPDATE SUBMISSION */
            const submittedFile = @json(
                $doc->submission_document
                    ? [
                        'name' => $doc->submission_document,
                        'url' => asset('storage/' . $submission_dir . '/' . $doc->submission_document),
                    ]
                    : null);

            $('#updateSubmissionBtn').on('click', function() {
                if (submittedFile) {
                    uppy.cancelAll();

                    $('#submission_status').fadeOut(150, function() {
                        $('#submission_area').fadeIn(300).removeClass('d-none');
                    });

                    fetch(submittedFile.url)
                        .then(response => response.blob())
                        .then(blob => {
                            uppy.addFile({
                                name: submittedFile.name,
                                type: blob.type,
                                data: blob,
                                source: 'server',
                                isRemote: false,
                            });
                        })
                        .catch(err => {
                            console.error('Failed to load previous file:', err);
                            alert('Could not load the previous submission file.');
                        });
                } else {
                    alert('No previous submission found.');
                }
            });


        });
    </script>

    <script type="module">
        import {
            Uppy,
            Dashboard,
            XHRUpload,
        } from 'https://releases.transloadit.com/uppy/v3.23.0/uppy.min.mjs';

        const uppy = new Uppy({
                debug: true,
                autoProceed: false,
                restrictions: {
                    maxNumberOfFiles: 1,
                    maxFileSize: 100 * 1024 * 1024,
                    allowedFileTypes: ['.pdf']
                }
            })
            .use(Dashboard, {
                target: '#pc-uppy-1',
                inline: true,
                showProgressDetails: true,
                showRemoveButtonAfterComplete: true,
                proudlyDisplayPoweredByUppy: false,
                hideUploadButton: true,
                height: '320px',
            })
            .use(XHRUpload, {
                endpoint: "{{ route('student-submit-document-post') }}",
                fieldName: 'file',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

        uppy.on('file-added', (file) => {
            const fileType = file.type;

            if (fileType === 'application/pdf') {
                const blobUrl = URL.createObjectURL(file.data);
                const $previewFrame = $('#pdfPreview');

                $previewFrame.attr('src', blobUrl).hide().fadeIn(300);
            } else {
                $('#pdfPreview').fadeOut(300, function() {
                    $(this).attr('src', '');
                });
            }
        });

        uppy.on('file-removed', (file) => {
            const previewFrame = $('#pdfPreview');
            previewFrame.fadeOut(300, function() {
                previewFrame.attr('src', '');
                previewFrame.css('display', 'none');
            });
        });
    </script>
@endsection
