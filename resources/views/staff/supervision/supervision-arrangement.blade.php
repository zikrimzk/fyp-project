@php
    use App\Models\Semester;
@endphp
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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Supervision</a></li>
                                <li class="breadcrumb-item" aria-current="page">Supervision Arrangement</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Supervision Arrangement</h2>
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

                <!-- [ Supervision Arrangement ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- [ Option Section ] start -->
                            <div class="mb-4 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button"
                                    class="btn btn-outline-primary  d-flex align-items-center gap-2 d-none"
                                    id="clearSelectionBtn">
                                    <i class="ti ti-x f-18"></i>
                                    0 selected
                                </button>
                                <button type="button" class="btn btn-outline-primary  d-flex align-items-center gap-2"
                                    id="excelExportBtn" title="Export Data">
                                    <i class="ti ti-file-export f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Export Data
                                    </span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <!-- [ Filter Section ] Start -->
                            <div class="row g-3 align-items-end">

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_faculty_id" class="form-select">
                                            <option value="">-- Select Faculty --</option>
                                            @foreach ($facs as $fil)
                                                @if ($fil->fac_status == 1)
                                                    <option value="{{ $fil->id }}">{{ $fil->fac_code }}</option>
                                                @elseif($fil->fac_status == 2)
                                                    <option value="{{ $fil->id }}" class="bg-light-danger">
                                                        {{ $fil->fac_code }} [Inactive]
                                                    </option>
                                                @elseif($fil->fac_status == 3)
                                                    <option value="{{ $fil->id }}" class="bg-light-success" selected>
                                                        {{ $fil->fac_code }} [Default]
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFacFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_semester_id" class="form-select">
                                            <option value="">-- Select Semester --</option>
                                            @foreach ($sems as $fil)
                                                @if ($fil->sem_status == 1)
                                                    <option value="{{ $fil->id }}" class="bg-light-success" selected>
                                                        {{ $fil->sem_label }} [Current]
                                                    </option>
                                                @elseif($fil->sem_status == 3)
                                                    <option value="{{ $fil->id }}"> {{ $fil->sem_label }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSemFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_programme_id" class="form-select">
                                            <option value="">-- Select Programme --</option>
                                            @foreach ($progs as $fil)
                                                @if ($fil->prog_status == 1)
                                                    <option value="{{ $fil->id }}"> {{ $fil->prog_code }}
                                                        ({{ $fil->prog_mode }})
                                                    </option>
                                                @elseif($fil->prog_status == 2)
                                                    <option value="{{ $fil->id }}" class="bg-light-danger">
                                                        {{ $fil->prog_code }}
                                                        ({{ $fil->prog_mode }}) [Inactive]</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearProgFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_status" class="form-select">
                                            <option value="">-- Select Status --</option>
                                            <option value="1">Assigned</option>
                                            <option value="2">Unassigned</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearStatusFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- [ Filter Section ] End -->

                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all" class="form-check-input"></th>
                                            <th scope="col">Student</th>
                                            <th scope="col">Research Title</th>
                                            <th scope="col">Supervisor</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                @foreach ($studs as $upd)
                    <!-- [ Update Title Of Research ] start -->
                    <form action="{{ route('update-titleOfResearch-post', Crypt::encrypt($upd->id)) }}" method="POST">
                        @csrf
                        <div class="modal fade" id="updateTitleOfResearchModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateTitleOfResearchModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content shadow rounded-3 border-0">

                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title" id="updateTitleOfResearchModalLabel">Update Title of
                                            Research</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body px-4 py-3">
                                        <div class="mb-3">
                                            <label for="student_titleOfResearch" class="form-label">
                                                Title of Research
                                            </label>
                                            <textarea name="student_titleOfResearch" class="form-control @error('student_titleOfResearch') is-invalid @enderror"
                                                id="student_titleOfResearch" rows="5" placeholder="Enter Title of Research">{{ $upd->student_titleOfResearch }}</textarea>
                                            @error('student_titleOfResearch')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="modal-footer pt-2 bg-light">
                                        <div class="row w-100 g-2">
                                            <div class="col-12 col-md-6">
                                                <button type="reset" class="btn btn-outline-secondary w-100"
                                                    data-bs-dismiss="modal">
                                                    Cancel
                                                </button>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    Save Changes
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Update Title Of Research ] end -->

                    <!-- [ Add Supervision Modal ] start -->
                    <form action="{{ route('add-supervision-post', Crypt::encrypt($upd->id)) }}" method="POST">
                        @csrf
                        <div class="modal fade" id="addSupervisionModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="addSupervisionModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content shadow-lg rounded-3 border-0">

                                    <!-- Modal Header -->
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title" id="addSupervisionModalLabel">Add Supervision</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <!-- Modal Body -->
                                    <div class="modal-body px-4 py-3">
                                        <div class="mb-4">
                                            <label for="staff_id_sv" class="form-label">
                                                Main Supervisor <span class="text-danger">*</span>
                                            </label>
                                            <select name="staff_id_sv" id="staff_id_sv"
                                                class="form-select @error('staff_id_sv') is-invalid @enderror" required>
                                                <option value="">- Select Main Supervisor -</option>
                                                @foreach ($staffs->where('staff_status', 1) as $st)
                                                    <option value="{{ $st->id }}"
                                                        {{ old('staff_id_sv') == $st->id ? 'selected' : '' }}>
                                                        {{ $st->staff_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('staff_id_sv')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <input type="hidden" name="supervision_svrole" value="1">
                                        </div>

                                        <div class="mb-3">
                                            <label for="staff_id_cosv" class="form-label">
                                                Co-Supervisor <span class="text-danger">*</span>
                                            </label>
                                            <select name="staff_id_cosv" id="staff_id_cosv"
                                                class="form-select @error('staff_id_cosv') is-invalid @enderror" required>
                                                <option value="">- Select Co-Supervisor -</option>
                                                @foreach ($staffs->where('staff_status', 1) as $st)
                                                    <option value="{{ $st->id }}"
                                                        {{ old('staff_id_cosv') == $st->id ? 'selected' : '' }}>
                                                        {{ $st->staff_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('staff_id_cosv')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <input type="hidden" name="supervision_cosvrole" value="2">
                                        </div>
                                    </div>

                                    <!-- Modal Footer -->
                                    <div class="modal-footer bg-light">
                                        <div class="row w-100 g-2">
                                            <div class="col-12 col-md-6">
                                                <button type="reset" class="btn btn-outline-secondary w-100"
                                                    data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <button type="submit" class="btn btn-primary w-100">Add
                                                    Supervision</button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Add Supervision Modal ] end -->

                    <!-- [ Update Supervision Modal ] start -->
                    <form action="{{ route('update-supervision-post', Crypt::encrypt($upd->id)) }}" method="POST">
                        @csrf
                        <div class="modal fade" id="updateSupervisionModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title" id="updateSupervisionModal">Update Supervision</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">

                                            @foreach ($svs->where('student_id', $upd->id) as $sv)
                                                @if ($sv->supervision_role == 1)
                                                    <!--[ Main Supervisor ] Staff Input-->
                                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                                        <div class="mb-3">
                                                            <label for="staff_id_sv_up" class="form-label">Main Supervisor
                                                                <span class="text-danger">*</span></label>
                                                            <select name="staff_id_sv_up" id="staff_id_sv_up"
                                                                class="form-select @error('staff_id_sv_up') is-invalid @enderror"
                                                                required>
                                                                <option value="">- Select Main Supervisor -</option>
                                                                @foreach ($staffs as $st)
                                                                    @if ($sv->staff_id == $st->id)
                                                                        <option value="{{ $st->id }}" selected>
                                                                            {{ $st->staff_name }}
                                                                            @if ($st->staff_status == 2)
                                                                                [Inactive]
                                                                            @endif
                                                                        </option>
                                                                    @else
                                                                        @if ($st->staff_status == 1)
                                                                            <option value="{{ $st->id }}">
                                                                                {{ $st->staff_name }}
                                                                            </option>
                                                                        @endif
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                            @error('staff_id_sv_up')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                @elseif($sv->supervision_role == 2)
                                                    <!--[ Co-Supervisor ] Staff Input-->
                                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                                        <div class="mb-3">
                                                            <label for="staff_id_cosv_up" class="form-label">Co-Supervisor
                                                                <span class="text-danger">*</span></label>
                                                            <select name="staff_id_cosv_up" id="staff_id_cosv_up"
                                                                class="form-select @error('staff_id_cosv_up') is-invalid @enderror"
                                                                required>
                                                                <option value="">- Select Co-Supervisor -</option>
                                                                @foreach ($staffs as $st)
                                                                    @if ($sv->staff_id == $st->id)
                                                                        <option value="{{ $st->id }}" selected>
                                                                            {{ $st->staff_name }}
                                                                            @if ($st->staff_status == 2)
                                                                                [Inactive]
                                                                            @endif
                                                                        </option>
                                                                    @else
                                                                        @if ($st->staff_status == 1)
                                                                            <option value="{{ $st->id }}">
                                                                                {{ $st->staff_name }}
                                                                            </option>
                                                                        @endif
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                            @error('staff_id_cosv_up')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Modal Footer -->
                                    <div class="modal-footer bg-light">
                                        <div class="row w-100 g-2">
                                            <div class="col-12 col-md-6">
                                                <button type="reset" class="btn btn-outline-secondary w-100"
                                                    data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    Save Changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Update Supervision Modal ] end -->

                    <!-- [ Delete Modal ] start -->
                    <div class="modal fade" id="deleteSupervisionModal-{{ $upd->id }}" data-bs-backdrop="static"
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
                                    <p class="text-center text-muted mb-4">
                                        This action will unassign both
                                        main supervisor and co-supervisor from the student. You will need to
                                        reassign them afterward.
                                    </p>

                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="{{ route('delete-supervision-get', ['id' => Crypt::encrypt($upd->id)]) }}"
                                            class="btn btn-danger w-100">Delete Anyway</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Delete Modal ] end -->
                @endforeach

                <!-- [ Supervision Arrangement ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : SUPERVISION
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('supervision-arrangement') }}",
                    data: function(d) {
                        d.faculty = $('#fil_faculty_id')
                            .val();
                        d.programme = $('#fil_programme_id')
                            .val();
                        d.semester = $('#fil_semester_id')
                            .val();
                        d.status = $('#fil_status')
                            .val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,

                    },
                    {
                        data: 'student_photo',
                        name: 'student_photo'
                    },
                    {
                        data: 'student_title',
                        name: 'student_title',

                    },
                    {
                        data: 'supervisor',
                        name: 'supervisor'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                    }

                ]

            });

            var modalToShow = "{{ session('modal') }}";
            if (modalToShow) {
                var modalElement = $("#" + modalToShow);
                if (modalElement.length) {
                    var modal = new bootstrap.Modal(modalElement[0]);
                    modal.show();
                }
            }

            // Faculty Filter
            $('#fil_faculty_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });
            $('#clearFacFilter').click(function() {
                $('#fil_faculty_id').val('').change();
            });

            // Programme Filter
            $('#fil_programme_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });
            $('#clearProgFilter').click(function() {
                $('#fil_programme_id').val('').change();
            });

            // Semester Filter
            $('#fil_semester_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });
            $('#clearSemFilter').click(function() {
                $('#fil_semester_id').val('').change();
            });

            // Status Filter
            $('#fil_status').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });
            $('#clearStatusFilter').click(function() {
                $('#fil_status').val('').change();
            });


            /* SELECT : MULTIPLE STUDENT SELECT */
            const excelExportBtn = $("#excelExportBtn");
            const clearBtn = $("#clearSelectionBtn");

            let selectedIds = new Set();

            // Handle "Select All" checkbox
            $("#select-all").on("change", function() {
                let isChecked = $(this).prop("checked");

                $(".user-checkbox").each(function() {
                    let id = $(this).val();
                    this.checked = isChecked;

                    if (isChecked) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                });
                toggleSelectButton();
            });

            // Handle individual checkbox selection
            $(document).on("change", ".user-checkbox", function() {
                let id = $(this).val();
                if ($(this).prop("checked")) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
                toggleSelectButton();
            });

            // Restore checkbox states after DataTables refresh
            $('.data-table').on("draw.dt", function() {
                $(".user-checkbox").each(function() {
                    let id = $(this).val();
                    this.checked = selectedIds.has(id);
                });

                // If all checkboxes are selected, keep "Select All" checked
                $("#select-all").prop(
                    "checked",
                    $(".user-checkbox").length === $(".user-checkbox:checked").length
                );

                toggleSelectButton();
            });

            function toggleSelectButton() {
                let selectedCount = selectedIds.size;

                if (selectedCount > 0) {
                    clearBtn.removeClass("d-none").html(
                        `<i class="ti ti-x f-18"></i> ${selectedCount} selected`);
                } else {
                    clearBtn.addClass("d-none");
                }
            }

            clearBtn.on("click", function() {
                $(".user-checkbox").prop("checked", false);
                $("#select-all").prop("checked", false);
                selectedIds.clear();
                toggleSelectButton();
            });

            excelExportBtn.click(function(e) {
                e.preventDefault();
                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                let url = "{{ route('export-supervision-get') }}";

                if (selectedIds.length > 0) {
                    url += "?ids=" + selectedIds.join(",");
                }
                window.location.href = url;
                clearBtn.trigger('click');
            });


        });
    </script>
@endsection
