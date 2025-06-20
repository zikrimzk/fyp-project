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

                <!-- [ Activity Setting ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2 gap-md-3 d-md-flex flex-wrap">
                                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#addModal"><i class="ti ti-plus f-18"></i>
                                    Add Activity
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
                                            <th scope="col">Activity</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- [ Add Modal ] start -->
                <form action="{{ route('add-activity-post') }}" method="POST">
                    @csrf
                    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModal" aria-hidden="true">
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
                                                <button type="submit" class="btn btn-primary w-100" id="addApplicationBtn">
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
                <!-- [ Add Modal ] end -->

                @foreach ($acts as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-activity-post', Crypt::encrypt($upd->id)) }}" method="POST">
                        @csrf
                        <div class="modal fade" id="updateModal-{{ $upd->id }}" tabindex="-1"
                            aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateModalLabel">Update Activity</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">

                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="act_name_up" class="form-label">Activity Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control @error('act_name_up') is-invalid @enderror"
                                                        id="act_name_up" name="act_name_up"
                                                        placeholder="Enter Activity Name" value="{{ $upd->act_name }}"
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
                    <div class="modal fade" id="deleteModal-{{ $upd->id }}" data-bs-keyboard="false" tabindex="-1"
                        aria-hidden="true">
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
                                                <a href="{{ route('delete-activity-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 1]) }}"
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
                                                <a href="{{ route('delete-activity-get', ['id' => Crypt::encrypt($upd->id), 'opt' => 2]) }}"
                                                    class="btn btn-warning w-100">Disable</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Disable Modal ] end -->
                @endforeach



                <!-- [ Activity Setting ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <!-- Table Fields -->
    {{-- <div class="border-top pt-3 mt-3">
                                    <h6>Table Settings</h6>

                                    <!-- Is Table Checkbox -->
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="ff_is_table"
                                            name="ff_is_table" value="1">
                                        <label class="form-check-label" for="ff_is_table">This field is a dynamic
                                            table</label>
                                    </div>

                                    <!-- Table Structure -->
                                    <div class="mb-3 table-settings-group" style="display: none;">
                                        <label for="ff_table_structure" class="form-label">Table Structure
                                            (JSON)</label>
                                        <textarea class="form-control" id="ff_table_structure" name="ff_table_structure" rows="3"
                                            placeholder='e.g. [{"header": "Column 1", "type": "text"}, {"header": "Column 2", "type": "select", "options": ["A", "B"]}]'></textarea>
                                        <small class="form-text text-muted">Define columns: header, type
                                            (text/select/date/etc), and options (if applicable).</small>
                                    </div>

                                    <!-- Table Default Data -->
                                    <div class="mb-3 table-settings-group" style="display: none;">
                                        <label for="ff_table_data" class="form-label">Default Table Data
                                            (optional)</label>
                                        <textarea class="form-control" id="ff_table_data" name="ff_table_data" rows="3"
                                            placeholder='e.g. [["Row 1 Col 1", "Row 1 Col 2"], ["Row 2 Col 1", "Row 2 Col 2"]]'></textarea>
                                        <small class="form-text text-muted">Optional: Enter default row data to
                                            pre-fill the table.</small>
                                    </div>
                                </div> --}}

    {{-- <div class="row">
                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                        <div class="mb-3">
                                            <label for="txt_label" class="form-label">Field Label</label>
                                            <input type="text" name="row_label" id="txt_label" class="form-control"
                                                placeholder="Enter Field Label">
                                        </div>
                                        <div class="mb-3">
                                            <label for="select_category" class="form-label">Field Category</label>
                                            <select name="row_category" class="form-select" id="select_category">
                                                <option value="" selected>-- Select Field Category --</option>
                                                <option value="1">Input</option>
                                                <option value="2">Output</option>
                                            </select>
                                        </div>

                                        <!-- [ Input Setting ] start -->
                                        <div id="inputSetting" class="d-none">
                                            <div class="mb-3">
                                                <label for="select_type" class="form-label">Field Type</label>
                                                <select name="row_type" class="form-select" id="select_type">
                                                    <option value="" selected>-- Select Field Type --</option>
                                                    <option value="1">Text</option>
                                                    <option value="2">Textarea</option>
                                                    <option value="6">Date</option>
                                                    <option value="7">Time</option>
                                                    <option value="8">Datetime</option>
                                                    <option value="9">Upload</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="txt_placeholder" class="form-label">Field Placeholder</label>
                                                <input type="text" name="row_placeholder" id="txt_placeholder"
                                                    class="form-control" placeholder="Enter Field Placeholder">
                                            </div>
                                        </div>
                                        <!-- [ Input Setting ] end -->

                                        <!-- [ Output Setting ] start -->
                                        <div id="outputSetting" class="d-none">
                                            <div class="mb-3">
                                                <label for="select_table" class="form-label">Field Table</label>
                                                <select name="row_table" class="form-select" id="select_table">
                                                    <option value="" selected>-- Select Field Table --</option>
                                                    <option value="students">Student</option>
                                                    <option value="staffs">Staff</option>
                                                    <option value="activities">Activity</option>
                                                    <option value="submissions">Submission</option>
                                                    <option value="semesters">Semester</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="select_datakey" class="form-label">Field Attribute</label>
                                                <select name="row_datakey" class="form-select" id="select_datakey">
                                                    <option value="" selected>-- Select Field Attribute --</option>

                                                    <option value="" disabled>-- Student --</option>
                                                    <option value="student_name" data-table="students">Name</option>
                                                    <option value="student_matricno" data-table="students">Matric No
                                                    </option>
                                                    <option value="student_gender" data-table="students">Gender</option>
                                                    <option value="student_phoneno" data-table="students">Phone No
                                                    </option>
                                                    <option value="student_email" data-table="students">Email</option>
                                                    <option value="student_titleOfResearch" data-table="students">Title of
                                                        Research</option>
                                                    <option value="programme_code" data-table="students">Programme
                                                    </option>

                                                    <option value="" disabled>-- Staff --</option>
                                                    <option value="staff_name" data-table="staffs">Name</option>
                                                    <option value="staff_id" data-table="staffs">Staff ID</option>
                                                    <option value="staff_email" data-table="staffs">Email</option>
                                                    <option value="staff_phoneno" data-table="staffs">Phone No</option>

                                                    <option value="" disabled>-- Activity --</option>
                                                    <option value="doc_name" data-table="activities">Document Name
                                                    </option>

                                                    <option value="" disabled>-- Submission --</option>
                                                    <option value="submission_duedate" data-table="submissions">Submission
                                                        Due
                                                        Date</option>
                                                    <option value="submission_date" data-table="submissions">Submission
                                                        Date
                                                    </option>

                                                    <option value="" disabled>-- Semester --</option>
                                                    <option value="sem_label" data-table="semesters">Current Semester
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- [ Output Setting ] end -->

                                    </div>
                                </div> --}}
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

                // DATATABLE : ACTIVITY
                var table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    autoWidth: true,
                    ajax: {
                        url: "{{ route('activity-setting') }}",
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
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ]

                });

            });

            // function toggleSelectButton() {
            //     let selectedCount = selectedIds.size;

            //     reassignBtn.toggleClass("d-none", selectedIds.size !== 0);
            //     updatemultipleModalBtn.toggleClass("d-none", selectedIds.size === 0);
            //     deletemultipleModalBtn.toggleClass("d-none", selectedIds.size === 0);
            //     downloadmultipleModalBtn.toggleClass("d-none", selectedIds.size === 0);

            //     if (selectedCount > 0) {
            //         clearBtn.removeClass("d-none").html(
            //             `<i class="ti ti-x f-18"></i> ${selectedCount} selected`);
            //     } else {
            //         clearBtn.addClass("d-none");
            //     }
            // }

        });
    </script>
