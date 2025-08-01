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

                            <!-- [ Procedure Setup Guidelines ] start -->
                            <div class="alert alert-light d-flex align-items-start gap-3 p-4" role="alert">
                                <i class="ti ti-info-circle fs-3"></i>
                                <div class="w-100">
                                    <h4 class="mb-3 fw-semibold">Procedure Setup Guidelines</h4>
                                    <ul class="mb-0 ps-3 small">
                                        <li class="mb-2">
                                            Ensure procedures follow the programme structure. Avoid duplicate entries for
                                            the same activity.
                                        </li>
                                        <li class="mb-2">
                                            <strong>Sequence</strong> defines the activity order shown to students.
                                        </li>
                                        <li class="mb-2">
                                            <strong>Timeline Semester</strong> is the earliest semester the activity will be
                                            available to students.
                                        </li>
                                        <li class="mb-2">
                                            <strong>Timeline Week</strong> sets the submission due date for the activity.
                                        </li>
                                        <li class="mb-2">
                                            <strong>Initial Status:</strong>
                                            <span class="text-dark">L</span> - Locked (requires committee approval to
                                            appear),
                                            <span class="text-dark">O</span> - Open (always visible).
                                        </li>
                                        <li class="mb-0">
                                            <strong>Evaluation:</strong> Activities with evaluations complete after
                                            evaluation; others complete after approvals.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- [ Procedure Setup Guidelines ] start -->

                            <!-- [ Option Section ] start -->
                            <div class="mb-4 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#addModal" title="Add Procedure"
                                    id="addStaffBtn">
                                    <i class="ti ti-plus f-18"></i> <span class="d-none d-sm-inline me-2">Add
                                        Procedure</span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->
                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Activity</th>
                                            <th scope="col">Sequence</th>
                                            <th scope="col">Semester</th>
                                            <th scope="col">Week</th>
                                            <th scope="col">Evaluation</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Material</th>
                                            <th scope="col">Programme</th>
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
                                <div class="modal-header bg-light">
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
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="init_status" class="form-label">Initial Status <span
                                                        class="text-danger">*</span></label>
                                                <select name="init_status" id="init_status"
                                                    class="form-select @error('init_status') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Status -</option>
                                                    <option value="1"
                                                        @if (old('init_status') == 1) selected @endif>(O) Open Always
                                                    </option>
                                                    <option value="2"
                                                        @if (old('init_status') == 2) selected @endif>(L) Locked
                                                    </option>
                                                </select>
                                                @error('init_status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Activity Repeatable Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="is_repeatable" class="form-label">Repeatable <span
                                                        class="text-danger">*</span></label>
                                                <select name="is_repeatable" id="is_repeatable"
                                                    class="form-select @error('is_repeatable') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Option -</option>
                                                    <option value="1"
                                                        @if (old('is_repeatable') == 1) selected @endif>Yes
                                                    </option>
                                                    <option value="0"
                                                        @if (old('is_repeatable') == 0) selected @endif>No
                                                    </option>
                                                </select>
                                                @error('is_repeatable')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Evaluation Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="is_haveEva" class="form-label">Evaluation <span
                                                        class="text-danger">*</span></label>
                                                <select name="is_haveEva" id="is_haveEva"
                                                    class="form-select @error('is_haveEva') is-invalid @enderror"
                                                    required>
                                                    @if (old('is_haveEva') == 1)
                                                        <option value="1" selected>Yes</option>
                                                        <option value="0">No</option>
                                                    @elseif(old('is_haveEva') == 0)
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
                                        <!--Correction Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="is_haveCorrection" class="form-label">Correction <span
                                                        class="text-danger">*</span></label>
                                                <select name="is_haveCorrection" id="is_haveCorrection"
                                                    class="form-select @error('is_haveCorrection') is-invalid @enderror"
                                                    disabled>
                                                    @if (old('is_haveCorrection') == 1)
                                                        <option value="1" selected>Yes</option>
                                                        <option value="0">No</option>
                                                    @elseif(old('is_haveCorrection') == 0)
                                                        <option value="1">Yes</option>
                                                        <option value="0"selected>No</option>
                                                    @else
                                                        <option value="" selected>- Select Option -</option>
                                                        <option value="1">Yes</option>
                                                        <option value="0">No</option>
                                                    @endif
                                                </select>
                                                @error('is_haveCorrection')
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
                                <div class="modal-footer bg-light justify-content-end">
                                    <div class="flex-grow-1 text-end">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="button"
                                                    class="btn btn btn-outline-secondary btn-pc-default w-100"
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

                                    <div class="modal-header bg-light">
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
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="init_status_up" class="form-label">Initial Status <span
                                                            class="text-danger">*</span></label>
                                                    <select name="init_status_up" id="init_status_up"
                                                        class="form-select @error('init_status_up') is-invalid @enderror"
                                                        required>
                                                        <option value="1"
                                                            @if ($upd->init_status == 1) selected @endif>(O) Open
                                                            Always
                                                        </option>
                                                        <option value="2"
                                                            @if ($upd->init_status == 2) selected @endif>(L) Locked
                                                        </option>
                                                    </select>
                                                    @error('init_status_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Activity Initial Status Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="is_repeatable_up" class="form-label">Repeatable <span
                                                            class="text-danger">*</span></label>
                                                    <select name="is_repeatable_up" id="is_repeatable_up"
                                                        class="form-select @error('is_repeatable_up') is-invalid @enderror"
                                                        required>
                                                        <option value="1"
                                                            @if ($upd->is_repeatable == 1) selected @endif>Yes
                                                        </option>
                                                        <option value="0"
                                                            @if ($upd->is_repeatable == 0) selected @endif>No
                                                        </option>
                                                    </select>
                                                    @error('is_repeatable_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Evaluation Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="is_haveEva_up" class="form-label">Evaluation <span
                                                            class="text-danger">*</span></label>
                                                    <select name="is_haveEva_up" id="is_haveEva_up"
                                                        class="form-select is-haveEva @error('is_haveEva_up') is-invalid @enderror"
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
                                            <!--Correction Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="is_haveCorrection_up" class="form-label">Correction <span
                                                            class="text-danger">*</span></label>
                                                    <select name="is_haveCorrection_up" id="is_haveCorrection_up"
                                                        class="form-select is-haveCorrection @error('is_haveCorrection_up') is-invalid @enderror"
                                                        required>
                                                        <option value="">- Select Option -</option>
                                                        <option value="1"
                                                            @if ($upd->is_haveCorrection == 1) selected @endif>
                                                            Yes
                                                        </option>
                                                        <option value="0"
                                                            @if ($upd->is_haveCorrection == 0) selected @endif>
                                                            No
                                                        </option>
                                                    </select>
                                                    @error('is_haveCorrection_up')
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
                    <div class="modal fade" id="deleteModal-{{ $upd->activity_id }}-{{ $upd->programme_id }}"
                        data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                        aria-labelledby="deleteModalLabel-{{ $upd->activity_id }}-{{ $upd->programme_id }}"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-body p-4">
                                    <div class="text-center mb-3">
                                        <i class="ti ti-trash text-danger" style="font-size: 80px;"></i>
                                    </div>
                                    <h4 class="text-center mb-2"
                                        id="deleteModalLabel-{{ $upd->activity_id }}-{{ $upd->programme_id }}">Are you
                                        sure?
                                    </h4>
                                    <p class="text-center text-muted mb-4">This action cannot be undone and may affect
                                        related procedures.</p>

                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="{{ route('delete-procedure-get', ['actID' => Crypt::encrypt($upd->activity_id), 'progID' => Crypt::encrypt($upd->programme_id)]) }}"
                                            class="btn btn-danger w-100">Delete Anyway</a>
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
        $(document).ready(function() {

            /*********************************************************
             ***************GLOBAL FUNCTION & VARIABLES***************
             *********************************************************/

            var modalToShow = "{{ session('modal') }}";
            if (modalToShow) {
                var modalElement = $("#" + modalToShow);
                if (modalElement.length) {
                    var modal = new bootstrap.Modal(modalElement[0]);
                    modal.show();
                }
            }

            // DATATABLE : PROCEDURES
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
                        data: 'act_seq',
                        name: 'act_seq',
                        orderable: false,

                    },
                    {
                        data: 'timeline_sem',
                        name: 'timeline_sem',
                        orderable: false,

                    },
                    {
                        data: 'timeline_week',
                        name: 'timeline_week',
                        orderable: false,

                    },
                    {
                        data: 'is_haveEva',
                        name: 'is_haveEva',
                        orderable: false,

                    },
                    {
                        data: 'init_status',
                        name: 'init_status'
                    },
                    {
                        data: 'material',
                        name: 'material',
                        orderable: false,

                    },
                    {
                        data: 'prog_code_mode',
                        name: 'prog_code_mode',
                        visible: false,
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                rowGroup: {
                    dataSrc: 'prog_code_mode',
                    startRender: function(rows, group) {
                        return $('<tr class="group-header" style="cursor:pointer"/>')
                            .attr('data-group', group)
                            .append(
                                '<td colspan="9" class="bg-light text-center">' +
                                '<span class="fw-semibold text-uppercase me-2">' + group + '</span>' +
                                ' <span class="badge bg-primary">' + rows.count() + '</span>' +
                                ' <i class="ti ti-chevron-down float-end toggle-icon"></i>' +
                                '</td>'
                            );
                    }
                },

            });

            var collapsedGroups = {};

            $('.data-table tbody').on('click', 'tr.group-header', function() {
                var group = $(this).data('group');
                collapsedGroups[group] = !collapsedGroups[group];

                // Toggle icon
                var icon = $(this).find('.toggle-icon');
                if (collapsedGroups[group]) {
                    icon.removeClass('ti-chevron-down').addClass('ti-chevron-right');
                } else {
                    icon.removeClass('ti-chevron-right').addClass('ti-chevron-down');
                }

                table.rows().every(function() {
                    if (this.data().programme === group) {
                        $(this.node()).toggle(!collapsedGroups[group]);
                    }
                });
            });


            $('#is_haveEva').change(function() {
                if (this.value == 1) {
                    $('#is_haveCorrection').attr('disabled', false);
                } else {
                    $('#is_haveCorrection').attr('disabled', true);
                }
            });

            $('.is-haveEva').on('change', function() {
                const correctionSelect = $(this).closest('.row').find('.is-haveCorrection');

                if ($(this).val() == '1') {
                    correctionSelect.prop('disabled', false);
                } else {
                    correctionSelect.prop('disabled', true);
                }
            });

            $('.is-haveEva').each(function() {
                $(this).trigger('change');
            });

        });
    </script>
@endsection
