@php
    $sd = $supervisorDashboard;
    $filter = $sd['filters'];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="fw-bold text-dark mb-1">Supervisor Workspace</h5>
        <div class="text-muted small">{{ $sd['semester']->sem_label ?? 'Selected semester' }} · your assigned students only</div>
    </div>
    <span class="badge bg-light text-dark border no-print">Last updated {{ now()->format('M j, Y g:i A') }}</span>
</div>

<form method="GET" action="{{ route('staff-dashboard') }}" class="dashboard-filter-card mb-4 no-print">
    <input type="hidden" name="dashboard_role" value="supervisor">
    <div class="row g-3 align-items-end">
        <div class="col-sm-6 col-xl-3">
            <label for="supervisor-dashboard-semester" class="form-label small fw-semibold">Semester</label>
            <select id="supervisor-dashboard-semester" name="semester" class="form-select">
                @foreach ($sd['options']['semesters'] as $semester)
                    <option value="{{ $semester->id }}" @selected((int) $filter['semester_id'] === (int) $semester->id)>
                        {{ $semester->sem_label }}{{ (int) $semester->sem_status === 1 ? ' (Current)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-xl-3">
            <label for="supervisor-dashboard-programme" class="form-label small fw-semibold">Programme</label>
            <select id="supervisor-dashboard-programme" name="programme" class="form-select">
                <option value="">All supervised programmes</option>
                @foreach ($sd['options']['programmes'] as $programme)
                    <option value="{{ $programme->id }}" @selected((int) $filter['programme_id'] === (int) $programme->id)>
                        {{ $programme->prog_code }} - {{ $programme->prog_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-xl-2">
            <label for="supervisor-dashboard-mode" class="form-label small fw-semibold">Study mode</label>
            <select id="supervisor-dashboard-mode" name="mode" class="form-select">
                <option value="">All modes</option>
                @foreach ($sd['options']['modes'] as $mode)
                    <option value="{{ $mode }}" @selected($filter['mode'] === $mode)>{{ $mode }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-xl-2">
            <label for="supervisor-dashboard-activity" class="form-label small fw-semibold">Activity</label>
            <select id="supervisor-dashboard-activity" name="activity" class="form-select">
                <option value="">All activities</option>
                @foreach ($sd['options']['activities'] as $activity)
                    <option value="{{ $activity->id }}" @selected((int) $filter['activity_id'] === (int) $activity->id)>
                        {{ $activity->act_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Apply</button>
            <a href="{{ route('staff-dashboard', ['dashboard_role' => 'supervisor']) }}" class="btn btn-outline-secondary"
                title="Reset filters" aria-label="Reset filters"><i class="fas fa-undo"></i></a>
        </div>
    </div>
</form>

<section class="mb-4" aria-labelledby="supervisor-actions-title">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 id="supervisor-actions-title" class="dashboard-section-title">Tasks requiring your action</h6>
        @if (!$filter['activity_id'])
            <span class="text-muted small">Activity-specific queues open at the first activity containing work.</span>
        @endif
    </div>
    <div class="row g-3">
        @foreach ($sd['actions'] as $action)
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

<section class="mb-4" aria-labelledby="supervisor-pulse-title">
    <div class="d-flex align-items-center gap-2 mb-3">
        <h6 id="supervisor-pulse-title" class="dashboard-section-title">Supervision overview</h6>
        <span class="text-muted" title="{{ $sd['definitions']['completion_rate'] }}"><i class="fas fa-info-circle"></i></span>
    </div>
    <div class="row g-3">
        @foreach ([
            ['Supervised students', $sd['kpis']['supervised_students'], 'fas fa-user-graduate', null],
            ['Completed activities', $sd['kpis']['completed_activities'], 'fas fa-check-circle', null],
            ['Activities in progress', $sd['kpis']['in_progress_activities'], 'fas fa-spinner', null],
            ['Students needing follow-up', $sd['kpis']['at_risk_students'], 'fas fa-exclamation-triangle', $sd['definitions']['at_risk']],
            ['Completed activities (%)', $sd['kpis']['completion_rate'].'%', 'fas fa-chart-line', $sd['definitions']['completion_rate']],
            ['Deadlines within 14 days', $sd['kpis']['due_soon'], 'fas fa-calendar-day', null],
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

<div class="row g-4 mb-4">
    <div class="col-xl-8" id="supervisor-attention">
        <section class="dashboard-panel" aria-labelledby="supervisor-attention-title">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 id="supervisor-attention-title" class="dashboard-section-title">Students needing follow-up</h6>
                <span class="badge bg-light text-dark border">{{ number_format($sd['attention_total']) }} identified</span>
            </div>
            @if (count($sd['attention']))
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
                            @foreach ($sd['attention'] as $item)
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
                    No supervised students currently match the attention rules.
                </div>
            @endif
        </section>
    </div>
    <div class="col-xl-4">
        <section class="dashboard-panel" aria-labelledby="supervisor-deadlines-title">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 id="supervisor-deadlines-title" class="dashboard-section-title">Upcoming deadlines</h6>
                <span class="text-muted small">Next 14 days</span>
            </div>
            @forelse ($sd['deadlines'] as $deadline)
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

<section class="dashboard-panel mb-4" aria-labelledby="supervised-students-title">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 id="supervised-students-title" class="dashboard-section-title">My students overview</h6>
            <div class="text-muted small mt-1">Recorded activity counts, pending actions and nearest known deadline.</div>
        </div>
        <a href="{{ route('my-supervision-student-list') }}" class="btn btn-sm btn-outline-primary">View all students</a>
    </div>
    @if (count($sd['students']))
        <div class="table-responsive">
            <table class="table attention-table mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Programme</th>
                        <th>Supervision</th>
                        <th>Enrollment</th>
                        <th>Activities</th>
                        <th>Your actions</th>
                        <th>Next deadline</th>
                        <th class="text-end">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sd['students'] as $student)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $student['student_name'] }}</div>
                                <div class="small text-muted">{{ $student['matric_no'] }}</div>
                            </td>
                            <td>{{ $student['programme'] }}</td>
                            <td>{{ $student['supervision_role'] }}</td>
                            <td>{{ $student['enrollment_status'] }}</td>
                            <td>
                                <span class="text-success fw-semibold">{{ $student['completed'] }}</span> completed ·
                                <span class="text-primary fw-semibold">{{ $student['in_progress'] }}</span> in progress
                            </td>
                            <td>
                                <span class="badge {{ $student['pending_actions'] ? 'bg-light-warning text-warning' : 'bg-light-secondary text-secondary' }}">
                                    {{ $student['pending_actions'] }} pending
                                </span>
                            </td>
                            <td>
                                @if ($student['next_deadline'])
                                    {{ \Carbon\Carbon::parse($student['next_deadline']['due_at'])->format('d M Y') }}
                                    <div class="small text-muted">{{ $student['next_deadline']['type'] }}</div>
                                @else
                                    <span class="text-muted">None recorded</span>
                                @endif
                            </td>
                            <td class="text-end"><a href="{{ $student['url'] }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5 text-muted">No supervised students are enrolled in the selected semester and filters.</div>
    @endif
</section>