@endsection
{{-- @php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12pt;
            margin: 40px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header img {
            width: 140px;
            margin-bottom: 10px;
        }

        .header h2,
        .header h3 {
            margin: 0;
            font-weight: bold;
        }

        .line-title {
            border-top: 1px solid #000;
            margin-top: 5px;
        }

        .form-title {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 12px;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0 20px;
        }

        .info-table td {
            padding: 10px 4px;
            vertical-align: top;
        }

        .label {
            width: 35%;
            font-weight: bold;
        }

        .colon {
            width: 2%;
        }

        .value {
            width: 63%;
            border-bottom: 1px solid #000;
            text-transform: uppercase;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }

        .signature-table td {
            vertical-align: center;
            padding: 0 10px;
        }

        .signature-user {
            height: 50px;
        }

        .signature-label {
            font-weight: bold;
            font-size: 11pt;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .date-label {
            font-size: 10.5pt;
            margin-top: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <!-- Logo & Faculty based on system default faculty -->
        <img src="{{ public_path('assets/images/logo-faculty/ftmk.png') }}" alt="FTMK Logo">
        <h2>FAKULTI TEKNOLOGI MAKLUMAT DAN KOMUNIKASI</h2>
        <h3>UNIVERSITI TEKNIKAL MALAYSIA MELAKA</h3>
        <div class="line-title"></div>
        <!-- Activity Name will be based on user selection [Activity Table] -->
        <div class="form-title">{{ $form_title }}</div>
    </div>

    <!-- Student Info [ Dynamic Field Here ] -->
    <table class="info-table">
        @foreach ($formfields as $ff)
            <tr>
                <td class="label">{{ $ff->ff_label }}</td>
                <td class="colon">:</td>
                <td class="value">{{ $ff->ff_datakey }}</td>
            </tr>
        @endforeach
    </table>

    <!-- Signature Section -->
    <table class="signature-table">
        <tr>
            <td style="width: 33.33%; height: 120px;  border: 1px solid #000; border-bottom: none;"></td>
            <td style="width: 33.33%; height: 120px;  border: 1px solid #000; border-bottom: none;"></td>
            <td style="width: 33.33%; height: 120px;  border: 1px solid #000; border-bottom: none;"></td>
        </tr>
        <tr class="signature-user">
            <td class="signature-label">Student’s Signature</td>
            <td class="signature-label">Supervisor’s Signature & Stamp</td>
            <td class="signature-label">Deputy Dean (Research & Postgraduate)</td>
        </tr>
        <tr>
            <td class="signature-label">
                <div class="date-label">Date:</div>
            </td>
            <td class="signature-label">
                <div class="date-label">Date:</div>
            </td>
            <td class="signature-label">
                <div class="date-label">Date:</div>
            </td>
        </tr>
    </table>

</body>

</html>
 --}}




{{-- // // DESIGN PART
            // function appendFormField(label, datakey, order, ff_id = null) {
            //     const id = ff_id ?? `temp_${fieldIdCounter++}`;
            //     const shortLabel = truncateText(stripHTML(label), 10);
            //     const item = `
        //         <li class="list-group-item draggable-item" data-id="${id}">
        //             <div class="d-flex align-items-center gap-2 mb-2">
        //                 <span class="drag-handle text-secondary" title="Drag to reorder">
        //                     <i class="ti ti-drag-drop fs-5"></i>
        //                 </span>
        //                 <div>
        //                     <strong>${shortLabel}</strong>
        //                     <div class="text-muted small">[${datakey ?? 'Others'}]</div>
        //                 </div>
        //             </div>
        //             <div class="row g-1">

        //                 <div class="col-2">
        //                     <button class="btn btn-sm btn-outline-primary w-100 move-up-btn" data-id="${id}" title="Move Up">
        //                         <i class="ti ti-chevron-up"></i>
        //                     </button>
        //                 </div>
        //                 <div class="col-2">
        //                     <button class="btn btn-sm btn-outline-primary w-100 move-down-btn" data-id="${id}" title="Move Down">
        //                         <i class="ti ti-chevron-down"></i>
        //                     </button>
        //                 </div>
        //                 <div class="col-2">
        //                     <button class="btn btn-sm btn-outline-secondary w-100 update-field-btn" data-id="${id}" data-label="${label}" data-key="${datakey}">
        //                         <i class="ti ti-edit-circle"></i>
        //                     </button>
        //                 </div>
        //                 <div class="col-2">
        //                     <button class="btn btn-sm btn-outline-secondary w-100 copy-field-btn" data-id="${id}" data-key="${datakey}">
        //                         <i class="ti ti-copy"></i>
        //                     </button>
        //                 </div>
        //                 <div class="col-4">
        //                     <button class="btn btn-sm btn-outline-danger w-100 delete-field-btn" data-id="${id}">
        //                         <i class="ti ti-trash"></i>
        //                     </button>
        //                 </div>

        //             </div>
        //         </li>
        //     `;
            //     $('#fieldList').append(item);
            // } --}}



<!-- Add this inside your formFieldModal -->
{{-- <div class="table-settings-group mb-3" style="display: none;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Table Configuration</h5>
        <div>
            <button type="button" id="add-table-row" class="btn btn-sm btn-primary me-1">
                <i class="ph ph-plus"></i> Add Row
            </button>
            <button type="button" id="add-table-col" class="btn btn-sm btn-primary">
                <i class="ph ph-plus"></i> Add Column
            </button>
        </div>
    </div>
    
    <div class="table-responsive border rounded p-2 bg-light">
        <table id="table-builder" class="table table-bordered mb-0">
            <thead>
                <tr id="table-headers">
                    <th width="50" class="text-center">#</th>
                    <!-- Column headers will be added here -->
                </tr>
            </thead>
            <tbody id="table-body">
                <tr class="no-rows">
                    <td colspan="100" class="text-center py-4 text-muted">
                        <i class="ph ph-table ph-2x mb-2"></i>
                        <p>No columns or rows added yet</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="mt-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Default Rows</label>
                <input type="number" min="0" class="form-control" id="ff_table_default_rows" value="0">
                <div class="form-text">Number of rows shown when form is first loaded</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Minimum Rows</label>
                <input type="number" min="0" class="form-control" id="ff_table_min_rows" value="0">
                <div class="form-text">Minimum rows user must fill before submitting</div>
            </div>
        </div>
    </div>
</div>

<!-- Column Configuration Modal -->
<div class="modal fade" id="columnConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configure Column</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="columnConfigContent">
                <!-- Content will be loaded here dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveColumnConfig">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Cell Configuration Modal -->
<div class="modal fade" id="cellConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Default Value</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cellConfigContent">
                <!-- Content will be loaded here dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCellConfig">Save</button>
            </div>
        </div>
    </div>
</div>

<style>
    .table-builder-cell {
        position: relative;
        min-width: 150px;
        background: white;
    }
    
    .cell-actions {
        position: absolute;
        top: 5px;
        right: 5px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .table-builder-cell:hover .cell-actions {
        opacity: 1;
    }
    
    .cell-type-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        font-size: 0.7rem;
        opacity: 0.7;
        background: rgba(0,0,0,0.05);
        padding: 2px 5px;
        border-radius: 3px;
    }
    
    .column-config-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
    }
</style> --}}

{{-- // Table configuration object
let tableConfig = {
columns: [],
rows: [],
defaultRows: 0,
minRows: 0
};

// Column types with configuration options
const columnTypes = [
{ value: 'text', label: 'Text', icon: 'ph ph-text-aa', hasOptions: false },
{ value: 'textarea', label: 'Textarea', icon: 'ph ph-textbox', hasOptions: false },
{ value: 'number', label: 'Number', icon: 'ph ph-number-square-one', hasOptions: false },
{ value: 'select', label: 'Dropdown', icon: 'ph ph-caret-down', hasOptions: true },
{ value: 'checkbox', label: 'Checkbox', icon: 'ph ph-check-square', hasOptions: false },
{ value: 'radio', label: 'Radio', icon: 'ph ph-radio-button', hasOptions: true },
{ value: 'date', label: 'Date', icon: 'ph ph-calendar', hasOptions: false },
{ value: 'static', label: 'Static Text', icon: 'ph ph-text-outdent', hasOptions: false }
];

// Current column/cell being edited
let currentColumnIndex = null;
let currentCellPosition = { row: null, col: null };

// Initialize table builder
function initTableBuilder() {
tableConfig = {
columns: [],
rows: [],
defaultRows: 0,
minRows: 0
};
renderTable();
}

// Render the table in the editor
function renderTable() {
const $headers = $('#table-headers');
const $body = $('#table-body');

// Clear existing content
$headers.find('th:gt(0)').remove();
$body.empty();

// Add column headers
tableConfig.columns.forEach((col, colIndex) => {
$headers.append(`
<th class="table-builder-cell">
    <div class="fw-normal">${escapeHtml(col.header || `Column ${colIndex+1}`)}</div>
    <span class="cell-type-badge">${columnTypes.find(t => t.value === col.type).label}</span>
    <div class="cell-actions">
        <button type="button" class="btn btn-xs btn-icon btn-light config-col" data-index="${colIndex}">
            <i class="ph ph-gear"></i>
        </button>
    </div>
</th>
`);
});

// Add column header for row actions
if (tableConfig.columns.length > 0) {
$headers.append('<th width="50" class="text-center">Actions</th>');
}

// Add rows if they exist
if (tableConfig.columns.length === 0) {
$body.html(`
<tr class="no-rows">
    <td colspan="100" class="text-center py-4 text-muted">
        <i class="ph ph-table ph-2x mb-2"></i>
        <p>No columns or rows added yet</p>
    </td>
</tr>
`);
return;
}

// Add rows
tableConfig.rows.forEach((row, rowIndex) => {
const $row = $(`<tr data-row="${rowIndex}"></tr>`);
$row.append(`<td class="align-middle text-center">${rowIndex+1}</td>`);

tableConfig.columns.forEach((col, colIndex) => {
const cellValue = row[colIndex]?.value || '';
const displayValue = formatCellValue(col, cellValue);

$row.append(`
<td class="table-builder-cell">
    <div class="cell-content">${displayValue}</div>
    <div class="cell-actions">
        <button type="button" class="btn btn-xs btn-icon btn-light config-cell" data-row="${rowIndex}"
            data-col="${colIndex}">
            <i class="ph ph-pencil-simple"></i>
        </button>
    </div>
</td>
`);
});

$row.append(`
<td class="align-middle text-center">
    <button type="button" class="btn btn-sm btn-icon btn-danger remove-row" data-row="${rowIndex}">
        <i class="ph ph-trash"></i>
    </button>
</td>
`);

$body.append($row);
});

// Add "add row" button if there are columns
if (tableConfig.columns.length > 0 && tableConfig.rows.length === 0) {
$body.html(`
<tr class="no-rows">
    <td colspan="${tableConfig.columns.length + 2}" class="text-center py-4">
        <button type="button" class="btn btn-primary" id="add-first-row">
            <i class="ph ph-plus me-1"></i> Add First Row
        </button>
    </td>
</tr>
`);
}
}

// Format cell value for display in editor
function formatCellValue(column, value) {
if (column.type === 'checkbox') {
return value ? '<i class="ph ph-check-square-fill text-success"></i> Yes' : '<i class="ph ph-square"></i> No';
}
if (column.type === 'radio' || column.type === 'select') {
return value || '-';
}
return value || '<span class="text-muted">Empty</span>';
}

// Show column configuration modal
function showColumnConfig(colIndex) {
const col = tableConfig.columns[colIndex] || {
header: '',
type: 'text',
options: '',
required: false,
placeholder: ''
};

currentColumnIndex = colIndex;

const modalContent = `
<div class="column-config-card">
    <div class="mb-3">
        <label class="form-label">Column Header</label>
        <input type="text" class="form-control col-header" value="${escapeHtml(col.header)}"
            placeholder="e.g., Criteria">
    </div>

    <div class="mb-3">
        <label class="form-label">Field Type</label>
        <select class="form-select col-type">
            ${columnTypes.map(type =>
            `<option value="${type.value}" ${col.type===type.value ? 'selected' : '' }>
                ${type.label}
            </option>`
            ).join('')}
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Placeholder (optional)</label>
        <input type="text" class="form-control col-placeholder" value="${escapeHtml(col.placeholder || '')}"
            placeholder="Hint text for users">
    </div>

    <div class="mb-3 col-options-group"
        style="${col.type === 'select' || col.type === 'radio' ? '' : 'display: none;'}">
        <label class="form-label">Options (one per line)</label>
        <textarea class="form-control col-options" rows="3" placeholder="Option 1\nOption 2">${escapeHtml(col.options || '')}</textarea>
    </div>

    <div class="d-flex justify-content-between">
        <div class="form-check form-switch mb-3">
            <input type="checkbox" class="form-check-input col-required" ${col.required ? 'checked' : '' }>
            <label class="form-check-label">Required Field</label>
        </div>
        <button type="button" class="btn btn-danger btn-sm remove-col-btn" data-index="${colIndex}">
            <i class="ph ph-trash me-1"></i> Remove Column
        </button>
    </div>
</div>
`;

$('#columnConfigContent').html(modalContent);
const modal = new bootstrap.Modal(document.getElementById('columnConfigModal'));
modal.show();

// Handle type change to show/hide options
$('#columnConfigModal .col-type').on('change', function() {
const $optionsGroup = $('#columnConfigModal .col-options-group');
if ($(this).val() === 'select' || $(this).val() === 'radio') {
$optionsGroup.show();
} else {
$optionsGroup.hide();
}
});
}

// Show cell configuration modal
function showCellConfig(rowIndex, colIndex) {
const col = tableConfig.columns[colIndex];
const cell = tableConfig.rows[rowIndex]?.[colIndex] || { value: '' };

currentCellPosition = { row: rowIndex, col: colIndex };

let inputField = '';

if (col.type === 'select' || col.type === 'radio') {
const options = col.options.split('\n').filter(opt => opt.trim() !== '');
inputField = `
<select class="form-select cell-value">
    <option value="">-- Select --</option>
    ${options.map(opt =>
    `<option value="${escapeHtml(opt)}" ${cell.value===opt ? 'selected' : '' }>
        ${escapeHtml(opt)}
    </option>`
    ).join('')}
</select>
`;
}
else if (col.type === 'checkbox') {
inputField = `
<div class="form-check form-switch">
    <input type="checkbox" class="form-check-input cell-value" ${cell.value ? 'checked' : '' }>
    <label class="form-check-label">${cell.value ? 'Checked' : 'Unchecked'}</label>
</div>
`;
}
else {
inputField = `
<input type="${col.type === 'number' ? 'number' : 'text'}" class="form-control cell-value"
    value="${escapeHtml(cell.value)}" placeholder="${escapeHtml(col.placeholder || '')}">
`;
}

$('#cellConfigContent').html(`
<div class="mb-3">
    <label class="form-label">${escapeHtml(col.header || `Column ${colIndex+1}`)}</label>
    ${inputField}
</div>
<div class="form-text">This value will be pre-filled in the form</div>
`);

const modal = new bootstrap.Modal(document.getElementById('cellConfigModal'));
modal.show();

if (col.type === 'checkbox') {
$('#cellConfigModal .cell-value').on('change', function() {
$('#cellConfigModal .form-check-label').text(this.checked ? 'Checked' : 'Unchecked');
});
}
}

// Save column configuration
$('#saveColumnConfig').click(function() {
const colIndex = currentColumnIndex;

if (colIndex !== null) {
tableConfig.columns[colIndex] = {
header: $('#columnConfigModal .col-header').val(),
type: $('#columnConfigModal .col-type').val(),
placeholder: $('#columnConfigModal .col-placeholder').val(),
options: $('#columnConfigModal .col-options').val() || '',
required: $('#columnConfigModal .col-required').is(':checked')
};

renderTable();
$('#columnConfigModal').modal('hide');
}
});

// Save cell configuration
$('#saveCellConfig').click(function() {
const { row, col } = currentCellPosition;
const colType = tableConfig.columns[col].type;
let value = '';

if (colType === 'checkbox') {
value = $('#cellConfigModal .cell-value').is(':checked');
}
else if (colType === 'select' || colType === 'radio') {
value = $('#cellConfigModal .cell-value').val();
}
else {
value = $('#cellConfigModal .cell-value').val();
}

// Initialize row if needed
if (!tableConfig.rows[row]) {
tableConfig.rows[row] = [];
}

// Initialize cell if needed
if (!tableConfig.rows[row][col]) {
tableConfig.rows[row][col] = {};
}

// Save value
tableConfig.rows[row][col].value = value;
renderTable();
$('#cellConfigModal').modal('hide');
});

// Helper function to escape HTML
function escapeHtml(unsafe) {
if (!unsafe) return '';
return unsafe
.replace(/&/g, "&amp;")
.replace(/</g, "&lt;" ) .replace( />/g, "&gt;")
.replace(/"/g, "&quot;")
.replace(/'/g, "&#039;");
}

// Add new column
$('#add-table-col').click(function() {
const colIndex = tableConfig.columns.length;
tableConfig.columns.push({
header: `Column ${colIndex+1}`,
type: 'text',
placeholder: '',
options: '',
required: false
});
renderTable();
showColumnConfig(colIndex);
});

// Add new row
$('#add-table-row').click(function() {
addTableRow();
});

$(document).on('click', '#add-first-row', function() {
addTableRow();
});

function addTableRow() {
const rowIndex = tableConfig.rows.length;
tableConfig.rows.push([]);
renderTable();
}

// Remove column
$(document).on('click', '.remove-col-btn', function() {
const colIndex = $(this).data('index');
tableConfig.columns.splice(colIndex, 1);

// Remove this column from all rows
tableConfig.rows.forEach(row => {
if (row[colIndex]) {
row.splice(colIndex, 1);
}
});

renderTable();
$('#columnConfigModal').modal('hide');
});

// Remove row
$(document).on('click', '.remove-row', function() {
const rowIndex = $(this).closest('tr').data('row');
tableConfig.rows.splice(rowIndex, 1);
renderTable();
});

// Configure column
$(document).on('click', '.config-col', function() {
const $th = $(this).closest('th');
const colIndex = $('#table-headers th').index($th) - 1; // -1 for row number column
showColumnConfig(colIndex);
});

// Configure cell
$(document).on('click', '.config-cell', function() {
const rowIndex = $(this).data('row');
const colIndex = $(this).data('col');
showCellConfig(rowIndex, colIndex);
});

// Update category change handler
$('#ff_category').on('change', function() {
var category = $(this).val();

resetFormSections();

if (category == 1) {
$('.input-field-group').show();
$('#ff_label').parent().show();
$('#ff_label-ckeditor').parent().hide();
$('#ff_component_type').trigger('change');
}
if (category == 2) {
$('.output-field-group').show();
$('#ff_label').parent().show();
$('#ff_label-ckeditor').parent().hide();
}
if (category == 3) {
$('#ff_label').parent().show();
$('#ff_label-ckeditor').parent().hide();
}
if (category == 4) {
$('#ff_label').parent().hide();
$('#ff_label-ckeditor').parent().show();
}
if (category == 5) {
$('.table-settings-group').show();
$('#ff_label').parent().show();
$('#ff_label-ckeditor').parent().hide();
}
if (category == 6) {
$('.signature-field-group').show();
$('#ff_label').parent().show();
$('#ff_label-ckeditor').parent().hide();
}
});

// Update resetFormSections
function resetFormSections() {
$('.input-field-group').hide();
$('.output-field-group').hide();
$('.signature-field-group').hide();
$('.table-settings-group').hide();

$('#ff_label').parent().hide();
$('#ff_label-ckeditor').parent().hide();

$('#ff_datakey').prop('disabled', true);
initTableBuilder();
}

// Update modalInit function
function modalInit(option, isOpen) {
if (option == "add") {
// ... existing reset code ...
initTableBuilder();
}
// ... rest of existing code ...
}

// Prepare table data for submission
function prepareTableData() {
return {
columns: tableConfig.columns,
rows: tableConfig.rows,
defaultRows: $('#ff_table_default_rows').val() || 0,
minRows: $('#ff_table_min_rows').val() || 0
};
}

// In your add/submit functions:
$('#addFormFieldBtn-submit').click(function() {
// ... existing code ...

if (rowCategory == "5") {
const tableData = prepareTableData();

if (tableData.columns.length === 0) {
showToast('error', 'Please add at least one column to the table.');
return;
}

requestData.ff_table_structure = JSON.stringify({
columns: tableData.columns,
defaultRows: tableData.defaultRows,
minRows: tableData.minRows
});

requestData.ff_table_data = JSON.stringify(tableData.rows);
}

// ... rest of existing code ...
});

// For update function (similar to add)
$('#updateFormFieldBtn-submit').click(function() {
// ... existing code ...

if (rowCategory == "5") {
// Same as add function above
}

// ... rest of existing code ...
});

// For edit population
function populateTableData(data) {
try {
if (data && data.columns) {
tableConfig.columns = data.columns;
tableConfig.rows = data.rows || [];
tableConfig.defaultRows = data.defaultRows || 0;
tableConfig.minRows = data.minRows || 0;

$('#ff_table_default_rows').val(tableConfig.defaultRows);
$('#ff_table_min_rows').val(tableConfig.minRows);

renderTable();
}
} catch (e) {
console.error('Error populating table data', e);
}
}

// In your copy and update field loading:
$.ajax({
url: "{{ route('get-single-form-field-data-get') }}",
method: "GET",
data: { ff_id: id },
success: function(response) {
// ... existing code ...

if (response.fields.ff_table_structure) {
try {
const tableStructure = JSON.parse(response.fields.ff_table_structure);
const tableData = JSON.parse(response.fields.ff_table_data || '[]');

populateTableData({
columns: tableStructure.columns || [],
rows: tableData,
defaultRows: tableStructure.defaultRows || 0,
minRows: tableStructure.minRows || 0
});
} catch (e) {
console.error('Error parsing table data', e);
}
}

// ... rest of existing code ...
}
});

// Initialize on document ready
$(document).ready(function() {
initTableBuilder();
}); --}}


{{-- @extends('staff.layouts.main')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript: void(0)">My Supervision</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Submission</a></li>
                                <li class="breadcrumb-item" aria-current="page">Submission Management</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Submission Management</h2>
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

                <!-- [ Submission Management ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- [ Option Section ] start -->
                            <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-4"
                                    id="clearSelectionBtn">
                                    0 selected <i class="ti ti-x f-18"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-4"
                                    data-bs-toggle="modal" data-bs-target="#multipleSettingModal"
                                    id="updatemultipleModalBtn" title="Update Submission">
                                    <i class="ti ti-edit-circle f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Update Submission
                                    </span>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-4"
                                    data-bs-toggle="modal" data-bs-target="#archiveMultipleModal"
                                    id="archivemultipleModalBtn" title="Archive">
                                    <i class="ti ti-archive f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Archive
                                    </span>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-4"
                                    data-bs-toggle="modal" data-bs-target="#unarchiveMultipleModal"
                                    id="unarchivemultipleModalBtn" title="Unarchive">
                                    <i class="ti ti-history f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Unarchive
                                    </span>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2 d-none mb-4"
                                    id="downloadmultipleModalBtn" title="Download (.zip)">
                                    <i class="ti ti-arrow-bar-to-down f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Download (.zip)
                                    </span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <!-- [ Filter Section ] Start -->
                            <div class="row g-3 align-items-center mb-3">

                                <div class="col-sm-12 col-md-4">
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

                                <div class="col-sm-12 col-md-4">
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
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearSemFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4">
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

                                <div class="col-sm-12 col-md-4">
                                    <div class="input-group">
                                        <select id="fil_activity_id" class="form-select">
                                            <option value="">-- Select Activity --</option>
                                            @foreach ($acts as $fil)
                                                <option value="{{ $fil->id }}">{{ $fil->act_name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearActivityFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4">
                                    <div class="input-group">
                                        <select id="fil_document_id" class="form-select">
                                            <option value="">-- Select Document --</option>
                                            @foreach ($docs as $fil)
                                                @if ($fil->doc_status == 1)
                                                    <option value="{{ $fil->id }}">{{ $fil->doc_name }}</option>
                                                @elseif($fil->doc_status == 2)
                                                    <option value="{{ $fil->id }}" class="bg-light-danger">
                                                        {{ $fil->doc_name }} [Inactive]
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="clearDocumentFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4">
                                    <div class="input-group">
                                        <select id="fil_status" class="form-select">
                                            <option value="">-- Select Status --</option>
                                            <option value="1">No Attempt</option>
                                            <option value="2">Locked</option>
                                            <option value="3">Submitted</option>
                                            <option value="4">Overdue</option>
                                            <option value="5">Archive</option>
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
                                            <th scope="col">Document</th>
                                            <th scope="col">Due Date</th>
                                            <th scope="col">Submission Date</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Activity</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- [ Multiple Submission Update Modal ] start -->
                <div class="modal fade" id="multipleSettingModal" tabindex="-1" aria-labelledby="multipleSettingModal"
                    aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title" id="multipleSettingModalLabel">Submission Setting</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <!-- Due Date Input -->
                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                        <div class="mb-3">
                                            <label for="submission_duedate_ups" class="form-label">
                                                Due Date
                                            </label>
                                            <input type="datetime-local"
                                                class="form-control @error('submission_duedate_ups') is-invalid @enderror"
                                                id="submission_duedate_ups" name="submission_duedate_ups">
                                            @error('submission_duedate_ups')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Status Input -->
                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                        <div class="mb-3">
                                            <label for="submission_status_ups" class="form-label">
                                                Status
                                            </label>
                                            <select
                                                class="form-select @error('submission_status_ups') is-invalid @enderror"
                                                name="submission_status_ups" id="submission_status_ups">
                                                <option value ="" selected>- Select Status -</option>
                                                <option value ="1">Open Submission</option>
                                                <option value ="2">Locked</option>
                                            </select>
                                            @error('submission_status_ups')
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
                                    <button type="submit" class="btn btn-primary" id="multipleSubmissionUpdateBtn">Save
                                        Changes</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- [ Multiple Submission Update Modal ] end -->

                <!-- [ Archive Multiple Submission Modal ] start -->
                <div class="modal fade" id="archiveMultipleModal" data-bs-keyboard="false" tabindex="-1"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12 mb-4">
                                        <div class="d-flex justify-content-center align-items-center mb-3">
                                            <i class="ti ti-archive text-secondary" style="font-size: 100px"></i>
                                        </div>

                                    </div>
                                    <div class="col-sm-12">
                                        <div class="d-flex justify-content-center align-items-center text-center">
                                            <h2>Are you sure to archive the selected submission ?</h2>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <p class="fw-normal f-18 text-center">You can revert this action.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="d-flex justify-content-between gap-3 align-items-center">
                                            <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-secondary w-100"
                                                id="multipleSubmissionArchiveBtn">
                                                Archive
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Archive Multiple Submission Modal ] end -->

                <!-- [ Unarchive Multiple Submission Modal ] start -->
                <div class="modal fade" id="unarchiveMultipleModal" data-bs-keyboard="false" tabindex="-1"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12 mb-4">
                                        <div class="d-flex justify-content-center align-items-center mb-3">
                                            <i class="ti ti-history text-primary" style="font-size: 100px"></i>
                                        </div>

                                    </div>
                                    <div class="col-sm-12">
                                        <div class="d-flex justify-content-center align-items-center text-center">
                                            <h2>Are you sure to unarchive the selected submission ?</h2>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <p class="fw-normal f-18 text-center">You can archive back if needed.</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="d-flex justify-content-between gap-3 align-items-center">
                                            <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary w-100"
                                                id="multipleSubmissionUnarchiveBtn">
                                                Unarchive
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Unarchive Multiple Submission Modal ] end -->

                @foreach ($subs as $upd)
                    <!-- [ Update Modal ] start -->
                    <form action="{{ route('update-submission-post', Crypt::encrypt($upd->submission_id)) }}"
                        method="POST">
                        @csrf
                        <div class="modal fade" id="settingModal-{{ $upd->submission_id }}" tabindex="-1"
                            aria-labelledby="settingModal" aria-hidden="true">
                            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="settingModalLabel">Submission Setting</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">

                                            <!-- Student Name -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="student_name_up" class="form-label">Student Name </label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $upd->student_name }}" readonly>
                                                </div>
                                            </div>

                                            <!-- Document Name -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="student_email_up" class="form-label">Document</label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $upd->document_name }}" readonly>
                                                </div>
                                            </div>

                                            <!-- Due Date Input -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="submission_duedate_up" class="form-label">Due Date
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="datetime-local"
                                                        class="form-control @error('submission_duedate_up') is-invalid @enderror"
                                                        id="submission_duedate_up" name="submission_duedate_up"
                                                        placeholder="Enter Matric Number"
                                                        value="{{ $upd->submission_duedate }}" required>
                                                    @error('submission_duedate_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Status Input -->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="submission_status_up" class="form-label">
                                                        Status <span class="text-danger">*</span>
                                                    </label>
                                                    <select
                                                        class="form-select @error('submission_status_up') is-invalid @enderror"
                                                        name="submission_status_up" id="submission_status_up" required>
                                                        <option value ="" selected>- Select Status -</option>
                                                        @if ($upd->submission_status == 1 || $upd->submission_status == 2 || $upd->submission_status == 5)
                                                            <option value ="1"
                                                                @if ($upd->submission_status == 1) selected @endif>Open
                                                                Submission
                                                            </option>
                                                            <option value ="2"
                                                                @if ($upd->submission_status == 2) selected @endif>Locked
                                                            </option>
                                                        @elseif($upd->submission_status == 3)
                                                            <option value ="3" selected>
                                                                Submitted
                                                            </option>
                                                        @elseif($upd->submission_status == 4)
                                                            <option value ="2">
                                                                Locked
                                                            </option>
                                                            <option value ="4"selected>
                                                                Overdue
                                                            </option>
                                                        @endif
                                                    </select>
                                                    @error('submission_status_up')
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
                    <!-- [ Update Modal ] end -->

                    <!-- [ Archive Modal ] start -->
                    <div class="modal fade" id="deleteModal-{{ $upd->submission_id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 mb-4">
                                            <div class="d-flex justify-content-center align-items-center mb-3">
                                                <i class="ti ti-archive text-secondary" style="font-size: 100px"></i>
                                            </div>

                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-center align-items-center text-center">
                                                <h2>Are you sure to archive this submission ?</h2>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 mb-3">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <p class="fw-normal f-18 text-center">You can revert this action.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <a href="{{ route('archive-submission-get', ['id' => Crypt::encrypt($upd->submission_id), 'opt' => 1]) }}"
                                                    class="btn btn-secondary w-100">Archive</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Archive Modal ] end -->

                    <!-- [ Unarchive Modal ] start -->
                    <div class="modal fade" id="unarchiveModal-{{ $upd->submission_id }}" data-bs-keyboard="false"
                        tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 mb-4">
                                            <div class="d-flex justify-content-center align-items-center mb-3">
                                                <i class="ti ti-history text-primary" style="font-size: 100px"></i>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-center align-items-center text-center">
                                                <h2>Are you sure to unarchive this submission ?</h2>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 mb-3">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <p class="fw-normal f-18 text-center">You can archive back if needed.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <a href="{{ route('archive-submission-get', ['id' => Crypt::encrypt($upd->submission_id), 'opt' => 2]) }}"
                                                    class="btn btn-primary w-100">Unarchive</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Unarchive Modal ] end -->
                @endforeach

                <!-- [ Submission Management ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            // DATATABLE : SUBMISSION
            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('my-supervision-submission-management') }}",
                    data: function(d) {
                        d.faculty = $('#fil_faculty_id').val();
                        d.programme = $('#fil_programme_id').val();
                        d.semester = $('#fil_semester_id').val();
                        d.activity = $('#fil_activity_id').val();
                        d.document = $('#fil_document_id').val();
                        d.status = $('#fil_status').val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'student_photo',
                        name: 'student_photo'
                    },
                    {
                        data: 'document_name',
                        name: 'document_name'
                    },
                    {
                        data: 'submission_duedate',
                        name: 'submission_duedate'
                    },
                    {
                        data: 'submission_date',
                        name: 'submission_date'
                    },
                    {
                        data: 'submission_status',
                        name: 'submission_status'
                    },
                    {
                        data: 'activity_name',
                        name: 'activity_name',
                        visible: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                rowGroup: {
                    dataSrc: 'activity_name',
                    startRender: function(rows, group) {
                        return $('<tr/>')
                            .append(
                                '<td colspan="7" class="bg-light text-center"> <span class="fw-semibold text-uppercase me-2">' +
                                group + '</span> <span class="badge bg-primary">' + rows.count() +
                                '</span></td>');
                    }
                }

            });

            var modalToShow = "{{ session('modal') }}";
            if (modalToShow) {
                var modalElement = $("#" + modalToShow);
                if (modalElement.length) {
                    var modal = new bootstrap.Modal(modalElement[0]);
                    modal.show();
                }
            }

            // FILTER : FACULTY
            $('#fil_faculty_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearFacFilter').click(function() {
                $('#fil_faculty_id').val('').change();
            });

            // FILTER : PROGRAMME
            $('#fil_programme_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearProgFilter').click(function() {
                $('#fil_programme_id').val('').change();
            });

            // FILTER : SEMESTER
            $('#fil_semester_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearSemFilter').click(function() {
                $('#fil_semester_id').val('').change();
            });

            // FILTER : ACTIVITY
            $('#fil_activity_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearActivityFilter').click(function() {
                $('#fil_activity_id').val('').change();
            });

            // FILTER : DOCUMENT
            $('#fil_document_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearDocumentFilter').click(function() {
                $('#fil_document_id').val('').change();
            });


            // FILTER : STATUS
            $('#fil_status').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
                clearBtn.trigger('click');

            });

            $('#clearStatusFilter').click(function() {
                $('#fil_status').val('').change();
            });

            /* SELECT : MULTIPLE STUDENT SELECT */
            const clearBtn = $("#clearSelectionBtn");
            const updatemultipleModalBtn = $("#updatemultipleModalBtn");
            const archivemultipleModalBtn = $('#archivemultipleModalBtn');
            const unarchivemultipleModalBtn = $('#unarchivemultipleModalBtn');
            const downloadmultipleModalBtn = $('#downloadmultipleModalBtn');
            const multipleSubmissionUpdateBtn = $("#multipleSubmissionUpdateBtn");
            const multipleSubmissionArchiveBtn = $("#multipleSubmissionArchiveBtn");
            const multipleSubmissionUnarchiveBtn = $("#multipleSubmissionUnarchiveBtn");

            let selectedIds = new Set();

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

            $(document).on("change", ".user-checkbox", function() {
                let id = $(this).val();
                if ($(this).prop("checked")) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
                toggleSelectButton();
            });

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
                let hasSubmitted = false;
                let hasArchived = false;

                $(".user-checkbox:checked").each(function() {
                    const row = $(this).closest("tr");
                    const table = $('.data-table').DataTable();
                    const rowIndex = table.row(row).index();

                    const submitted = table.cell(rowIndex, 5).data();
                    const archive = table.cell(rowIndex, 5).data();
                    const subDate = table.cell(rowIndex, 4).data();

                    if (submitted.trim().toLowerCase() !=
                        '<span class="badge bg-light-success">submitted</span>' && subDate.trim() == '-') {
                        hasSubmitted = false;
                    } else {
                        hasSubmitted = true;
                    }

                    if (archive.trim().toLowerCase() !=
                        '<span class="badge bg-secondary">archive</span>') {
                        hasArchived = false;
                    } else {
                        hasArchived = true;
                    }
                });


                updatemultipleModalBtn.prop("disabled", hasArchived);
                updatemultipleModalBtn.toggleClass("d-none", selectedCount === 0 || hasArchived);

                archivemultipleModalBtn.prop("disabled", hasArchived);
                archivemultipleModalBtn.toggleClass("d-none", selectedCount === 0 || hasArchived);

                unarchivemultipleModalBtn.prop("disabled", !hasArchived);
                unarchivemultipleModalBtn.toggleClass("d-none", selectedCount === 0 || !hasArchived);

                downloadmultipleModalBtn.prop("disabled", !hasSubmitted);
                downloadmultipleModalBtn.toggleClass("d-none", selectedCount === 0);

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

            multipleSubmissionUpdateBtn.on('click', function() {
                const $button = $(this);
                const duedate = $('#submission_duedate_ups').val();
                const status = $('#submission_status_ups').val();

                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Saving...'
                    );

                    $.ajax({
                        url: "{{ route('update-multiple-submission-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedIds,
                            submission_status_ups: status,
                            submission_duedate_ups: duedate,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#submission_duedate_ups').val('');
                            $('#submission_status_ups').val('');
                            $('#multipleSettingModal').modal('hide');
                            clearBtn.trigger('click');
                            $('.data-table').DataTable().ajax
                                .reload();

                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert("Error: " + (xhr.responseJSON?.message || xhr.responseText));
                        },
                        complete: function() {
                            $button.prop('disabled', false).html('Save Changes');
                        }
                    });
                } else {
                    alert("No valid data selected for submission update.");
                }
            });

            multipleSubmissionArchiveBtn.on('click', function() {
                const $button = $(this);

                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Archiving...'
                    );

                    $.ajax({
                        url: "{{ route('archive-multiple-submission-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedIds,
                            option: 1,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#archiveMultipleModal').modal('hide');
                            clearBtn.trigger('click');
                            $('.data-table').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert("Error: " + (xhr.responseJSON?.message || xhr.responseText));
                        },
                        complete: function() {
                            $button.prop('disabled', false).html('Archive');
                        }
                    });
                } else {
                    alert("No valid data selected for submission archive.");
                }
            });

            multipleSubmissionUnarchiveBtn.on('click', function() {
                const $button = $(this);

                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {
                    $button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Unarchiving...'
                    );

                    $.ajax({
                        url: "{{ route('archive-multiple-submission-post') }}",
                        type: "POST",
                        data: {
                            selectedIds: selectedIds,
                            option: 2,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#unarchiveMultipleModal').modal('hide');
                            clearBtn.trigger('click');
                            $('.data-table').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert("Error: " + (xhr.responseJSON?.message || xhr.responseText));
                        },
                        complete: function() {
                            $button.prop('disabled', false).html('Unarchive');
                        }
                    });
                } else {
                    alert("No valid data selected for submission unarchive.");
                }
            });

            downloadmultipleModalBtn.on('click', function() {
                let selectedIds = $(".user-checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length > 0) {

                    let idsParam = encodeURIComponent(JSON.stringify(selectedIds));
                    window.location.href = "{{ route('download-multiple-submission-get') }}?ids=" +
                        idsParam;
                    clearBtn.trigger('click');
                    $('.data-table').DataTable().ajax.reload();
                } else {
                    alert("No valid data selected for submission download.");
                }
            });
        });
    </script>
