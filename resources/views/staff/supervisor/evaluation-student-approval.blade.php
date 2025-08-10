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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Supervisor</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">My Supervision</a></li>
                                <li class="breadcrumb-item"><a
                                        href="{{ route('my-supervision-evaluation-approval', strtolower(str_replace(' ', '-', $activity->act_name))) }}">{{ $activity->act_name }}
                                        -
                                        Evaluation
                                        Management</a></li>
                                <li class="breadcrumb-item" aria-current="page">
                                    {{ $student->student_name }} - Evaluation Approval
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h3 class="mb-0 d-flex align-items-center ">
                                    <a href="{{ route('my-supervision-evaluation-approval', strtolower(str_replace(' ', '-', $activity->act_name))) }}"
                                        class="btn me-2">
                                        <span class="f-18">
                                            <i class="ti ti-arrow-left"></i>
                                        </span>
                                    </a>
                                    {{ $student->student_name }} - Evaluation Approval
                                </h3>
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
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                    <div id="toastContainer"></div>
                </div>
            </div>
            <!-- [ Alert ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- [ Supervisor - Evaluation Approval ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">

                            <!-- [ Filter Section ] Start -->
                            <div class="row g-3 align-items-end">

                                <div class="col-sm-12 col-md-3 mb-3">
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
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSemFilter">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-3 mb-3">
                                    <div class="input-group">
                                        <select id="fil_status" class="form-select">
                                            <option value="">-- Select Status --</option>
                                            <option value="1">Pending</option>
                                            <option value="7">Submitted [Draft]</option>
                                            <option value="10">Final & Confirmed</option>
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
                                            <th>#</th>
                                            <th scope="col">Student</th>
                                            <th scope="col">Evaluator Report</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- [ Supervisor - Evaluation Approval ] end -->


                @foreach ($data as $upd)
                    <!-- [ Approve Modal ] Start -->
                    <form method="POST"
                        action="{{ route('approve-evaluation-post', ['evaluationID' => Crypt::encrypt($upd->evaluation_id), 'option' => 1]) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal fade" id="approveModal-{{ $upd->evaluation_id }}" data-bs-keyboard="false"
                            tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <div class="row">

                                            <!-- Icon -->
                                            <div class="col-sm-12 mb-4 text-center">
                                                <i class="ti ti-circle-check text-success" style="font-size: 100px"></i>
                                            </div>

                                            <!-- Title -->
                                            <div class="col-sm-12 text-center">
                                                <h2 class="f-18">Approve Evaluation Report?</h2>
                                            </div>

                                            <!-- Instruction -->
                                            <div class="col-sm-12 mb-3">
                                                <div class="alert alert-light border text-start f-14">
                                                    <strong class="d-block mb-1">Instructions:</strong>
                                                    <ul class="mb-2 ps-3">
                                                        <li>Carefully review the panel's evaluation report.</li>
                                                        <li>Sign using the signature box to confirm your approval.</li>
                                                        <li>Click <strong>"Confirm & Sign"</strong> to finalize this action.
                                                        </li>
                                                        <li class="text-danger"><strong>This action is final and cannot be
                                                                undone.</strong></li>
                                                    </ul>
                                                    <div class="mt-2">
                                                        <strong class="text-muted fst-italic d-block">
                                                            By signing below, you confirm that the signature is your own
                                                            handwriting and that it is legally binding within the
                                                            institution’s authority and applicable regulations.
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Signature Canvas -->
                                            <div class="col-sm-12 mb-3">
                                                <label class="form-label">Signature <span
                                                        class="text-danger">*</span></label>
                                                <canvas id="signatureCanvas-{{ $upd->evaluation_id }}"
                                                    style="border:1px solid #000000; border-radius:10px; width:100%; height:200px;"></canvas>
                                                <input type="hidden" name="signatureData"
                                                    id="signatureData-{{ $upd->evaluation_id }}">
                                            </div>

                                            <!-- Signature Actions -->
                                            <div class="col-sm-12 mb-3 d-flex justify-content-between gap-3">
                                                <button type="button" class="btn btn-light w-100"
                                                    data-clear="{{ $upd->evaluation_id }}">
                                                    <i class="ti ti-eraser me-2"></i> Clear Signature
                                                </button>
                                            </div>

                                            <!-- Signature Notice -->
                                            <div class="col-sm-12 mb-3">
                                                <div class="d-flex align-items-center text-muted">
                                                    <div class="avtar avtar-s bg-light-primary flex-shrink-0 me-2">
                                                        <i class="fas fa-shield-alt text-primary f-20"></i>
                                                    </div>
                                                    <span class="text-sm">All signatures are securely stored within the
                                                        system.</span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <div class="d-flex justify-content-between gap-3 w-100">
                                            <button type="button" class="btn btn-light w-50"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success w-100"
                                                data-submit="{{ $upd->evaluation_id }}">
                                                <i class="ti ti-writing-sign me-2"></i> Confirm & Sign
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Approve Modal ] End -->

                    <!-- [ Reject Modal ] Start -->
                    <form method="POST"
                        action="{{ route('approve-evaluation-post', ['evaluationID' => Crypt::encrypt($upd->evaluation_id), 'option' => 2]) }}">
                        @csrf
                        <div class="modal fade" id="rejectModal-{{ $upd->evaluation_id }}" data-bs-keyboard="false"
                            tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered  modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <div class="row">

                                            <!-- Icon -->
                                            <div class="col-sm-12 mb-4">
                                                <div class="d-flex justify-content-center align-items-center mb-3">
                                                    <i class="ti ti-circle-x text-danger" style="font-size: 100px"></i>
                                                </div>
                                            </div>

                                            <!-- Title -->
                                            <div class="col-sm-12">
                                                <div class="d-flex justify-content-center align-items-center text-center">
                                                    <h2 class="f-18">Reject Evaluation Report?</h2>
                                                </div>
                                            </div>

                                            <!-- Message -->
                                            <div class="col-sm-12 mb-3">
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <p class="fw-normal f-14 text-center text-muted">
                                                        This action will mark the panel's evaluation report as
                                                        <strong>rejected</strong>.<br>
                                                        The panel will need to review and <strong>resubmit</strong> their
                                                        report for another approval.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <div class="d-flex justify-content-between gap-3 align-items-center w-100">
                                            <button type="reset" class="btn btn-light w-50"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger w-50 confirm-btn">Confirm
                                                Reject</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- [ Reject Modal ] End -->
                @endforeach

            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>


    <script type="text/javascript">
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

        var modalToShow = "{{ session('modal') }}";
        if (modalToShow) {
            var modalElement = $("#" + modalToShow);
            if (modalElement.length) {
                var modal = new bootstrap.Modal(modalElement[0]);
                modal.show();
            }
        }


        /*********************************************************
         ***************SIGNATURE PADS INITIALIZATION*************
         *********************************************************/
        const signaturePads = {};

        document.addEventListener('shown.bs.modal', function(event) {
            const modal = event.target;
            const studentActivityId = modal.id.split('-')[1];
            const canvas = document.getElementById(`signatureCanvas-${studentActivityId}`);

            if (canvas && !signaturePads[studentActivityId]) {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);

                signaturePads[studentActivityId] = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255,255,255,1)',
                    penColor: 'black'
                });
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target.matches('[data-clear]')) {
                const id = e.target.getAttribute('data-clear');
                if (signaturePads[id]) signaturePads[id].clear();
            }

            if (e.target.matches('[data-submit]')) {
                const confirmId = e.target.getAttribute('data-submit');
                if (signaturePads[confirmId] && signaturePads[confirmId].isEmpty()) {
                    e.preventDefault();
                    showToast('danger', 'Please provide a signature.');
                } else {
                    const $btn = $(e.target);
                    $btn.prop('disabled', true);
                    $btn.html(
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...'
                    );

                    const dataURL = signaturePads[confirmId].toDataURL('image/png');
                    document.getElementById(`signatureData-${confirmId}`).value = dataURL;

                    $btn.closest('form').submit();
                }
            }
        });

        $(document).ready(function() {

            /*********************************************************/
            /************DATATABLE : STUDENT ACTIVITIES***************/
            /*********************************************************/

            // DATATABLE : STUDENT
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('my-supervision-student-evaluation-approval', ['activityID' => Crypt::encrypt($activity->id), 'studentID' => Crypt::encrypt($student->id)]) }}",
                    data: function(d) {
                        d.semester = $('#fil_semester_id')
                            .val();
                        d.status = $('#fil_status')
                            .val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        className: "text-start"
                    },
                    {
                        data: 'student_photo',
                        name: 'student_photo',
                    },
                    {
                        data: 'evaluator',
                        name: 'evaluator',
                    },
                    {
                        data: 'evaluation_status',
                        name: 'evaluation_status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],


            });

            // FILTER : SEMESTER
            $('#fil_semester_id').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearSemFilter').click(function() {
                $('#fil_semester_id').val('').change();
            });

            // FILTER : STATUS
            $('#fil_status').on('change', function() {
                $('.data-table').DataTable().ajax
                    .reload();
            });

            $('#clearStatusFilter').click(function() {
                $('#fil_status').val('').change();
            });

        });
    </script>
@endsection
