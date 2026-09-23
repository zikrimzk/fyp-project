@extends('staff.layouts.main')

@section('content')
    <style>
        .procedure-flow-modal .modal-dialog {
            max-width: min(1280px, calc(100vw - 2rem));
        }

        .procedure-flow-summary {
            background: var(--ep-surface-muted);
            border: 1px solid var(--ep-border);
            border-radius: var(--ep-radius-sm);
        }

        .procedure-flow-legend-item {
            align-items: center;
            display: inline-flex;
            gap: .4rem;
        }

        .procedure-flow-legend-mark {
            border-radius: 50%;
            display: inline-block;
            height: .7rem;
            width: .7rem;
        }

        .procedure-flow-legend-mark.is-open { background: var(--ep-primary); }
        .procedure-flow-legend-mark.is-locked { background: var(--ep-warning); }

        .procedure-flow-track {
            align-items: stretch;
            display: flex;
            min-width: max-content;
            padding: .5rem .25rem 1.25rem;
        }

        .procedure-flow-scroll {
            overflow-x: auto;
            overscroll-behavior-inline: contain;
            scrollbar-color: #b7c4d1 transparent;
        }

        .procedure-flow-node {
            background: var(--ep-surface);
            border: 1px solid var(--ep-border);
            border-radius: var(--ep-radius);
            box-shadow: var(--ep-shadow-sm);
            display: flex;
            flex: 0 0 270px;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        .procedure-flow-node::before {
            content: "";
            height: 4px;
            inset: 0 0 auto;
            position: absolute;
        }

        .procedure-flow-node.is-open::before { background: var(--ep-primary); }
        .procedure-flow-node.is-locked::before { background: var(--ep-warning); }
        .procedure-flow-node.is-record::before { background: var(--ep-text-muted); }

        .procedure-flow-sequence {
            align-items: center;
            background: var(--ep-primary-soft);
            border-radius: 999px;
            color: var(--ep-primary-dark);
            display: inline-flex;
            font-size: .75rem;
            font-weight: 700;
            height: 1.75rem;
            justify-content: center;
            min-width: 1.75rem;
            padding: 0 .55rem;
        }

        .procedure-flow-timeline {
            background: var(--ep-surface-muted);
            border: 1px solid var(--ep-border);
            border-radius: var(--ep-radius-sm);
        }

        .procedure-flow-status {
            border-radius: .4rem;
            font-size: .76rem;
            font-weight: 700;
            padding: .3rem .5rem;
        }

        .procedure-flow-status.is-open {
            background: var(--ep-primary-soft);
            color: var(--ep-primary-dark);
        }

        .procedure-flow-status.is-locked {
            background: #fff6e5;
            color: #8a5200;
        }

        .procedure-flow-status.is-record {
            background: #eef2f6;
            color: #475569;
        }

        .procedure-flow-connector {
            align-items: center;
            align-self: center;
            display: flex;
            flex: 0 0 120px;
            flex-direction: column;
            justify-content: center;
            padding: 0 .6rem;
        }

        .procedure-flow-connector-label {
            color: var(--ep-text-muted);
            font-size: .68rem;
            line-height: 1.25;
            margin-bottom: .45rem;
            min-height: 1.7rem;
            text-align: center;
        }

        .procedure-flow-connector-line {
            background: var(--ep-warning);
            height: 2px;
            position: relative;
            width: 100%;
        }

        .procedure-flow-connector-line::after {
            border-bottom: 5px solid transparent;
            border-left: 7px solid var(--ep-warning);
            border-top: 5px solid transparent;
            content: "";
            position: absolute;
            right: -1px;
            top: 50%;
            transform: translateY(-50%);
        }

        .procedure-flow-connector.is-open .procedure-flow-connector-line {
            background: repeating-linear-gradient(90deg, var(--ep-primary) 0 7px, transparent 7px 12px);
        }

        .procedure-flow-connector.is-open .procedure-flow-connector-line::after {
            border-left-color: var(--ep-primary);
        }

        @media (max-width: 767.98px) {
            .procedure-flow-modal .modal-dialog { margin: .5rem; max-width: none; }
            .procedure-flow-track { align-items: stretch; flex-direction: column; min-width: 0; }
            .procedure-flow-node { flex-basis: auto; width: 100%; }
            .procedure-flow-connector { flex-basis: 76px; min-height: 76px; padding: .5rem 0; }
            .procedure-flow-connector-label { margin-bottom: .3rem; min-height: 0; }
            .procedure-flow-connector-line { height: 38px; width: 2px; }
            .procedure-flow-connector-line::after {
                border-left: 5px solid transparent;
                border-right: 5px solid transparent;
                border-top: 7px solid var(--ep-warning);
                bottom: -1px;
                left: 50%;
                right: auto;
                top: auto;
                transform: translateX(-50%);
            }
            .procedure-flow-connector.is-open .procedure-flow-connector-line {
                background: repeating-linear-gradient(180deg, var(--ep-primary) 0 7px, transparent 7px 12px);
            }
            .procedure-flow-connector.is-open .procedure-flow-connector-line::after {
                border-left-color: transparent;
                border-top-color: var(--ep-primary);
            }
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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Administrator</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">SOP</a></li>
                                <li class="breadcrumb-item" aria-current="page">Procedure Setting</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Procedure Setting</h2>
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

                <!-- [ Procedure Setting ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">

                            {{-- <!-- [ Procedure Setup Guidelines ] start -->
                            <div class="alert alert-light d-flex align-items-start gap-3 p-4" role="alert">
                                <i class="ti ti-info-circle fs-3"></i>
                                <div class="w-100">
                                    <h4 class="mb-3 fw-semibold">Procedure Setup Guidelines</h4>
                                    <ul class="mb-0 ps-3 small">
                                        <li class="mb-2">
                                            Ensure procedures follow the programme structure. Avoid duplicate entries for
                                            the same activity.
                                        </li>
                                        <li class="mb-2">
                                            <strong>Sequence</strong> defines the activity order shown to students.
                                        </li>
                                        <li class="mb-2">
                                            <strong>Timeline Semester</strong> is the earliest semester the activity will be
                                            available to students.
                                        </li>
                                        <li class="mb-2">
                                            <strong>Timeline Week</strong> sets the submission due date for the activity.
                                        </li>
                                        <li class="mb-2">
                                            <strong>Initial Status:</strong>
                                            <span class="text-dark">L</span> - Locked (requires committee approval to
                                            appear),
                                            <span class="text-dark">O</span> - Open (always visible).
                                        </li>
                                        <li class="mb-0">
                                            <strong>Evaluation:</strong> Activities with evaluations complete after
                                            evaluation; others complete after approvals.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- [ Procedure Setup Guidelines ] start --> --}}

                            <!-- [ Option Section ] start -->
                            <div class="mb-4 d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#addModal" title="Add Procedure"
                                    id="addStaffBtn">
                                    <i class="ti ti-plus f-18"></i> <span class="d-none d-sm-inline me-2">Add
                                        Procedure</span>
                                </button>
                            </div>
                            <!-- [ Option Section ] end -->

                            <!-- [ Data Table ] start -->
                            <div class="dt-responsive table-responsive">
                                <table class="table data-table table-hover nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Activity</th>
                                            <th scope="col">Sequence</th>
                                            <th scope="col">Semester</th>
                                            <th scope="col">Week</th>
                                            <th scope="col">Evaluation</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Material</th>
                                            <th scope="col">Programme</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <!-- [ Data Table ] end -->
                        </div>
                    </div>
                </div>

                <!-- [ Add Modal ] start -->
                <form action="{{ route('add-procedure-post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title" id="addModalLabel">Add Procedure</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <!--Activity Input-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="activity_id" class="form-label">Activity <span
                                                        class="text-danger">*</span></label>
                                                <select name="activity_id" id="activity_id"
                                                    class="form-select @error('activity_id') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Activity -</option>
                                                    @foreach ($acts as $act)
                                                        @if (old('activity_id') == $act->id)
                                                            <option value="{{ $act->id }}" selected>
                                                                {{ $act->act_name }}
                                                            </option>
                                                        @else
                                                            <option value="{{ $act->id }}">{{ $act->act_name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @error('activity_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Programme Input-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="programme_id" class="form-label">Programme <span
                                                        class="text-danger">*</span></label>
                                                <select name="programme_id" id="programme_id"
                                                    class="form-select @error('programme_id') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Programme -</option>
                                                    @foreach ($progs as $prog)
                                                        @if (old('programme_id') == $prog->id)
                                                            <option value="{{ $prog->id }}" selected>
                                                                {{ $prog->prog_code }} ({{ $prog->prog_mode }})
                                                            </option>
                                                        @else
                                                            <option value="{{ $prog->id }}">
                                                                {{ $prog->prog_code }} ({{ $prog->prog_mode }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @error('programme_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <hr>

                                        <h6 class="mb-3">Activity Setting</h6>
                                        <!--Activity Type Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="activity_type" class="form-label">Activity Type <span
                                                        class="text-danger">*</span></label>
                                                <select name="activity_type" id="activity_type"
                                                    class="form-select @error('activity_type') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Activity Type -</option>
                                                    <option value="1"
                                                        @if (old('activity_type') == 1) selected @endif>Submission
                                                    </option>
                                                    <option value="2"
                                                        @if (old('activity_type') == 2) selected @endif>Record-based
                                                    </option>
                                                </select>
                                                @error('activity_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Activity Sequence Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="act_seq" class="form-label">Activity Sequence <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="act_seq" id="act_seq"
                                                    class="form-control @error('act_seq') is-invalid @enderror"
                                                    min="1" max="50" value="{{ old('act_seq') ?? 1 }}"
                                                    required>
                                                @error('act_seq')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Semester Timeline Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="timeline_sem" class="form-label">Semester Timeline <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="timeline_sem" id="timeline_sem"
                                                    class="form-control @error('timeline_sem') is-invalid @enderror"
                                                    min="1" max="50" value="{{ old('timeline_sem') ?? 1 }}"
                                                    required>
                                                @error('timeline_sem')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Week Timeline Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="timeline_week" class="form-label">Week Timeline <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="timeline_week" id="timeline_week"
                                                    class="form-control @error('timeline_week') is-invalid @enderror"
                                                    min="1" max="100"
                                                    value="{{ old('timeline_week') ?? 1 }}" required>
                                                @error('timeline_week')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Activity Material Input-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="material" class="form-label">Activity Material </label>
                                                <input type="file" name="material" id="material"
                                                    class="form-control @error('material') is-invalid @enderror">
                                                @error('material')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <hr>

                                        <h6 class="mb-3">Submission Setting</h6>
                                        <!--Activity Initial Status Input-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="init_status" class="form-label">Initial Status <span
                                                        class="text-danger">*</span></label>
                                                <select name="init_status" id="init_status"
                                                    class="form-select @error('init_status') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Status -</option>
                                                    <option value="1"
                                                        @if (old('init_status') == 1) selected @endif>(O) Open
                                                    </option>
                                                    <option value="2"
                                                        @if (old('init_status') == 2) selected @endif>(L) Locked
                                                    </option>
                                                </select>
                                                @error('init_status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text mt-2">
                                                    <div><strong>Open:</strong> Opens after the semester timeline is reached, without waiting for an earlier activity.</div>
                                                    <div><strong>Locked:</strong> Opens after the semester timeline is reached and any prerequisite activity is completed.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--Activity Repeatable Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="is_repeatable" class="form-label">Repeatable <span
                                                        class="text-danger">*</span></label>
                                                <select name="is_repeatable" id="is_repeatable"
                                                    class="form-select @error('is_repeatable') is-invalid @enderror"
                                                    required>
                                                    <option value="" selected>- Select Option -</option>
                                                    <option value="1"
                                                        @if (old('is_repeatable') == 1) selected @endif>Yes
                                                    </option>
                                                    <option value="0"
                                                        @if (old('is_repeatable') == 0) selected @endif>No
                                                    </option>
                                                </select>
                                                <small class="form-text text-muted mt-1 d-block">
                                                    <i class="ti ti-info-circle"></i> Choosing <strong>Yes</strong> will repeat this activity every semester.
                                                </small>
                                                @error('is_repeatable')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Journal Publication Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="is_haveJournalPublication" class="form-label">Journal
                                                    Publication<span class="text-danger">*</span></label>
                                                <select name="is_haveJournalPublication" id="is_haveJournalPublication"
                                                    class="form-select @error('is_haveJournalPublication') is-invalid @enderror"
                                                    disabled>
                                                    @if (old('is_haveJournalPublication') == 1)
                                                        <option value="1" selected>Yes</option>
                                                        <option value="0">No</option>
                                                    @elseif(old('is_haveJournalPublication') == 0)
                                                        <option value="1">Yes</option>
                                                        <option value="0"selected>No</option>
                                                    @else
                                                        <option value="" selected>- Select Option -</option>
                                                        <option value="1">Yes</option>
                                                        <option value="0">No</option>
                                                    @endif
                                                </select>
                                                <small class="form-text text-muted mt-1 d-block">
                                                    <i class="ti ti-info-circle"></i> Is the submission requires journal publication?
                                                </small>
                                                @error('is_haveJournalPublication')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <hr>

                                        <h6 class="mb-3">Evaluation Setting</h6>
                                        <!--Evaluation Input-->
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label for="is_haveEva" class="form-label">Evaluation <span
                                                        class="text-danger">*</span></label>
                                                <select name="is_haveEva" id="is_haveEva"
                                                    class="form-select @error('is_haveEva') is-invalid @enderror"
                                                    required>
                                                    <option value="">- Select Option -</option>
                                                    <option value="1"
                                                        @if (old('is_haveEva') === 1) selected @endif>Yes</option>
                                                    <option value="0"
                                                        @if (old('is_haveEva') === 0) selected @endif>No</option>
                                                </select>
                                                @error('is_haveEva')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!--Evaluation Mode Input-->
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-3">
                                                <label for="evaluation_mode" class="form-label">Evaluation Mode</label>
                                                <select name="evaluation_mode" id="evaluation_mode"
                                                    class="form-select @error('evaluation_mode') is-invalid @enderror">
                                                    <option value="">- Select Evaluation Mode -</option>
                                                    <option value="1"
                                                        @if (old('evaluation_mode') == 1) selected @endif>Examiner/Panel &
                                                        Chairman (Report)</option>
                                                    <option value="2"
                                                        @if (old('evaluation_mode') == 2) selected @endif>Examiner/Panel
                                                        ONLY (Report + Approval)</option>
                                                </select>
                                                @error('evaluation_mode')
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
                                                    class="btn btn btn-outline-secondary btn-pc-default w-100"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary w-100"
                                                    id="addApplicationBtn">
                                                    Add Procedure
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

                @foreach ($procedureFlows as $programmeId => $flowProcedures)
                    @php
                        $programme = $flowProcedures->first();
                    @endphp
                    <div class="modal fade procedure-flow-modal" id="procedureFlowModal-{{ $programmeId }}"
                        tabindex="-1" aria-labelledby="procedureFlowModalLabel-{{ $programmeId }}" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <div class="text-muted small mb-1">Programme procedure flow</div>
                                        <h5 class="modal-title" id="procedureFlowModalLabel-{{ $programmeId }}">
                                            {{ $programme->prog_code }} ({{ $programme->prog_mode }})
                                        </h5>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-3 p-md-4">
                                    <div class="procedure-flow-summary d-flex flex-column flex-lg-row justify-content-between gap-3 p-3 mb-4">
                                        <div>
                                            <div class="fw-semibold">{{ $programme->prog_name }}</div>
                                            <div class="small text-muted">
                                                {{ $flowProcedures->count() }} configured
                                                {{ Str::plural('activity', $flowProcedures->count()) }}, shown in sequence order.
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-3 small" aria-label="Flow legend">
                                            <span class="procedure-flow-legend-item">
                                                <span class="procedure-flow-legend-mark is-open" aria-hidden="true"></span>
                                                Open: timeline only
                                            </span>
                                            <span class="procedure-flow-legend-item">
                                                <span class="procedure-flow-legend-mark is-locked" aria-hidden="true"></span>
                                                Locked: timeline + prerequisites
                                            </span>
                                        </div>
                                    </div>

                                    <div class="procedure-flow-scroll" tabindex="0"
                                        aria-label="Scrollable procedure flow for {{ $programme->prog_code }} {{ $programme->prog_mode }}">
                                        <div class="procedure-flow-track" role="list">
                                            @foreach ($flowProcedures as $procedure)
                                                @php
                                                    $isRecord = (int) $procedure->activity_type === 2;
                                                    $isOpen = !$isRecord && (int) $procedure->init_status === 1;
                                                    $nodeClass = $isRecord ? 'is-record' : ($isOpen ? 'is-open' : 'is-locked');
                                                    $nextProcedure = $flowProcedures->get($loop->index + 1);
                                                    $nextIsOpen = $nextProcedure
                                                        && (int) $nextProcedure->activity_type !== 2
                                                        && (int) $nextProcedure->init_status === 1;
                                                @endphp
                                                <article class="procedure-flow-node {{ $nodeClass }}" role="listitem">
                                                    <div class="p-3 p-lg-4 d-flex flex-column h-100">
                                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                                            <span class="procedure-flow-sequence">{{ $procedure->act_seq }}</span>
                                                            @if ($isRecord)
                                                                <span class="procedure-flow-status is-record">Record-based</span>
                                                            @elseif ($isOpen)
                                                                <span class="procedure-flow-status is-open">Open</span>
                                                            @else
                                                                <span class="procedure-flow-status is-locked">Locked</span>
                                                            @endif
                                                        </div>

                                                        <h6 class="mb-3">{{ $procedure->act_name }}</h6>

                                                        <div class="procedure-flow-timeline d-flex gap-3 p-3 mb-3">
                                                            <div>
                                                                <div class="small text-muted">Semester</div>
                                                                <div class="fw-bold">{{ $procedure->timeline_sem }}</div>
                                                            </div>
                                                            <div class="border-start ps-3">
                                                                <div class="small text-muted">Week</div>
                                                                <div class="fw-bold">{{ $procedure->timeline_week }}</div>
                                                            </div>
                                                        </div>

                                                        <p class="small text-muted mb-3">
                                                            @if ($isRecord)
                                                                Recorded administratively according to the configured timeline.
                                                            @elseif ($isOpen)
                                                                Opens when the semester timeline is reached; earlier activities are not required.
                                                            @else
                                                                Opens when the semester timeline and all earlier prerequisites are completed.
                                                            @endif
                                                        </p>

                                                        <div class="d-flex flex-wrap gap-1 mt-auto">
                                                            @if ((int) $procedure->is_haveEva === 1)
                                                                <span class="badge bg-light-primary text-primary">Evaluation</span>
                                                            @endif
                                                            @if ((int) $procedure->is_repeatable === 1)
                                                                <span class="badge bg-light-secondary text-secondary">
                                                                    <i class="ti ti-refresh me-1" aria-hidden="true"></i>Repeats every semester
                                                                </span>
                                                            @endif
                                                            @if ((int) $procedure->is_haveJournalPublication === 1)
                                                                <span class="badge bg-light-info text-info">Journal required</span>
                                                            @endif
                                                            @if ($procedure->material)
                                                                <a class="badge bg-light-dark text-dark text-decoration-none"
                                                                    href="{{ URL::signedRoute('view-material-get', ['filename' => Crypt::encrypt($procedure->material)]) }}"
                                                                    target="_blank" rel="noopener">
                                                                    <i class="ti ti-file-text me-1" aria-hidden="true"></i>Material
                                                                </a>
                                                            @endif
                                                        </div>

                                                        @if ((int) $procedure->is_haveEva === 1 && $procedure->evaluation_mode)
                                                            <div class="small text-muted border-top mt-3 pt-3">
                                                                <strong>Evaluation:</strong>
                                                                {{ (int) $procedure->evaluation_mode === 1
                                                                    ? 'Panel and chairman report'
                                                                    : 'Panel report and approval' }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </article>

                                                @unless ($loop->last)
                                                    <div class="procedure-flow-connector {{ $nextIsOpen ? 'is-open' : 'is-locked' }}"
                                                        aria-hidden="true">
                                                        <span class="procedure-flow-connector-label">
                                                            {{ $nextIsOpen ? 'Timeline only' : 'Complete previous' }}
                                                        </span>
                                                        <span class="procedure-flow-connector-line"></span>
                                                    </div>
                                                @endunless
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                @foreach ($pros as $upd)
                    <!-- [ Update Modal ] start -->
                    <form
                        action="{{ route('update-procedure-post', ['actID' => Crypt::encrypt($upd->activity_id), 'progID' => Crypt::encrypt($upd->programme_id)]) }}"
                        method="POST" enctype="multipart/form-data" class="updateForm">
                        @csrf
                        <div class="modal fade" id="updateModal-{{ $upd->activity_id }}-{{ $upd->programme_id }}"
                            tabindex="-1" aria-labelledby="updateModal" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title" id="updateModalLabel">Update Procedure</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row repeatable-group">
                                            <!--Activity Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="activity_id_up" class="form-label">Activity <span
                                                            class="text-danger">*</span></label>
                                                    <select name="activity_id_up" id="activity_id_up"
                                                        class="form-select @error('activity_id_up') is-invalid @enderror"
                                                        required>
                                                        <option value="" disabled>- Select Activity -</option>
                                                        @foreach ($acts as $act)
                                                            @if ($upd->activity_id == $act->id)
                                                                <option value="{{ $act->id }}" selected>
                                                                    {{ $act->act_name }}
                                                                </option>
                                                            @else
                                                                <option value="{{ $act->id }}" disabled>
                                                                    {{ $act->act_name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @error('activity_id_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Programme Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="programme_id_up" class="form-label">Programme <span
                                                            class="text-danger">*</span></label>
                                                    <select name="programme_id_up" id="programme_id_up"
                                                        class="form-select @error('programme_id_up') is-invalid @enderror"
                                                        required>
                                                        <option value="" disabled>- Select Programme -</option>
                                                        @foreach ($progs as $prog)
                                                            @if ($upd->programme_id == $prog->id)
                                                                <option value="{{ $prog->id }}" selected>
                                                                    {{ $prog->prog_code }} ({{ $prog->prog_mode }})
                                                                </option>
                                                            @else
                                                                <option value="{{ $prog->id }}" disabled>
                                                                    {{ $prog->prog_code }} ({{ $prog->prog_mode }})
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @error('programme_id_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <hr>

                                            <h6 class="mb-3">Activity Setting</h6>
                                            <!--Activity Type Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="activity_type_up" class="form-label">Activity Type <span
                                                            class="text-danger">*</span></label>
                                                    <select name="activity_type_up" id="activity_type_up"
                                                        class="form-select activity_type_up @error('activity_type_up') is-invalid @enderror"
                                                        required>
                                                        <option value="" selected>- Select Activity Type -</option>
                                                        <option value="1"
                                                            @if ($upd->activity_type == 1) selected @endif>Submission
                                                        </option>
                                                        <option value="2"
                                                            @if ($upd->activity_type == 2) selected @endif>Record-based
                                                        </option>
                                                    </select>
                                                    @error('activity_type_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Activity Sequence Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="act_seq_up" class="form-label">Activity Sequence <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" name="act_seq_up" id="act_seq_up"
                                                        class="form-control @error('act_seq_up') is-invalid @enderror"
                                                        min="1" max="50" value="{{ $upd->act_seq }}"
                                                        required>
                                                    @error('act_seq_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Semester Timeline Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="timeline_sem_up" class="form-label">Semester Timeline
                                                        <span class="text-danger">*</span></label>
                                                    <input type="number" name="timeline_sem_up" id="timeline_sem_up"
                                                        class="form-control @error('timeline_sem_up') is-invalid @enderror"
                                                        min="1" max="50" value="{{ $upd->timeline_sem }}"
                                                        required>
                                                    @error('timeline_sem_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Week Timeline Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="timeline_week_up" class="form-label">Week Timeline <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" name="timeline_week_up" id="timeline_week_up"
                                                        class="form-control @error('timeline_week_up') is-invalid @enderror"
                                                        min="1" max="100" value="{{ $upd->timeline_week }}"
                                                        required>
                                                    @error('timeline_week_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Activity Material Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="material_up" class="form-label">Activity Material </label>
                                                    <input type="file" name="material_up" id="material_up"
                                                        class="form-control @error('material_up') is-invalid @enderror mb-2">
                                                    @if ($upd->material)
                                                        <a href="{{ URL::signedRoute('view-material-get', ['filename' => Crypt::encrypt($upd->material)]) }}"
                                                            target="_blank" class="link-primary">View Uploaded
                                                            Material</a>
                                                    @endif
                                                    @error('material_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <hr>

                                            <h6 class="mb-3">Submission Setting</h6>
                                            <!--Activity Initial Status Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="init_status_up" class="form-label">Initial Status <span
                                                            class="text-danger">*</span></label>
                                                    <select name="init_status_up" id="init_status_up"
                                                        class="form-select init_status_up @error('init_status_up') is-invalid @enderror"
                                                        required>
                                                        <option value="1"
                                                            @if ($upd->init_status == 1) selected @endif>(O) Open
                                                        </option>
                                                        <option value="2"
                                                            @if ($upd->init_status == 2) selected @endif>(L) Locked
                                                        </option>
                                                    </select>
                                                    @error('init_status_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text mt-2">
                                                        <div><strong>Open:</strong> Opens after the semester timeline is reached, without waiting for an earlier activity.</div>
                                                        <div><strong>Locked:</strong> Opens after the semester timeline is reached and any prerequisite activity is completed.</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--Activity Repeatable Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="is_repeatable_up" class="form-label">Repeatable <span
                                                            class="text-danger">*</span></label>
                                                    <select name="is_repeatable_up" id="is_repeatable_up"
                                                        class="form-select is-repeat is_repeatable_up @error('is_repeatable_up') is-invalid @enderror"
                                                        required>
                                                        <option value="1"
                                                            @if ($upd->is_repeatable == 1) selected @endif>Yes
                                                        </option>
                                                        <option value="0"
                                                            @if ($upd->is_repeatable == 0) selected @endif>No
                                                        </option>
                                                    </select>
                                                    <small class="form-text text-muted mt-1 d-block">
                                                        <i class="ti ti-info-circle"></i> Choosing <strong>Yes</strong> will repeat this activity every semester.
                                                    </small>
                                                    @error('is_repeatable_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Journal Publication Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="is_haveJournalPublication_up" class="form-label">Journal
                                                        Publication <span class="text-danger">*</span></label>
                                                    <select name="is_haveJournalPublication_up"
                                                        id="is_haveJournalPublication_up"
                                                        class="form-select is-havePublication is_haveJournalPublication_up @error('is_haveJournalPublication_up') is-invalid @enderror"
                                                        required>
                                                        <option value="">- Select Option -</option>
                                                        <option value="1"
                                                            @if ($upd->is_haveJournalPublication == 1) selected @endif>
                                                            Yes
                                                        </option>
                                                        <option value="0"
                                                            @if ($upd->is_haveJournalPublication == 0) selected @endif>
                                                            No
                                                        </option>
                                                    </select>
                                                    <small class="form-text text-muted mt-1 d-block">
                                                        <i class="ti ti-info-circle"></i> Is the submission requires journal publication?
                                                    </small>

                                                    @error('is_haveJournalPublication_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <hr>

                                            <h6 class="mb-3">Evaluation Setting</h6>
                                            <!--Evaluation Input-->
                                            <div class="col-sm-12 col-md-6 col-lg-6">
                                                <div class="mb-3">
                                                    <label for="is_haveEva_up" class="form-label">Evaluation <span
                                                            class="text-danger">*</span></label>
                                                    <select name="is_haveEva_up" id="is_haveEva_up"
                                                        class="form-select is_haveEva_up @error('is_haveEva_up') is-invalid @enderror"
                                                        required>
                                                        <option value="">- Select Option -</option>
                                                        <option value="1"
                                                            @if ($upd->is_haveEva == 1) selected @endif>
                                                            Yes
                                                        </option>
                                                        <option value="0"
                                                            @if ($upd->is_haveEva == 0) selected @endif>
                                                            No
                                                        </option>
                                                    </select>
                                                    @error('is_haveEva_up')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!--Evaluation Mode Input-->
                                            <div class="col-sm-12 col-md-12 col-lg-12">
                                                <div class="mb-3">
                                                    <label for="evaluation_mode_up" class="form-label">Evaluation Mode</label>
                                                    <select name="evaluation_mode_up" id="evaluation_mode_up"
                                                        class="form-select evaluation_mode_up @error('evaluation_mode_up') is-invalid @enderror">
                                                        <option value="" selected>- Select Evaluation Mode -</option>
                                                        <option value="1"
                                                            @if ($upd->evaluation_mode == 1) selected @endif>
                                                            Examiner/Panel & Chairman (Report)</option>
                                                        <option value="2"
                                                            @if ($upd->evaluation_mode == 2) selected @endif>
                                                            Examiner/Panel ONLY (Report + Approval)</option>
                                                    </select>
                                                    @error('evaluation_mode_up')
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
                    <div class="modal fade" id="deleteModal-{{ $upd->activity_id }}-{{ $upd->programme_id }}"
                        data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                        aria-labelledby="deleteModalLabel-{{ $upd->activity_id }}-{{ $upd->programme_id }}"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-body p-4">
                                    <div class="text-center mb-3">
                                        <i class="ti ti-trash text-danger" style="font-size: 80px;"></i>
                                    </div>
                                    <h4 class="text-center mb-2"
                                        id="deleteModalLabel-{{ $upd->activity_id }}-{{ $upd->programme_id }}">Are you
                                        sure?
                                    </h4>
                                    <p class="text-center text-muted mb-4">This action cannot be undone and may affect
                                        related procedures.</p>

                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary w-100"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <x-mutation-button :action="route('delete-procedure-get', ['actID' => Crypt::encrypt($upd->activity_id), 'progID' => Crypt::encrypt($upd->programme_id)])"
                                            class="btn btn-danger w-100">Delete Anyway</x-mutation-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Delete Modal ] end -->
                @endforeach
                <!-- [ Procedure Setting ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {

            /*********************************************************
             ***************GLOBAL FUNCTION & VARIABLES***************
             *********************************************************/

            var modalToShow = "{{ session('modal') }}";
            if (modalToShow) {
                var modalElement = $("#" + modalToShow);
                if (modalElement.length) {
                    var modal = new bootstrap.Modal(modalElement[0]);
                    modal.show();
                }
            }

            // DATATABLE : PROCEDURES
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: true,
                ajax: {
                    url: "{{ route('procedure-setting') }}",
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
                        data: 'act_seq',
                        name: 'act_seq',
                        orderable: false,

                    },
                    {
                        data: 'timeline_sem',
                        name: 'timeline_sem',
                        orderable: false,

                    },
                    {
                        data: 'timeline_week',
                        name: 'timeline_week',
                        orderable: false,

                    },
                    {
                        data: 'is_haveEva',
                        name: 'is_haveEva',
                        orderable: false,

                    },
                    {
                        data: 'init_status',
                        name: 'init_status'
                    },
                    {
                        data: 'material',
                        name: 'material',
                        orderable: false,

                    },
                    {
                        data: 'prog_code_mode',
                        name: 'prog_code_mode',
                        visible: false,
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                rowGroup: {
                    dataSrc: 'prog_code_mode',
                    startRender: function(rows, group) {
                        const firstRow = rows.data()[0];
                        const $row = $('<tr class="group-header" style="cursor:pointer"/>')
                            .attr('data-group', group);
                        const $cell = $('<td colspan="9" class="bg-light"/>');
                        const $layout = $('<div class="d-flex flex-wrap align-items-center justify-content-between gap-2"/>');
                        const $identity = $('<div class="d-flex align-items-center gap-2"/>');

                        $('<span class="fw-semibold text-uppercase"/>').text(group).appendTo($identity);
                        $('<span class="badge bg-primary"/>').text(rows.count()).appendTo($identity);

                        const $actions = $('<div class="d-flex align-items-center gap-2"/>');
                        $('<button type="button" class="btn btn-sm btn-outline-primary procedure-flow-trigger"/>')
                            .attr('data-bs-toggle', 'modal')
                            .attr('data-bs-target', '#procedureFlowModal-' + firstRow.programme_id)
                            .attr('aria-label', 'Visualize procedure flow for ' + group)
                            .append('<i class="ti ti-chart-arrows me-1" aria-hidden="true"></i>')
                            .append(document.createTextNode('Visualize'))
                            .appendTo($actions);
                        $('<i class="ti ti-chevron-down toggle-icon" aria-hidden="true"></i>').appendTo($actions);

                        $layout.append($identity, $actions);
                        $cell.append($layout);
                        return $row.append($cell);
                    }
                },

            });

            var collapsedGroups = {};

            $('.data-table tbody').on('click', 'tr.group-header', function(event) {
                if ($(event.target).closest('.procedure-flow-trigger').length) {
                    return;
                }

                var group = $(this).data('group');
                collapsedGroups[group] = !collapsedGroups[group];

                // Toggle icon
                var icon = $(this).find('.toggle-icon');
                if (collapsedGroups[group]) {
                    icon.removeClass('ti-chevron-down').addClass('ti-chevron-right');
                } else {
                    icon.removeClass('ti-chevron-right').addClass('ti-chevron-down');
                }

                table.rows().every(function() {
                    if (this.data().programme === group) {
                        $(this.node()).toggle(!collapsedGroups[group]);
                    }
                });
            });




            // -------------------------------
            // [ ADD MODAL ] Logic
            // -------------------------------
            function toggleAddFields() {
                const type = $('#activity_type').val();
                const targets = [
                    '#init_status',
                    '#is_repeatable',
                    '#is_haveJournalPublication',
                    '#is_haveEva',
                    '#evaluation_mode'
                ];
                const disable = (type === '2');
                targets.forEach(id => $(id).prop('disabled', disable));
            }

            $('#activity_type').on('change', toggleAddFields);
            toggleAddFields(); // Init on load

            // -------------------------------
            // [ UPDATE MODALS ] Logic
            // -------------------------------
            $('.updateForm').each(function() {
                const $form = $(this);
                const $typeField = $form.find('.activity_type_up');

                function toggleUpdateFields() {
                    const type = $typeField.val();
                    const disable = (type === '2');

                    $form.find('.init_status_up').prop('disabled', disable);
                    $form.find('.is_repeatable_up').prop('disabled', disable);
                    $form.find('.is_haveJournalPublication_up').prop('disabled', disable);
                    $form.find('.is_haveEva_up').prop('disabled', disable);
                    $form.find('.evaluation_mode_up').prop('disabled', disable);
                }

                // Init on page load
                toggleUpdateFields();

                // Watch for changes
                $typeField.on('change', toggleUpdateFields);
            });



            $('#is_repeatable').change(function() {
                if (this.value == 1) {
                    $('#is_haveJournalPublication').attr('disabled', false);
                } else {
                    $('#is_haveJournalPublication').val(0); // Reset to No
                    $('#is_haveJournalPublication').attr('disabled', true);
                }
            });

            // Trigger on load for the Add Procedure modal
            $('#is_repeatable').trigger('change');

            $('.is-repeat').on('change', function() {
                const publicationSelect = $(this).closest('.repeatable-group').find('.is-havePublication');

                if ($(this).val() == '1') {
                    publicationSelect.prop('disabled', false);
                } else {
                    publicationSelect.prop('disabled', true);
                }
            });

            $('.is-repeat').each(function() {
                $(this).trigger('change');
            });




        });
    </script>
@endsection
