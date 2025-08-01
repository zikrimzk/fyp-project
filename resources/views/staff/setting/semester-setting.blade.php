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
                                <li class="breadcrumb-item" aria-current="page">Semester Setting</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Semester Setting</h2>
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

                <!-- [ Semester Setting ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">

                            <!-- [ Option Section ] start -->
                            <div class="mb-4 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    title="Add Semester" id="addModalBtn" data-bs-toggle="modal" data-bs-target="#addModal">
                                    <i class="ti ti-plus f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Add Semester
                                    </span>
                                </button>

                                <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2"
                                    title="Set Current Semester" data-bs-toggle="modal" data-bs-target="#setCurrSem">
                                    <i class="ti ti-edit-circle f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Set Current Semester
                                    </span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Semester</th>
                                            <th scope="col">Start Date</th>
                                            <th scope="col">End Date</th>
                                            <th scope="col">Duration</th>
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
                <form action="{{ route('add-semester-post') }}" method="POST">
                    @csrf
                    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header bg-light">
                                    <h5 class="modal-title" id="addModalLabel">Add Semester</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row">

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="sem_label" class="form-label">Semester Label <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('sem_label') is-invalid @enderror"
                                                    id="sem_label" name="sem_label" placeholder="Enter Semester Label"
                                                    value="{{ old('sem_label') }}" required>
                                                @error('sem_label')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="sem_startdate" class="form-label">Start Date <span
                                                        class="text-danger">*</span></label>
                                                <input type="date"
                                                    class="form-control @error('sem_startdate') is-invalid @enderror"
                                                    id="sem_startdate" name="sem_startdate"
                                                    placeholder="Choose Start Date" value="{{ old('sem_startdate') }}"
                                                    required>
                                                @error('sem_startdate')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="sem_enddate" class="form-label">End Date <span
                                                        class="text-danger">*</span></label>
                                                <input type="date"
                                                    class="form-control @error('sem_enddate') is-invalid @enderror"
                                                    id="sem_enddate" name="sem_enddate" placeholder="Choose End Date"
                                                    value="{{ old('sem_enddate') }}" required>
                                                @error('sem_enddate')
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
                                                    Add Semester
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

                <!-- [ Change Semester Modal ] start -->
                <form action="{{ route('change-semester-post') }}" method="POST">
                    @csrf
                    <div class="modal fade" id="setCurrSem" tabindex="-1" aria-labelledby="setCurrSem"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header bg-light">
                                    <h5 class="modal-title" id="setCurrSemLabel">Set Current Semester</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row">

                                        <div class="col-sm-12">
                                            <!-- [ Important Note: Set Current Semester ] start-->
                                            <div class="alert alert-light d-flex align-items-start gap-3 p-4"
                                                role="alert">
                                                <i class="ti ti-alert-triangle fs-3"></i>
                                                <div class="w-100">
                                                    <h5 class="mb-3 text-danger">Important Note</h5>
                                                    <ul class="mb-0 ps-3 small">
                                                        <li class="mb-2">
                                                            Please <strong>verify carefully</strong> before setting the
                                                            current semester. <strong>This action is NOT
                                                                reversible</strong>.
                                                        </li>
                                                        <li class="mb-2">
                                                            Any mistake in setting the semester may lead to <strong>major
                                                                data inconsistency</strong> in the system.
                                                        </li>
                                                        <li class="mb-2">
                                                            Once the current semester is changed, all previously
                                                            <strong>Active</strong> students from the old semester will be
                                                            updated to <strong>Completed</strong> status.
                                                        </li>
                                                        <li class="mb-2">
                                                            After the semester is updated, <strong>student-related modules
                                                                will reset</strong> and show no data until students are
                                                            enrolled into the new semester by the committee.
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <!-- [ Important Note: Set Current Semester ] end -->
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="sem_label" class="form-label">Current Semester</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $sems->where('sem_status', 1)->first()->sem_label ?? '-' }}"
                                                    placeholder="Current Active Semester" readonly>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="sem_label" class="form-label">New Semester <span
                                                        class="text-danger">*</span></label>
                                                <select name="semester_id" id="semester_id" class="form-select" required>
                                                    <option value="">- Select Semester -</option>
                                                    @foreach ($sems->where('sem_status', 2) as $sem)
                                                        <option value="{{ $sem->id }}">{{ $sem->sem_label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="modal-footer bg-light justify-content-end">
                                    <div class="flex-grow-1 text-end">
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="button" class="btn btn-outline-secondary btn-pc-default w-100"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary w-100"
                                                    id="addApplicationBtn">
                                                    Confirm & Set Semester
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Change Semester Modal ] end -->

                @foreach ($sems as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-semester-post', Crypt::encrypt($upd->id)) }}" method="POST">
                        @csrf
                        <div class="modal fade" id="updateModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title" id="updateModalLabel">Update Semester</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="sem_label_up" class="form-label">Semester Label <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control @error('sem_label_up') is-invalid @enderror"
                                                        id="sem_label_up" name="sem_label_up"
                                                        placeholder="Enter Semester Label" value="{{ $upd->sem_label }}"
                                                        required>
                                                    @error('sem_label_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="sem_startdate_up" class="form-label">Start Date <span
                                                            class="text-danger">*</span></label>
                                                    <input type="date"
                                                        class="form-control @error('sem_startdate_up') is-invalid @enderror"
                                                        id="sem_startdate_up" name="sem_startdate_up"
                                                        placeholder="Choose Start Date" value="{{ $upd->sem_startdate }}"
                                                        required>
                                                    @error('sem_startdate_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="sem_enddate_up" class="form-label">End Date <span
                                                            class="text-danger">*</span></label>
                                                    <input type="date"
                                                        class="form-control @error('sem_enddate_up') is-invalid @enderror"
                                                        id="sem_enddate_up" name="sem_enddate_up"
                                                        placeholder="Choose End Date" value="{{ $upd->sem_enddate }}"
                                                        required>
                                                    @error('sem_enddate_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="prog_mode_up" class="form-label">Status</label>
                                                    <input type="text" class="form-control"
                                                        value="@if ($upd->sem_status == 1) Active @elseif($upd->sem_status == 2) Upcoming @else Past @endif"
                                                        readonly>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="modal-footer bg-light justify-content-end">
                                        <div class="flex-grow-1 text-end">
                                            <div class="col-sm-12">
                                                <div class="d-flex justify-content-between gap-3 align-items-center">
                                                    <button type="button" class="btn btn-outline-secondary btn-pc-default w-100"
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
                                        <a href="{{ route('delete-semester-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 1]) }}"
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
                                    <h4 class="text-center mb-2" id="disableModalLabel-{{ $upd->id }}">Semester
                                        Inactivation</h4>
                                    <p class="text-center text-muted mb-4">
                                        Oops! You can't delete this semester.<br>
                                        However, you can inactivate them instead. Would you like to proceed?
                                    </p>

                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="{{ route('delete-semester-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 2]) }}"
                                            class="btn btn-warning w-100">Inactivate</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Disable Modal ] end -->
                @endforeach

                <!-- [ Semester Setting ] end -->
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

            // DATATABLE : SEMESTER
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('semester-setting') }}",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        className: "text-start"
                    },
                    {
                        data: 'sem_label',
                        name: 'sem_label'
                    },
                    {
                        data: 'sem_startdate',
                        name: 'sem_startdate'
                    },
                    {
                        data: 'sem_enddate',
                        name: 'sem_enddate'
                    },
                    {
                        data: 'sem_duration',
                        name: 'sem_duration'
                    },
                    {
                        data: 'sem_status',
                        name: 'sem_status'
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
