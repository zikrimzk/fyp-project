@php
    use Carbon\Carbon;
@endphp
@extends('student.layouts.main')

@section('content')

    <style>
        :root {
            --brand: #343a40;
            /* main */
            --brand-600: #2d3338;
            /* darker */
            --brand-500: #343a40;
            --brand-200: #ced4da;
            /* light border */
            --brand-100: #f1f3f5;
            /* light bg */
            --muted: #6c757d;
        }

        /* Cards */
        .card {
            border: 0 !important;
            border-radius: .8rem !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
            overflow: hidden;
            background: #fff;
        }

        .card-header {
            background: #fff;
        }

        .card-header.brand-gradient {
            background: linear-gradient(135deg, var(--brand-600), var(--brand-500));
            color: #fff;
            border: 0 !important;
        }

        .card-header .subtle {
            opacity: .85;
        }

        /* Headings + chips */
        .brand-badge {
            background: #fff;
            color: var(--brand-500);
            border: 1px solid rgba(255, 255, 255, .6);
            border-radius: 999px;
        }

        .section-title {
            font-weight: 600;
            color: var(--muted);
            letter-spacing: .2px;
        }

        .list-group-item {
            border-color: #eef1f4 !important;
        }

        /* Tabs (underline style) */
        .nav-tabs-underline {
            border-bottom: 1px solid #e9ecef;
        }

        .nav-tabs-underline .nav-link {
            border: 0;
            color: var(--muted);
            font-weight: 600;
            padding: .5rem 1rem;
            position: relative;
        }

        .nav-tabs-underline .nav-link.active {
            color: var(--brand);
        }

        .nav-tabs-underline .nav-link.active::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            height: 3px;
            background: var(--brand);
            border-radius: 3px 3px 0 0;
        }

        /* Soft badges */
        .badge-soft {
            border-radius: .5rem;
            font-weight: 600;
            padding: .35rem .6rem;
        }

        .badge-soft-brand {
            background: rgba(52, 58, 64, .08);
            color: var(--brand-600);
        }

        .badge-soft-ok {
            background: rgba(25, 135, 84, .12);
            color: #198754;
        }

        .badge-soft-warn {
            background: rgba(255, 193, 7, .18);
            color: #9c7910;
        }

        .badge-soft-danger {
            background: rgba(220, 53, 69, .14);
            color: #b02a37;
        }

        /* Empty states */
        .empty-state {
            text-align: center;
            padding: 1rem .5rem;
            color: var(--muted);
        }

        .empty-state i {
            opacity: .7;
        }

        /* Small utilities */
        .text-brand {
            color: var(--brand-500) !important;
        }

        .bg-brand-100 {
            background: var(--brand-100) !important;
        }

        .border-brand-200 {
            border-color: var(--brand-200) !important;
        }

        .link-brand {
            color: var(--brand-500);
            text-decoration: none;
        }

        .link-brand:hover {
            color: #212529;
            text-decoration: underline;
        }

        .avatar-chip {
            width: 36px;
            height: 36px;
            display: inline-grid;
            place-items: center;
            border-radius: 50%;
            background: var(--brand-100);
            color: var(--brand-500);
            border: 1px solid var(--brand-200);
        }

        /* Responsive tweaks */
        @media (max-width: 575.98px) {
            .h4 {
                font-size: 1.15rem;
            }

            .h6 {
                font-size: .95rem;
            }
        }
    </style>
    <div class="pc-container">
        <div class="pc-content">

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

                <!-- [ Dashboard ] start -->

                <!-- [ Main ] start -->
                <div class="col-sm-8">

                    <!-- [ Student Details ] start -->
                    <div class="card rounded-lg overflow-hidden mb-4">
                        <!-- Header -->
                        <div class="card-header brand-gradient p-4">
                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                <div class="mb-3 mb-md-0">
                                    <h3 class="h4 mb-1 d-flex align-items-center text-white">
                                        <span class="avatar-chip me-2"><i class="fas fa-user-graduate"></i></span>
                                        Welcome, {{ auth()->user()->student_name }}
                                    </h3>
                                    <div class="subtle">Student Dashboard</div>
                                </div>
                                <span class="brand-badge px-3 py-2 d-inline-flex align-items-center">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    {{ now()->format('F Y') }}
                                </span>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <!-- Research Title -->
                                <div class="list-group-item p-4">
                                    <div
                                        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                        <div class="mb-3 mb-md-0">
                                            <div class="section-title mb-2">
                                                <i class="fas fa-book-open me-2 text-brand"></i> Research Title
                                            </div>
                                            <p class="mb-0 fw-semibold">
                                                {{ auth()->user()->student_titleOfResearch == null ? '-' : auth()->user()->student_titleOfResearch ?? 'Not specified' }}
                                            </p>
                                        </div>
                                        <button class="btn btn-light border-brand-200 text-brand btn-sm rounded-pill px-3"
                                            data-bs-toggle="modal" data-bs-target="#editResearchModal">
                                            <i class="ti ti-edit me-1"></i> Edit
                                        </button>
                                    </div>
                                </div>

                                {{-- Supervisor --}}
                                @php
                                    $supervisor = DB::table('supervisions as a')
                                        ->join('staff as b', 'a.staff_id', '=', 'b.id')
                                        ->select('b.staff_name as supervisor_name', 'b.staff_email')
                                        ->where('student_id', auth()->user()->id)
                                        ->where('supervision_role', 1)
                                        ->first();
                                @endphp

                                <div class="list-group-item p-4">
                                    <div class="section-title mb-2">
                                        <i class="fas fa-chalkboard-teacher me-2 text-brand"></i> Supervisor
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <div class="avatar-chip me-3"><i class="fas fa-user-tie"></i></div>
                                        <div>
                                            <p class="mb-1 fw-semibold">{{ $supervisor->supervisor_name ?? 'Not assigned' }}
                                            </p>
                                            @if ($supervisor)
                                                <small class="text-muted">
                                                    <a href="mailto:{{ $supervisor->staff_email }}" class="link-primary">
                                                        <i class="fas fa-envelope me-1"></i>{{ $supervisor->staff_email }}
                                                    </a>
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Co-Supervisor --}}
                                @php
                                    $cosupervisor = DB::table('supervisions as a')
                                        ->join('staff as b', 'a.staff_id', '=', 'b.id')
                                        ->select('b.staff_name as supervisor_name', 'b.staff_email')
                                        ->where('student_id', auth()->user()->id)
                                        ->where('supervision_role', 2)
                                        ->first();
                                @endphp

                                <div class="list-group-item p-4">
                                    <div class="section-title mb-2">
                                        <i class="fas fa-user-tie me-2 text-brand"></i> Co-Supervisor
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <div class="avatar-chip me-3"><i class="fas fa-user-tie"></i></div>
                                        <div>
                                            <p class="mb-1 fw-semibold">
                                                {{ $cosupervisor->supervisor_name ?? 'Not assigned' }}</p>
                                            @if ($cosupervisor)
                                                <small class="text-muted">
                                                    <a href="mailto:{{ $cosupervisor->staff_email }}" class="link-primary">
                                                        <i
                                                            class="fas fa-envelope me-1"></i>{{ $cosupervisor->staff_email }}
                                                    </a>
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- [ Student Details ] end -->

                </div>
                <!-- [ Main ] end -->


                <!-- [ Aside ] start -->
                <div class="col-sm-4">

                    <!-- [ Reminders ] start -->
                    <div class="card mb-4">
                        <div class="card-header bg-white pb-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fas fa-bell me-2 text-brand"></i> Reminders
                                </h5>
                            </div>

                            <!-- Tabs -->
                            <ul class="nav nav-tabs nav-tabs-underline mt-3" id="remindersTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab"
                                        data-bs-target="#upcoming-pane" type="button" role="tab"
                                        aria-controls="upcoming-pane" aria-selected="true">
                                        Upcoming
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past-pane"
                                        type="button" role="tab" aria-controls="past-pane" aria-selected="false">
                                        Past Due
                                    </button>
                                </li>
                                {{-- <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab"
                                            data-bs-target="#completed-pane" type="button" role="tab"
                                            aria-controls="completed-pane" aria-selected="false">
                                        Completed
                                    </button>
                                    </li> --}}
                            </ul>
                        </div>

                        <div class="card-body p-2">
                            <div class="tab-content" id="remindersTabContent">

                                {{-- Upcoming (submission_status = 1) --}}
                                <div class="tab-pane fade show active" id="upcoming-pane" role="tabpanel"
                                    aria-labelledby="upcoming-tab">
                                    <div class="list-group list-group-flush">
                                        @forelse ($documents->where('submission_status', 1) as $doc)
                                            <a href="{{ route('student-document-submission', Crypt::encrypt($doc->submission_id)) }}"
                                                class="text-reset text-decoration-none">
                                                <div class="list-group-item border-0 py-3 px-4">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">{{ $doc->document_name }}</h6>

                                                            @php
                                                                $dueDate = \Carbon\Carbon::parse(
                                                                    $doc->submission_duedate,
                                                                );
                                                                $daysRemaining = \Carbon\Carbon::now()->diffInDays(
                                                                    $dueDate,
                                                                    false,
                                                                );

                                                                $badgeClass = 'badge-soft-ok';
                                                                $label = 'Due in ' . $daysRemaining . ' days';
                                                                if ($daysRemaining > 7) {
                                                                    $badgeClass = 'badge-soft';
                                                                    $label = 'Due in ' . $daysRemaining . ' days';
                                                                } elseif ($daysRemaining > 1) {
                                                                    $badgeClass = 'badge-soft-warn';
                                                                } elseif ($daysRemaining == 1) {
                                                                    $badgeClass = 'badge-soft-warn';
                                                                    $label = 'Due tomorrow';
                                                                } elseif ($daysRemaining == 0) {
                                                                    $badgeClass = 'badge-soft-danger';
                                                                    $label = 'Due today';
                                                                } elseif ($daysRemaining < 0) {
                                                                    $badgeClass = 'badge-soft-danger';
                                                                    $label =
                                                                        'Overdue by ' . abs($daysRemaining) . ' days';
                                                                }
                                                            @endphp

                                                            <div class="d-flex align-items-center gap-2 small">
                                                                <span class="badge badge-soft {{ $badgeClass }}">
                                                                    <i
                                                                        class="far fa-calendar-alt me-1"></i>{{ $label }}
                                                                </span>
                                                                <span
                                                                    class="text-muted">({{ $dueDate->format('M d, Y') }})</span>
                                                            </div>

                                                            <div class="small mt-1">
                                                                <span class="badge badge-soft badge-soft-brand">
                                                                    {{ $doc->activity_name }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="list-group-item border-0 py-3 px-4">
                                                <div class="empty-state">
                                                    <i class="fas fa-info-circle me-1"></i> No upcoming submissions.
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Past Due (submission_status = 4) --}}
                                <div class="tab-pane fade" id="past-pane" role="tabpanel" aria-labelledby="past-tab">
                                    <div class="list-group list-group-flush">
                                        @forelse ($documents->where('submission_status', 4) as $doc)
                                            <a href="{{ route('student-document-submission', Crypt::encrypt($doc->submission_id)) }}"
                                                class="text-reset text-decoration-none">
                                                <div class="list-group-item border-0 py-3 px-4">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">{{ $doc->document_name }}</h6>

                                                            @php
                                                                $dueDate = \Carbon\Carbon::parse(
                                                                    $doc->submission_duedate,
                                                                );
                                                                $daysRemaining = \Carbon\Carbon::now()->diffInDays(
                                                                    $dueDate,
                                                                    false,
                                                                );

                                                                $badgeClass = 'badge-soft-danger';
                                                                $label =
                                                                    $daysRemaining < 0
                                                                        ? 'Overdue by ' . abs($daysRemaining) . ' days'
                                                                        : 'Due in ' . $daysRemaining . ' days';
                                                            @endphp

                                                            <div class="d-flex align-items-center gap-2 small">
                                                                <span class="badge badge-soft {{ $badgeClass }}">
                                                                    <i class="far fa-clock me-1"></i>{{ $label }}
                                                                </span>
                                                                <span
                                                                    class="text-muted">({{ $dueDate->format('M d, Y') }})</span>
                                                            </div>

                                                            <div class="small mt-1">
                                                                <span class="badge badge-soft badge-soft-brand">
                                                                    {{ $doc->activity_name }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="list-group-item border-0 py-3 px-4">
                                                <div class="empty-state">
                                                    <i class="fas fa-info-circle me-1"></i> No overdue submissions.
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Completed (kept as example / hidden by default) --}}
                                <div class="tab-pane fade" id="completed-pane" role="tabpanel"
                                    aria-labelledby="completed-tab">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item border-0 py-3 px-4">
                                            <div class="d-flex align-items-start">
                                                <div class="avatar-chip me-3"><i class="fas fa-check"></i></div>
                                                <div>
                                                    <h6 class="mb-1">Chapter 1 Submission</h6>
                                                    <div class="small text-muted mb-1">
                                                        <i class="far fa-calendar-alt me-1"></i> Submitted on May 28, 2023
                                                    </div>
                                                    <div class="small badge badge-soft badge-soft-ok">
                                                        <i class="fas fa-check-circle me-1"></i> Approved by supervisor
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- /.tab-content -->
                        </div>
                    </div>
                    <!-- [ Reminders ] end -->

                    <!-- [ Nomination Details ] start -->
                    <div class="card">
                        <div class="card-header bg-white px-4 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-users me-2 text-brand"></i> Nomination Details
                                </h5>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">

                                @php
                                    $examiner = DB::table('nominations as a')
                                        ->join('evaluators as b', 'a.id', '=', 'b.nom_id')
                                        ->join('staff as c', 'b.staff_id', '=', 'c.id')
                                        ->join('activities as e', 'a.activity_id', '=', 'e.id')
                                        ->where('b.eva_status', 3)
                                        ->where('a.student_id', auth()->user()->id)
                                        ->whereNotExists(function ($query) {
                                            $query
                                                ->select(DB::raw(1))
                                                ->from('student_activities as d')
                                                ->whereColumn('d.activity_id', 'a.activity_id')
                                                ->where('d.student_id', auth()->user()->id)
                                                ->where('d.sa_status', 3);
                                        })
                                        ->select(
                                            'c.staff_name',
                                            'c.staff_email',
                                            'b.eva_role',
                                            'e.act_name',
                                            'a.student_id',
                                        )
                                        ->get()
                                        ->groupBy('act_name');
                                @endphp

                                @forelse ($examiner as $activityName => $examiners)
                                    <!-- Activity Header -->
                                    <div class="list-group-item px-4 py-3 bg-brand-100 border-0">
                                        <h6 class="mb-0 text-brand fw-semibold">
                                            <i class="fas fa-book me-2"></i>{{ $activityName }}
                                        </h6>
                                    </div>

                                    @foreach ($examiners as $e)
                                        @php
                                            $role = match ($e->eva_role) {
                                                1 => 'Examiner',
                                                2 => 'Panel Member',
                                                3 => 'Chairman',
                                                default => 'N/A',
                                            };
                                        @endphp

                                        <div class="list-group-item px-4 py-3">
                                            <div
                                                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                                <div class="d-flex align-items-start">
                                                    <div class="avatar-chip me-3"><i class="fas fa-user-tie"></i></div>
                                                    <div>
                                                        <div class="section-title mb-1"><i
                                                                class="fas fa-id-badge me-1 text-brand"></i>{{ $role }}
                                                        </div>
                                                        <p class="fw-semibold mb-0">{{ $e->staff_name ?? 'N/A' }}</p>
                                                        <small class="text-muted d-block">
                                                            <a href="mailto:{{ $e->staff_email }}" class="link-primary">
                                                                <i class="fas fa-envelope me-1"></i>{{ $e->staff_email }}
                                                            </a>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @empty
                                    <div class="list-group-item px-4 py-4 text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No nomination details available — the activity may be completed or examiners are
                                            not yet assigned.
                                        </div>
                                    </div>
                                @endforelse

                            </div>
                        </div>
                    </div>
                    <!-- [ Nomination Details ] end -->

                </div>
                <!-- [ Aside ] end -->

                <!-- [ Edit Research Modal ] start -->
                <form action="{{ route('student-update-titleOfResearch-post', Crypt::encrypt(auth()->user()->id)) }}"
                    method="POST">
                    @csrf
                    <div class="modal fade" id="editResearchModal" tabindex="-1"
                        aria-labelledby="editResearchModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title" id="editResearchModalLabel">
                                        <i class="ti ti-edit me-2" style="color: var(--brand);"></i>
                                        Update Research Title
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form>
                                        <div class="mb-3">
                                            <label for="researchTitle" class="form-label">Research Title</label>
                                            <textarea class="form-control" id="researchTitle" rows="3" name="student_titleOfResearch">{{ auth()->user()->student_titleOfResearch ?? '' }}</textarea>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer pt-2 bg-light">
                                    <div class="row w-100 g-2">
                                        <div class="col-12 col-md-6">
                                            <button type="reset" class="btn btn-outline-secondary w-100"
                                                data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <button type="submit" class="btn btn-primary w-100">
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- [ Edit Research Modal ] end -->

                <!-- [ Dashboard ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
@endsection
