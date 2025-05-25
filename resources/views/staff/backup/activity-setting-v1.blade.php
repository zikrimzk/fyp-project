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