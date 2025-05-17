@php
    use Carbon\Carbon;

    $submissionDate = Carbon::parse($doc->submission_date);
    $dueDate = Carbon::parse($doc->submission_duedate);

    $diffInSeconds = $submissionDate->diffInSeconds($dueDate);
    $isEarly = $submissionDate->lessThan($dueDate);

    // Get human readable difference
    $humanDiff = $submissionDate->diffForHumans($dueDate, [
        'parts' => 3,
        'short' => false,
        'syntax' => Carbon::DIFF_ABSOLUTE,
    ]);
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
                                <h2 class="mb-0">{{ $doc->document_name }}</h2>
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

            <div class="d-flex justify-content-start align-items-center mb-3">
                <a href="{{ route('student-programme-overview') }}"
                    class="btn btn-sm btn-light-primary d-flex align-items-center justify-content-center me-2">
                    <i class="ti ti-arrow-left me-2"></i>
                    <span class="me-2">Back</span>
                </a>
            </div>

            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- [ Submission Document ] start -->

                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-4">

                            <div id="submission_status">
                                <h4 class="mb-4">Submission Status</h4>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <tbody>
                                            {{-- Submission Status --}}
                                            <tr style="height:80px" class="bg-light">
                                                <th scope="row" class="fw-bold">Submission Status</th>
                                                @if ($doc->submission_status == 1)
                                                    <td class="bg-light-warning">
                                                        No Attempt
                                                    </td>
                                                @elseif($doc->submission_status == 2)
                                                    <td class="bg-light-danger">
                                                        Locked
                                                    </td>
                                                @elseif($doc->submission_status == 3)
                                                    <td class="bg-light-success">
                                                        Submitted
                                                    </td>
                                                @elseif($doc->submission_status == 4)
                                                    <td class="bg-light-danger">
                                                        Overdue
                                                    </td>
                                                @else
                                                    <span class="badge bg-secondary mt-2 mt-md-0">Prohibited</span>
                                                @endif
                                            </tr>

                                            {{-- Appear when only have file --}}
                                            @if ($doc->submission_document != '-')
                                                <tr style="height:80px">
                                                    <th scope="row" class="fw-bold">Submission Date</th>
                                                    <td>
                                                        {{ Carbon::parse($doc->submission_date)->format('d M Y , g:i a') }}
                                                    </td>
                                                </tr>
                                                <tr style="height:80px" class="bg-light">
                                                    <th scope="row" class="fw-bold">Time remaining</th>
                                                    <td class="{{ $isEarly ? 'bg-light-success' : 'bg-light-danger' }}">
                                                        Document was submitted {{ $humanDiff }}
                                                        {{ $isEarly ? 'earlier' : 'late' }}
                                                    </td>
                                                </tr>
                                                <tr style="height:80px">
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
                                                <tr style="height:80px">
                                                    <th scope="row" class="fw-bold">Submission Due Date</th>
                                                    <td> {{ Carbon::parse($doc->submission_duedate)->format('d M Y , g:i a') }}
                                                    </td>
                                                </tr>

                                                <tr style="height:80px" class="bg-light">
                                                    <th scope="row" class="fw-bold">Time Remaining</th>
                                                    <td>
                                                        {{ Carbon::parse($doc->submission_duedate)->diffForHumans(Carbon::now(), [
                                                            'parts' => 3,
                                                            'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                                                        ]) }}
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <hr>

                                <!-- [ Option Section ] start -->
                                <div class="mb-5 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                    @if ($doc->submission_document != '-')
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
                                            class="btn btn-outline-primary d-flex align-items-center gap-2"
                                            id="addSubmissionBtn" title="Add Submission">
                                            <i class="ti ti-plus f-18"></i>
                                            <span class="d-none d-sm-inline me-2">
                                                Add Submission
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
                                        File format: <strong>PDF, DOCX</strong>
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
                                <iframe id="pdfPreview" width="100%" height="1000px"
                                    style="display: none; border: 1px solid #ccc;"></iframe>
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

                <!-- [ Delete Modal ] start -->
                <div class="modal fade" id="removeSubmissionModal" data-bs-keyboard="false" tabindex="-1"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12 mb-4">
                                        <div class="d-flex justify-content-center align-items-center mb-3">
                                            <i class="ti ti-trash text-danger" style="font-size: 100px"></i>
                                        </div>

                                    </div>
                                    <div class="col-sm-12">
                                        <div class="d-flex justify-content-center align-items-center mb-5">
                                            <h4 class="text-center">Are you sure to remove this submission ?</h4>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="d-flex justify-content-between gap-3 align-items-center mt-2">
                                            <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <a class="btn btn-danger w-100"
                                                href="{{ route('student-remove-document-get', ['id' => Crypt::encrypt($doc->submission_id), 'filename' => Crypt::encrypt($submission_dir . '/' . $doc->submission_document)]) }}">Remove
                                                Anyways</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Delete Modal ] end -->

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
                    allowedFileTypes: ['.pdf', '.docx']
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
