@extends('staff.layouts.main')

@section('content')
    <style>
        .draggable-item {
            cursor: move;
            transition: background-color 0.2s ease;
            border: 2px dashed #000000;

        }

        .draggable-item:hover {
            background-color: #f8f9fa;
        }

        .drag-handle {
            cursor: grab;
        }

        .ui-state-highlight {
            background-color: #e9ecef !important;
            border: 2px dashed #adb5bd;
            height: 3rem;
            margin-bottom: 0.5rem;
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

            <div class="d-flex justify-content-start align-items-center mb-3">
                <a href="{{ route('form-setting') }}"
                    class="btn btn-sm btn-light-primary d-flex align-items-center justify-content-center me-2">
                    <i class="ti ti-arrow-left me-2"></i>
                    <span class="me-2">Back</span>
                </a>
            </div>



            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ Form Generator ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- [ Form Setting ] start -->
                                <div class="col-sm-4">
                                    <h5 class="mb-3 mt-3 text-center">Form Configuration</h5>

                                    <div class="accordion card" id="formConfigAccordion">
                                        <!-- [ Form Fields ] start -->
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingTwo">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                                    aria-expanded="true" aria-controls="collapseTwo">
                                                    <div class="mb-2 mt-2">
                                                        <h5 class="mb-0">Form Fields</h5>
                                                        <small>Customize form fields</small>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapseTwo" class="accordion-collapse collapse"
                                                aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                                <div class="accordion-body">
                                                    <div
                                                        class="mb-3 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                                        <button type="button"
                                                            class="btn btn-light-primary btn-sm d-flex align-items-center gap-2"
                                                            data-bs-toggle="modal" data-bs-target="#addAttributeModal"
                                                            title="Add Attribute" id="addAttributeBtn" disabled>
                                                            <i class="ti ti-plus f-18"></i> <span
                                                                class="d-none d-sm-inline me-2">Add Attribute</span>
                                                        </button>
                                                    </div>

                                                    <ul id="fieldList" class="list-group">
                                                        <!-- Form fields will be injected here -->
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- [ Form Fields ] end -->

                                        <!-- [ Form Settings ] start -->
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingOne">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                                    aria-expanded="false" aria-controls="collapseOne">
                                                    <div class="mb-2 mt-2">
                                                        <h5 class="mb-0">Form Settings</h5>
                                                        <small>Customize title, target, and status</small>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapseOne" class="accordion-collapse collapse"
                                                aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                <div class="accordion-body">

                                                    <div class="mb-3">
                                                        <label for="txt_label" class="form-label">Form Title</label>
                                                        <input type="text" name="form_title" id="txt_form_title"
                                                            class="form-control" placeholder="Enter Form Title">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="txt_label" class="form-label">Form Target</label>
                                                        <select name="select_form_target" class="form-select"
                                                            id="select_form_target" disabled>
                                                            <option value="1">Submission</option>
                                                            <option value="2">Evaluation</option>
                                                            <option value="3">Nomination</option>
                                                        </select>
                                                        <input type="hidden" id="select_form_target_hidden"
                                                            name="formTarget">
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
                                                        <button type="button" class="btn btn-primary"
                                                            id="saveFormSetting">Save
                                                            Changes</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- [ Form Settings ] end -->
                                    </div>
                                </div>
                                <!-- [ Form Setting ] end -->

                                <!-- [ Form Preview ] start -->
                                <div class="col-sm-8 text-center">
                                    <h5 class="mb-3 mt-3 text-center">Preview</h5>
                                    <a href="{{ route('preview-activity-document-get') }}?actid={{ $formdata->activity_id }}&af_id={{ $formdata->id }}" class="link-primary">View Preview (.html)</a>
                                    <iframe id="documentContainer" style="width:100%; height:1000px;"
                                        frameborder="1" class="mt-3"></iframe>
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
                                <h5 class="modal-title" id="addModalLabel">Add Field</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Field Category -->
                                <div class="mb-3">
                                    <label for="ff_category" class="form-label">Field Category</label>
                                    <select class="form-select" id="ff_category" name="ff_category" required>
                                        <option value="" selected>- Select Category -</option>
                                        <option value="1">Input</option>
                                        <option value="2">Output</option>
                                        <option value="3">Section</option>
                                        <option value="4">Text</option>
                                        <option value="5">Ordered List</option>
                                        <option value="6">Unordered List</option>
                                    </select>
                                </div>

                                <!-- Label/Title -->
                                <div class="mb-3">
                                    <label for="ff_label" class="form-label">Label / Title / Description</label>
                                    <textarea class="form-control" id="ff_label" name="ff_label" rows="2"></textarea>
                                </div>

                                <!-- Component Type -->
                                <div class="mb-3 input-field-group">
                                    <label for="ff_component_type" class="form-label">Component Type</label>
                                    <select class="form-select" id="ff_component_type" name="ff_component_type">
                                        <option value="">-- Select Component --</option>
                                        <option value="text">Text</option>
                                        <option value="textarea">Textarea</option>
                                        <option value="select">Select</option>
                                        <option value="checkbox">Checkbox</option>
                                        <option value="radio">Radio</option>
                                        <option value="date">Date</option>
                                        <option value="datetime-local">DateTime</option>
                                    </select>
                                </div>

                                <!-- Placeholder -->
                                <div class="mb-3 input-field-group">
                                    <label for="ff_placeholder" class="form-label">Placeholder</label>
                                    <input type="text" class="form-control" id="ff_placeholder"
                                        name="ff_placeholder">
                                </div>

                                <!-- Required -->
                                <div class="mb-3 input-field-group">
                                    <label for="ff_component_required" class="form-label">Is this field
                                        required?</label>
                                    <select class="form-select" id="ff_component_required" name="ff_component_required">
                                        <option value="1">Required</option>
                                        <option value="2">Optional</option>
                                    </select>
                                </div>

                                <!-- Value Options -->
                                <div class="mb-3 input-field-group">
                                    <label for="ff_value_options" class="form-label">Value Options (for select,
                                        checkbox, radio)</label>
                                    <textarea class="form-control" id="ff_value_options" name="ff_value_options" rows="2"
                                        placeholder='e.g. ["Option 1", "Option 2"]'></textarea>
                                </div>

                                <!-- Repeatable -->
                                <div class="mb-3 input-field-group">
                                    <label for="ff_repeatable" class="form-label">Repeatable Field?</label>
                                    <select class="form-select" id="ff_repeatable" name="ff_repeatable">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>

                                <!-- Append Text -->
                                <div class="mb-3 input-field-group">
                                    <label for="ff_append_text" class="form-label">Append Text (after label)</label>
                                    <textarea class="form-control" id="ff_append_text" name="ff_append_text" rows="2"></textarea>
                                </div>

                                <!-- Field Table -->
                                <div class="mb-3 output-field-group">
                                    <label for="ff_table" class="form-label">Field Table</label>
                                    <select name="ff_table" class="form-select" id="ff_table">
                                        <option value="" selected>-- Select Field Table --</option>
                                        <option value="students">Student</option>
                                        <option value="staffs">Staff</option>
                                        <option value="activities">Activity</option>
                                        <option value="submissions">Submission</option>
                                        <option value="semesters">Semester</option>
                                    </select>
                                </div>

                                <!-- Field Attribute -->
                                <div class="mb-3 output-field-group">
                                    <label for="ff_datakey" class="form-label">Field Attribute</label>
                                    <select name="ff_datakey" class="form-select" id="ff_datakey">
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

            var selectedOpt = "{{ $formdata->activity_id }}";
            var af_id = "{{ $formdata->id }}";
            let debounceTimer;
            let fieldIdCounter = 0;

            window.onload = function() {
                getFormData();
                initializeFormVisibility();
                resetFormSections();
            };

            function getFormData() {
                const addAttrBtn = $('#addAttributeBtn');
                $.ajax({
                    url: "{{ route('get-activity-form-data-post') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        af_id: af_id,
                        actid: selectedOpt
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#txt_form_title').val(response.formTitle);
                            $('#select_form_target').val(response.formTarget);
                            $('#select_form_target_hidden').val(response.formTarget);
                            $('#select_form_status').val(response.formStatus);
                            $('#documentContainer').attr('src',
                                '{{ route('activity-document-preview-get') }}' +
                                '?actid=' + encodeURIComponent(selectedOpt) +
                                '&af_id=' + encodeURIComponent(af_id) +
                                '&title=' + response.formTitle
                            );
                            addAttrBtn.prop('disabled', false);
                            getFormFieldsData(response.formID);
                        } else {
                            $('#txt_form_title').val("");
                            $('#select_form_target').val("");
                            $('#select_form_target_hidden').val("");
                            $('#select_form_status').val("");
                            $('#documentContainer').attr('src',
                                '{{ route('activity-document-preview-get') }}' +
                                '?actid=' + encodeURIComponent(selectedOpt) +
                                '&af_id=' + encodeURIComponent(af_id) +
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

            function getFormFieldsData(af_id) {
                $.ajax({
                    url: "{{ route('get-form-field-data-get') }}",
                    method: "GET",
                    data: {
                        af_id: af_id
                    },
                    success: function(response) {
                        const $fieldList = $('#fieldList');
                        $fieldList.empty();

                        if (response.success && Array.isArray(response.fields)) {
                            if (response.fields.length === 0) {
                                $fieldList.append(`
                                    <li class="list-group-item text-center text-muted" id="noFieldMsg">
                                        This form doesn’t have any fields. Add one to get started!
                                    </li>
                                `);
                            } else {
                                const sortedFields = response.fields.sort((a, b) => a.ff_order - b
                                    .ff_order);

                                sortedFields.forEach(field => {
                                    appendFormField(field.ff_label, field.ff_component_type, field
                                        .ff_order, field.id);
                                });
                            }
                        } else {
                            $fieldList.append(`
                                <li class="list-group-item text-center text-muted" id="noFieldMsg">
                                    This form doesn’t have any fields. Add one to get started!
                                </li>
                            `);
                        }
                    },
                    error: function() {
                        showToast('error', 'Failed to load form fields.');
                    }
                });
            }

            function appendFormField(label, datakey, order, ff_id = null) {
                const id = ff_id ?? `temp_${fieldIdCounter++}`;
                console.log(`Appending field: ${label} (${datakey}), ID: ${id}`);

                const item = `
                    <li class="list-group-item draggable-item" data-id="${id}">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="drag-handle text-secondary" title="Drag to reorder">
                                <i class="ti ti-drag-drop fs-5"></i>
                            </span>
                            <div>
                                <strong>${label}</strong>
                                <div class="text-muted small">[${datakey ?? 'Others'}]</div>
                            </div>
                        </div>
                        <div class="row g-1">
                            <div class="col-6">
                                <button class="btn btn-sm btn-outline-primary w-100 update-field-btn" data-id="${id}" data-label="${label}" data-key="${datakey}">
                                    <i class="bi bi-pencil"></i> Update
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-sm btn-outline-danger w-100 delete-field-btn" data-id="${id}">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </li>
                `;
                $('#fieldList').append(item);
            }

            // Update Form Title in Preview
            $('#txt_form_title').on('input', function() {
                clearTimeout(debounceTimer);

                debounceTimer = setTimeout(() => {
                    const txtvalue = $(this).val();

                    if (selectedOpt) {
                        $('#documentContainer').attr('src',
                            '{{ route('activity-document-preview-get') }}' +
                            '?actid=' + encodeURIComponent(selectedOpt) +
                            '&af_id=' + encodeURIComponent(af_id) +
                            '&title=' + encodeURIComponent(txtvalue)
                        );
                    }
                }, 300);
            });

            // Update Form Setting
            $('#saveFormSetting').click(function() {
                var formTarget = $('#select_form_target_hidden').val();
                var formStatus = $('#select_form_status').val();
                var formTitle = $('#txt_form_title').val();

                $.ajax({
                    url: "{{ route('add-activity-form-post') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        actid: selectedOpt,
                        af_id: af_id,
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

            $('#ff_category').on('change', function() {
                var category = $(this).val();

                resetFormSections();

                if (category == 1) {
                    $('.input-field-group').show(); // Show input-related fields
                }
                if (category == 2) {
                    $('.output-field-group').show(); // Show output-related fields
                }
                if (category == 1 || category == 2 || category == 3 || category == 4 || category == 5 ||
                    category == 6) {
                    // Show ordered/unordered list
                    $('#ff_label').parent().show();
                }
            });


            $('#ff_is_table').on('change', function() {
                if ($(this).prop('checked')) {
                    $('.table-settings-group').show();
                } else {
                    $('.table-settings-group').hide();
                }
            });

            function initializeFormVisibility() {
                var selectedCategory = $('#ff_category').val();
                var isTableChecked = $('#ff_is_table').prop('checked');

                if (selectedCategory == 1) {
                    $('#ff_label').parent().show();

                    $('.input-field-group').show();
                }
                if (selectedCategory == 2) {
                    $('#ff_label').parent().show();

                    $('.output-field-group').show();
                }
                if (selectedCategory == 1 || selectedCategory == 2 || selectedCategory == 3 || selectedCategory ==
                    4 || selectedCategory == 5 || selectedCategory == 6) {
                    $('#ff_label').parent().show();
                }
                // Handle table-related settings visibility
                if (isTableChecked) {
                    $('.table-settings-group').show();
                } else {
                    $('.table-settings-group').hide();
                }
            }

            // Reset the form fields visibility
            function resetFormSections() {
                $('#ff_datakey').prop('disabled', true);
                $('.input-field-group').hide();
                $('.output-field-group').hide();
                $('#ff_label').parent().hide();
                $('.table-settings-group').hide();
            }

            // Filter output attributes based on selected table
            $('#ff_table').on('change', function() {
                var selectedTable = $(this).val();

                if (selectedTable != '') {
                    $('#ff_datakey').prop('disabled', false);
                } else {
                    $('#ff_datakey').prop('disabled', true);
                }

                $('#ff_datakey option').each(function() {
                    var table = $(this).data('table');

                    // Keep disabled headers and empty values visible
                    if ($(this).is(':disabled') || !table) {
                        $(this).hide();
                    } else if (table === selectedTable) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                $('#ff_datakey').val('');
            });


            // Add Attribute Function
            $('#addAttributeBtn-submit').click(function() {
                // Gather values for general attributes
                var rowLabel = $('#ff_label').val();
                var rowCategory = $('#ff_category').val();

                // Gather values for input attributes
                var rowType = $('#ff_component_type').val();
                var rowPlaceholder = $('#ff_placeholder').val();
                var rowRequired = $('#ff_component_required').val();
                var rowValueOptions = $('#ff_value_options').val();
                var rowRepeatable = $('#ff_repeatable').val();
                var rowAppendText = $('#ff_append_text').val();

                // Gather values for output attributes
                var rowTable = $('#ff_table').val();
                var rowDataKey = $('#ff_datakey').val();


                // Create a data object to send in the request
                var requestData = {
                    _token: "{{ csrf_token() }}",
                    actid: selectedOpt,
                    af_id: af_id,
                    ff_label: rowLabel,
                    ff_category: rowCategory,
                    ff_component_type: rowType,
                    ff_placeholder: rowPlaceholder,
                    ff_component_required: rowRequired,
                    ff_value_options: rowValueOptions,
                    ff_repeatable: rowRepeatable,
                    ff_append_text: rowAppendText,
                    ff_table: rowTable,
                    ff_datakey: rowDataKey
                };

                // Send the AJAX request
                $.ajax({
                    url: "{{ route('add-attribute-post') }}",
                    type: "POST",
                    data: requestData,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            appendFormField(rowLabel, rowDataKey, 0, response.formfield.id);
                            $('#addAttributeModal').modal('hide');
                            $('#ff_label').val('');
                            $('#ff_category').val('');
                            $('#ff_component_type').val('');
                            $('#ff_placeholder').val('');
                            $('#ff_component_required').val('1');
                            $('#ff_value_options').val('');
                            $('#ff_repeatable').val('0');
                            $('#ff_append_text').val('');
                            $('#ff_table').val('');
                            $('#ff_datakey').val('');
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

            // Enable drag and drop sorting Function
            $('#fieldList').sortable({
                placeholder: "ui-state-highlight",
                update: function(event, ui) {
                    console.log("New order:", $('#fieldList').sortable('toArray', {
                        attribute: 'data-id'
                    }));

                    const newOrder = [];
                    $('#fieldList li').each(function(index) {
                        newOrder.push({
                            id: $(this).data('id'),
                            order: index + 1
                        });
                    });

                    $.ajax({
                        url: "{{ route('update-order-attribute-post') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            fields: newOrder
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                getFormData();
                            } else {
                                showToast('error', response.message);
                            }
                        },
                        error: function() {
                            showToast('error', 'Failed to update field order.');
                        }
                    });
                }
            }).disableSelection();

            // Update field button [Unfinished]
            $(document).on('click', '.update-field-btn', function() {
                const id = $(this).data('id');
                const label = $(this).data('label');
                const key = $(this).data('key');
                // show modal or inline editing
                alert(`Update feature triggered for: ${label} [${key}]`);
                // You can implement modal re-use here
            });

            // Delete Attribute Function
            $(document).on('click', '.delete-field-btn', function() {
                $.ajax({
                    url: "{{ route('delete-attribute-post') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        ff_id: $(this).data('id'),
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            getFormData();
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function() {
                        showToast('error', 'Failed to delete attribute.');
                    }
                });
            });

        });
    </script>
@endsection
