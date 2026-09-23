@extends('staff.layouts.main')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb mb-2">
                                <li class="breadcrumb-item">Administrator</li>
                                <li class="breadcrumb-item" aria-current="page">Audit Log</li>
                            </ul>
                            <h2 class="mb-0">Audit Log</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><strong><i class="ti ti-filter me-2"></i>Filters</strong></div>
                <div class="card-body">
                    <form method="GET" action="{{ route('audit-log-index') }}" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="category" class="form-label">Category</label>
                            <select id="category" name="category" class="form-select">
                                <option value="">All categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}" @selected(request('category') === $category)>
                                        {{ str($category)->headline() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="outcome" class="form-label">Outcome</label>
                            <select id="outcome" name="outcome" class="form-select">
                                <option value="">All outcomes</option>
                                <option value="success" @selected(request('outcome') === 'success')>Success</option>
                                <option value="failed" @selected(request('outcome') === 'failed')>Failed</option>
                                <option value="skipped" @selected(request('outcome') === 'skipped')>Skipped</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="actor_type" class="form-label">Actor type</label>
                            <select id="actor_type" name="actor_type" class="form-select">
                                <option value="">All actors</option>
                                <option value="staff" @selected(request('actor_type') === 'staff')>Staff</option>
                                <option value="student" @selected(request('actor_type') === 'student')>Student</option>
                                <option value="system" @selected(request('actor_type') === 'system')>System</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From</label>
                            <input id="date_from" name="date_from" type="date" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To</label>
                            <input id="date_to" name="date_to" type="date" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="search" class="form-label">Search</label>
                            <input id="search" name="search" type="search" class="form-control" maxlength="150"
                                value="{{ request('search') }}" placeholder="Actor, event, reference...">
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit"><i class="ti ti-search me-1"></i>Apply</button>
                            <a class="btn btn-outline-secondary" href="{{ route('audit-log-index') }}">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Date &amp; time</th>
                                    <th>Category</th>
                                    <th>Actor</th>
                                    <th>Activity</th>
                                    <th>Outcome</th>
                                    <th>Reference</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td class="text-nowrap">{{ $log->occurred_at?->format('d M Y, h:i:s A') }}</td>
                                        <td><span class="badge bg-light-primary text-primary">{{ str($log->category)->headline() }}</span></td>
                                        <td>
                                            <div class="fw-semibold">{{ $log->actor_name ?: 'System / Unknown' }}</div>
                                            <small class="text-muted">{{ $log->actor_identifier ?: str($log->actor_type)->headline() }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $log->description }}</div>
                                            <small class="text-muted">{{ str($log->event)->replace(['-', '.'], ' ')->headline() }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $log->outcome === 'success' ? 'bg-light-success text-success' : ($log->outcome === 'skipped' ? 'bg-light-warning text-warning' : 'bg-light-danger text-danger') }}">
                                                {{ str($log->outcome)->headline() }}
                                            </span>
                                        </td>
                                        <td><code title="Request reference">{{ $log->request_id ?: '—' }}</code></td>
                                        <td>
                                            <details>
                                                <summary class="text-primary" style="cursor: pointer">View</summary>
                                                <div class="mt-2" style="min-width: 320px; max-width: 560px">
                                                    @php($client = data_get($log->metadata, 'client', []))
                                                    @if ($log->subject_type || $log->subject_label || $log->subject_id)
                                                        <div><strong>Subject:</strong> {{ $log->subject_label ?: str($log->subject_type)->headline() }}{{ $log->subject_id ? ' (' . $log->subject_id . ')' : '' }}</div>
                                                    @endif
                                                    <div><strong>IP address:</strong> {{ $log->ip_address ?: '—' }}</div>
                                                    <div><strong>Browser:</strong> {{ data_get($client, 'browser', '—') }}</div>
                                                    <div><strong>Operating system:</strong> {{ data_get($client, 'operating_system', '—') }}</div>
                                                    <div><strong>Device:</strong> {{ data_get($client, 'device_type', '—') }}</div>
                                                    @if (data_get($client, 'session_reference'))
                                                        <div><strong>Session reference:</strong> <code>{{ data_get($client, 'session_reference') }}</code></div>
                                                    @endif
                                                    @if ($log->user_agent)
                                                        <details class="mt-1">
                                                            <summary class="small text-muted" style="cursor: pointer">Technical client details</summary>
                                                            <div class="small text-break mt-1"><strong>User agent:</strong> {{ $log->user_agent }}</div>
                                                            @if (data_get($client, 'ip_chain'))
                                                                <div class="small"><strong>Trusted IP chain:</strong> {{ implode(' → ', data_get($client, 'ip_chain', [])) }}</div>
                                                            @endif
                                                            @if (data_get($client, 'language'))
                                                                <div class="small"><strong>Browser language:</strong> {{ data_get($client, 'language') }}</div>
                                                            @endif
                                                        </details>
                                                    @endif
                                                    @if (!empty($log->metadata))
                                                        <pre class="small bg-light border rounded p-2 mt-2 mb-0 text-wrap">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                    @endif
                                                </div>
                                            </details>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="ti ti-clipboard-off fs-1 text-muted"></i>
                                            <h5 class="mt-3">No audit records match these filters</h5>
                                            <p class="text-muted mb-2">Try a wider date range or clear one of the selected filters.</p>
                                            <a href="{{ route('audit-log-index') }}" class="btn btn-sm btn-outline-primary">Clear filters</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                        <small class="text-muted">
                            Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} records
                        </small>
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
