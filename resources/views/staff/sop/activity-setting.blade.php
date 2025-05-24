@extends('staff.layouts.main')

@section('content')
    <!--[ Page Specific Style ] start -->
    <style>
        /* Pastikan teks aktiviti tidak overflow */
        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 90%;
        }

        /* Pastikan Edit & Delete butang lebih kecil & kemas */
        .edit-act,
        .delete-act {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Tukar ikon panah bila accordion dibuka */
        .accordion-button:not(.collapsed) .toggle-icon {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }

        @media (max-width: 768px) {

            .accordion-button {
                flex-wrap: wrap;
            }

            .text-truncate {
                max-width: 80%;
            }

            .list-group-item {
                flex-direction: column;
                align-items: flex-start !important;
                text-align: left;
            }

            .list-group-item>div {
                width: 100%;
            }

            .list-group-item .btn {
                width: 100%;
                margin-top: 5px;
            }
        }
    </style>
    <!--[ Page Specific Style ] end -->

    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript: void(0)">SOP</a></li>
                                <li class="breadcrumb-item" aria-current="page">Activity Setting</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Activity Setting</h2>
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
            
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                <div id="toastContainer"></div>
            </div>
            <!-- [ Alert ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- [ Activity Setting ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- [ Option Section ] start -->
                            <div class="mb-3 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#addActivityModal" title="Add Activity"
                                    id="addActivity">
                                    <i class="ti ti-plus f-18"></i> <span class="d-none d-sm-inline me-2">Add
                                        Activity</span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <!-- [ Notes ] start -->
                            <div class=" mb-3 alert alert-info d-flex align-items-center gap-2 p-3">
                                <i class="ti ti-info-circle f-18"></i>
                                <span><strong>Note : </strong>You can add a document for each activity by click the (+)
                                    button.</span>
                            </div>
                            <!-- [ Notes ] end -->

                            <!-- Dynamically load accordion -->
                            <div class="accordion accordion-flush" id="accordionFlushExample"></div>
                        </div>
                    </div>
                </div>

                <!-- [ Add Activity Modal ] start -->
                <form id="addActivityForm">
                    @csrf
                    <div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModal"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addModalLabel">Add Activity</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="act_name" class="form-label">Activity Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('act_name') is-invalid @enderror"
                                                    id="act_name" name="act_name" placeholder="Enter Activity Name"
                                                    value="{{ old('act_name') }}" required>
                                                @error('act_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
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
                                                    Add Activity
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Add Activity Modal ] end -->

                <!-- [ Update Activity Modal ] start -->
                <form id="updateActivityForm">
                    @csrf
                    <div class="modal fade" id="updateActModal" tabindex="-1" aria-labelledby="updateActModals"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateActivityLabel">Update Activity</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <input type="hidden" class="form-control" id="activity_id_up"
                                                name="id">
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="act_name_up" class="form-label">Activity Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('act_name_up') is-invalid @enderror"
                                                    id="act_name_up" name="act_name_up" placeholder="Enter Activity Name"
                                                    required>
                                                @error('act_name_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
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
                                                    Save Changes
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Update Activity Modal ] end -->

                <!-- [ Add Document Modal ] start -->
                <form id="addDocumentForm">
                    @csrf
                    <div class="modal fade" id="addDocModal" tabindex="-1" aria-labelledby="addDocModal"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addModalLabel">Add Document</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <!--Hidden Ids-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <input type="hidden" id="act_id" name="act_id">
                                        </div>
                                        <!--Document Name-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="doc_name" class="form-label">Document Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('doc_name') is-invalid @enderror"
                                                    id="doc_name" name="doc_name" placeholder="Enter Document Name"
                                                    required>
                                                @error('doc_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Document is Required-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="isRequired" class="form-label">Required <span
                                                        class="text-danger">*</span></label>
                                                <select name="isRequired" id="isRequired"
                                                    class="form-select @error('isRequired') is-invalid @enderror" required>
                                                    <option value="">- Select Option -</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                                @error('isRequired')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Document is ShowForm-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="isShowDoc" class="form-label">Appear in Form <span
                                                        class="text-danger">*</span></label>
                                                <select name="isShowDoc" id="isShowDoc"
                                                    class="form-select @error('isShowDoc') is-invalid @enderror" required>
                                                    <option value="">- Select Option -</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                                @error('isShowDoc')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Document Status-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="doc_status" class="form-label">Status <span
                                                        class="text-danger">*</span></label>
                                                <select name="doc_status" id="doc_status"
                                                    class="form-select @error('doc_status') is-invalid @enderror" required>
                                                    <option value="">- Select Status -</option>
                                                    <option value="1">Active</option>
                                                    <option value="2">Inactive</option>
                                                </select>
                                                @error('doc_status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
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
                                                <button type="submit" class="btn btn-primary w-100" id="addDocBtn">
                                                    Add Document
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Add Document Modal ] end -->

                <!-- [ Update Document Modal ] start -->
                <form id="updateDocumentForm">
                    @csrf
                    <div class="modal fade" id="updateDocModal" tabindex="-1" aria-labelledby="updateDocModal"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateModalLabel">Update Document</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <!--Hidden Ids-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <input type="hidden" id="doc_id_up" name="doc_id_up">
                                        </div>
                                        <!--Document Name-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="doc_name_up" class="form-label">Document Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('doc_name_up') is-invalid @enderror"
                                                    id="doc_name_up" name="doc_name_up" placeholder="Enter Document Name"
                                                    required>
                                                @error('doc_name_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Document is Required-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="isRequired_up" class="form-label">Required <span
                                                        class="text-danger">*</span></label>
                                                <select name="isRequired_up" id="isRequired_up"
                                                    class="form-select @error('isRequired_up') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Option -</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                                @error('isRequired_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Document is ShowForm-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="isShowDoc_up" class="form-label">Appear in Form <span
                                                        class="text-danger">*</span></label>
                                                <select name="isShowDoc_up" id="isShowDoc_up"
                                                    class="form-select @error('isShowDoc_up') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Option -</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                                @error('isShowDoc_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Document Status-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="doc_status_up" class="form-label">Status <span
                                                        class="text-danger">*</span></label>
                                                <select name="doc_status_up" id="doc_status_up"
                                                    class="form-select @error('doc_status_up') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Status -</option>
                                                    <option value="1">Active</option>
                                                    <option value="2">Inactive</option>
                                                </select>
                                                @error('doc_status_up')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
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
                                                <button type="submit" class="btn btn-primary w-100" id="updateDocBtn">
                                                    Save Changes
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Update Document Modal ] end -->

                <!-- [ Delete Modal ] start -->
                <div class="modal fade" id="deleteDocModal" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
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
                                        <div class="d-flex justify-content-center align-items-center">
                                            <h2>Are you sure ?</h2>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <p class="fw-normal f-18 text-center">This action cannot be undone.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="d-flex justify-content-between gap-3 align-items-center">
                                            <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <a class="btn btn-danger w-100">Delete Anyways</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Delete Modal ] end -->

                <!-- [ Activity Setting ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            /*********************************************************
             *******************GLOBAL FUNCTION***********************
             *********************************************************/

            function showAlert(type, message) {
                let alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                         <div class="d-flex justify-content-between align-items-center">
                            <h5 class="alert-heading">
                                <strong>${type === "success" ? " <i class='fas fa-check-circle'></i> Success" : "<i class='fas fa-info-circle'></i> Error"}</strong>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <p class="mb-0">${message}</p>
                    </div>`;
                $("#alert-container").html(alertHtml);
            }

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

            function displayValidationErrors(form, errors) {
                clearValidationErrors(form);
                $.each(errors, function(field, messages) {
                    let input = form.find(`[name='${field}']`);
                    input.addClass("is-invalid");
                    input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                });
            }

            function clearValidationErrors(form) {
                form.find(".is-invalid").removeClass("is-invalid");
                form.find(".invalid-feedback").remove();
            }

            function handleDocumentFormSubmit(form, url, modalId) {
                let formData = new FormData(form[0]);

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast("success", response.message);
                            getDocumentList(response.document['activity_id']);
                            form[0].reset();
                            $(modalId).modal("hide");
                            clearValidationErrors(form);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            displayValidationErrors(form, xhr.responseJSON.errors);
                        } else {
                            showToast("danger", "An error occurred. Please try again.");
                        }
                    }
                });
            }

            function handleActivityFormSubmit(form, url, modalId) {
                let formData = new FormData(form[0]);

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast("success", response.message);
                            getActivityList();
                            $(modalId).modal("hide");
                            form[0].reset();
                            clearValidationErrors(form);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            displayValidationErrors(form, xhr.responseJSON.errors);
                        } else {
                            showToast("danger", "An error occurred. Please try again.");
                        }
                    }
                });
            }

            /*********************************************************
             *******************CRUD ACTIVITY FUNCTION****************
             *********************************************************/

            // READ : VIEW ACTIVITY LIST
            function getActivityList() {
                $.ajax({
                    url: "{{ route('view-activity-get') }}",
                    type: "GET",
                    success: function(activities) {
                        let accordionHtml = "";

                        activities.forEach((act) => {
                            let activityHtml = `
                                <div class="accordion-item border rounded-2 mb-2">
                                    <div class="d-flex align-items-center justify-content-between p-3">
                                        <!-- Accordion Header -->
                                        <h2 class="accordion-header flex-grow-1 me-0 me-md-2">
                                            <button
                                                class="accordion-button collapsed p-3 w-100 d-flex align-items-center justify-content-between"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#flush-collapse-${act.id}" aria-expanded="false"
                                                aria-controls="flush-collapse-${act.id}" data-activity-id="${act.id}">
                                                <span class="fw-bold text-truncate">${act.act_name}</span>
                                            </button>
                                        </h2>

                                        <!-- Buttons (Desktop) -->
                                        <div class="d-none d-md-flex align-items-center gap-2">
                                            <a class="btn btn-secondary btn-sm edit-act" data-id="${act.id}"
                                                data-act_name="${act.act_name}" data-bs-toggle="modal"
                                                data-bs-target="#updateActModal">
                                                <i class="ti ti-edit text-white"></i>
                                            </a>
                                            <a class="btn btn-danger btn-sm delete-act ${act.documents_count > 0 ? 'disabled-a' : ''}" data-id="${act.id}">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            <a class="btn btn-warning btn-sm edit-act" data-act-id="${act.id}"
                                                data-bs-toggle="modal" data-bs-target="#addDocModal">
                                                <i class="ti ti-plus text-white"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Buttons (Mobile) - Full Width -->
                                    <div class="d-md-none d-flex gap-2 p-3">
                                        <a class="btn btn-secondary w-100 edit-act" data-id="${act.id}"
                                            data-act_name="${act.act_name}" data-bs-toggle="modal"
                                            data-bs-target="#updateActModal">
                                            <i class="ti ti-edit text-white"></i>
                                        </a>
                                        <a class="btn btn-danger w-100 delete-act" data-id="${act.id}">
                                            <i class="ti ti-trash text-white"></i>
                                        </a>
                                        <a class="btn btn-warning w-100 edit-act" data-act-id="${act.id}"
                                            data-bs-toggle="modal" data-bs-target="#addDocModal">
                                            <i class="ti ti-plus text-white"></i>
                                        </a>
                                    </div>

                                    <div id="flush-collapse-${act.id}" class="accordion-collapse collapse"
                                        aria-labelledby="flush-heading-${act.id}" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body bg-light">
                                            <!-- Dynamic: Document List -->
                                            <ul class="list-group mb-3" id="document-list-${act.id}"></ul>
                                        </div>
                                    </div>
                                </div>
                            `;
                            accordionHtml += activityHtml;
                        });

                        $("#accordionFlushExample").html(accordionHtml);
                    },
                    error: function(xhr) {
                        showToast("danger", "Error fetching activities:" + xhr.responseText);
                    }
                });
            }

            // TOGGLER : FUNCTION DECLARATION
            getActivityList();

            // CREATE : ADD ACTIVITY
            $("#addActivityForm").submit(function(e) {
                e.preventDefault();
                handleActivityFormSubmit($(this), "{{ route('add-activity-post') }}", "#addActivityModal");
            });

            // UPDATE : UPDATE ACTIVITY
            $('#updateActModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                $('#activity_id_up').val(button.data('id'));
                $('#act_name_up').val(button.data('act_name'));
            });

            // TOGGLER : UPDATE FORM
            $("#updateActivityForm").submit(function(e) {
                e.preventDefault();
                handleActivityFormSubmit($(this), "{{ route('update-activity-post') }}",
                    "#updateActModal");
            });

            //DELETE : DELETE ACTIVITY
            $(document).on("click", ".delete-act", function() {
                let actId = $(this).data("id");

                if (confirm("Are you sure you want to delete this activity?")) {
                    $.ajax({
                        url: "/staff/delete-activity-" + actId,
                        type: "GET",
                        success: function(response) {
                            if (response.success) {
                                getActivityList();
                                showToast("success", response.message);
                            } else {
                                showToast("success", response.message);
                            }
                        },
                        error: function() {
                            showToast("danger",
                                "This activity cannot be deleted because it is already linked to a procedure or there are documents associated with it."
                            );
                        },
                    });
                }
            });

            /*********************************************************
             *******************CRUD DOCUMENT FUNCTION****************
             *********************************************************/
            // READ : VIEW DOCUMENT LIST
            function getDocumentList(activityId) {
                let documentList = $("#document-list-" + activityId);
                $.ajax({
                    url: "/staff/view-document-by-activity-" + activityId, // API dari Laravel
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        documentList.html(
                            '<li class="list-group-item text-center fade">Loading...</li>'
                        );
                    },
                    success: function(response) {
                        if (response.success) {
                            documentList.empty().addClass("loaded");
                            if (response.data.length > 0) {
                                $.each(response.data, function(index, doc) {
                                    let docStatus = doc.doc_status == 1 ? "Active" : doc
                                        .doc_status == 2 ? "Inactive" : "N/A";
                                    let isShowDoc = doc.isShowDoc == 1 ? "Yes" : doc
                                        .isShowDoc == 0 ? "No" : "N/A";
                                    let isRequired = doc.isRequired == 1 ? "Yes" : doc
                                        .isRequired == 0 ? "No" : "N/A";

                                    let docHtml = `
                                    <li class="list-group-item d-flex flex-wrap align-items-center">
                                        <div class="flex-grow-1">
                                            <strong>${doc.doc_name}</strong>
                                            <small class="text-muted">(${docStatus})</small>
                                            <br>
                                            <small>Show: ${isShowDoc} | Required: ${isRequired}</small>
                                        </div>
                                        <div class="d-flex gap-2 mt-2 mt-md-0">
                                            <a class="btn avtar avtar-xs btn-light-primary edit-doc"
                                                data-id="${doc.id}"
                                                data-docname="${doc.doc_name}"
                                                data-isshowdoc="${doc.isShowDoc}"
                                                data-isrequired="${doc.isRequired}"
                                                data-doc_status="${doc.doc_status}"
                                                data-bs-toggle="modal" data-bs-target="#updateDocModal">
                                                <i class="ti ti-edit f-20"></i>
                                            </a>
                                            <button class="btn btn-light-danger avtar avtar-xs delete-doc"
                                                data-id="${doc.id}"  data-activity_id="${doc.activity_id}">
                                                <i class="ti ti-trash f-20"></i>
                                            </button>
                                        </div>
                                    </li>
                                `;
                                    documentList.append(docHtml);
                                });
                            } else {
                                documentList.html(
                                    '<li class="list-group-item text-center">No documents found.</li>'
                                );
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        alert(error);
                        documentList.html(
                            '<li class="list-group-item text-center text-danger">Error loading documents.</li>'
                        );
                    }
                });
            }

            // TOGGLER : TOGGLE ACCORDION
            $(document).on("click", ".accordion-button", function() {
                let activityId = $(this).data("activity-id");
                let documentList = $("#document-list-" + activityId);
                if (!documentList.hasClass("loaded")) {
                    getDocumentList(activityId);
                }
            });

            // CREATE : ADD DOCUMENT
            $('#addDocModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                $('#act_id').val(button.data('act-id'));
            });

            // TOGGLER : ADD FORM
            $("#addDocumentForm").submit(function(e) {
                e.preventDefault();
                handleDocumentFormSubmit($(this), "{{ route('add-document-post') }}", "#addDocModal");
            });

            // UPDATE : UPDATE DOCUMENT
            $('#updateDocModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                $('#doc_id_up').val(button.data('id'));
                $('#doc_name_up').val(button.data('docname'));
                $('#isShowDoc_up').val(button.data('isshowdoc'));
                $('#isRequired_up').val(button.data('isrequired'));
                $('#doc_status_up').val(button.data('doc_status'));

            });

            // TOGGLER : UPDATE FORM
            $("#updateDocumentForm").submit(function(e) {
                e.preventDefault();
                handleDocumentFormSubmit($(this), "{{ route('update-document-post') }}",
                    "#updateDocModal");
            });

            //DELETE : DELETE DOCUMENT
            $(document).on("click", ".delete-doc", function() {
                let docId = $(this).data("id");
                let actId = $(this).data("activity_id");


                if (confirm("Are you sure you want to delete this document?")) {
                    $.ajax({
                        url: "/staff/delete-document-" + docId, // API Delete dari Laravel
                        type: "GET",
                        success: function(response) {
                            if (response.success) {
                                showToast("success", response.message);
                                // $("#document-list-" + actId).find(
                                //     `[data-id='${docId}']`).closest("li").remove();
                                getDocumentList(actId);
                            } else {
                                showToast("success", response.message);
                            }
                        },
                        error: function() {
                            showToast("danger", "An error occurred. Please try again.");
                        },
                    });
                }
            });

        });
    </script>
@endsection
