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
                            <div class="mb-3 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button"
                                    class="btn btn-outline-primary  d-flex align-items-center gap-2 d-none"
                                    id="clearSelectionBtn">
                                    0 selected <i class="ti ti-x f-18"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary  d-flex align-items-center gap-2"
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
                                                    <option value="{{ $fil->id }}">{{ $fil->fac_code }} [Inactive]
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary btn-sm" id="clearFacFilter">
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
                                                    <option value="{{ $fil->id }}"> {{ $fil->prog_code }}
                                                        ({{ $fil->prog_mode }}) [Inactive]</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            id="clearProgFilter">
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
                                                    <option value="{{ $fil->id }}"> {{ $fil->sem_label }} [Current]
                                                    </option>
                                                @elseif($fil->sem_status == 0)
                                                    <option value="{{ $fil->id }}"> {{ $fil->sem_label }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            id="clearSemFilter">
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
                                        <button type="button" class="btn btn-secondary btn-sm"
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
                            aria-labelledby="updateTitleOfResearchModal" aria-hidden="true">
                            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateTitleOfResearchModal">Title Of Research</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <!-- Title Of Research Input -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <input type="text"
                                                    class="form-control @error('student_titleOfResearch') is-invalid @enderror"
                                                    id="student_titleOfResearch" name="student_titleOfResearch"
                                                    placeholder="Enter Title Of Research"
                                                    value="{{ $upd->student_titleOfResearch }}">
                                                @error('student_titleOfResearch')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer justify-content-end">
                                        <div class="flex-grow-1 text-end">
                                            <button type="reset" class="btn btn-link-danger btn-pc-default"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
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
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addSupervisionModalLabel">Add Supervision</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <!--[ Main Supervisor ] Staff Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="staff_id_sv" class="form-label">Main Supervisor <span
                                                            class="text-danger">*</span></label>
                                                    <select name="staff_id_sv" id="staff_id_sv"
                                                        class="form-select @error('staff_id_sv') is-invalid @enderror"
                                                        required>
                                                        <option value="">- Select Main Supervisor -</option>
                                                        @foreach ($staffs->where('staff_status', 1) as $st)
                                                            @if (old('staff_id_sv') == $st->id)
                                                                <option value="{{ $st->id }}" selected>
                                                                    {{ $st->staff_name }}
                                                                </option>
                                                            @else
                                                                <option value="{{ $st->id }}">
                                                                    {{ $st->staff_name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @error('staff_id_sv')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <input type="hidden" name="supervision_svrole"
                                                        id="supervision_role-sv" value="1">
                                                </div>
                                            </div>

                                            <!--[ Co-Supervisor ] Staff Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="staff_id_cosv" class="form-label">Co-Supervisor <span
                                                            class="text-danger">*</span></label>
                                                    <select name="staff_id_cosv" id="staff_id_cosv"
                                                        class="form-select @error('staff_id_cosv') is-invalid @enderror"
                                                        required>
                                                        <option value="">- Select Co-Supervisor -</option>
                                                        @foreach ($staffs->where('staff_status', 1) as $st)
                                                            @if (old('staff_id_cosv') == $st->id)
                                                                <option value="{{ $st->id }}" selected>
                                                                    {{ $st->staff_name }}
                                                                </option>
                                                            @else
                                                                <option value="{{ $st->id }}">
                                                                    {{ $st->staff_name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @error('staff_id_cosv')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <input type="hidden" name="supervision_cosvrole"
                                                        id="supervision_role-cosv" value="2">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer justify-content-end">
                                        <div class="flex-grow-1 text-end">
                                            <button type="reset" class="btn btn-link-danger btn-pc-default"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Add Supervision</button>
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

                                    <div class="modal-header">
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
                                    <div class="modal-footer justify-content-end">
                                        <div class="flex-grow-1 text-end">
                                            <button type="reset" class="btn btn-link-danger btn-pc-default"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Update Supervision Modal ] end -->

                    <!-- [ Delete Modal ] start -->
                    <div class="modal fade" id="deleteSupervisionModal-{{ $upd->id }}" data-bs-keyboard="false"
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
                                                <p class="fw-normal f-18 text-center">This action will unassign both
                                                    main supervisor and co-supervisor from the student. You will need to
                                                    reassign them afterward.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <a href="{{ route('delete-supervision-get', ['id' => Crypt::encrypt($upd->id)]) }}"
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

                <!-- [ Supervision Arrangement ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : SUPERVISION
            var table = $('.data-table').DataTable({
                processing: false,
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
                        `${selectedCount} selected <i class="ms-2 ti ti-x f-18"></i>`);
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
            });


        });
    </script>
@endsection
