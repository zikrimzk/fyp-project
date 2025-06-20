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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Administrator</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Setting</a></li>
                                <li class="breadcrumb-item" aria-current="page">Programme Setting</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Programme Setting</h2>
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

                <!-- [ Programme Setting ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">

                            <!-- [ Option Section ] start -->
                            <div class="mb-4 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    title="Add Programme" id="addModalBtn" data-bs-toggle="modal"
                                    data-bs-target="#addModal">
                                    <i class="ti ti-plus f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Add Programme
                                    </span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Code</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Mode</th>
                                            <th scope="col">Faculty</th>
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
                <form action="{{ route('add-programme-post') }}" method="POST">
                    @csrf
                    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header bg-light ">
                                    <h5 class="modal-title" id="addModalLabel">Add Programme</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row">

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="prog_name" class="form-label">Programme Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('prog_name') is-invalid @enderror"
                                                    id="prog_name" name="prog_name" placeholder="Enter Programme Name"
                                                    value="{{ old('prog_name') }}" required>
                                                @error('prog_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="prog_code" class="form-label">Programme Code <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('prog_code') is-invalid @enderror"
                                                    id="prog_code" name="prog_code" placeholder="Enter Programme Code"
                                                    value="{{ old('prog_code') }}" required>
                                                @error('prog_code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="prog_mode" class="form-label">Programme Mode <span
                                                        class="text-danger">*</span></label>
                                                <select name="prog_mode" id="prog_mode"
                                                    class="form-select 
                                                @error('prog_mode') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Mode -</option>
                                                    <option value="FT"
                                                        @if (old('prog_mode') == 'FT') selected @endif>
                                                        Full Time
                                                    </option>
                                                    <option value="PT"
                                                        @if (old('prog_mode') == 'PT') selected @endif>
                                                        Part Time
                                                    </option>
                                                </select>
                                                @error('prog_mode')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="fac_id" class="form-label">Faculty <span
                                                        class="text-danger">*</span></label>
                                                <select name="fac_id" id="fac_id"
                                                    class="form-select 
                                                @error('fac_id') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Faculty -</option>
                                                    @foreach ($facs->whereIn('fac_status', [1,3]) as $fac)
                                                        <option value="{{ $fac->id }}"
                                                            @if ($fac->id == old('fac_id')) selected @endif>
                                                            ({{ $fac->fac_code }})
                                                            - {{ $fac->fac_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('fac_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="prog_status" class="form-label">Status <span
                                                        class="text-danger">*</span></label>
                                                <select name="prog_status" id="prog_status"
                                                    class="form-select  @error('prog_status') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Status -</option>
                                                    <option value="1"
                                                        @if (old('prog_status') == 1) selected @endif)>Active</option>
                                                    <option value="2"
                                                        @if (old('prog_status') == 2) selected @endif>Inactive</option>
                                                </select>
                                                @error('prog_status')
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
                                                    Add Programme
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

                @foreach ($progs as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-programme-post', Crypt::encrypt($upd->id)) }}" method="POST">
                        @csrf
                        <div class="modal fade" id="updateModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title" id="updateModalLabel">Update Programme</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="prog_name_up" class="form-label">Programme Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control @error('prog_name_up') is-invalid @enderror"
                                                        id="prog_name_up" name="prog_name_up"
                                                        placeholder="Enter Programme Name" value="{{ $upd->prog_name }}"
                                                        required>
                                                    @error('prog_name_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="prog_code_up" class="form-label">Programme Code <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control @error('prog_code_up') is-invalid @enderror"
                                                        id="prog_code_up" name="prog_code_up"
                                                        placeholder="Enter Programme Code" value="{{ $upd->prog_code }}"
                                                        required>
                                                    @error('prog_code_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="prog_mode_up" class="form-label">Programme Mode <span
                                                            class="text-danger">*</span></label>
                                                    <select name="prog_mode_up" id="prog_mode"
                                                        class="form-select 
                                                    @error('prog_mode_up') is-invalid @enderror"
                                                        required>
                                                        <option value="" disabled>- Select Mode -</option>
                                                        <option value="FT"
                                                            @if ($upd->prog_mode == 'FT') selected @endif>
                                                            Full Time
                                                        </option>
                                                        <option value="PT"
                                                            @if ($upd->prog_mode == 'PT') selected @endif>
                                                            Part Time
                                                        </option>
                                                    </select>
                                                    @error('prog_mode_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="fac_id_up" class="form-label">Faculty <span
                                                            class="text-danger">*</span></label>
                                                    <select name="fac_id_up" id="fac_id_up"
                                                        class="form-select 
                                                    @error('fac_id_up') is-invalid @enderror"
                                                        required>
                                                        <option value="" disabled>- Select Faculty -</option>
                                                        @foreach ($facs as $fac)
                                                            @if ($fac->id == $upd->fac_id)
                                                                <option value="{{ $fac->id }}" selected>
                                                                    ({{ $fac->fac_code }})
                                                                    - {{ $fac->fac_name }}
                                                                    @if ($fac->fac_status == 2)
                                                                        [Inactive]
                                                                    @endif
                                                                </option>
                                                            @endif
                                                        @endforeach

                                                        @foreach ($facs->where('fac_status', 1) as $fac)
                                                            @if ($fac->id != $upd->fac_id)
                                                                <option value="{{ $fac->id }}">
                                                                    ({{ $fac->fac_code }})
                                                                    - {{ $fac->fac_name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @error('fac_id_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="prog_status_up" class="form-label">Status <span
                                                            class="text-danger">*</span></label>
                                                    <select name="prog_status_up" id="prog_status"
                                                        class="form-select  @error('prog_status_up') is-invalid @enderror"
                                                        required>
                                                        <option value=""
                                                            @if ($upd->prog_status == 0) selected @else disabled @endif>
                                                            - Select Status -</option>
                                                        <option value="1"
                                                            @if ($upd->prog_status == 1) selected @endif)>Active
                                                        </option>
                                                        <option value="2"
                                                            @if ($upd->prog_status == 2) selected @endif>Inactive
                                                        </option>
                                                    </select>
                                                    @error('prog_status_up')
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
                                        <a href="{{ route('delete-programme-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 1]) }}"
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
                                    <h4 class="text-center mb-2" id="disableModalLabel-{{ $upd->id }}">Programme
                                        Inactivation</h4>
                                    <p class="text-center text-muted mb-4">
                                        Oops! You can't delete this programme.<br>
                                        However, you can inactivate them instead. Would you like to proceed?
                                    </p>

                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="{{ route('delete-programme-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 2]) }}"
                                            class="btn btn-warning w-100">Inactivate</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Disable Modal ] end -->
                @endforeach

                <!-- [ Programme Setting ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script type="text/javascript">

        document.addEventListener('DOMContentLoaded', function() {
            var modalToShow = "{{ session('modal') }}";
            if (modalToShow) {
                var modalElement = document.getElementById(modalToShow);
                if (modalElement) {
                    var modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }
        });

        $(document).ready(function() {

            // DATATABLE : PROGRAMME
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('programme-setting') }}",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        className: "text-start"
                    },
                    {
                        data: 'prog_code',
                        name: 'prog_code'
                    },
                    {
                        data: 'prog_name',
                        name: 'prog_name'
                    },
                    {
                        data: 'prog_mode',
                        name: 'prog_mode'
                    },
                    {
                        data: 'fac_code',
                        name: 'fac_code'
                    },
                    {
                        data: 'prog_status',
                        name: 'prog_status'
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
