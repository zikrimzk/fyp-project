@extends('student.layouts.main')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a
                                        href="{{ route('student-programme-overview') }}">{{ auth()->user()->programmes->prog_code }}</a>
                                </li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Journal Publication</a></li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h3 class="mb-0 d-flex align-items-center ">
                                    <a href="{{ route('student-programme-overview') }}" class="btn me-2">
                                        <span class="f-18">
                                            <i class="ti ti-arrow-left"></i>
                                        </span>
                                    </a>
                                    Journal Publication
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- [ Alert ] start -->
            <div>
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                    <div id="toastContainer"></div>
                </div>
            </div>
            <!-- [ Alert ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- [ Manage Journal Publication ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">

                            <!-- [ Option Section ] start -->
                            <div class="mb-4 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    title="Add Programme" id="addModalBtn" data-bs-toggle="modal"
                                    data-bs-target="#addModal">
                                    <i class="ti ti-plus f-18"></i>
                                    <span class="d-none d-sm-inline me-2">
                                        Add Journal Publication
                                    </span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Journal Name</th>
                                            <th scope="col">Scopus/ISI</th>
                                            <th scope="col">Date Created</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- [ Add Modal ] start -->
                <form id="addJournalForm">
                    @csrf
                    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header bg-light ">
                                    <h5 class="modal-title" id="addModalLabel">Add Journal Publication</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row">

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="add_journal_name" class="form-label">Journal Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('journal_name') is-invalid @enderror"
                                                    id="add_journal_name" name="journal_name"
                                                    placeholder="Enter Journal Name" value="{{ old('journal_name') }}"
                                                    required>
                                                @error('journal_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="add_journal_scopus_isi" class="form-label">Scopus/ISI <span
                                                        class="text-danger">*</span></label>
                                                <select name="journal_scopus_isi" id="add_journal_scopus_isi"
                                                    class="form-select @error('journal_scopus_isi') is-invalid @enderror"
                                                    required>
                                                    <option value="" selected>- Select Status -</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                                @error('journal_scopus_isi')
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
                                                <button type="submit" class="btn btn-primary w-100" id="addJournalBtn">
                                                    Add Journal Publication
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

                <!-- [ Edit Modal ] start -->
                <form id="editJournalForm">
                    @csrf
                    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModal"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header bg-light ">
                                    <h5 class="modal-title" id="editModalLabel">Update Journal Publication</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row">
                                        <input type="hidden" name="journal_id" id="edit_journal_id">

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="edit_journal_name" class="form-label">Journal Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('journal_name') is-invalid @enderror"
                                                    id="edit_journal_name" name="journal_name"
                                                    placeholder="Enter Journal Name" value="{{ old('journal_name') }}"
                                                    required>
                                                @error('journal_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="edit_journal_scopus_isi" class="form-label">Scopus/ISI <span
                                                        class="text-danger">*</span></label>
                                                <select name="journal_scopus_isi" id="edit_journal_scopus_isi"
                                                    class="form-select @error('journal_scopus_isi') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Status -</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No
                                                    </option>
                                                </select>
                                                @error('journal_scopus_isi')
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
                                                    id="updateJournalBtn">
                                                    Update Journal Publication
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Edit Modal ] end -->

                <!-- [ Delete Modal ] start -->
                <div class="modal fade" id="deleteModal" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-body p-4">
                                <div class="text-center mb-3">
                                    <i class="ti ti-trash text-danger" style="font-size: 80px;"></i>
                                </div>
                                <h4 class="text-center mb-2" id="deleteModalLabel">Are you sure?
                                </h4>
                                <p class="text-center text-muted mb-4">This action cannot be undone.</p>

                                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                    <button type="button" class="btn btn-outline-secondary w-100"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" id="confirmDeleteJournalBtn"
                                        class="btn btn-danger w-100">Delete Anyway</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ Delete Modal ] end -->

                <!-- [ Manage Journal Publication ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script type="text/javascript">
        // AJAX : HEADER SETUP
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {

            // ALERT : TOAST FUNCTION
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

            // DATATABLE : JOURNAL PUBLICATION
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('student-journal-publication') }}",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        className: "text-start"
                    },
                    {
                        data: 'journal_name',
                        name: 'journal_name'
                    },
                    {
                        data: 'journal_scopus_isi',
                        name: 'journal_scopus_isi',
                        className: "text-start"
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]

            });

            // UTILITY : RESET ERRORS
            function clearErrors(form) {
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
            }

            // AJAX : ADD JOURNAL PUBLICATION
            $('#addJournalForm').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                clearErrors(form);

                // 1) grab the data while everything is still enabled
                var payload = form.serialize();

                // 2) disable only the buttons so the user can’t double–click
                form.find('button').prop('disabled', true);

                // 3) send it
                $.post("{{ route('student-add-journal-publication-post') }}", payload)
                    .done(function(res) {
                        $('#addModal').modal('hide');
                        form[0].reset();
                        showToast('success', res.message);
                        table.ajax.reload(null, false);
                    })
                    .fail(function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, msgs) {
                                var el = form.find('[name="' + key + '"]');
                                el.addClass('is-invalid')
                                    .after('<div class="invalid-feedback">' + msgs[0] +
                                        '</div>');
                            });
                            showToast('error', 'Please fix the highlighted errors.');
                        } else {
                            showToast('error', 'An unexpected error occurred.');
                        }
                    })
                    .always(function() {
                        // 4) re-enable buttons
                        form.find('button').prop('disabled', false);
                    });
            });

            // TRIGGER : UPDATE JOURNAL PUBLICATION MODAL
            $(document).on('click', '.editJournalBtn', function() {
                clearErrors($('#editJournalForm'));
                let btn = $(this);
                $('#edit_journal_id').val(btn.data('id'));
                $('#edit_journal_name').val(btn.data('name'));
                $('#edit_journal_scopus_isi').val(btn.data('scopus'));
                $('#editModal').modal('show'); // ← now matches the corrected ID
            });

            // AJAX : UPDATE JOURNAL PUBLICATION
            $('#editJournalForm').submit(function(e) {
                e.preventDefault();
                let form = $(this);
                clearErrors(form);
                let payload = form.serialize(); // serialize before disabling
                form.find('button').prop('disabled', true);

                $.post("{{ route('student-update-journal-publication-post') }}", payload)
                    .done(res => {
                        $('#editModal').modal('hide');
                        showToast('success', res.message);
                        table.ajax.reload(null, false);
                    })
                    .fail(xhr => {
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, (k, v) => {
                                let el = form.find('[name="' + k + '"]');
                                el.addClass('is-invalid')
                                    .after('<div class="invalid-feedback">' + v[0] + '</div>');
                            });
                            showToast('error', 'Please fix the errors.');
                        } else {
                            showToast('error', 'An unexpected error occurred.');
                        }
                    })
                    .always(() => {
                        form.find('button').prop('disabled', false);
                    });
            });

            // TRIGGER : DELETE JOURNAL PUBLICATION MODAL
            var deleteId = null;
            $(document).on('click', '.deleteJournalBtn', function() {
                deleteId = $(this).data('id');
                $('#deleteModal').modal('show');
            });

            // AJAX : DELETE JOURNAL PUBLICATION
            $('#confirmDeleteJournalBtn').click(function(e) {
                e.preventDefault();
                var btn = $(this);
                btn.prop('disabled', true);

                $.post("{{ route('student-delete-journal-publication-post') }}", {
                        id: deleteId
                    })
                    .done(function(res) {
                        $('#deleteModal').modal('hide');
                        showToast('success', res.message);
                        table.ajax.reload(null, false);
                    })
                    .fail(function(xhr) {
                        showToast('error', 'Could not delete. Try again.');
                    })
                    .always(function() {
                        btn.prop('disabled', false);
                    });
            });

        });
    </script>
@endsection
