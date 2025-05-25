@extends('staff.layouts.main')

@section('content')
    <style>
        .imagePreview img {
            border: 2px dashed #dee2e6;
            padding: 10px;
            border-radius: 10px;
            max-height: 300px;
            max-width: 100%;
            background: #f8f9fa;
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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Setting</a></li>
                                <li class="breadcrumb-item" aria-current="page">Faculty Setting</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Faculty Setting</h2>
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

                <!-- [ Faculty Setting ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- [ Option Section ] start -->
                            <div class="mb-4 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    title="Add Faculty" id="addModalBtn" data-bs-toggle="modal" data-bs-target="#addModal">
                                    <i class="ti ti-plus f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Add Faculty
                                    </span>
                                </button>

                                <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2"
                                    title="Set Default Faculty" id="setdefaultModalBtn" data-bs-toggle="modal"
                                    data-bs-target="#setdefaultModal">
                                    <i class="ti ti-edit-circle f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Set Default Faculty
                                    </span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Faculty Code</th>
                                            <th scope="col">Faculty Name</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- [ Add Modal ] start -->
                <form action="{{ route('add-faculty-post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header bg-light">
                                    <h5 class="modal-title" id="addModalLabel">Add Faculty</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row">

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="fac_name" class="form-label">Faculty Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('fac_name') is-invalid @enderror"
                                                    id="fac_name" name="fac_name" placeholder="Enter Faculty Name"
                                                    value="{{ old('fac_name') }}" required>
                                                @error('fac_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="fac_code" class="form-label">Faculty Code <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('fac_code') is-invalid @enderror"
                                                    id="fac_code" name="fac_code" placeholder="Enter Faculty Code"
                                                    value="{{ old('fac_code') }}" required>
                                                @error('fac_code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="fac_logo" class="form-label">Faculty Logo </label>
                                                <input type="file"
                                                    class="form-control @error('fac_logo') is-invalid @enderror"
                                                    id="fac_logo" name="fac_logo"
                                                    accept="image/png, image/jpeg, image/jpg"
                                                    onchange="previewImageAdd(event)">
                                                @error('fac_logo')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div id="imagePreviewAdd" class="mt-4 mb-4 text-center">
                                                <small class="text-muted d-block mb-2">Image preview will appear
                                                    here</small>
                                            </div>
                                        </div>


                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="fac_status" class="form-label">Status <span
                                                        class="text-danger">*</span></label>
                                                <select name="fac_status" id="fac_status"
                                                    class="form-select  @error('fac_status') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Status -</option>
                                                    <option value="1"
                                                        @if (old('fac_status') == 1) selected @endif)>Active</option>
                                                    <option value="2"
                                                        @if (old('fac_status') == 2) selected @endif>Inactive</option>
                                                </select>
                                                @error('fac_status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="modal-footer bg-light justify-content-end">
                                    <div class="flex-grow-1 text-end">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="button"
                                                    class="btn btn-outline-secondary btn-pc-default w-100"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary w-100"
                                                    id="addApplicationBtn">
                                                    Add Faculty
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Add Modal ] end -->

                <!-- [ Set Default Faculty ] start -->
                <form action="{{ route('set-default-faculty-post') }}" method="POST">
                    @csrf
                    <div class="modal fade" id="setdefaultModal" tabindex="-1" aria-labelledby="setdefaultModal"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header  bg-light">
                                    <h5 class="modal-title" id="addModalLabel">Set Default Faculty</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row">

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="fac_name" class="form-label">Current Default Faculty</label>
                                                <input type="text" class="form-control" placeholder="Default Faculty"
                                                    value="{{ $facs->where('fac_status', 3)->first() ? '[' . $facs->where('fac_status', 3)->first()->fac_code . '] - ' . $facs->where('fac_status', 3)->first()->fac_name : '-' }}"
                                                    readonly>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="faculty_id" class="form-label">Default Faculty <span
                                                        class="text-danger">*</span></label>
                                                <select name="faculty_id" id="faculty_id"
                                                    class="form-select  @error('faculty_id') is-invalid @enderror">
                                                    <option value="">- Select Faculty -</option>
                                                    @foreach ($facs->where('fac_status', 1) as $faculty)
                                                        <option value="{{ $faculty->id }}">
                                                            [{{ $faculty->fac_code }}] - {{ $faculty->fac_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('faculty_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer bg-light justify-content-end">
                                    <div class="flex-grow-1 text-end">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="button"
                                                    class="btn btn-outline-secondary btn-pc-default w-100"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary w-100"
                                                    id="addApplicationBtn">
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
                <!-- [ Set Default Faculty ] end -->

                @foreach ($facs as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-faculty-post', Crypt::encrypt($upd->id)) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal fade" id="updateModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title" id="updateModalLabel">Update Faculty</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="fac_name_up" class="form-label">Faculty Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control @error('fac_name_up') is-invalid @enderror"
                                                        id="fac_name_up" name="fac_name_up"
                                                        placeholder="Enter Faculty Name" value="{{ $upd->fac_name }}"
                                                        required>
                                                    @error('fac_name_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="fac_code_up" class="form-label">Faculty Code <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control @error('fac_code_up') is-invalid @enderror"
                                                        id="fac_code_up" name="fac_code_up"
                                                        placeholder="Enter Faculty Code" value="{{ $upd->fac_code }}"
                                                        required>
                                                    @error('fac_code_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="fac_logo_up" class="form-label">Faculty Logo</label>
                                                    <input type="file"
                                                        class="form-control @error('fac_logo_up') is-invalid @enderror"
                                                        id="fac_logo_up_{{ $upd->id }}" name="fac_logo_up"
                                                        accept="image/png, image/jpeg, image/jpg"
                                                        onchange="previewImageUpdate(event, {{ $upd->id }})">
                                                </div>
                                                <div id="imagePreviewUpdate_{{ $upd->id }}"
                                                    class="mt-4 mb-4 text-center">
                                                    @if ($upd->fac_logo)
                                                        <img src="{{ asset('storage/' . $upd->fac_logo) }}"
                                                            alt="Current Faculty Logo" class="img-fluid"
                                                            style="max-height: 300px; border: 2px dashed #dee2e6; padding: 10px; border-radius: 10px; background: #f8f9fa;">
                                                    @else
                                                        <small class="text-muted d-block mb-2">Image preview will
                                                            appear here</small>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="fac_status_up" class="form-label">Status <span
                                                            class="text-danger">*</span></label>
                                                    <select name="fac_status_up" id="fac_status"
                                                        class="form-select  @error('fac_status_up') is-invalid @enderror"
                                                        required>
                                                        <option value=""
                                                            @if ($upd->fac_status == 0) selected @else disabled @endif>
                                                            - Select Status -</option>
                                                        <option value="1"
                                                            @if ($upd->fac_status == 1) selected @endif>Active
                                                        </option>
                                                        <option value="2"
                                                            @if ($upd->fac_status == 2) selected @endif>Inactive
                                                        </option>
                                                        @if ($upd->fac_status == 3)
                                                            <option value="3"
                                                                @if ($upd->fac_status == 3) selected @endif>Default
                                                            </option>
                                                        @endif
                                                    </select>
                                                    @error('fac_status_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer bg-light justify-content-end">
                                        <div class="flex-grow-1 text-end">
                                            <div class="col-sm-12">
                                                <div class="d-flex justify-content-between gap-3 align-items-center">
                                                    <button type="button"
                                                        class="btn btn-outline-secondary btn-pc-default w-100"
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
                    <div class="modal fade" id="deleteModal-{{ $upd->id }}" data-bs-backdrop="static"
                        data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $upd->id }}"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-body p-4">
                                    <div class="text-center mb-3">
                                        <i class="ti ti-trash text-danger" style="font-size: 80px;"></i>
                                    </div>
                                    <h4 class="text-center mb-2" id="deleteModalLabel-{{ $upd->id }}">Are you sure?
                                    </h4>
                                    <p class="text-center text-muted mb-4">This action cannot be undone.</p>

                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="{{ route('delete-faculty-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 1]) }}"
                                            class="btn btn-danger w-100">Delete Anyway</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Delete Modal ] end -->

                    <!-- [ Disable Modal ] start -->
                    <div class="modal fade" id="disableModal-{{ $upd->id }}" data-bs-backdrop="static"
                        data-bs-keyboard="false" tabindex="-1" aria-labelledby="disableModalLabel-{{ $upd->id }}"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-body p-4">
                                    <div class="text-center mb-3">
                                        <i class="ti ti-alert-circle text-warning" style="font-size: 80px;"></i>
                                    </div>
                                    <h4 class="text-center mb-2" id="disableModalLabel-{{ $upd->id }}">Faculty
                                        Inactivation</h4>
                                    <p class="text-center text-muted mb-4">
                                        Oops! You can't delete this faculty.<br>
                                        However, you can inactivate them instead. Would you like to proceed?
                                    </p>

                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="{{ route('delete-faculty-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 2]) }}"
                                            class="btn btn-warning w-100">Inactivate</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Disable Modal ] end -->
                @endforeach



                <!-- [ Faculty Setting ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
    
        document.addEventListener('DOMContentLoaded', function() {
            var modalToShow = "{{ session('modal') }}"; // Ambil modal yang perlu dibuka dari session
            if (modalToShow) {
                var modalElement = document.getElementById(modalToShow);
                if (modalElement) {
                    var modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }
        });

        function previewImageAdd(event) {
            const preview = $('#imagePreviewAdd');
            preview.empty();

            const file = event.target.files[0];
            if (file) {
                const img = $('<img>', {
                    src: URL.createObjectURL(file),
                    class: 'img-fluid',
                    css: {
                        maxHeight: '300px'
                    }
                });
                preview.append(img.hide().fadeIn(500));
            }
        }

        function previewImageUpdate(event, id) {
            const preview = $('#imagePreviewUpdate_' + id);
            preview.empty();

            const file = event.target.files[0];
            if (file) {
                const img = $('<img>', {
                    src: URL.createObjectURL(file),
                    class: 'img-fluid',
                    css: {
                        maxHeight: '300px',
                        border: '2px dashed #dee2e6',
                        padding: '10px',
                        borderRadius: '10px',
                        background: '#f8f9fa'
                    }
                });
                preview.append(img.hide().fadeIn(500));
            }
        }

        $(document).ready(function() {

            // DATATABLE : FACULTY
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('faculty-setting') }}",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        className: "text-start"
                    },
                    {
                        data: 'fac_code',
                        name: 'fac_code'
                    },
                    {
                        data: 'fac_name',
                        name: 'fac_name'
                    },
                    {
                        data: 'fac_status',
                        name: 'fac_status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]

            });

        });
    </script>
@endsection
