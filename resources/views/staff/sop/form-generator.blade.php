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
                                <div class="col-sm-8">
                                    <h5 class="mb-3 mt-3 text-center">Preview</h5>
                                    <iframe id="documentContainer" style="width:100%; height:1000px;"
                                        frameborder="1"></iframe>
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
                                                <option value="student_name">Student Name</option>
                                                <option value="student_matricno">Student Matric No</option>

                                            </select>
                                        </div>
                                        {{-- <div class="mb-3">
                                            <label for="txt_order" class="form-label">Order</label>
                                            <input type="number" name="row_order" id="txt_order" class="form-control"
                                                value="0" min="0" max="100">
                                        </div> --}}
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

            var selectedOpt = "{{ $formdata->activity_id }}";
            var af_id = "{{ $formdata->id }}";
            let debounceTimer;
            let fieldIdCounter = 0;

            window.onload = function() {
                getFormData();
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
                                    appendFormField(field.ff_label, field.ff_datakey, field
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
                                <div class="text-muted small">[${datakey}]</div>
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

            // Add Attribute Function
            $('#addAttributeBtn-submit').click(function() {
                var rowLabel = $('#txt_label').val();
                var rowDataKey = $('#select_datakey').val();

                $.ajax({
                    url: "{{ route('add-attribute-post') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        actid: selectedOpt,
                        af_id: af_id,
                        ff_label: rowLabel,
                        ff_datakey: rowDataKey,
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            appendFormField(rowLabel, rowDataKey, 0, response.formfield.id);
                            $('#addAttributeModal').modal('hide');
                            $('#txt_label').val('');
                            $('#select_datakey').val('');
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
