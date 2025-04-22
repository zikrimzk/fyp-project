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
                                                {{-- <button type="button" class="btn btn-primary" id="generateForm">Generate
                                                    Form</button> --}}
                                                <a href="{{ route('view-activity-template') }}" class="btn btn-primary"
                                                    id="generateForm">Generate
                                                    Form</a>
                                            </div>
                                        </div>
                                        <div class="col-sm-12" id="formSetting">
                                            <div class="mb-3">
                                                <h5 class="mb-0">Form Settings</h5>
                                                <small>Customize your form settings here</small>
                                            </div>
                                            <div
                                                class="mb-3 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                                <button type="button"
                                                    class="btn btn-light-primary btn-sm d-flex align-items-center gap-2"
                                                    data-bs-toggle="modal" data-bs-target="#addAttributeModal"
                                                    title="Add Attribute" id="addActivity">
                                                    <i class="ti ti-plus f-18"></i> <span
                                                        class="d-none d-sm-inline me-2">Add Attribute</span>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-light-danger btn-sm d-flex align-items-center gap-2"
                                                    data-bs-toggle="modal" data-bs-target="#addActivityModal"
                                                    title="Add Attribute" id="addActivity">
                                                    <i class="ti ti-trash f-18"></i> <span
                                                        class="d-none d-sm-inline me-2">Reset Attribute</span>
                                                </button>
                                            </div>

                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Form Title</label>
                                                <input type="text" name="form_title" id="txt_form_title"
                                                    class="form-control" placeholder="Enter Form Title">
                                            </div>

                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Form Target</label>
                                                <select name="select_form_target" class="form-select" id="select_form_target">
                                                    <option value="" selected>-- Select Target --</option>
                                                    <option value="1">Submission</option>
                                                    <option value="2" selected>Evaluation</option>
                                                    <option value="3" selected>Nomination</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Form Status</label>
                                                <select name="select_form_status" class="form-select" id="select_form_status">
                                                    <option value="" selected>-- Select Status --</option>
                                                    <option value="1">Active</option>
                                                    <option value="2" selected>Inactive</option>
                                                </select>
                                            </div>

                                            <div class="d-grid mt-4 mb-4">
                                                <button type="button" class="btn btn-primary" id="generateForm">Save Changes</button>
                                            </div>
                                        </div>

                                        <div class="col-sm-12" id="formOption">
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

                <!-- [ Add Attribute Modal ] start -->
                <form id="addActivityForm">
                    @csrf
                    <div class="modal fade" id="addAttributeModal" tabindex="-1" aria-labelledby="addAttributeModal"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addModalLabel">Add Attribute</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Label</label>
                                                <input type="text" name="row_label" id="txt_label"
                                                    class="form-control" placeholder="Enter Attribute Label">
                                            </div>
                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Attribute</label>
                                                <select name="row_datakey" class="form-select" id="select_datakey">
                                                    <option value="" selected>-- Select Attribute --</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Order</label>
                                                <input type="number" name="row_order" id="txt_order"
                                                    class="form-control" value="0" min="0" max="100">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Font Style</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="fontStyle"
                                                            id="fontNormal" value="normal" checked>
                                                        <label class="form-check-label" for="fontNormal">Normal</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="fontStyle"
                                                            id="fontBold" value="bold">
                                                        <label class="form-check-label" for="fontBold">Bold</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Header Label</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="isHeader"
                                                            id="headerTrue" value="true">
                                                        <label class="form-check-label" for="headerTrue">True</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="isHeader"
                                                            id="headerFalse" value="false" checked>
                                                        <label class="form-check-label" for="headerFalse">False</label>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-end">
                                    <div class="flex-grow-1 text-end">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="button" class="btn btn-light btn-pc-default w-100"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary w-100">
                                                    Add Attribute
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Add Attribute Modal ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {


        });
    </script>
@endsection
