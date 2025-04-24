@extends('staff.layouts.main')

@section('content')
    <style>
        #form-wrapper {
            width: 794px;
            height: 1123px;
            margin: 40px auto;
            padding: 40px;
            font-family: 'Arial', sans-serif;
            font-size: 12pt;
            background: white;
            color: #000;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
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
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript: void(0)">SOP</a></li>
                                <li class="breadcrumb-item" aria-current="page">Form Generator</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Form Generator</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->


            <!-- [ Alert ] start -->
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                <div id="toastContainer"></div>
            </div>
            <!-- [ Alert ] end -->



            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ Form Generator ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- [ Form Setting ] start -->
                                <div class="col-sm-4">
                                    <div class="row">
                                        <div class="col-sm-12" id="formGeneration">
                                            <div class="mb-3">
                                                <h5 class="mb-0">Generate Form</h5>
                                                <small>Select the activity first before generating the form</small>
                                            </div>

                                            <div class="mb-3">
                                                <select name="activity_id" class="form-select" id="selectActivity">
                                                    <option value="" selected>-- Select Activity --</option>
                                                    @foreach ($acts as $activity)
                                                        <option value="{{ $activity->id }}">{{ $activity->act_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="d-grid mt-4 mb-4">
                                                <button type="button" class="btn btn-primary" id="generateForm">Generate
                                                    Form</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-12" id="formSetting">
                                            <div class="mb-3">
                                                <h5 class="mb-0">Form Settings</h5>
                                                <small>Customize your form settings here</small>
                                            </div>
                                            <div
                                                class="mb-3 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                                <button type="button"
                                                    class="btn btn-light-primary btn-sm d-flex align-items-center gap-2"
                                                    data-bs-toggle="modal" data-bs-target="#addAttributeModal"
                                                    title="Add Attribute" id="addAttributeBtn" disabled>
                                                    <i class="ti ti-plus f-18"></i> <span
                                                        class="d-none d-sm-inline me-2">Add Attribute</span>
                                                </button>
                                                {{-- <button type="button"
                                                    class="btn btn-light-danger btn-sm d-flex align-items-center gap-2"
                                                    data-bs-toggle="modal" data-bs-target="#addActivityModal"
                                                    title="Add Attribute" id="addActivity">
                                                    <i class="ti ti-trash f-18"></i> <span
                                                        class="d-none d-sm-inline me-2">Reset Attribute</span>
                                                </button> --}}
                                            </div>

                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Form Title</label>
                                                <input type="text" name="form_title" id="txt_form_title"
                                                    class="form-control" placeholder="Enter Form Title">
                                            </div>

                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Form Target</label>
                                                <select name="select_form_target" class="form-select"
                                                    id="select_form_target">
                                                    <option value="" selected>-- Select Target --</option>
                                                    <option value="1">Submission</option>
                                                    <option value="2">Evaluation</option>
                                                    <option value="3">Nomination</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Form Status</label>
                                                <select name="select_form_status" class="form-select"
                                                    id="select_form_status">
                                                    <option value="" selected>-- Select Status --</option>
                                                    <option value="1">Active</option>
                                                    <option value="2">Inactive</option>
                                                </select>
                                            </div>

                                            <div class="d-grid mt-4 mb-4">
                                                <button type="button" class="btn btn-primary" id="saveFormSetting">Save
                                                    Changes</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <!-- [ Form Setting ] end -->

                                <!-- [ Form Preview ] start -->
                                <div class="col-sm-8 border">
                                    <h5 class="mb-3 mt-3 text-center">Preview</h5>
                                    <div id="loadingSpinner" class="text-center my-3" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <iframe id="documentContainer" style="width:100%; height:1000px;"
                                        frameborder="0"></iframe>
                                </div>
                                <!-- [ Form Preview ] end -->

                            </div>

                        </div>
                    </div>
                </div>
                <!-- [ Form Generator ] end -->

                <!-- [ Add Attribute Modal ] start -->
                <div class="modal fade" id="addAttributeModal" tabindex="-1" aria-labelledby="addAttributeModal"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addModalLabel">Add Attribute</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                        <div class="mb-3">
                                            <label for="txt_label" class="form-label">Label</label>
                                            <input type="text" name="row_label" id="txt_label" class="form-control"
                                                placeholder="Enter Attribute Label">
                                        </div>
                                        <div class="mb-3">
                                            <label for="select_datakey" class="form-label">Attribute</label>
                                            <select name="row_datakey" class="form-select" id="select_datakey">
                                                <option value="" selected>-- Select Attribute --</option>
                                            </select>
                                        </div>
                                        {{-- <div class="mb-3">
                                            <label for="txt_order" class="form-label">Order</label>
                                            <input type="number" name="row_order" id="txt_order" class="form-control"
                                                value="0" min="0" max="100">
                                        </div> --}}
                                        <div class="mb-3">
                                            <label class="form-label">Font Style</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="fontStyle"
                                                        id="fontNormal" value="normal" checked>
                                                    <label class="form-check-label" for="fontNormal">Normal</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="fontStyle"
                                                        id="fontBold" value="bold">
                                                    <label class="form-check-label" for="fontBold">Bold</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Header Label</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="isHeader"
                                                        id="headerTrue" value="true">
                                                    <label class="form-check-label" for="headerTrue">True</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="isHeader"
                                                        id="headerFalse" value="false" checked>
                                                    <label class="form-check-label" for="headerFalse">False</label>
                                                </div>
                                            </div>
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
                                            <button type="button" id="addAttributeBtn-submit"
                                                class="btn btn-primary w-100">
                                                Add Attribute
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Add Attribute Modal ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            function showToast(type, message) {
                const toastId = 'toast-' + Date.now();
                const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-info-circle';
                const bgClass = type === 'success' ? 'bg-light-success' : 'bg-light-danger';
                const txtClass = type === 'success' ? 'text-success' : 'text-danger';
                const colorClass = type === 'success' ? 'success' : 'danger';
                const title = type === 'success' ? 'Success' : 'Error';

                const toastHtml = `
                    <div id="${toastId}" class="toast border-0 shadow-sm mb-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                        <div class="toast-body text-white ${bgClass} rounded d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0 ${txtClass}">
                                    <i class="${iconClass} me-2"></i> ${title}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <p class="mb-0 ${txtClass}">${message}</p>
                        </div>
                    </div>
                `;

                $('#toastContainer').append(toastHtml);
                const toastEl = new bootstrap.Toast(document.getElementById(toastId));
                toastEl.show();
            }

            function getFormData() {
                var selectedOpt = $('#selectActivity').val();
                const addAttrBtn = $('#addAttributeBtn');

                $.ajax({
                    url: "{{ route('get-activity-form-data-post') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        actid: selectedOpt
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#txt_form_title').val(response.formTitle);
                            $('#select_form_target').val(response.formTarget);
                            $('#select_form_status').val(response.formStatus);
                            $('#documentContainer').attr('src',
                                '{{ route('activity-document-preview-get') }}' +
                                '?actid=' + encodeURIComponent(selectedOpt) +
                                '&title=' + response.formTitle
                            );
                            addAttrBtn.prop('disabled', false);
                        } else {
                            $('#txt_form_title').val("");
                            $('#select_form_target').val("");
                            $('#select_form_status').val("");
                            $('#documentContainer').attr('src',
                                '{{ route('activity-document-preview-get') }}' +
                                '?actid=' + encodeURIComponent(selectedOpt) +
                                '&title='
                            );
                            addAttrBtn.prop('disabled', true);

                        }
                    },
                    error: function() {
                        showToast('error', 'Oops! Something went wrong. Please try again.');
                    }
                });
            }

            $('#generateForm').click(function() {
                getFormData();
            });

            let debounceTimer;

            $('#txt_form_title').on('input', function() {
                clearTimeout(debounceTimer);

                debounceTimer = setTimeout(() => {
                    const txtvalue = $(this).val();
                    const selectedOpt = $('#selectActivity').val();

                    if (selectedOpt) {
                        $('#documentContainer').attr('src',
                            '{{ route('activity-document-preview-get') }}' +
                            '?actid=' + encodeURIComponent(selectedOpt) +
                            '&title=' + encodeURIComponent(txtvalue)
                        );
                    }
                }, 300);
            });

            $('#saveFormSetting').click(function() {
                var selectedOpt = $('#selectActivity').val();
                var formTarget = $('#select_form_target').val();
                var formStatus = $('#select_form_status').val();
                var formTitle = $('#txt_form_title').val();

                $.ajax({
                    url: "{{ route('add-activity-form-post') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        actid: selectedOpt,
                        formTitle: formTitle,
                        formTarget: formTarget,
                        formStatus: formStatus,
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            getFormData();
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Laravel validation error
                            const errors = xhr.responseJSON?.message;
                            if (errors) {
                                let msg = '';
                                Object.values(errors).forEach(function(error) {
                                    msg += `• ${error[0]}<br>`;
                                });
                                showToast('error', msg);
                            } else {
                                showToast('error',
                                    'Validation failed, but no message returned.');
                            }
                        } else {
                            // Other server errors
                            showToast('error', 'Something went wrong. Please try again.');
                        }
                    }
                });
            });

            $('#addAttributeBtn-submit').click(function() {
                var selectedOpt = $('#selectActivity').val(); 
                var rowLabel = $('#txt_label').val();
                var rowDataKey = $('#select_datakey').val();
                // var rowOrder = $('#txt_order').val();
                var fontStyle = $('input[name="fontStyle"]:checked').val();
                var isHeader = $('input[name="isHeader"]:checked').val();

                $.ajax({
                    url: "{{ route('add-attribute-post') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        actid: selectedOpt, 
                        ff_label: rowLabel,
                        ff_datakey: rowDataKey,
                        // ff_order: rowOrder,
                        ff_isbold: fontStyle,
                        ff_isheader: isHeader,
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#addAttributeModal').modal('hide');
                            getFormData(); 
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors;
                            if (errors) {
                                let msg = '';
                                Object.values(errors).forEach(function(error) {
                                    msg += `• ${error[0]}<br>`;
                                });
                                showToast('error', msg);
                            } else {
                                showToast('error',
                                    'Validation failed, but no message returned.');
                            }
                        } else {
                            showToast('error', 'Something went wrong. Please try again.');
                        }
                    }
                });
            });



        });
    </script>
@endsection