@endsection --}}



{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <title>e-PostGrad | {{ $title }}</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=0.9, maximum-scale=1.0, user-scalable=no, minimal-ui">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="-" />
    <meta name="keywords" content="-" />
    <meta name="author" content="ZikriMzk" />

    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/images/logo-test-white.png" type="image/x-icon" />
    <!-- [Font] Family -->
    <link rel="stylesheet" href="../assets/fonts/inter/inter.css" id="main-font-link" />
    <!-- [phosphor Icons] https://phosphoricons.com/ -->
    <link rel="stylesheet" href="../assets/fonts/phosphor/duotone/style.css" />
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <!-- [Feather Icons] https://feathericons.com -->
    <link rel="stylesheet" href="../assets/fonts/feather.css" />
    <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
    <link rel="stylesheet" href="../assets/fonts/fontawesome.css" />
    <!-- [Material Icons] https://fonts.google.com/icons -->
    <link rel="stylesheet" href="../assets/fonts/material.css" />
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/style-preset.css" />
    <link rel="stylesheet" href="../assets/css/landing.css" />

</head>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
    data-pc-theme="light" class="landing-page">


    <div class="auth-main">
        <div class="auth-wrapper v1">
            <div class="auth-form">
                <div class="text-center">
                    <a href=""><img src="../assets/images/logo-utem.PNG" alt="img" class="img-fluid"
                            width="120" height="60" /></a>
                </div>

                <div class="card my-5 shadow shadow-lg">
                    <form action="{{ route('user-authenticate') }}" method="POST" autocomplete="off">
                        @csrf
                        <div class="card-body">
                            <div class="text-center mt-3 mb-4">
                                <h3 class="text-center f-w-500 mb-1">Login</h3>
                                <h5 class="text-center text-muted">e-PostGrad System</h5>
                            </div>

                            <!-- Start Alert -->
                            <div>
                                @if (session()->has('success'))
                                    <div class="alert alert-success alert-dismissible" role="alert">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="alert-heading">
                                                <i class="fas fa-check-circle"></i>
                                                Success
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
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
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
                                        </div>
                                        <p class="mb-0">{{ session('error') }}</p>
                                    </div>
                                @endif
                            </div>
                            <!-- End Alert -->

                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" placeholder="Email"
                                    name="email" value="{{ old('email') }}" autocomplete="off"
                                    title="Staff or Student Email" required />
                                <label for="email">Staff / Student Email</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" placeholder="Password"
                                    name="password" autocomplete="off" title="Password" required />
                                <label for="password">Password</label>

                                <!-- Show/Hide Button -->
                                <button type="button" class="btn position-absolute end-0 top-0 me-2"
                                    style="background-color: transparent; margin-top:.60rem;" id="show-password">
                                    <i id="toggle-icon-password" class="ti ti-eye"></i>
                                </button>
                            </div>
                            <div class="d-flex mt-1 justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input input-primary" type="checkbox" id="customCheckc1"
                                        name="remember" />
                                    <label class="form-check-label text-muted" for="customCheckc1">Remember
                                        me?</label>
                                </div>
                                <h6 class="f-w-400 mb-0">
                                    <a href="{{ route('forgot-password') }}" class="link-primary"> Forgot Password?
                                    </a>
                                </h6>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg" title="Login">Login</button>
                            </div>
                        </div>
                    </form>
                </div>


            </div>
        </div>
    </div>

    <!-- Required Js -->
    <script>
        function showpassword(buttonName, txtName, iconName) {
            document.getElementById(buttonName).addEventListener('click', function() {
                const passwordInput = document.getElementById(txtName);
                const icon = document.getElementById(iconName);

                // Toggle password visibility
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text'; // Change to text to show password
                    icon.classList.remove('ti-eye'); // Remove eye icon
                    icon.classList.add('ti-eye-off'); // Add eye-slash icon
                } else {
                    passwordInput.type = 'password'; // Change to password to hide it
                    icon.classList.remove('ti-eye-off'); // Remove eye-slash icon
                    icon.classList.add('ti-eye'); // Add eye icon
                }
            });
        }
        showpassword('show-password', 'password', 'toggle-icon-password');
    </script>
    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../assets/js/fonts/custom-font.js"></script>
    <script src="../assets/js/pcoded.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>

    <script>
        // Prevent pinch-to-zoom
        document.addEventListener('gesturestart', function(e) {
            e.preventDefault();
        });

        // Prevent double-tap zoom
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            let now = new Date().getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>

</body>

</html> --}}
