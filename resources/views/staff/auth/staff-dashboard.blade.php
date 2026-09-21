@extends('staff.layouts.main')

@section('content')

    <style>
        /* --- Professional Dashboard Theme --- */
        :root {
            --theme-primary: var(--ep-primary-dark);
            --theme-secondary: var(--ep-text-muted);
            --theme-background: var(--ep-canvas);
            --theme-card-bg: var(--ep-surface);
            --theme-text-dark: var(--ep-text);
            --theme-text-light: #475569;
            --theme-border: var(--ep-border);
            --theme-accent: var(--ep-primary);
            --theme-success: var(--ep-success);
            --theme-danger: var(--ep-danger);
            --theme-warning: #d68a16;
            --theme-info: #3182a8;
            --theme-shadow: var(--ep-shadow-sm);
            --theme-shadow-hover: var(--ep-shadow);
        }

        body {
            background-color: var(--theme-background);
            color: var(--theme-text-light);
        }

        /* --- Dashboard Header --- */
        .dashboard-header {
            background: var(--theme-card-bg);
            border: 1px solid var(--theme-border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--theme-shadow);
        }

        .greeting-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--theme-text-dark);
        }

        .greeting-title span {
            font-weight: 400;
        }

        .info-bar {
            font-size: 0.9rem;
            color: var(--theme-secondary);
            flex-wrap: wrap;
        }

        .info-bar .fas {
            color: var(--theme-primary);
        }

        .print-btn {
            background-color: var(--theme-accent);
            border: none;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s ease;
        }

        .print-btn:hover {
            background-color: var(--ep-primary-dark);
        }

        /* --- Stat Cards --- */
        .stat-card {
            background-color: var(--theme-card-bg);
            border: 1px solid var(--theme-border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: var(--theme-shadow);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--theme-shadow-hover);
        }

        .stat-card .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background-color: var(--theme-background);
            color: var(--theme-primary);
        }

        .stat-card .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--theme-primary);
        }

        .stat-card .card-label {
            font-size: 0.9rem;
            color: var(--theme-text-light);
            margin-bottom: 0.5rem;
        }

        .stat-card .card-link {
            text-decoration: none;
            font-weight: 500;
            color: var(--theme-accent);
        }

        .stat-card.danger .card-icon {
            color: #dc3545;
        }

        .stat-card.danger .card-link {
            color: #dc3545;
        }

        /* --- Chart Cards --- */
        .chart-card {
            background-color: var(--theme-card-bg);
            border: 1px solid var(--theme-border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--theme-shadow);
        }

        .chart-card .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--theme-text-dark);
        }

        .dashboard-filter-card,
        .dashboard-panel {
            background: var(--theme-card-bg);
            border: 1px solid var(--theme-border);
            border-radius: .75rem;
            box-shadow: var(--theme-shadow);
        }

        .dashboard-filter-card {
            padding: 1rem 1.25rem;
        }

        .dashboard-panel {
            padding: 1.25rem;
            height: 100%;
        }

        .dashboard-section-title {
            color: var(--theme-text-dark);
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
        }

        .operational-card,
        .action-card,
        .pulse-card {
            background: #fff;
            border: 1px solid var(--theme-border);
            border-radius: .7rem;
            height: 100%;
            padding: 1rem;
        }

        .operational-card {
            border-left: 4px solid var(--theme-accent);
        }

        .operational-card.is-danger {
            border-left-color: var(--theme-danger);
        }

        .operational-card.is-warning {
            border-left-color: #f59f00;
        }

        .metric-value {
            color: var(--theme-text-dark);
            font-size: 1.65rem;
            font-weight: 750;
            line-height: 1;
        }

        .metric-label {
            color: var(--theme-secondary);
            font-size: .8rem;
            font-weight: 600;
            margin-top: .45rem;
        }

        .action-card {
            color: inherit;
            display: block;
            text-decoration: none;
            transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
        }

        .action-card:hover {
            border-color: #9ec5fe;
            box-shadow: var(--theme-shadow-hover);
            color: inherit;
            transform: translateY(-2px);
        }

        .action-icon {
            align-items: center;
            background: #eef5ff;
            border-radius: .6rem;
            color: var(--theme-accent);
            display: flex;
            height: 42px;
            justify-content: center;
            width: 42px;
        }

        .pulse-card .pulse-icon {
            color: var(--theme-accent);
            font-size: 1rem;
        }

        .pipeline {
            display: grid;
            gap: .75rem;
            grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
        }

        .pipeline-step {
            background: #f8f9fa;
            border: 1px solid var(--theme-border);
            border-radius: .65rem;
            padding: 1rem .75rem;
            position: relative;
            text-align: center;
        }

        .pipeline-count {
            color: var(--theme-text-dark);
            font-size: 1.4rem;
            font-weight: 700;
        }

        .attention-table th {
            color: var(--theme-secondary);
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .attention-table td {
            vertical-align: middle;
        }

        .deadline-item + .deadline-item {
            border-top: 1px solid #edf0f2;
        }

        .deadline-item {
            padding: .85rem 0;
        }

        @media (max-width: 1199.98px) {
            .pipeline {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .pipeline-step::after {
                display: none;
            }
        }

        @media (max-width: 575.98px) {
            .dashboard-header {
                margin-bottom: 1.25rem;
                padding: 1rem;
            }

            .greeting-title {
                font-size: 1.1rem;
            }

            .pipeline {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* --- Print Styles --- */
        @media print {
            body {
                background-color: #fff;
            }

            .no-print {
                display: none !important;
            }

            .container,
            .row,
            .col {
                padding: 0 !important;
                margin: 0 !important;
            }

            .stat-card,
            .chart-card,
            .dashboard-header {
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }

            .chart-card {
                margin-top: 2rem;
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
                                <li class="breadcrumb-item" aria-current="page">Dashboard</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Dashboard</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- [ Main Content ] start -->

            <div class="dashboard-header no-print">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h1 class="greeting-title mb-1">
                            <span>Welcome back,</span> {{ Auth::user()->staff_name }}!
                        </h1>
                        <p class="mb-0 text-muted">Here's your dashboard overview.</p>
                    </div>
                    <div class="mt-3 mt-md-0 d-flex flex-column align-items-md-end text-start text-md-end">
                        <div class="info-bar d-flex gap-3 mb-2">
                            <span><i class="fas fa-calendar-alt me-2"></i>{{ now()->format('l, F j, Y') }}</span>
                            <span id="live-clock"><i class="fas fa-clock me-2"></i>Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            @if (count($dashboardRoles) > 1)
                <nav class="d-flex flex-wrap gap-2 mb-4 no-print" aria-label="Dashboard role">
                    @foreach ($dashboardRoles as $roleKey => $roleLabel)
                        <a href="{{ route('staff-dashboard', ['dashboard_role' => $roleKey]) }}"
                            class="btn btn-sm {{ $dashboardRole === $roleKey ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $roleLabel }}
                        </a>
                    @endforeach
                </nav>
            @endif

            @if ($dashboardRole === 'supervisor' && $supervisorDashboard)
                @include('staff.auth.dashboard.supervisor')
            @elseif ($dashboardRole === 'supervisor')
                <div class="alert alert-warning">No semester is configured yet. Create a semester before dashboard reporting can be generated.</div>
            @elseif (!$dashboardRole)
                <div class="col-12">
                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-start gap-2"
                        role="alert">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>
                            <strong>Oops!</strong> There’s nothing to display on your dashboard yet.
                            A role hasn’t been assigned to your account. Please wait until your role is assigned in the
                            system.
                        </div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif

            <!-- [ Higher Ups Dashboard ] start -->
            @if ($dashboardRole === 'administrator' && $isHigherUps)
                @if ($committeeDashboard)
                    @php
                        $cd = $committeeDashboard;
                        $filter = $cd['filters'];
                    @endphp

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Administrator Dashboard</h5>
                            <div class="text-muted small">
                                Overview for {{ $cd['semester']->sem_label ?? 'selected semester' }}
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border no-print">
                            Last updated {{ now()->format('M j, Y g:i A') }}
                        </span>
                    </div>

                    <form method="GET" action="{{ route('staff-dashboard') }}" class="dashboard-filter-card mb-4 no-print">
                        <input type="hidden" name="dashboard_role" value="administrator">
                        <div class="row g-3 align-items-end">
                            <div class="col-sm-6 col-xl-3">
                                <label for="dashboard-semester" class="form-label small fw-semibold">Semester</label>
                                <select id="dashboard-semester" name="semester" class="form-select">
                                    @foreach ($cd['options']['semesters'] as $semester)
                                        <option value="{{ $semester->id }}" @selected((int) $filter['semester_id'] === (int) $semester->id)>
                                            {{ $semester->sem_label }}{{ (int) $semester->sem_status === 1 ? ' (Current)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <label for="dashboard-programme" class="form-label small fw-semibold">Programme</label>
                                <select id="dashboard-programme" name="programme" class="form-select">
                                    <option value="">All programmes</option>
                                    @foreach ($cd['options']['programmes'] as $programme)
                                        <option value="{{ $programme->id }}" @selected((int) $filter['programme_id'] === (int) $programme->id)>
                                            {{ $programme->prog_code }} - {{ $programme->prog_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-xl-2">
                                <label for="dashboard-mode" class="form-label small fw-semibold">Study mode</label>
                                <select id="dashboard-mode" name="mode" class="form-select">
                                    <option value="">All modes</option>
                                    @foreach ($cd['options']['modes'] as $mode)
                                        <option value="{{ $mode }}" @selected($filter['mode'] === $mode)>{{ $mode }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-xl-2">
                                <label for="dashboard-activity" class="form-label small fw-semibold">Activity</label>
                                <select id="dashboard-activity" name="activity" class="form-select">
                                    <option value="">All activities</option>
                                    @foreach ($cd['options']['activities'] as $activity)
                                        <option value="{{ $activity->id }}" @selected((int) $filter['activity_id'] === (int) $activity->id)>
                                            {{ $activity->act_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">Apply</button>
                                <a href="{{ route('staff-dashboard', ['dashboard_role' => 'administrator']) }}" class="btn btn-outline-secondary" title="Reset filters" aria-label="Reset filters">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="row g-3 mb-4">
                        <div class="col-6 col-xl-3">
                            <div class="operational-card">
                                <div class="metric-value">{{ number_format($cd['operational']['pending_actions']) }}</div>
                                <div class="metric-label">Approvals awaiting you</div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="operational-card is-danger">
                                <div class="metric-value">{{ number_format($cd['operational']['overdue']) }}</div>
                                <div class="metric-label">Overdue activity submissions or corrections</div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="operational-card is-danger">
                                <div class="metric-value">{{ number_format($cd['operational']['unassigned']) }}</div>
                                <div class="metric-label">Students without supervision</div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="operational-card is-warning">
                                <div class="metric-value">{{ number_format($cd['operational']['stalled']) }}</div>
                                <div class="metric-label" title="{{ $cd['definitions']['stalled'] }}">Items waiting over 7 days</div>
                            </div>
                        </div>
                    </div>

                    <section class="mb-4" aria-labelledby="action-required-title">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 id="action-required-title" class="dashboard-section-title">Pending approvals</h6>
                            @if (!$filter['activity_id'])
                                <span class="text-muted small">Select an activity to open nomination or evaluation queues directly.</span>
                            @endif
                        </div>
                        <div class="row g-3">
                            @foreach ($cd['actions'] as $action)
                                <div class="col-sm-6 col-xl-3">
                                    <a href="{{ $action['url'] }}" class="action-card">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="metric-value">{{ number_format($action['count']) }}</div>
                                                <div class="metric-label">{{ $action['label'] }}</div>
                                            </div>
                                            <span class="action-icon"><i class="{{ $action['icon'] }}"></i></span>
                                        </div>
                                        <div class="small text-primary mt-3">Open queue <i class="fas fa-arrow-right ms-1"></i></div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="mb-4" aria-labelledby="semester-pulse-title">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <h6 id="semester-pulse-title" class="dashboard-section-title">Semester overview</h6>
                            <span class="text-muted" title="{{ $cd['definitions']['completion_rate'] }}"><i class="fas fa-info-circle"></i></span>
                        </div>
                        <div class="row g-3">
                            @foreach ([
                                ['Active enrolled students', $cd['kpis']['enrolled_students'], 'fas fa-user-graduate', null],
                                ['Completed activities', $cd['kpis']['completed_activities'], 'fas fa-check-circle', null],
                                ['Activities in progress', $cd['kpis']['in_progress_activities'], 'fas fa-spinner', null],
                                ['Students needing follow-up', $cd['kpis']['at_risk_students'], 'fas fa-exclamation-triangle', $cd['definitions']['at_risk']],
                                ['Completed activities (%)', $cd['kpis']['completion_rate'].'%', 'fas fa-chart-line', $cd['definitions']['completion_rate']],
                                ['Deadlines within 14 days', $cd['kpis']['due_soon'], 'fas fa-calendar-day', null],
                            ] as [$label, $value, $icon, $definition])
                                <div class="col-6 col-lg-4 col-xl-2">
                                    <div class="pulse-card" @if ($definition) title="{{ $definition }}" @endif>
                                        <i class="{{ $icon }} pulse-icon"></i>
                                        <div class="metric-value mt-3">{{ is_numeric($value) ? number_format($value) : $value }}</div>
                                        <div class="metric-label">{{ $label }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="dashboard-panel mb-4" aria-labelledby="workflow-title">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <h6 id="workflow-title" class="dashboard-section-title">Student progress by stage</h6>
                            <span class="text-muted small">Number of student activities currently waiting at each stage.</span>
                        </div>
                        <div class="pipeline">
                            @foreach ($cd['pipeline'] as $stage)
                                <div class="pipeline-step">
                                    <i class="{{ $stage['icon'] }} text-primary mb-2"></i>
                                    <div class="pipeline-count">{{ number_format($stage['count']) }}</div>
                                    <div class="small text-muted">{{ $stage['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-8" id="committee-attention">
                            <section class="dashboard-panel" aria-labelledby="attention-title">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 id="attention-title" class="dashboard-section-title">Students needing follow-up</h6>
                                    <span class="badge bg-light text-dark border">{{ number_format($cd['attention_total']) }} identified</span>
                                </div>
                                @if (count($cd['attention']))
                                    <div class="table-responsive">
                                        <table class="table attention-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Programme</th>
                                                    <th>Activity</th>
                                                    <th>Issue</th>
                                                    <th>Waiting</th>
                                                    <th class="text-end">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($cd['attention'] as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold text-dark">{{ $item['student_name'] }}</div>
                                                            <div class="small text-muted">{{ $item['matric_no'] }}</div>
                                                        </td>
                                                        <td>{{ $item['programme'] }}</td>
                                                        <td>{{ $item['activity'] }}</td>
                                                        <td><span class="badge bg-light-{{ $item['severity'] }} text-{{ $item['severity'] }}">{{ $item['issue'] }}</span></td>
                                                        <td>{{ number_format($item['waiting_days']) }} days</td>
                                                        <td class="text-end"><a href="{{ $item['url'] }}" class="btn btn-sm btn-outline-primary">Review</a></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-check-circle text-success fs-3 d-block mb-2"></i>
                                        No students currently match the attention rules.
                                    </div>
                                @endif
                            </section>
                        </div>
                        <div class="col-xl-4">
                            <section class="dashboard-panel" aria-labelledby="deadlines-title">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 id="deadlines-title" class="dashboard-section-title">Upcoming deadlines</h6>
                                    <span class="text-muted small">Next 14 days</span>
                                </div>
                                @forelse ($cd['deadlines'] as $deadline)
                                    <a href="{{ $deadline['url'] }}" class="deadline-item d-flex justify-content-between gap-3 text-decoration-none text-reset">
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-dark text-truncate">{{ $deadline['student_name'] }}</div>
                                            <div class="small text-muted text-truncate">{{ $deadline['type'] }} · {{ $deadline['activity'] }}</div>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($deadline['due_at'])->format('d M') }}</div>
                                            <div class="small {{ $deadline['days'] <= 3 ? 'text-danger' : 'text-muted' }}">
                                                {{ $deadline['days'] === 0 ? 'Today' : $deadline['days'].' days' }}
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-check fs-3 d-block mb-2"></i>
                                        No deadlines in the next 14 days.
                                    </div>
                                @endforelse
                            </section>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        No semester is configured yet. Create a semester before dashboard reporting can be generated.
                    </div>
                @endif
            @endif
            <!-- [ Higher Ups Dashboard ] end -->

            @if (in_array($dashboardRole, ['chairman', 'examiner'], true))
                <div class="dashboard-panel mb-4">
                    <div class="d-flex align-items-start gap-3">
                        <span class="action-icon"><i class="fas fa-info-circle"></i></span>
                        <div>
                            <h5 class="text-dark mb-1">{{ $dashboardRoles[$dashboardRole] }} workspace</h5>
                            <p class="text-muted mb-0">
                                This role is valid for your account, but its reporting workspace is not part of the current
                                Supervisor phase. Use the role-specific navigation in the sidebar for the available work queues.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <script>
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
                const clockElement = document.getElementById('live-clock');
                if (clockElement) {
                    clockElement.innerHTML = `<i class="fas fa-clock me-2"></i>${timeString}`;
                }
            }
            setInterval(updateClock, 1000);
            updateClock();
        </script>
        <!-- [ Main Content ] end -->
    </div>
@endsection
