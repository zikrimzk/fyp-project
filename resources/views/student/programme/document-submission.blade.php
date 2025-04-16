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

            <div class="d-flex justify-content-start align-items-center mb-3">
                <a href="{{ route('student-programme-overview') }}"
                    class="btn btn-sm btn-light-primary d-flex align-items-center justify-content-center me-2">
                    <i class="ti ti-arrow-left me-2"></i>
                    <span class="me-2">Back</span>
                </a>
            </div>

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
                    <div class="card">
                        <div class="card-body p-4">

                            <div id="submission_status">
                                <!-- [ Option Section ] start -->
                                <div class="mb-5 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                    <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                        id="addSubmissionBtn" title="Add Submission">
                                        <i class="ti ti-plus f-18"></i>
                                        <span class="d-none d-sm-inline me-2">
                                            Add Submission
                                        </span>
                                    </button>
                                </div>
                                <!-- [ Option Section ] end -->

                                <h4 class="mb-4">Submission Status</h4>

                                <hr>

                                <div class="table-responsive">
                                    <table
                                        class="table text-nowrap table-striped table-hover table-bordered border-primary">
                                        <tbody>

                                            <tr style="height:80px">
                                                <th scope="row" class="fw-bold ">Submission Status</th>
                                                <td class=" bg-warning-transparent">No Attempt</td>
                                            </tr>

                                            <tr style="height:80px">
                                                <th scope="row" class="fw-bold">Submission Due Date</th>
                                                <td> 15 December 2025, 12:00 AM </td>
                                            </tr>
                                            <tr style="height:80px">
                                                <th scope="row" class="fw-bold">Time Remaining</th>
                                                <td>1 month 2 days 3 hours 54 minutes from now</td>
                                            </tr>


                                            <tr style="height:80px">
                                                <th scope="row" class="fw-bold">File Submission</th>
                                                <td>

                                                    <a href="javascript:void(0);" class="me-0"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            class="me-1 align-middle" viewBox="0 0 24 24"
                                                            style="fill: rgba(218, 19, 19, 1);transform: ;msFilter:;">
                                                            <path
                                                                d="M8.267 14.68c-.184 0-.308.018-.372.036v1.178c.076.018.171.023.302.023.479 0 .774-.242.774-.651 0-.366-.254-.586-.704-.586zm3.487.012c-.2 0-.33.018-.407.036v2.61c.077.018.201.018.313.018.817.006 1.349-.444 1.349-1.396.006-.83-.479-1.268-1.255-1.268z">
                                                            </path>
                                                            <path
                                                                d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM9.498 16.19c-.309.29-.765.42-1.296.42a2.23 2.23 0 0 1-.308-.018v1.426H7v-3.936A7.558 7.558 0 0 1 8.219 14c.557 0 .953.106 1.22.319.254.202.426.533.426.923-.001.392-.131.723-.367.948zm3.807 1.355c-.42.349-1.059.515-1.84.515-.468 0-.799-.03-1.024-.06v-3.917A7.947 7.947 0 0 1 11.66 14c.757 0 1.249.136 1.633.426.415.308.675.799.675 1.504 0 .763-.279 1.29-.663 1.615zM17 14.77h-1.532v.911H16.9v.734h-1.432v1.604h-.906V14.03H17v.74zM14 9h-1V4l5 5h-4z">
                                                            </path>
                                                        </svg>
                                                        B032320063_Technical_Report.pdf
                                                    </a>

                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="submission_area">

                                <h4 class="mb-4">Add Submission</h4>

                                <hr>

                                <div class="pc-uppy" id="uploadSubmissionArea"> </div>

                                <!-- [ Option Section ] start -->
                                <div class="mb-5 d-flex justify-content-start align-items-center gap-2">
                                    <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                        id="saveChangesBtn" title="Save Changes">
                                        <span>
                                            Save Changes
                                        </span>
                                    </button>
                                    <button type="button" class="btn btn-light-danger d-flex align-items-center gap-2"
                                        id="cancelSubmissionAreaBtn" title="Save Changes">
                                        <span>
                                            Cancel
                                        </span>
                                    </button>
                                </div>
                                <!-- [ Option Section ] end -->

                            </div>

                        </div>
                    </div>
                </div>

                <!-- [ Submission Document ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {

            $('#addSubmissionBtn').on('click', function() {
                $("#submission_area").removeClass('d-none');
                $('#submission_status').addClass("d-none");
            });

            $('#cancelSubmissionAreaBtn').on('click', function() {
                $("#submission_area").addClass('d-none');
                $('#submission_status').removeClass("d-none");
            });

        });
    </script>
@endsection
