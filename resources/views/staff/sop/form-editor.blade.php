@extends('staff.layouts.main')

@section('content')
    <style>
        .preview-wrapper {
            height: 100%;
            min-height: 1000px;
            position: relative;
            background-color: rgba(50, 54, 57, 255);
            border-radius: 4px;

        }

        .document-frame {
            width: 100%;
            height: 950px;
            border: none;
        }

        .preview-loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 5;
            background-color: rgba(255, 255, 255, 0.85);
            padding: 10px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }

        .draggable-item {
            cursor: move;
            transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
            background-color: #ffffff;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            user-select: none;
            margin-bottom: 0.75rem;
            border: 1px solid #e9ecef;
            position: relative;
            overflow: hidden;
        }

        .draggable-item:hover {
            background-color: #f8f9fa;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
            border-color: #dee2e6;
        }

        .draggable-item.ui-sortable-helper {
            transform: scale(1.02) translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
            z-index: 1000;
            cursor: grabbing;
            border-color: #adb5bd;
            background-color: #f8f9fa;
        }

        .draggable-item.ui-sortable-helper .drag-handle {
            color: #495057;
        }

        .drag-handle {
            cursor: grab;
            color: #adb5bd;
            transition: color 0.2s ease;
            padding: 8px;
            margin: -8px;
            border-radius: 4px;
        }

        .drag-handle:hover {
            color: #495057;
            background-color: rgba(0, 0, 0, 0.03);
        }

        .drag-handle:active {
            cursor: grabbing;
            color: #212529;
        }

        .ui-state-highlight {
            background-color: #e9ecef !important;
            border: 2px dashed #adb5bd !important;
            height: 60px;
            margin-bottom: 0.75rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            box-shadow: none;
        }

        /* Button styles */
        .draggable-item .btn {
            transition: all 0.15s ease;
            padding: 0.35rem 0.5rem;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .draggable-item .btn:hover {
            transform: translateY(-1px);
        }

        .draggable-item .btn:active {
            transform: translateY(0);
        }

        /* Pulse animation for placeholder */
        @keyframes pulseHighlight {
            0% {
                opacity: 0.6;
            }

            50% {
                opacity: 0.8;
            }

            100% {
                opacity: 0.6;
            }
        }

        .ui-state-highlight {
            animation: pulseHighlight 1.5s ease infinite;
        }

        /* Visual feedback during drag */
        .draggable-item.dragging {
            opacity: 0.8;
        }

        .ck-editor__editable_inline {
            min-height: 200px;
            max-height: 500px;
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom classes to avoid conflicts with existing modals */
        .startup-modal .modal-dialog {
            max-height: 90vh;
            margin: 1.75rem auto;
        }

        .startup-modal .modal-dialog-scrollable {
            height: auto;
        }

        .startup-modal .modal-content {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            background-color: rgba(255, 255, 255, 0.95);
        }

        .startup-modal .modal-header {
            background-color: rgba(50, 54, 57, 255);
            color: white;
            border-bottom: none;
            padding: 2rem 1.5rem 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .startup-modal .modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
            opacity: 0.5;
        }

        .startup-modal .modal-body {
            overflow-y: auto;
            padding: 1.5rem;
            flex: 1 1 auto;
            -webkit-overflow-scrolling: touch;
            max-height: calc(90vh - 200px);
        }

        /* Custom scrollbar for modal body */
        .startup-modal .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .startup-modal .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .startup-modal .modal-body::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .startup-modal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .startup-modal .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Rest of styles with updated color */
        .startup-modal .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .startup-modal .modal-subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            font-weight: 400;
            position: relative;
            z-index: 1;
            margin: 0;
        }

        .startup-modal .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .startup-modal .section-icon {
            width: 22px;
            height: 22px;
            background: rgba(50, 54, 57, 255);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .startup-modal .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .startup-modal .category-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 0.875rem;
            transition: all 0.2s ease;
        }

        .startup-modal .category-item:hover {
            border-color: rgba(50, 54, 57, 255);
            box-shadow: 0 2px 8px rgba(50, 54, 57, 0.1);
        }

        .startup-modal .category-name {
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .startup-modal .category-desc {
            font-size: 0.8rem;
            color: #6c757d;
            line-height: 1.3;
            margin: 0;
        }

        .startup-modal .guidelines-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .startup-modal .guidelines-title {
            font-size: 1rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .startup-modal .guidelines-content {
            font-size: 0.875rem;
            line-height: 1.5;
            color: #6c757d;
        }

        .startup-modal .guidelines-content p {
            margin-bottom: 0.75rem;
        }

        .startup-modal .guideline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0.75rem;
            padding: 0.5rem;
            background: white;
            border-radius: 0.375rem;
            border-left: 3px solid rgba(50, 54, 57, 255);
            /* Updated color */
        }

        .startup-modal .guideline-item:last-child {
            margin-bottom: 0;
        }

        .startup-modal .guideline-icon {
            margin-right: 0.5rem;
            margin-top: 0.1rem;
            color: rgba(50, 54, 57, 255);
            /* Updated color */
            font-size: 1rem;
        }

        .startup-modal .highlight {
            background: #fff3cd;
            color: #856404;
            padding: 0.1rem 0.3rem;
            border-radius: 0.25rem;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .startup-modal .danger-text {
            color: #dc3545;
            font-weight: 600;
        }

        .startup-modal .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.5rem;
            margin: 0.75rem 0;
        }

        .startup-modal .status-item {
            padding: 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            text-align: center;
        }

        .startup-modal .status-pass {
            background: #d1edff;
            color: rgba(50, 54, 57, 255);
            /* Updated color */
        }

        .startup-modal .status-minor {
            background: #fff3cd;
            color: #856404;
        }

        .startup-modal .status-major {
            background: #cff4fc;
            color: #055160;
        }

        .startup-modal .status-resubmit {
            background: #e2e3e5;
            color: #41464b;
        }

        .startup-modal .status-fail {
            background: #f8d7da;
            color: #721c24;
        }

        .startup-modal .note-badge {
            background: #e7f3ff;
            color: rgba(50, 54, 57, 255);
            /* Updated color */
            padding: 0.4rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid #b3d7ff;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .startup-modal .startup-submit-btn {
            background: rgba(50, 54, 57, 255);
            /* Updated color */
            border: none;
            padding: 0.75rem 2.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: white;
            transition: all 0.2s ease;
        }

        .startup-modal .startup-submit-btn:hover {
            background: rgba(40, 44, 47, 255);
            /* Slightly darker on hover */
            transform: translateY(-1px);
            color: white;
            box-shadow: 0 4px 12px rgba(50, 54, 57, 0.3);
            /* Updated shadow color */
        }

        @media (max-width: 768px) {
            .preview-wrapper {
                min-height: 320px;
                min-width: 320px;
                padding: 0.5rem;
            }

            .document-frame {
                height: 80vh;
            }

            .draggable-item .btn {
                padding: 0.25rem;
                font-size: 0.75rem;
            }

            .draggable-item {
                padding: 10px 12px;
            }

            .startup-modal .modal-dialog {
                margin: 0.5rem;
            }

            .startup-modal .category-grid {
                grid-template-columns: 1fr;
            }

            .startup-modal .status-grid {
                grid-template-columns: 1fr;
            }

            .startup-modal .modal-header {
                padding: 1.5rem 1rem 1rem;
            }

            .startup-modal .modal-title {
                font-size: 1.3rem;
            }
        }
    </style>


    <div class="pc-container">
        <div class="pc-content">
            <!-- [ Alert ] start -->
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                <div id="toastContainer"></div>
            </div>
            <!-- [ Alert ] end -->


            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- [ Form Editor ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header" style="background-color: rgba(82,86,89,255); border:none;">
                            <!-- [ breadcrumb ] start -->
                            <div class="page-header">
                                <div class="page-block">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <ul class="breadcrumb text-white">
                                                <li class="breadcrumb-item"><a href="javascript: void(0)"
                                                        class="text-white">Administrator</a>
                                                </li>
                                                <li class="breadcrumb-item">SOP</li>
                                                <li class="breadcrumb-item"><a href="{{ route('form-setting') }}"
                                                        class="text-white">Form Setting</a></li>
                                                <li class="breadcrumb-item">{{ $acts->act_name }}</li>
                                                <li class="breadcrumb-item" aria-current="page">Form Editor</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="page-header-title">
                                                <h2 class="mb-0 text-white d-flex align-items-center ">
                                                    <a href="{{ route('form-setting') }}" class="btn me-2">
                                                        <span class="f-18 text-white">
                                                            <i class="ti ti-arrow-left"></i>
                                                        </span>
                                                    </a>
                                                    Form Editor
                                                </h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ breadcrumb ] end -->
                        </div>
                        <div class="card-body" style="background-color: rgba(50, 54, 57, 255);">
                            <div class="row">
                                <!-- [ Form Setting ] start -->
                                <div class="col-sm-4">
                                    <h5 class="mb-3 mt-3 text-center text-white">Form Configuration</h5>

                                    <div class="accordion card" id="formConfigAccordion">
                                        <!-- [ Form Fields ] start -->
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingTwo">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseTwo" aria-expanded="true"
                                                    aria-controls="collapseTwo">
                                                    <div class="mb-2 mt-2">
                                                        <h5 class="mb-0">Form Fields</h5>
                                                        <small>Customize form fields</small>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapseTwo" class="accordion-collapse show"
                                                aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                                <div class="accordion-body">
                                                    <div
                                                        class="mb-3 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                                        <button type="button"
                                                            class="btn btn-light-primary btn-sm d-flex align-items-center gap-2"
                                                            title="Add Field" id="addFormFieldBtn" disabled>
                                                            <i class="ti ti-plus f-18"></i> <span
                                                                class="d-none d-sm-inline me-2">Add Field</span>
                                                        </button>

                                                        <button type="button"
                                                            class="btn btn-light-primary btn-sm d-flex align-items-center gap-2"
                                                            title="Update Preview" id="updatePreviewBtn">
                                                            <i class="ti ti-eye f-18"></i> <span
                                                                class="d-none d-sm-inline me-2">Update Preview</span>
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
                                                            <option value="3">Nomination</option>
                                                            <option value="4">Evaluation - Chairman </option>
                                                            <option value="5">Evaluation - Examiner / Panel</option>
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
                                <div class="col-sm-8 position-relative preview-wrapper">

                                    <!-- Loading Spinner -->
                                    <div id="preview-loader" class="preview-loader d-none">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <span class="ms-2">Loading preview...</span>
                                    </div>

                                    <!-- Iframe Preview -->
                                    <iframe id="documentContainer" class="mt-3 document-frame" frameborder="0"></iframe>

                                    <div class="text-center m-3">
                                        <a href="{{ route('preview-activity-document-get') }}?actid={{ $formdata->activity_id }}&af_id={{ $formdata->id }}"
                                            class="link-light" target="_blank">View Preview (.html)</a>
                                    </div>
                                </div>
                                <!-- [ Form Preview ] end -->
                            </div>

                        </div>
                    </div>
                </div>
                <!-- [ Form Editor ] end -->

                <!-- [ Add & Update Form Field Modal ] start -->
                <div class="modal fade" id="formFieldModal" tabindex="-1" aria-labelledby="formFieldModal"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title" id="formFieldModalLabel"></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- ff_id Hidden -->
                                <input type="hidden" id="ff_id-hidden" />

                                <!-- Field Category -->
                                <div class="mb-3">
                                    <label for="ff_category" class="form-label">Field Category</label>
                                    <select class="form-select" id="ff_category" name="ff_category" required>
                                        <option value="" selected>- Select Category -</option>
                                        <option value="1">Input</option>
                                        <option value="2">Output</option>
                                        <option value="3">Section</option>
                                        <option value="4">Text Editor</option>
                                        <option value="6">Signature</option>
                                    </select>
                                </div>

                                <!-- Label/Title -->
                                <div class="mb-3">
                                    <label for="ff_label" class="form-label">Label / Title</label>
                                    <textarea class="form-control" id="ff_label" name="ff_label" rows="2" placeholder="Enter label / title"></textarea>
                                </div>

                                <!-- Label/Title [CKEDITOR] -->
                                <div class="mb-3">
                                    <label for="ff_label" class="form-label">Text Editor</label>
                                    <textarea id="ff_label-ckeditor" rows="30" cols="30"></textarea>
                                </div>

                                <!-- Component Type -->
                                <div class="mb-3 input-field-group">
                                    <label for="ff_component_type" class="form-label">Component Type</label>
                                    <select class="form-select" id="ff_component_type" name="ff_component_type">
                                        <option value="">-- Select Component --</option>
                                        <option value="text">Text</option>
                                        <option value="textarea">Textarea</option>
                                        <option value="longtextarea">Long Textarea</option>
                                        <option value="select">Select</option>
                                        <option value="checkbox">Checkbox</option>
                                        <option value="radio">Radio</option>
                                        <option value="date">Date</option>
                                        <option value="datetime-local">DateTime</option>
                                    </select>
                                </div>

                                <!-- Placeholder -->
                                <div class="mb-3 input-field-group placeholder-option">
                                    <label for="ff_placeholder" class="form-label">Placeholder</label>
                                    <input type="text" class="form-control" id="ff_placeholder" name="ff_placeholder"
                                        placeholder="Enter input placeholder">
                                </div>

                                <!-- Value Options -->
                                <div class="mb-3 input-field-group value-options">
                                    <div class="card border-1">
                                        <div class="card-header">
                                            <h5 class="mb-0">Value Options</h5>
                                            <small>Define options for select, checkbox, or radio fields</small>
                                        </div>
                                        <div class="card-body">
                                            <!-- Comma-separated input section -->
                                            <div class="mb-3">
                                                <label for="ff_value_options" class="form-label">Value Options
                                                    (for select, checkbox, radio)</label>
                                                <textarea type="text" id="options-input" class="form-control" rows="2"
                                                    placeholder="Option 1, Option 2, Option 3"></textarea>
                                                <small class="text-muted">Enter options separated by commas</small>
                                                <input type="hidden" id="ff_value_options" name="ff_value_options">
                                            </div>

                                            <!-- Divider with "OR" -->
                                            <div class="d-flex align-items-center my-3">
                                                <hr class="flex-grow-1">
                                                <span class="px-3 text-muted small">OR</span>
                                                <hr class="flex-grow-1">
                                            </div>

                                            <!-- Table selection section -->
                                            <div class="mb-3">
                                                <label for="ff_value_options_table" class="form-label">Value
                                                    Options (Using Table)</label>
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <select name="ff_value_options_table" class="form-select"
                                                            id="ff_value_options_table">
                                                            <option value="" selected>-- Select Table --</option>
                                                            <option value="students">Student</option>
                                                            <option value="staff">Staff</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <select name="ff_value_options_column" class="form-select"
                                                            id="ff_value_options_column">
                                                            <option value="" selected>-- Select Column --</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Required -->
                                <div class="mb-3 input-field-group required-option">
                                    <label for="ff_component_required" class="form-label">Is this field
                                        required?</label>
                                    <select class="form-select" id="ff_component_required" name="ff_component_required">
                                        <option value="1">Required</option>
                                        <option value="2">Optional</option>
                                    </select>
                                </div>

                                <!-- Required Role -->
                                <div class="mb-3 input-field-group required-option">
                                    <label for="ff_component_required_role" class="form-label">Is this field
                                        required for specific role?</label>
                                    <select class="form-select" id="ff_component_required_role"
                                        name="ff_component_required_role">
                                        <option value="0">-- No specific role --</option>
                                        <option value="1">Supervisors [Main/Co]</option>
                                        <option value="2">Committee</option>
                                        <option value="3">Deputy Dean</option>
                                        <option value="4">Dean</option>
                                        <option value="5">Examiner / Panel</option>
                                        <option value="6">Chairman</option>
                                    </select>
                                </div>

                                <!-- Append Text -->
                                <div class="mb-3 input-field-group append-text">
                                    <label for="ff_append_text" class="form-label">Append Text (after label)</label>
                                    <textarea class="form-control" id="ff_append_text" name="ff_append_text" rows="2"
                                        placeholder="Enter append text"></textarea>
                                </div>

                                <!-- Field Table -->
                                <div class="mb-3 output-field-group">
                                    <label for="ff_table" class="form-label">Field Table</label>
                                    <select name="ff_table" class="form-select" id="ff_table">
                                        <option value="" selected>-- Select Field Table --</option>
                                        <option value="students">Student</option>
                                        <option value="staff">Staff</option>
                                        <option value="documents">Document</option>
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
                                        <option value="student_matricno" data-table="students">Matric No</option>
                                        <option value="student_gender" data-table="students">Gender</option>
                                        <option value="student_phoneno" data-table="students">Phone No</option>
                                        <option value="student_email" data-table="students">Email</option>
                                        <option value="student_titleOfResearch" data-table="students">Title of Research
                                        </option>
                                        <option value="prog_code [prog_mode]" data-table="students">Programme Code [Mode]
                                        </option>

                                        <option value="" disabled>-- Staff --</option>
                                        <option value="staff_name" data-table="staff">Name</option>
                                        <option value="staff_email" data-table="staff">Email</option>
                                        <option value="staff_phoneno" data-table="staff">Phone No</option>

                                        <option value="" disabled>-- Document --</option>
                                        <option value="submission_document" data-table="documents">Journal/Conference Name
                                        </option>

                                        <option value="" disabled>-- Submission --</option>
                                        <option value="doc_name : [submission_duedate]" data-table="submissions">
                                            Submission Due Date</option>
                                        <option value="doc_name : [submission_date]" data-table="submissions">Submission
                                            Date</option>

                                        <option value="" disabled>-- Semester --</option>
                                        <option value="sem_label" data-table="semesters">Current Semester</option>
                                    </select>
                                </div>

                                <!-- Field Extra Datakey -->
                                <div class="mb-3 output-field-group">
                                    <label for="ff_extra_datakey" class="form-label">Extra Field Attribute</label>
                                    <select name="ff_extra_datakey" class="form-select" id="ff_extra_datakey">
                                        <option value="" selected>-- Select Extra Attribute --</option>
                                        <option value="supervision_role" data-table="staff">Supervision Role</option>
                                    </select>
                                </div>

                                <!-- Field Extra condition -->
                                <div class="mb-3 output-field-group">
                                    <label for="ff_extra_condition" class="form-label">Extra Condition</label>
                                    <select name="ff_extra_condition" class="form-select" id="ff_extra_condition">
                                        <option value="" selected>-- Select Extra Condition --</option>
                                        <option value="1" data-table="supervision_role">Main Supervisor
                                        </option>
                                        <option value="2" data-table="supervision_role">Co-Supervisor
                                        </option>

                                    </select>
                                </div>

                                <!-- Field Signature Role -->
                                <div class="mb-3 signature-field-group">
                                    <label for="ff_signature_role" class="form-label">Signature by</label>
                                    <select class="form-select" id="ff_signature_role" name="ff_signature_role">
                                        <option value="" selected>-- Select User Role --</option>
                                        <option value="1">Student</option>
                                        <option value="2">Main Supervisor</option>
                                        <option value="3">Co-Supervisor</option>
                                        <option value="4">Committee Member</option>
                                        <option value="5">Deputy Dean</option>
                                        <option value="6">Dean</option>
                                        <option value="7">Chairman</option>
                                        <option value="8">Examiner / Panel</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer bg-light justify-content-end">
                                <div class="flex-grow-1 text-end">
                                    <div class="col-sm-12">
                                        <div class="d-flex justify-content-between gap-3 align-items-center">
                                            <button type="button" class="btn btn-outline-secondary btn-pc-default w-100"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" id="addFormFieldBtn-submit"
                                                class="btn btn-primary w-100 d-block">
                                                Add Field
                                            </button>
                                            <button type="button" id="updateFormFieldBtn-submit"
                                                class="btn btn-primary w-100 d-none">
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Add & Update Form Field Modal ] end -->

                <form id="formStartupForm" action="{{ route('form-get-started-post') }}" method="post">
                    @csrf
                    <div class="modal fade startup-modal" id="formStartupModal" data-bs-backdrop="static"
                        data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">


                                <!-- Header -->
                                <div class="modal-header">
                                    <div class="w-100">
                                        <h3 class="modal-title text-white">
                                            <i class="ti ti-forms me-2"></i>
                                            e-PostGrad Form Builder
                                        </h3>
                                        <p class="modal-subtitle">Configure your form settings and guidelines before
                                            building</p>
                                    </div>
                                </div>

                                <!-- Scrollable Body -->
                                <div class="modal-body">
                                    <!-- Field Categories Overview -->
                                    <div class="mb-4">
                                        <div class="section-title">
                                            <span class="section-icon">
                                                <i class="ti ti-adjustments"></i>
                                            </span>
                                            Form Field Categories
                                        </div>

                                        <div class="category-grid">
                                            <div class="category-item">
                                                <div class="category-name">
                                                    <i class="ti ti-edit text-primary"></i>
                                                    Input*
                                                </div>
                                                <p class="category-desc">Interactive fields for data entry</p>
                                            </div>

                                            <div class="category-item">
                                                <div class="category-name">
                                                    <i class="ti ti-eye text-success"></i>
                                                    Output
                                                </div>
                                                <p class="category-desc">Read-only system fields</p>
                                            </div>

                                            <div class="category-item">
                                                <div class="category-name">
                                                    <i class="ti ti-section text-info"></i>
                                                    Section
                                                </div>
                                                <p class="category-desc">Form section headings</p>
                                            </div>

                                            <div class="category-item">
                                                <div class="category-name">
                                                    <i class="ti ti-file-text text-warning"></i>
                                                    Text
                                                </div>
                                                <p class="category-desc">Static information blocks</p>
                                            </div>

                                            <div class="category-item">
                                                <div class="category-name">
                                                    <i class="ti ti-writing-sign text-secondary"></i>
                                                    Signature
                                                </div>
                                                <p class="category-desc">Digital approval signatures</p>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <span class="note-badge">
                                                <i class="ti ti-info-circle"></i>
                                                Input fields are not allowed for Submission forms
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Specific Guidelines -->
                                    <div id="specificGuideline" class="guidelines-section">
                                        <!-- Dynamic content will be injected here -->
                                    </div>

                                    <!-- Hidden Fields -->
                                    <input type="hidden" id="startup_form_target" name="form_target" value="">
                                    <input type="hidden" id="startup_af_id" name="af_id" value="">
                                </div>

                                <!-- Modal Footer -->
                                <div class="modal-footer">
                                    <button type="submit" class="startup-submit-btn">
                                        <i class="ti ti-rocket me-2"></i>
                                        Start Building
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <!-- Ckeditor js -->
    <script src="../assets/js/plugins/ckeditor/classic/ckeditor.js"></script>
    <!-- jQuery UI (required for sortable) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>


    <script type="text/javascript">
        $(document).ready(function() {

            $('#options-input').on('input', function() {
                const options = $(this).val()
                    .split(',')
                    .map(opt => opt.trim())
                    .filter(opt => opt);
                $('#ff_value_options').val(JSON.stringify(options));
            });

            /*********************************************************
             ***************GLOBAL FUNCTION & VARIABLES***************
             *********************************************************/
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

            function stripHTML(html) {
                const div = document.createElement("div");
                div.innerHTML = html;
                return div.textContent || div.innerText || "";
            }

            function truncateText(text, maxLength = 50) {
                return text.length > maxLength ? text.substring(0, maxLength).trim() + "..." : text;
            }

            var selectedOpt = "{{ $formdata->activity_id }}";
            var af_id = "{{ $formdata->id }}";
            let debounceTimer;
            let fieldIdCounter = 0;
            let ckLabelEditor;

            getFormData();
            resetFormSections();
            resetExtraFields();


            /*********************************************************
             *******************CKEDITOR INITIALIZE*******************
             *********************************************************/

            ClassicEditor.create(document.querySelector('#ff_label-ckeditor'), {
                    toolbar: [
                        'heading', '|',
                        'bold', 'italic', '|',
                        'bulletedList', 'numberedList', '|',
                        'link', 'undo', 'redo'
                    ]
                })
                .then(editor => {
                    ckLabelEditor = editor;
                })
                .catch(error => {
                    console.error(error);
                });

            /*********************************************************
             *******************GETTERS FUNCTIONS*********************
             *********************************************************/

            function showFormStartupModal(formTarget, af_id) {
                // Set form target hidden field
                $('#startup_form_target').val(formTarget);
                $('#startup_af_id').val(af_id);

                let content = '';

                if (formTarget == 1) {
                    content = `
                    <div class="guidelines-title">
                        <span class="section-icon">
                            <i class="ti ti-file-upload"></i>
                        </span>
                        Submission Form Guidelines
                    </div>
                    <div class="guidelines-content">
                        <p>Configure your submission form with output fields and signatures only.</p>
                        
                        <div class="guideline-item">
                            <i class="ti ti-checkbox guideline-icon"></i>
                            <div>
                                <strong>Required Field:</strong> A <span class="highlight">signature field</span> is mandatory for all submission forms.
                            </div>
                        </div>
                        
                        <div class="guideline-item">
                            <i class="ti ti-settings guideline-icon"></i>
                            <div>
                                <strong>Auto-Generation:</strong> The system will create all necessary fields automatically for you.
                            </div>
                        </div>
                        
                        <div class="guideline-item">
                            <i class="ti ti-alert-triangle guideline-icon"></i>
                            <div>
                                <strong>Important:</strong> <span class="danger-text">Do not modify or remove system-generated fields</span> as this may cause submission failures.
                            </div>
                        </div>
                    </div>
                `;
                } else if (formTarget == 3) {
                    content = `
                    <div class="guidelines-title">
                        <span class="section-icon">
                            <i class="ti ti-users"></i>
                        </span>
                        Nomination Form Guidelines
                    </div>
                    <div class="guidelines-content">
                        <p>Set up nomination workflows with proper staff role mapping.</p>
                        
                        <div class="guideline-item">
                            <i class="ti ti-tags guideline-icon"></i>
                            <div>
                                <strong>Required Keywords:</strong> Use <span class="highlight">Examiner</span>, <span class="highlight">Panel</span>, and <span class="highlight">Chairman</span> in field labels for system mapping.
                            </div>
                        </div>
                        
                        <div class="guideline-item">
                            <i class="ti ti-tool guideline-icon"></i>
                            <div>
                                <strong>Customization:</strong> You can freely add or adjust custom fields as needed.
                            </div>
                        </div>
                        
                        <div class="guideline-item">
                            <i class="ti ti-shield-lock guideline-icon"></i>
                            <div>
                                <strong>System Fields:</strong> <span class="danger-text">Do not modify system-generated fields</span>. If modified, re-add them correctly.
                            </div>
                        </div>
                    </div>
                `;
                } else if (formTarget == 4 || formTarget == 5) {
                    const roleType = formTarget == 4 ? 'Chairman' : 'Examiner/Panel';
                    content = `
                    <div class="guidelines-title">
                        <span class="section-icon">
                            <i class="ti ti-clipboard-check"></i>
                        </span>
                        ${roleType} Evaluation Guidelines
                    </div>
                    <div class="guidelines-content">
                        <p>Configure evaluation criteria with proper status and scoring fields.</p>
                        
                        <div class="guideline-item">
                            <i class="ti ti-checklist guideline-icon"></i>
                            <div>
                                <strong>Status Fields:</strong> Include <span class="highlight">Status</span> or <span class="highlight">Decision</span> keywords in labels.
                                <div class="status-grid mt-2">
                                    <div class="status-item status-pass"><strong>Pass:</strong> Pass/Passed</div>
                                    <div class="status-item status-minor"><strong>Minor:</strong> Minor/Small</div>
                                    <div class="status-item status-major"><strong>Major:</strong> Major/Many</div>
                                    <div class="status-item status-resubmit"><strong>Resubmit:</strong> Represent/Resubmit</div>
                                    <div class="status-item status-fail"><strong>Fail:</strong> Fail/Failed</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="guideline-item">
                            <i class="ti ti-calculator guideline-icon"></i>
                            <div>
                                <strong>Score Fields:</strong> Use <span class="highlight">Score</span> or <span class="highlight">Mark</span> keywords (e.g., "Examiner 1 Score").
                            </div>
                        </div>
                        
                        <div class="guideline-item">
                            <i class="ti ti-writing-sign guideline-icon"></i>
                            <div>
                                <strong>Signature Labels:</strong> Use <span class="highlight">Chairman</span>, <span class="highlight">Examiner</span>, or <span class="highlight">Panel</span>. For multiple: "Examiner 1", "Panel 1", etc.
                            </div>
                        </div>
                        
                        ${formTarget == 5 ? `
                                                                <div class="guideline-item">
                                                                    <i class="ti ti-list-details guideline-icon"></i>
                                                                    <div>
                                                                        <strong>Additional Fields:</strong> <span class="highlight">Criteria</span> and <span class="highlight">Evaluation Level</span> fields will be auto-generated.
                                                                    </div>
                                                                </div>` : ''}
                        
                        <div class="guideline-item">
                            <i class="ti ti-shield-lock guideline-icon"></i>
                            <div>
                                <strong>System Protection:</strong> <span class="danger-text">Do not modify system-generated fields</span> to maintain evaluation integrity.
                            </div>
                        </div>
                    </div>
                `;
                }

                // Inject into modal content
                $('#specificGuideline').html(content);

                // Show modal
                $('#formStartupModal').modal('show');
            }

            function validateFunctions(formTarget) {
                if (formTarget == 1) {
                    $('#ff_category option[value="1"]').prop('disabled', true);
                }
            }


            function getFormData() {
                const addFFBtn = $('#addFormFieldBtn');
                $.ajax({
                    url: "{{ route('get-activity-form-data-post') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        af_id: af_id,
                        actid: selectedOpt
                    },
                    success: function(response) {
                        $('#preview-loader').removeClass('d-none');

                        $('#documentContainer').on('load', function() {
                            $('#preview-loader').addClass('d-none');
                        });

                        if (response.success) {
                            $('#txt_form_title').val(response.formTitle);
                            $('#select_form_target').val(response.formTarget);
                            validateFunctions(response.formTarget);
                            $('#select_form_target_hidden').val(response.formTarget);
                            $('#select_form_status').val(response.formStatus);
                            $('#documentContainer').attr('src',
                                '{{ route('activity-document-preview-get') }}' +
                                '?actid=' + encodeURIComponent(selectedOpt) +
                                '&af_id=' + encodeURIComponent(af_id) +
                                '&title=' + response.formTitle
                            );
                            addFFBtn.prop('disabled', false);
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
                            addFFBtn.prop('disabled', true);

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
                                showFormStartupModal($('#select_form_target_hidden').val(), af_id);
                                $fieldList.append(`
                                    <li class="list-group-item text-center text-muted" id="noFieldMsg">
                                        This form doesn’t have any fields. Add one to get started!
                                    </li>
                                `);
                            } else {
                                const sortedFields = response.fields.sort((a, b) => a.ff_order - b
                                    .ff_order);

                                sortedFields.forEach(field => {
                                    appendFormField(field.ff_label, field.ff_category,
                                        field
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

            /*********************************************************
             ******************UPDATE PREVIEW FUNCTION****************
             *********************************************************/

            $('#updatePreviewBtn').click(function() {
                getFormData();
            });

            /*********************************************************
             ****************FORM SETTING FUNCTION********************
             *********************************************************/

            // SAVE FORM SETTING 
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
                            showToast('error', 'Something went wrong. Please try again.');
                        }
                    }
                });
            });

            /*********************************************************
             ******************FORM FIELD FORM CONTROL****************
             *********************************************************/

            // CATEGORY FILTER
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
                if (category == 6) {
                    $('.signature-field-group').show();
                    $('#ff_label').parent().show();
                    $('#ff_label-ckeditor').parent().hide();
                }
            });

            // RESET FORM FUNCTIONS
            function resetFormSections() {
                $('.input-field-group').hide();
                $('.output-field-group').hide();
                $('.signature-field-group').hide();

                $('#ff_label').parent().hide();
                $('#ff_label-ckeditor').parent().hide();

                $('#ff_datakey').prop('disabled', true);

                $('.table-settings-group').hide();
            }

            function resetExtraFields() {
                $('#ff_table').trigger('change');
                $('#ff_extra_datakey').val("").prop('disabled', true);
                $('#ff_extra_condition').val("").prop('disabled', true);
            }

            // EXTRA FIELD AND CONDITION FILTER
            $('#ff_table').on('change', function() {
                const selectedTable = $(this).val();

                $('#ff_datakey').prop('disabled', selectedTable === '');
                $('#ff_datakey').val('');
                $('#ff_datakey option').each(function() {
                    const table = $(this).data('table');
                    if ($(this).is(':disabled') || !table) {
                        $(this).hide();
                    } else if (table === selectedTable) {
                        $(this).show();
                        $('#ff_extra_datakey').val("").prop('disabled', true);
                        $('#ff_extra_condition').val("").prop('disabled', true);
                    } else {
                        $(this).hide();
                    }
                });


                if (selectedTable === 'staff') {
                    $('#ff_extra_datakey option').each(function() {
                        const table = $(this).data('table');
                        if (table === 'staff') {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });

                    $('#ff_extra_datakey').prop('disabled', false).show();
                }
            });

            $('#ff_extra_datakey').on('change', function() {
                const selectedKey = $(this).val();

                if (selectedKey === 'supervision_role') {
                    $('#ff_extra_condition option').each(function() {
                        const table = $(this).data('table');
                        if (table === 'supervision_role') {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });

                    $('#ff_extra_condition').prop('disabled', false).show();
                } else {
                    $('#ff_extra_condition').val('').prop('disabled', true).hide();
                }
            });

            $('#ff_component_type').on('change', function() {
                const type = $(this).val();

                if (type == '') {
                    $('.required-option').hide();
                    $('.placeholder-option').hide();
                    $('.value-options').hide();
                    $('.append-text').hide();
                } else if (type == 'select' || type == 'checkbox' || type == 'radio') {
                    $('.value-options').show();
                    $('.placeholder-option').hide();
                    $('.required-option').show();
                    $('.append-text').show();
                } else if (type == 'date' || type == 'datetime-local') {
                    $('.value-options').hide();
                    $('.placeholder-option').hide();
                    $('.required-option').show();
                    $('.append-text').show();
                } else if (type == 'longtextarea') {
                    $('.value-options').hide();
                    $('.placeholder-option').show();
                    $('.required-option').show();
                    $('.append-text').show();
                } else {
                    $('.value-options').hide();
                    $('.placeholder-option').show();
                    $('.required-option').show();
                    $('.append-text').show();
                }
            });

            $('#ff_component_required').on('change', function() {
                const required = $(this).val();
                if (required == 1) {
                    $('#ff_component_required_role').prop('disabled', false);
                } else {
                    $('#ff_component_required_role').prop('disabled', true);
                }
            });

            // VALUE OPTIONS COLUMN CONTROL
            function loadTableColumns(table, callback) {
                if (table) {
                    $.ajax({
                        url: "{{ route('get-table-columns-get') }}",
                        method: "GET",
                        data: {
                            table: table
                        },
                        success: function(response) {
                            const columnSelect = $('#ff_value_options_column');
                            columnSelect.empty().append(
                                '<option value="">-- Select Column --</option>');

                            response.columns.forEach(column => {
                                columnSelect.append(
                                    `<option value="${column}">${column}</option>`);
                            });

                            if (callback) callback();
                        }
                    });
                }
            }

            $('#ff_value_options_table').change(function() {
                loadTableColumns($(this).val());
            });


            // TABLE SETTINGS [UNUSED]
            $('#ff_is_table').on('change', function() {
                if ($(this).prop('checked')) {
                    $('.table-settings-group').show();
                } else {
                    $('.table-settings-group').hide();
                }
            });

            /*********************************************************
             ***************DRAG & DROP SECTION CONTROL***************
             *********************************************************/

            // DESIGN FORM FIELD APPEND FUNCTION
            function appendFormField(label, datakey, order, ff_id = null) {
                const id = ff_id ?? `temp_${fieldIdCounter++}`;
                const shortLabel = truncateText(stripHTML(label), 12);
                const item = `
                    <li class="list-group-item draggable-item" data-id="${id}">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="drag-handle text-muted" title="Drag to reorder">
                                <i class="ti ti-grip-vertical fs-5"></i>
                            </span>
                            <div class="flex-grow-1">
                                <strong class="d-block">${shortLabel}</strong>
                                <small class="text-muted text-truncate d-block" style="max-width: 180px">${datakey || 'Custom Field'}</small>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-icon btn-outline-primary move-up-btn" data-id="${id}" title="Move Up">
                                    <i class="ti ti-arrow-up"></i>
                                </button>
                                <button class="btn btn-sm btn-icon btn-outline-primary move-down-btn" data-id="${id}" title="Move Down">
                                    <i class="ti ti-arrow-down"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between gap-2">
                            <button class="btn btn-sm btn-outline-secondary flex-grow-1 update-field-btn" data-id="${id}" data-label="${label}" data-key="${datakey}">
                                <i class="ti ti-edit me-1"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-secondary flex-grow-1 copy-field-btn" data-id="${id}" data-key="${datakey}">
                                <i class="ti ti-copy me-1"></i> Copy
                            </button>
                            <button class="btn btn-sm btn-outline-danger flex-grow-1 delete-field-btn" data-id="${id}">
                                <i class="ti ti-trash me-1"></i> Delete
                            </button>
                        </div>
                    </li>
                `;
                $('#fieldList').append(item);
            }

            // DRAG AND DROP INITIALIZATION & UPDATE FIELD ORDERING
            $('#fieldList').sortable({
                placeholder: "ui-state-highlight",
                update: updateOrderAjax
            }).disableSelection();

            $(document).on('click', '.move-up-btn', function() {
                const item = $(this).closest('li');
                const prev = item.prev('li');
                if (prev.length) {
                    item.insertBefore(prev).hide().slideDown();
                    updateOrderAjax();
                }
            });

            $(document).on('click', '.move-down-btn', function() {
                const item = $(this).closest('li');
                const next = item.next('li');
                if (next.length) {
                    item.insertAfter(next).hide().slideDown();
                    updateOrderAjax();
                }
            });

            function updateOrderAjax() {
                const newOrder = [];
                $('#fieldList li').each(function(index) {
                    newOrder.push({
                        id: $(this).data('id'),
                        order: index + 1
                    });
                });

                $.ajax({
                    url: "{{ route('update-order-form-field-post') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        fields: newOrder
                    },
                    success: function(response) {
                        if (response.success) {} else {
                            showToast('error', response.message);
                        }
                    },
                    error: function() {
                        showToast('error', 'Failed to update field order.');
                    }
                });
            }

            /*********************************************************
             ******************FORM FIELD CRUD CONTROL****************
             *********************************************************/

            function modalInit(option, isOpen) {
                if (option == "add") {
                    // MODAL TITLE
                    $('#formFieldModalLabel').html('Add Form Field');

                    // SUBMIT BUTTON SECTIONS
                    $('#addFormFieldBtn-submit').removeClass('d-none');
                    $('#addFormFieldBtn-submit').addClass('d-block');
                    $('#updateFormFieldBtn-submit').removeClass('d-block');
                    $('#updateFormFieldBtn-submit').addClass('d-none');

                    // RESET FORM
                    $('#formFieldModal').modal('hide');
                    $('#ff_label').val('');
                    ckLabelEditor.setData('');
                    $('#ff_category').val('');
                    $('#ff_category').trigger('change');
                    $('#ff_category').prop('disabled', false);
                    $('#ff_component_type').val('');
                    $('#ff_placeholder').val('');
                    $('#ff_component_required').val('1');
                    $('#ff_component_required_role').val('0');
                    $('#value_options').val('');
                    $('#ff_value_options').val('');
                    $('#ff_value_options_table').val('');
                    $('#ff_value_options_column').val('');
                    $('#ff_append_text').val('');
                    $('#ff_table').val('');
                    $('#ff_datakey').val('');
                    $('#ff_signature_role').val('');

                    resetFormSections();
                    resetExtraFields();


                } else if (option == "update") {
                    // MODAL TITLE
                    $('#formFieldModalLabel').html('Update Form Field');

                    // SUBMIT BUTTON SECTIONS
                    $('#updateFormFieldBtn-submit').addClass('d-block');
                    $('#updateFormFieldBtn-submit').removeClass('d-none');
                    $('#addFormFieldBtn-submit').addClass('d-none');
                    $('#addFormFieldBtn-submit').removeClass('d-block');

                } else if (option == "copy") {
                    // MODAL TITLE
                    $('#formFieldModalLabel').html('Copy Form Field');

                    // SUBMIT BUTTON SECTIONS
                    $('#addFormFieldBtn-submit').removeClass('d-none');
                    $('#addFormFieldBtn-submit').addClass('d-block');
                    $('#updateFormFieldBtn-submit').removeClass('d-block');
                    $('#updateFormFieldBtn-submit').addClass('d-none');

                    // RESET CERTAIN DATA
                    $('#ff_label').val('');
                    ckLabelEditor.setData('');

                }

                if (isOpen) {
                    $('#formFieldModal').modal('show');
                }
            }

            // TRIGGER: ADD BUTTON
            $('#addFormFieldBtn').click(function() {
                modalInit('add', true);
            });

            // TRIGGER: COPY BUTTON
            $(document).on('click', '.copy-field-btn', function() {
                const id = $(this).data('id');
                $('#ff_id-hidden').val(id);
                const key = $(this).data('key');

                $.ajax({
                    url: "{{ route('get-single-form-field-data-get') }}",
                    method: "GET",
                    data: {
                        ff_id: id
                    },
                    success: function(response) {
                        modalInit('copy', true);
                        $('#ff_category').val(response.fields.ff_category);
                        $('#ff_category').trigger('change');
                        $('#ff_category').prop('disabled', true);
                        $('#ff_component_type').val(response.fields.ff_component_type);
                        $('#ff_component_type').trigger('change');
                        $('#ff_placeholder').val(response.fields.ff_placeholder);
                        $('#ff_component_required').val(response.fields.ff_component_required);
                        $('#ff_component_required').trigger('change');
                        $('#ff_component_required_role').val(response.fields
                            .ff_component_required_role);
                        const valueOptions = response.fields.ff_value_options;
                        try {
                            const optionsJson = JSON.parse(valueOptions);
                            if (optionsJson && optionsJson.table) {

                                $('#ff_value_options').val('');
                                $('#ff_value_options_table').val(optionsJson.table);

                                loadTableColumns(optionsJson.table, function() {
                                    $('#ff_value_options_column').val(optionsJson
                                        .column);
                                });
                            } else {
                                // Manual options
                                $('#ff_value_options').val(valueOptions);
                                $('#ff_value_options_table').val('');
                                $('#ff_value_options_column').val('');

                                const initialOptions = $('#ff_value_options').val();
                                if (initialOptions) {
                                    try {
                                        const optionsArray = JSON.parse(initialOptions);
                                        $('#options-input').val(optionsArray.join(', '));
                                    } catch (e) {
                                        console.error("Invalid JSON in ff_value_options", e);
                                    }
                                }
                            }
                        } catch (e) {
                            // Not JSON - manual options
                            $('#ff_value_options').val(valueOptions);
                            $('#ff_value_options_table').val('');
                            $('#ff_value_options_column').val('');
                        }
                        $('#ff_append_text').val(response.fields.ff_append_text);
                        $('#ff_table').val(response.fields.ff_table);
                        $('#ff_table').trigger('change');
                        $('#ff_datakey').val(response.fields.ff_datakey);
                        $('#ff_datakey').trigger('change');
                        $('#ff_extra_datakey').val(response.fields.ff_extra_datakey);
                        if (response.fields.ff_extra_datakey !== null) {
                            $('#ff_extra_datakey').trigger('change');

                        }
                        $('#ff_extra_condition').val(response.fields.ff_extra_condition);
                        $('#ff_signature_role').val(response.fields.ff_signature_role);
                    },
                    error: function() {
                        showToast('error', 'Failed to load the form field data.');
                    }
                });


            });

            // ADD FORM FIELD FUNCTION
            $('#addFormFieldBtn-submit').click(function() {

                var rowCategory = $('#ff_category').val();
                var rowLabel;
                if (rowCategory != "4") {
                    rowLabel = $('#ff_label').val();
                } else {
                    rowLabel = ckLabelEditor.getData();
                }
                var rowType = $('#ff_component_type').val();
                var rowPlaceholder = $('#ff_placeholder').val();
                var rowRequired = $('#ff_component_required').val();
                var rowRequiredRole = $('#ff_component_required_role').val();
                var manualOptions = $('#ff_value_options').val();
                var table = $('#ff_value_options_table').val();
                var column = $('#ff_value_options_column').val();

                let valueOptions = '';

                if (manualOptions) {
                    valueOptions = manualOptions;
                } else if (table && column) {
                    valueOptions = JSON.stringify({
                        table: table,
                        column: column
                    });
                }
                var rowAppendText = $('#ff_append_text').val();
                var rowTable = $('#ff_table').val();
                var rowDataKey = $('#ff_datakey').val();
                var rowExtraDatakey = $('#ff_extra_datakey').val();
                var rowExtraCondition = $('#ff_extra_condition').val();
                var rowSignatureRole = $('#ff_signature_role').val();

                if (rowCategory == "1" && !rowType) {
                    showToast('error', 'Please select component type first before proceed.');
                    return;
                } else if (rowCategory == "2" && !rowTable) {
                    showToast('error', 'Please select table first before proceed.');
                    return;
                } else if (rowCategory == "6" && !rowSignatureRole) {
                    showToast('error', 'Please select signature by first before proceed.');
                    return;
                } else if (table && !column) {
                    showToast('error', 'Please select the column first before proceed.');
                    return;
                }

                if (rowTable && !rowDataKey) {
                    showToast('error', 'Please select datakey first before proceed.');
                    return;
                }
                if (rowTable === 'staff' && (!rowExtraDatakey || !rowExtraCondition)) {
                    showToast('error', 'Please select extra datakey and condition for staff table.');
                    return;
                }

                var requestData = {
                    _token: "{{ csrf_token() }}",
                    actid: selectedOpt,
                    af_id: af_id,
                    ff_label: rowLabel,
                    ff_category: rowCategory,
                    ff_component_type: rowType,
                    ff_placeholder: rowPlaceholder,
                    ff_component_required: rowRequired,
                    ff_component_required_role: rowRequiredRole,
                    ff_value_options: valueOptions,
                    ff_append_text: rowAppendText,
                    ff_table: rowTable,
                    ff_datakey: rowDataKey,
                    ff_extra_datakey: rowExtraDatakey,
                    ff_extra_condition: rowExtraCondition,
                    ff_signature_role: rowSignatureRole,
                };

                $.ajax({
                    url: "{{ route('add-form-field-post') }}",
                    type: "POST",
                    data: requestData,
                    success: function(response) {
                        if (response.success) {
                            $('#formFieldModal').modal('hide');
                            modalInit("add", false);
                            getFormData();
                            showToast('success', response.message);

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

            // TRIGGER: UPDATE BUTTON
            $(document).on('click', '.update-field-btn', function() {
                const id = $(this).data('id');
                $('#ff_id-hidden').val(id);
                const label = $(this).data('label');
                const key = $(this).data('key');

                $.ajax({
                    url: "{{ route('get-single-form-field-data-get') }}",
                    method: "GET",
                    data: {
                        ff_id: id
                    },
                    success: function(response) {
                        modalInit('update', true);
                        $('#ff_category').val(response.fields.ff_category);
                        $('#ff_category').trigger('change');
                        $('#ff_category').prop('disabled', true);
                        $('#ff_label').val(label);
                        ckLabelEditor.setData(label);
                        $('#ff_component_type').val(response.fields.ff_component_type);
                        $('#ff_component_type').trigger('change');
                        $('#ff_placeholder').val(response.fields.ff_placeholder);
                        $('#ff_component_required').val(response.fields.ff_component_required);
                        $('#ff_component_required').trigger('change');
                        $('#ff_component_required_role').val(response.fields
                            .ff_component_required_role);
                        const valueOptions = response.fields.ff_value_options;
                        try {
                            const optionsJson = JSON.parse(valueOptions);
                            if (optionsJson && optionsJson.table) {
                                // Options are from table
                                $('#ff_value_options').val('');
                                $('#ff_value_options_table').val(optionsJson.table);

                                // Load columns and then select the saved column
                                loadTableColumns(optionsJson.table, function() {
                                    $('#ff_value_options_column').val(optionsJson
                                        .column);
                                });
                            } else {
                                // Manual options
                                $('#ff_value_options').val(valueOptions);
                                $('#ff_value_options_table').val('');
                                $('#ff_value_options_column').val('');

                                const initialOptions = $('#ff_value_options').val();
                                if (initialOptions) {
                                    try {
                                        const optionsArray = JSON.parse(initialOptions);
                                        $('#options-input').val(optionsArray.join(', '));
                                    } catch (e) {
                                        console.error("Invalid JSON in ff_value_options", e);
                                    }
                                }
                            }
                        } catch (e) {
                            // Not JSON - manual options
                            $('#ff_value_options').val(valueOptions);
                            $('#ff_value_options_table').val('');
                            $('#ff_value_options_column').val('');
                        }
                        $('#ff_append_text').val(response.fields.ff_append_text);
                        $('#ff_table').val(response.fields.ff_table);
                        $('#ff_table').trigger('change');
                        $('#ff_datakey').val(response.fields.ff_datakey);
                        $('#ff_datakey').trigger('change');
                        $('#ff_extra_datakey').val(response.fields.ff_extra_datakey);
                        $('#ff_extra_condition').val(response.fields.ff_extra_condition);
                        if (response.fields.ff_extra_datakey !== null) {
                            $('#ff_extra_datakey').trigger('change');
                        }
                        $('#ff_signature_role').val(response.fields.ff_signature_role);
                    },
                    error: function() {
                        showToast('error', 'Failed to load the form field data.');
                    }
                });
            });

            // UPDATE FORM FIELD FUNCTION
            $('#updateFormFieldBtn-submit').click(function() {

                var rowID = $('#ff_id-hidden').val();
                var rowCategory = $('#ff_category').val();
                var rowLabel;
                if (rowCategory != "4") {
                    rowLabel = $('#ff_label').val();
                } else {
                    rowLabel = ckLabelEditor.getData();
                }
                var rowType = $('#ff_component_type').val();
                var rowPlaceholder = $('#ff_placeholder').val();
                var rowRequired = $('#ff_component_required').val();
                var rowRequiredRole = $('#ff_component_required_role').val();
                var manualOptions = $('#ff_value_options').val();
                var table = $('#ff_value_options_table').val();
                var column = $('#ff_value_options_column').val();

                let valueOptions = '';

                if (manualOptions) {
                    valueOptions = manualOptions;
                } else if (table && column) {
                    valueOptions = JSON.stringify({
                        table: table,
                        column: column
                    });
                }
                var rowAppendText = $('#ff_append_text').val();
                var rowTable = $('#ff_table').val();
                var rowDataKey = $('#ff_datakey').val();
                var rowExtraDatakey = $('#ff_extra_datakey').val();
                var rowExtraCondition = $('#ff_extra_condition').val();
                var rowSignatureRole = $('#ff_signature_role').val();

                if (rowCategory == "1" && !rowType) {
                    showToast('error', 'Please select component type first before proceed.');
                    return;
                } else if (rowCategory == "2" && !rowTable) {
                    showToast('error', 'Please select table first before proceed.');
                    return;
                } else if (rowCategory == "6" && !rowSignatureRole) {
                    showToast('error', 'Please select signature by first before proceed.');
                    return;
                } else if (table && !column) {
                    showToast('error', 'Please select the column first before proceed.');
                    return;
                }

                if (rowTable && !rowDataKey) {
                    showToast('error', 'Please select datakey first before proceed.');
                    return;
                }

                if (rowTable === 'staff' && (!rowExtraDatakey || !rowExtraCondition)) {
                    showToast('error', 'Please select extra datakey and condition for staff table.');
                    return;
                }

                var requestData = {
                    _token: "{{ csrf_token() }}",
                    ff_id: rowID,
                    ff_label: rowLabel,
                    ff_category: rowCategory,
                    ff_component_type: rowType,
                    ff_placeholder: rowPlaceholder,
                    ff_component_required: rowRequired,
                    ff_component_required_role: rowRequiredRole,
                    ff_value_options: valueOptions,
                    ff_append_text: rowAppendText,
                    ff_table: rowTable,
                    ff_datakey: rowDataKey,
                    ff_extra_datakey: rowExtraDatakey,
                    ff_extra_condition: rowExtraCondition,
                    ff_signature_role: rowSignatureRole,
                };

                $.ajax({
                    url: "{{ route('update-form-field-post') }}",
                    type: "POST",
                    data: requestData,
                    success: function(response) {
                        if (response.success) {
                            $('#formFieldModal').modal('hide');
                            modalInit("add", false);
                            getFormData();
                            showToast('success', response.message);
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

            // DELETE FORM FIELD FUNCTION
            $(document).on('click', '.delete-field-btn', function() {
                $.ajax({
                    url: "{{ route('delete-form-field-post') }}",
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
