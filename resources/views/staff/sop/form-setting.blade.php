@extends('staff.layouts.main')

@section('content')
    <style>
        .text-start-custom {
            text-align: start !important;
            width: 20px !important;
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
                                <li class="breadcrumb-item" aria-current="page">Form Setting</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Form Setting</h2>
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
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                <div id="toastContainer"></div>
            </div>
            <!-- [ Alert ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ Form Setting ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Form Title</th>
                                            <th scope="col">Target</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                            <th scope="col">Activity</th>
                                            <th scope="col"></th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                @foreach ($acts as $act)
                    <!-- [ Add Form Modal ] start -->
                    <div class="modal fade" id="addFormModal-{{ $act->id }}" tabindex="-1"
                        aria-labelledby="addFormModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addModalLabel">Add Form</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Activity</label>
                                                <select name="activity_id" class="form-select"
                                                    id="selectActivity-{{ $act->id }}">
                                                    <option value="{{ $act->id }}">{{ $act->act_name }}</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Form Title</label>
                                                <input type="text" name="form_title"
                                                    id="txt_form_title-{{ $act->id }}" class="form-control"
                                                    placeholder="Enter Form Title">
                                            </div>

                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Form Target</label>
                                                <select name="select_form_target" class="form-select"
                                                    id="select_form_target-{{ $act->id }}">
                                                    <option value="" selected>-- Select Target --</option>
                                                    <option value="1">Submission</option>
                                                    <option value="2">Evaluation</option>
                                                    <option value="3">Nomination</option>
                                                </select>

                                            </div>

                                            <div class="mb-3">
                                                <label for="txt_label" class="form-label">Form Status</label>
                                                <select name="select_form_status" class="form-select"
                                                    id="select_form_status-{{ $act->id }}">
                                                    <option value="" selected>-- Select Status --</option>
                                                    <option value="1">Active</option>
                                                    <option value="2">Inactive</option>
                                                </select>
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
                                                <button type="button" id="addForm-submit-{{ $act->id }}"
                                                    class="btn btn-primary w-100 addForm-submit-btn"
                                                    data-activity="{{ $act->id }}">
                                                    Add Form
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Add Form Modal ] end -->
                @endforeach

                @foreach ($actForms as $af)
                    <!-- [ Delete Modal ] start -->
                    <div class="modal fade" id="deleteModal-{{ $af->id }}"
                        data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
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
                                                <p class="fw-normal f-18 text-center">This action will remove all the form data and cannot be undone.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                                <button type="reset" class="btn btn-light btn-pc-default w-50"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <a href="{{ route('delete-form-activity-get', ['afID' => Crypt::encrypt($af->id)]) }}"
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
                <!-- [ Form Setting ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        let groupIndexMap = {}; // must be declared outside

        $(document).ready(function() {
            var table = $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('form-setting') }}",
                },
                columns: [{
                        data: null,
                        name: 'index',
                        orderable: false,
                        searchable: false,
                        className: 'text-start-custom',
                    },
                    {
                        data: 'form_title',
                        name: 'form_title'
                    },
                    {
                        data: 'form_target',
                        name: 'form_target'
                    },
                    {
                        data: 'form_status',
                        name: 'form_status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'act_name',
                        name: 'act_name',
                        visible: false,
                        orderable: false
                    },
                    {
                        data: 'form_count',
                        name: 'form_count',
                        visible: false,
                        searchable: false
                    },
                    {
                        data: 'activity_id',
                        name: 'activity_id',
                        visible: false,
                        searchable: false
                    },
                ],
                order: [
                    [6, 'desc'],
                    [5, 'asc']
                ],
                rowGroup: {
                    dataSrc: 'act_name',
                    startRender: function(rows, group) {
                        const activityId = rows.data()[0].activity_id;

                        return $('<tr/>')
                            .append(`
                                <td colspan="7" class="bg-light">
                                    <div class="d-flex justify-content-between align-items-center mt-2 mb-2">
                                        <span class="fw-semibold text-uppercase me-2">
                                            ${group}
                                        </span>
                                        <button type="button" class="d-flex align-items-center btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFormModal-${activityId}">
                                            <i class="ti ti-plus f-18"></i> 
                                        </button>
                                    </div>
                                </td>
                            `);
                    }
                },
                rowCallback: function(row, data, index) {
                    let group = data.act_name;

                    if (!groupIndexMap[group]) {
                        groupIndexMap[group] = 1;
                    }

                    if (data.af_id !== null) {
                        $('td:eq(0)', row).html(groupIndexMap[group]++);
                    } else {
                        $('td:eq(0)', row).html('');
                    }
                },
                drawCallback: function(settings) {
                    groupIndexMap = {};
                }
            });

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

            $('.addForm-submit-btn').click(function() {
                const activityId = $(this).data('activity');

                const selectedOpt = $('#selectActivity-' + activityId).val();
                const formTarget = $('#select_form_target-' + activityId).val();
                const formStatus = $('#select_form_status-' + activityId).val();
                const formTitle = $('#txt_form_title-' + activityId).val();

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
                            $('.data-table').DataTable().ajax.reload();
                            $('#addFormModal-' + selectedOpt).modal('hide');

                            // Reset the fields
                            $('#select_form_target-' + selectedOpt).val('');
                            $('#select_form_status-' + selectedOpt).val('');
                            $('#txt_form_title-' + selectedOpt).val('');

                            window.location.href = "form-generator-" + response.activityForm
                                .id + "-" + response.activityForm.af_target;
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
        });
    </script>
@endsection
