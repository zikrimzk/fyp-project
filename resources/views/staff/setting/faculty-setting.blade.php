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
                            <div class="d-grid gap-2 gap-md-3 d-md-flex flex-wrap">
                                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#addModal"><i class="ti ti-plus f-18"></i>
                                    Add Faculty
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
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
                <form action="{{ route('add-faculty-post') }}" method="POST">
                    @csrf
                    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header">
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
                                <div class="modal-footer justify-content-end">
                                    <div class="flex-grow-1 text-end">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="button" class="btn btn-light btn-pc-default w-100"
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

                @foreach ($facs as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-faculty-post', Crypt::encrypt($upd->id)) }}" method="POST">
                        @csrf
                        <div class="modal fade" id="updateModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
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
                                                    <label for="fac_status_up" class="form-label">Status <span
                                                            class="text-danger">*</span></label>
                                                    <select name="fac_status_up" id="fac_status"
                                                        class="form-select  @error('fac_status_up') is-invalid @enderror"
                                                        required>
                                                        <option value="" disabled>- Select Status -</option>
                                                        <option value="1"
                                                            @if ($upd->fac_status == 1) selected @endif)>Active
                                                        </option>
                                                        <option value="2"
                                                            @if ($upd->fac_status == 2) selected @endif>Inactive
                                                        </option>
                                                    </select>
                                                    @error('fac_status_up')
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
                                                <a href="{{ route('delete-faculty-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 1]) }}"
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
                                                <i class="ti ti-warning text-warning" style="font-size: 100px"></i>
                                            </div>

                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <h2>Data Deletion</h2>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 mb-3">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <p class="fw-normal f-18 text-center">Oops! You can't delete this data.
                                                    However, you can disable it. Are you want to disable this data ?</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <a href="{{ route('delete-faculty-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 2]) }}"
                                                    class="btn btn-danger w-100">Disable</a>
                                            </div>
                                        </div>
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

        $(document).ready(function() {

            $(function() {

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

        });
    </script>
@endsection
