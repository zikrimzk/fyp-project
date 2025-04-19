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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">SOP</a></li>
                                <li class="breadcrumb-item" aria-current="page">Form Generator</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Form Generator</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->


            <!-- [ Alert ] start -->
            <div id="alert-container">
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
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
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
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

                <!-- [ Form Generator ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- [ Form Setting ] start -->
                                <div class="col-sm-4">
                                    <div class="row">
                                        <div class="col-sm-12" id="formGeneration">
                                            <div class="mb-3">
                                                <h5 class="mb-0">Generate Form</h5>
                                                <small>Select the activity first before generating the form</small>
                                            </div>

                                            <div class="mb-3">
                                                <select name="activity_id" class="form-select" id="selectActivity">
                                                    <option value="" selected>-- Select Activity --</option>
                                                    @foreach ($acts as $activity)
                                                        <option value="{{ $activity->id }}">{{ $activity->act_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="d-grid mt-4 mb-4">
                                                <button type="button" class="btn btn-primary" id="generateForm">Generate
                                                    Form</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-12" id="formSetting">
                                            <div class="mb-3">
                                                <h5 class="mb-0">Settings</h5>
                                                <small>Customize your form settings here</small>
                                            </div>

                                            <div class="mb-3">
                                                <select name="activity_id" class="form-select" id="selectActivity">
                                                    <option value="" selected>-- Select Activity --</option>
                                                    @foreach ($acts as $activity)
                                                        <option value="{{ $activity->id }}">{{ $activity->act_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="d-grid mt-4 mb-4 gap-3">
                                                <button type="button" class="btn btn-primary" id="generateForm">Update Form
                                                    Settings</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-12" id="formSetting">
                                            <div class="mb-3">
                                                <h5 class="mb-0">Options</h5>
                                                <small>Select either to preview or download the form.</small>
                                            </div>

                                            <div class="d-grid mt-4 gap-3">
                                                <button type="button" class="btn btn-info" id="generateForm">View Form
                                                    (.pdf)</button>
                                                <button type="button" class="btn btn-danger" id="generateForm">Download
                                                    Form (.pdf)</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- [ Form Setting ] end -->

                                <!-- [ Form Preview ] start -->
                                <div class="col-sm-8 border">
                                    <h5 class="mb-3 mt-3 text-center">Preview</h5>

                                </div>
                                <!-- [ Form Preview ] end -->

                            </div>

                        </div>
                    </div>
                </div>
                <!-- [ Form Generator ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {


        });
    </script>
@endsection
