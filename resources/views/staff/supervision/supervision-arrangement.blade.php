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
                            <div class="d-grid gap-2 gap-md-3 d-md-flex flex-wrap">
                                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#importModal"><i
                                        class="ti ti-file-import f-18"></i>
                                    Import Supervision
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
                                            <th scope="col">Name</th>
                                            <th scope="col">Research Title</th>
                                            <th scope="col">Supervisor</th>
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
                                                    value="{{ $upd->student_titleOfResearch }}" required>
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
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addSupervisionModal">Add Supervision</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <!--Staff Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="staff_id" class="form-label">Staff <span
                                                            class="text-danger">*</span></label>
                                                    <select name="staff_id" id="staff_id"
                                                        class="form-select @error('staff_id') is-invalid @enderror"
                                                        required>
                                                        <option value="">- Select Programme -</option>
                                                        @foreach ($staffs as $st)
                                                            @if (old('staff_id') == $st->id)
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
                                                    @error('staff_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Staff Role Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="supervision_role_up" class="form-label">
                                                        Role <span class="text-danger">*</span>
                                                    </label>
                                                    <select
                                                        class="form-select @error('supervision_role') is-invalid @enderror"
                                                        name="supervision_role" id="supervision_role" required>
                                                        <option value ="" selected>- Select Role -</option>
                                                        @if (old('supervision_role') == 1)
                                                            <option value ="1" selected>Supervisor</option>
                                                            <option value ="2">Co-Supervisor</option>
                                                        @elseif(old('supervision_role') == 2)
                                                            <option value ="1">Supervisor</option>
                                                            <option value ="2" selected>Co-Supervisor</option>
                                                        @else
                                                            <option value ="1">Supervisor</option>
                                                            <option value ="2">Co-Supervisor</option>
                                                        @endif
                                                    </select>
                                                    @error('supervision_role')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
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
                    <form action="{{ route('update-supervision-post', Crypt::encrypt($upd->id)) }}"
                        enctype="multipart/form-data" method="POST">
                        @csrf
                        <div class="modal fade" id="updateSupervisionModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateSupervisionModal">Update Supervision</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <!--Staff Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="staff_id_up" class="form-label">Staff <span
                                                            class="text-danger">*</span></label>
                                                    <select name="staff_id_up" id="staff_id_up"
                                                        class="form-select @error('staff_id_up') is-invalid @enderror"
                                                        required>
                                                        <option value="">- Select Programme -</option>
                                                        @foreach ($staffs as $st)
                                                            @if ($upd->staff_id == $st->id)
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
                                                    @error('staff_id_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Staff Role Input -->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="supervision_role_up" class="form-label">
                                                        Role <span class="text-danger">*</span>
                                                    </label>
                                                    <select
                                                        class="form-select @error('supervision_role_up') is-invalid @enderror"
                                                        name="supervision_role_up" id="student_status_up" required>
                                                        <option value ="" selected>- Select Role -</option>
                                                        @if ($upd->supervision_role == 1)
                                                            <option value ="1" selected>Supervisor</option>
                                                            <option value ="2">Co-Supervisor</option>
                                                        @elseif($upd->supervision_role == 2)
                                                            <option value ="1">Supervisor</option>
                                                            <option value ="2" selected>Co-Supervisor</option>
                                                        @else
                                                            <option value ="1">Supervisor</option>
                                                            <option value ="2">Co-Supervisor</option>
                                                        @endif
                                                    </select>
                                                    @error('supervision_role_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
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
                    <!-- [ Update Supervision Modal ] end -->

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
                                                <a href="{{ route('delete-student-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 1]) }}"
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

                // DATATABLE : SUPERVISION
                var table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    autoWidth: true,
                    ajax: {
                        url: "{{ route('supervision-arrangement') }}",
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            searchable: false,
                            className: "text-start"
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

                    ]

                });

            });

            $('#student_photo').on('change', function() {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        $('.previewImage').attr('src', e.target.result).show();
                    };

                    reader.readAsDataURL(file);
                }
            });

            $('.student_photo').on('change', function() {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        $('.previewImage').attr('src', e.target.result).show();
                    };

                    reader.readAsDataURL(file);
                }
            });

            $('.phonenum-input').on('input', function() {
                let input = $(this).val().replace(/\D/g, '');
                let errorMessage = $('#phone-error-message');

                if (input.length <= 11) {
                    if (input.length === 10) {
                        // Format untuk 10 digit: ### ### ####
                        $(this).val(input.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3'));
                        errorMessage.hide();
                    } else if (input.length === 11) {
                        // Format untuk 11 digit: ### #### ####
                        $(this).val(input.replace(/(\d{3})(\d{4})(\d{4})/, '$1 $2 $3'));
                        errorMessage.hide();
                    } else {
                        $(this).val(input);
                        errorMessage.hide();
                    }
                } else {
                    errorMessage.show();
                }
            });

            $('.matric-input').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            $('#browse-btn').on('click', function() {
                $('#file').click();
            });

            $('#file').on('change', function() {
                let fileName = $(this).val().split("\\").pop();
                $('#file-name').val(fileName || "No file chosen");
                $('#import-btn').prop('disabled', false);
            });


        });
    </script>
@endsection
