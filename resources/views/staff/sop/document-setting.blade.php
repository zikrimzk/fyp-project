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
            <!-- [ Alert ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- [ Document Setting ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2 gap-md-3 d-md-flex flex-wrap">
                                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#addActivityModal"><i
                                        class="ti ti-plus f-18"></i>
                                    Add Activity
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="alert alert-info d-flex align-items-center gap-2 p-3">
                        <i class="ti ti-info-circle f-18"></i>
                        <span><strong>Note : </strong>You can add a document for each activity by click the (+)
                            button.</span>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="accordion accordion-flush" id="accordionFlushExample">
                                {{-- @foreach ($acts as $act)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="flush-heading-{{ $act->id }}">
                                            <button class="accordion-button collapsed p-4" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#flush-collapse-{{ $act->id }}" aria-expanded="false"
                                                aria-controls="flush-collapse-{{ $act->id }}"
                                                data-activity-id="{{ $act->id }}">
                                                <span class="fw-bold">{{ $act->act_name }}</span>
                                            </button>
                                        </h2>
                                        <div id="flush-collapse-{{ $act->id }}" class="accordion-collapse collapse"
                                            aria-labelledby="flush-heading-{{ $act->id }}"
                                            data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body">
                                                <!-- Dynamic : Document List -->
                                                <ul class="list-group mb-3" id="document-list-{{ $act->id }}"></ul>

                                                <!-- Button : Add Document -->
                                                <div class="d-grid gap-2 gap-md-3 d-md-flex flex-wrap">
                                                    <button type="button"
                                                        class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2"
                                                        data-bs-toggle="modal" data-bs-target="#addDocModal"
                                                        data-act-id="{{ $act->id }}">
                                                        <i class="ti ti-plus f-18"></i>
                                                        Add Document
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach --}}





                            </div>

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
                                            <input type="text" class="form-control" id="activity_id_up"
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

                {{-- @foreach ($acts as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-document-post', Crypt::encrypt($upd->id)) }}" method="POST">
                        @csrf
                        <div class="modal fade" id="updateModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateModalLabel">Update Document</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="act_name_up" class="form-label">Document Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control @error('act_name_up') is-invalid @enderror"
                                                        id="act_name_up" name="act_name_up"
                                                        placeholder="Enter Document Name" value="{{ $upd->act_name }}"
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
                                                    <button type="submit" class="btn btn-primary w-100"
                                                        id="updateApplicationBtn">
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
                    <!-- [ Update Modal ] end -->

                    <!-- [ Delete Modal ] start -->
                    <div class="modal fade" id="deleteModal-{{ $upd->id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true">
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
                                                <a href="{{ route('delete-document-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 1]) }}"
                                                    class="btn btn-danger w-100">Delete Anyways</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Delete Modal ] end -->

                    <!-- [ Disable Modal ] start -->
                    <div class="modal fade" id="disableModal-{{ $upd->id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 mb-4">
                                            <div class="d-flex justify-content-center align-items-center mb-3">
                                                <i class="ti ti-alert-circle text-warning" style="font-size: 100px"></i>
                                            </div>

                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <h2>Data Deletion</h2>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 mb-3">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <p class="fw-normal f-18 text-center">
                                                    Oops! You can't delete this data.
                                                    However, you can disable it instead. Would you like to proceed with
                                                    disabling this data?
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <a href="{{ route('delete-document-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 2]) }}"
                                                    class="btn btn-warning w-100">Disable</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Disable Modal ] end -->
                @endforeach --}}



                <!-- [ Document Setting ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

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
                            showAlert("success", response.message);
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
                            showAlert("danger", "An error occurred. Please try again.");
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
                            showAlert("success", response.message);
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
                            showAlert("danger", "An error occurred. Please try again.");
                        }
                    }
                });
            }

            // READ : VIEW ACTIVITY LIST
            function getActivityList() {
                $.ajax({
                    url: "{{ route('view-activity-get') }}",
                    type: "GET",
                    success: function(activities) {
                        let accordionHtml = "";

                        activities.forEach((act) => {
                            let activityHtml = `
                                <div class="accordion-item">
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
                                            <a class="btn btn-danger btn-sm delete-act" data-id="${act.id}">
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
                        alert("Error fetching activities: " + xhr.responseText);
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

            // READ : VIEW DOCUMENT LIST
            function getDocumentList(activityId) {
                let documentList = $("#document-list-" + activityId);
                $.ajax({
                    url: "/staff/view-document-by-activity-" + activityId, // API dari Laravel
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        documentList.html(
                            '<li class="list-group-item text-center">Loading...</li>'
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
                                showAlert('success', response.message);
                                $("#document-list-" + actId).find(
                                    `[data-id='${docId}']`).closest("li").remove();
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function() {
                            showAlert("danger", "An error occurred. Please try again.");
                        },
                    });
                }
            });


        });
    </script>
@endsection
