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
                                <li class="breadcrumb-item" aria-current="page">Procedure Setting</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Procedure Setting</h2>
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

                <!-- [ Procedure Setting ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- [ Option Section ] start -->
                            <div class="mb-5 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#addModal" title="Add Procedure" id="addStaffBtn">
                                    <i class="ti ti-plus f-18"></i> <span class="d-none d-sm-inline me-2">Add Procedure</span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->
                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Activity</th>
                                            <th scope="col">Programme</th>
                                            <th scope="col">Mode</th>
                                            <th scope="col">Sequence</th>
                                            <th scope="col">Semester</th>
                                            <th scope="col">Week</th>
                                            <th scope="col">Evaluation</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Material</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- [ Add Modal ] start -->
                <form action="{{ route('add-procedure-post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addModalLabel">Add Procedure</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <!--Activity Input-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="activity_id" class="form-label">Activity <span
                                                        class="text-danger">*</span></label>
                                                <select name="activity_id" id="activity_id"
                                                    class="form-select @error('activity_id') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Activity -</option>
                                                    @foreach ($acts as $act)
                                                        @if (old('activity_id') == $act->id)
                                                            <option value="{{ $act->id }}" selected>
                                                                {{ $act->act_name }}
                                                            </option>
                                                        @else
                                                            <option value="{{ $act->id }}">{{ $act->act_name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @error('activity_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Programme Input-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="programme_id" class="form-label">Programme <span
                                                        class="text-danger">*</span></label>
                                                <select name="programme_id" id="programme_id"
                                                    class="form-select @error('programme_id') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Programme -</option>
                                                    @foreach ($progs as $prog)
                                                        @if (old('programme_id') == $prog->id)
                                                            <option value="{{ $prog->id }}" selected>
                                                                {{ $prog->prog_code }} ({{ $prog->prog_mode }})
                                                            </option>
                                                        @else
                                                            <option value="{{ $prog->id }}">
                                                                {{ $prog->prog_code }} ({{ $prog->prog_mode }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @error('programme_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Activity Sequence Input-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="act_seq" class="form-label">Activity Sequence <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="act_seq" id="act_seq"
                                                    class="form-control @error('act_seq') is-invalid @enderror"
                                                    min="1" max="50" value="{{ old('act_seq') ?? 1 }}"
                                                    required>
                                                @error('act_seq')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Semester Timeline Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="timeline_sem" class="form-label">Semester Timeline <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="timeline_sem" id="timeline_sem"
                                                    class="form-control @error('timeline_sem') is-invalid @enderror"
                                                    min="1" max="50" value="{{ old('timeline_sem') ?? 1 }}"
                                                    required>
                                                @error('timeline_sem')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Week Timeline Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="timeline_week" class="form-label">Week Timeline <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="timeline_week" id="timeline_week"
                                                    class="form-control @error('timeline_week') is-invalid @enderror"
                                                    min="1" max="100"
                                                    value="{{ old('timeline_week') ?? 1 }}" required>
                                                @error('timeline_week')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Activity Initial Status Input-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="init_status" class="form-label">Initial Status <span
                                                        class="text-danger">*</span></label>
                                                <select name="init_status" id="init_status"
                                                    class="form-select @error('init_status') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Status -</option>
                                                    <option value="1"
                                                        @if (old('init_status') == 1) selected @endif>Open Always
                                                    </option>
                                                    <option value="2"
                                                        @if (old('init_status') == 2) selected @endif>Locked
                                                    </option>
                                                </select>
                                                @error('init_status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Evaluation Input-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="is_haveEva" class="form-label">Evaluation <span
                                                        class="text-danger">*</span></label>
                                                <select name="is_haveEva" id="is_haveEva"
                                                    class="form-select @error('is_haveEva') is-invalid @enderror" required>
                                                    @if (old('is_haveEva') == 1)
                                                        <option value="1" selected>Yes</option>
                                                        <option value="0">No</option>
                                                    @elseif(old('is_haveEva') == 2)
                                                        <option value="1">Yes</option>
                                                        <option value="0"selected>No</option>
                                                    @else
                                                        <option value="" selected>- Select Option -</option>
                                                        <option value="1">Yes</option>
                                                        <option value="0">No</option>
                                                    @endif
                                                </select>
                                                @error('is_haveEva')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Activity Material Input-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="material" class="form-label">Activity Material </label>
                                                <input type="file" name="material" id="material"
                                                    class="form-control @error('material') is-invalid @enderror">
                                                @error('material')
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
                                                    Add Procedure
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

                @foreach ($pros as $upd)
                    <!-- [ Update Modal ] start -->
                    <form
                        action="{{ route('update-procedure-post', ['actID' => Crypt::encrypt($upd->activity_id), 'progID' => Crypt::encrypt($upd->programme_id)]) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal fade" id="updateModal-{{ $upd->activity_id }}-{{ $upd->programme_id }}"
                            tabindex="-1" aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateModalLabel">Update Procedure</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">
                                            <!--Activity Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="activity_id_up" class="form-label">Activity <span
                                                            class="text-danger">*</span></label>
                                                    <select name="activity_id_up" id="activity_id_up"
                                                        class="form-select @error('activity_id_up') is-invalid @enderror"
                                                        required>
                                                        <option value="" disabled>- Select Activity -</option>
                                                        @foreach ($acts as $act)
                                                            @if ($upd->activity_id == $act->id)
                                                                <option value="{{ $act->id }}" selected>
                                                                    {{ $act->act_name }}
                                                                </option>
                                                            @else
                                                                <option value="{{ $act->id }}" disabled>
                                                                    {{ $act->act_name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @error('activity_id_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Programme Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="programme_id_up" class="form-label">Programme <span
                                                            class="text-danger">*</span></label>
                                                    <select name="programme_id_up" id="programme_id_up"
                                                        class="form-select @error('programme_id_up') is-invalid @enderror"
                                                        required>
                                                        <option value="" disabled>- Select Programme -</option>
                                                        @foreach ($progs as $prog)
                                                            @if ($upd->programme_id == $prog->id)
                                                                <option value="{{ $prog->id }}" selected>
                                                                    {{ $prog->prog_code }} ({{ $prog->prog_mode }})
                                                                </option>
                                                            @else
                                                                <option value="{{ $prog->id }}" disabled>
                                                                    {{ $prog->prog_code }} ({{ $prog->prog_mode }})
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @error('programme_id_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Activity Sequence Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="act_seq_up" class="form-label">Activity Sequence <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" name="act_seq_up" id="act_seq_up"
                                                        class="form-control @error('act_seq_up') is-invalid @enderror"
                                                        min="1" max="50" value="{{ $upd->act_seq }}"
                                                        required>
                                                    @error('act_seq_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Semester Timeline Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="timeline_sem_up" class="form-label">Semester Timeline
                                                        <span class="text-danger">*</span></label>
                                                    <input type="number" name="timeline_sem_up" id="timeline_sem_up"
                                                        class="form-control @error('timeline_sem_up') is-invalid @enderror"
                                                        min="1" max="50" value="{{ $upd->timeline_sem }}"
                                                        required>
                                                    @error('timeline_sem_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Week Timeline Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="timeline_week_up" class="form-label">Week Timeline <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" name="timeline_week_up" id="timeline_week_up"
                                                        class="form-control @error('timeline_week_up') is-invalid @enderror"
                                                        min="1" max="100" value="{{ $upd->timeline_week }}"
                                                        required>
                                                    @error('timeline_week_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Activity Initial Status Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="init_status_up" class="form-label">Initial Status <span
                                                            class="text-danger">*</span></label>
                                                    <select name="init_status_up" id="init_status_up"
                                                        class="form-select @error('init_status_up') is-invalid @enderror"
                                                        required>
                                                        @if ($upd->init_status == 1)
                                                            <option value="1" selected>Yes</option>
                                                            <option value="2">No</option>
                                                        @elseif($upd->init_status == 2)
                                                            <option value="1">Yes</option>
                                                            <option value="2"selected>No</option>
                                                        @else
                                                            <option value="" selected>- Select Option -</option>
                                                            <option value="1">Yes</option>
                                                            <option value="2">No</option>
                                                        @endif
                                                    </select>
                                                    @error('init_status_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Evaluation Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="is_haveEva_up" class="form-label">Evaluation <span
                                                            class="text-danger">*</span></label>
                                                    <select name="is_haveEva_up" id="is_haveEva_up"
                                                        class="form-select @error('is_haveEva_up') is-invalid @enderror"
                                                        required>
                                                        <option value="">- Select Option -</option>
                                                        <option value="1"
                                                            @if ($upd->is_haveEva == 1) selected @endif>
                                                            Yes
                                                        </option>
                                                        <option value="0"
                                                            @if ($upd->is_haveEva == 0) selected @endif>
                                                            No
                                                        </option>
                                                    </select>
                                                    @error('is_haveEva_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Activity Material Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="material_up" class="form-label">Activity Material </label>
                                                    <input type="file" name="material_up" id="material_up"
                                                        class="form-control @error('material_up') is-invalid @enderror mb-2">
                                                    @if ($upd->material)
                                                        <a href="{{ URL::signedRoute('view-material-get', ['filename' => Crypt::encrypt($upd->material)]) }}"
                                                            target="_blank" class="link-primary">View Uploaded
                                                            Material</a>
                                                    @endif
                                                    @error('material_up')
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
                    <div class="modal fade" id="deleteModal-{{ $upd->activity_id }}-{{ $upd->programme_id }}"
                        data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
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
                                                <a href="{{ route('delete-procedure-get', ['actID' => Crypt::encrypt($upd->activity_id), 'progID' => Crypt::encrypt($upd->programme_id)]) }}"
                                                    class="btn btn-danger w-100">Delete Anyways</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Delete Modal ] end -->
                @endforeach
                <!-- [ Procedure Setting ] end -->
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

                // DATATABLE : PROCEDURE
                var table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    autoWidth: true,
                    ajax: {
                        url: "{{ route('procedure-setting') }}",
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            searchable: false,
                            className: "text-start"
                        },
                        {
                            data: 'act_name',
                            name: 'act_name'
                        },
                        {
                            data: 'prog_code',
                            name: 'prog_code'
                        },
                        {
                            data: 'prog_mode',
                            name: 'prog_mode'
                        },
                        {
                            data: 'act_seq',
                            name: 'act_seq'
                        },
                        {
                            data: 'timeline_sem',
                            name: 'timeline_sem'
                        },
                        {
                            data: 'timeline_week',
                            name: 'timeline_week'
                        },
                        {
                            data: 'is_haveEva',
                            name: 'is_haveEva'
                        },
                        {
                            data: 'init_status',
                            name: 'init_status'
                        },
                        {
                            data: 'material',
                            name: 'material'
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
