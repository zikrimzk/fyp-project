@php
    use Carbon\Carbon;
@endphp
@extends('student.layouts.main')

@section('content')
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
                    <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
                        <!-- Card Header -->
                        <div class="card-header bg-white border-bottom p-4">
                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                <div class="mb-3 mb-md-0">
                                    <h3 class="h4 mb-1">
                                        <i class="fas fa-user-graduate me-2"></i>
                                        Welcome, {{ auth()->user()->student_name }}
                                    </h3>
                                    <p class="text-muted small mb-0">Student Dashboard</p>
                                </div>
                                <span class="badge bg-light text-dark px-3 py-2">
                                    <i class="fas fa-calendar-alt me-1"></i> {{ now()->format('F Y') }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <!-- Research Title -->
                                <div class="list-group-item p-4 border-bottom">
                                    <div
                                        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                        <div class="mb-3 mb-md-0">
                                            <h4 class="h6 text-muted mb-2">
                                                <i class="fas fa-book-open text-secondary me-2"></i>
                                                Research Title
                                            </h4>
                                            <p class="mb-0 fw-semibold text-dark">
                                                {{ auth()->user()->student_titleOfResearch == null ? '-' : auth()->user()->student_titleOfResearch ?? 'Not specified' }}
                                            </p>
                                        </div>
                                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3"
                                            data-bs-toggle="modal" data-bs-target="#editResearchModal">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Supervisor -->
                                @php
                                    $supervisor = DB::table('supervisions as a')
                                        ->join('staff as b', 'a.staff_id', '=', 'b.id')
                                        ->select('b.staff_name as supervisor_name', 'b.staff_email')
                                        ->where('student_id', auth()->user()->id)
                                        ->where('supervision_role', 1)
                                        ->first();
                                @endphp
                                <div class="list-group-item p-4 border-bottom">
                                    <div
                                        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                        <div class="mb-3 mb-md-0">
                                            <h4 class="h6 text-muted mb-2">
                                                <i class="fas fa-chalkboard-teacher text-secondary me-2"></i>
                                                Supervisor
                                            </h4>
                                            <div>
                                                <p class="mb-1 fw-semibold text-dark">
                                                    {{ $supervisor->supervisor_name ?? 'Not assigned' }}
                                                </p>
                                                @if ($supervisor)
                                                    <small class="text-muted">
                                                        <a href="mailto:{{ $supervisor->staff_email }}"
                                                            class="link-primary">
                                                            <i class="fas fa-envelope me-1"></i>
                                                            {{ $supervisor->staff_email }}
                                                        </a>
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Co-Supervisor -->
                                @php
                                    $cosupervisor = DB::table('supervisions as a')
                                        ->join('staff as b', 'a.staff_id', '=', 'b.id')
                                        ->select('b.staff_name as supervisor_name', 'b.staff_email')
                                        ->where('student_id', auth()->user()->id)
                                        ->where('supervision_role', 2)
                                        ->first();
                                @endphp
                                <div class="list-group-item p-4">
                                    <div
                                        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                        <div class="mb-3 mb-md-0">
                                            <h4 class="h6 text-muted mb-2">
                                                <i class="fas fa-user-tie text-secondary me-2"></i>
                                                Co-Supervisor
                                            </h4>
                                            <div>
                                                <p class="mb-1 fw-semibold text-dark">
                                                    {{ $cosupervisor->supervisor_name ?? 'Not assigned' }}
                                                </p>
                                                @if ($cosupervisor)
                                                    <small class="text-muted">
                                                        <a href="mailto:{{ $cosupervisor->staff_email }}"
                                                            class="link-primary">
                                                            <i class="fas fa-envelope me-1"></i>
                                                            {{ $cosupervisor->staff_email }}
                                                        </a>
                                                    </small>
                                                @endif
                                            </div>
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

                    <!-- [ Reminder ] start -->
                    <div class="card border-0 shadow-sm">
                        <!-- Card Header -->
                        <div class="card-header bg-white border-bottom-0 pb-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fas fa-bell me-2"></i>Reminders
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

                        <!-- Tab Content -->
                        <div class="card-body p-2">
                            <div class="tab-content" id="remindersTabContent">
                                <!-- Upcoming Submissions -->
                                <div class="tab-pane fade show active" id="upcoming-pane" role="tabpanel"
                                    aria-labelledby="upcoming-tab">
                                    <div class="list-group list-group-flush">
                                        <!-- Reminder Item -->
                                        @forelse ($documents->where('submission_status', 1) as $doc)
                                            <a
                                                href="{{ route('student-document-submission', Crypt::encrypt($doc->submission_id)) }}">
                                                <div class="list-group-item border-0 py-3 px-4">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">{{ $doc->document_name }}</h6>
                                                            <p class="small mb-1">
                                                                <i class="far fa-calendar-alt me-1"></i>
                                                                @php
                                                                    $dueDate = Carbon::parse($doc->submission_duedate);
                                                                    $daysRemaining = Carbon::now()->diffInDays(
                                                                        $dueDate,
                                                                        false,
                                                                    );

                                                                    if ($daysRemaining > 7) {
                                                                        $textClass = 'text-muted';
                                                                        echo "<span class='$textClass'>Due in $daysRemaining days (" .
                                                                            $dueDate->format('M d, Y') .
                                                                            ')</span>';
                                                                    } elseif ($daysRemaining > 1) {
                                                                        $textClass = 'text-warning';
                                                                        echo "<span class='$textClass'>Due in $daysRemaining days (" .
                                                                            $dueDate->format('M d, Y') .
                                                                            ')</span>';
                                                                    } elseif ($daysRemaining == 1) {
                                                                        $textClass = 'text-warning';
                                                                        echo "<span class='$textClass'>Due tomorrow (" .
                                                                            $dueDate->format('M d, Y') .
                                                                            ')</span>';
                                                                    } elseif ($daysRemaining == 0) {
                                                                        $textClass = 'text-danger';
                                                                        echo "<span class='$textClass'>Due today (" .
                                                                            $dueDate->format('M d, Y') .
                                                                            ')</span>';
                                                                    } else {
                                                                        $textClass = 'text-danger';
                                                                        echo "<span class='$textClass'>Overdue by " .
                                                                            abs($daysRemaining) .
                                                                            ' days (' .
                                                                            $dueDate->format('M d, Y') .
                                                                            ')</span>';
                                                                    }
                                                                @endphp
                                                            </p>
                                                            <p class="small mb-0">
                                                                <span
                                                                    class="badge bg-light text-dark">{{ $doc->activity_name }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                            </a>

                                        @empty
                                            <div class="list-group-item border-0 py-3 px-4">
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-muted mb-0">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            No upcoming submissions
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Past Due -->
                                <div class="tab-pane fade" id="past-pane" role="tabpanel" aria-labelledby="past-tab">
                                    <div class="list-group list-group-flush">
                                        <!-- Reminder Item -->
                                        @forelse ($documents->where('submission_status', 4) as $doc)
                                            <a
                                                href="{{ route('student-document-submission', Crypt::encrypt($doc->submission_id)) }}">
                                                <div class="list-group-item border-0 py-3 px-4">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">{{ $doc->document_name }}</h6>
                                                            <p class="small mb-1">
                                                                <i class="far fa-calendar-alt me-1"></i>
                                                                @php
                                                                    $dueDate = Carbon::parse($doc->submission_duedate);
                                                                    $daysRemaining = Carbon::now()->diffInDays(
                                                                        $dueDate,
                                                                        false,
                                                                    );

                                                                    if ($daysRemaining > 7) {
                                                                        $textClass = 'text-muted';
                                                                        echo "<span class='$textClass'>Due in $daysRemaining days (" .
                                                                            $dueDate->format('M d, Y') .
                                                                            ')</span>';
                                                                    } elseif ($daysRemaining > 1) {
                                                                        $textClass = 'text-warning';
                                                                        echo "<span class='$textClass'>Due in $daysRemaining days (" .
                                                                            $dueDate->format('M d, Y') .
                                                                            ')</span>';
                                                                    } elseif ($daysRemaining == 1) {
                                                                        $textClass = 'text-warning';
                                                                        echo "<span class='$textClass'>Due tomorrow (" .
                                                                            $dueDate->format('M d, Y') .
                                                                            ')</span>';
                                                                    } elseif ($daysRemaining == 0) {
                                                                        $textClass = 'text-danger';
                                                                        echo "<span class='$textClass'>Due today (" .
                                                                            $dueDate->format('M d, Y') .
                                                                            ')</span>';
                                                                    } else {
                                                                        $textClass = 'text-danger';
                                                                        echo "<span class='$textClass'>Overdue by " .
                                                                            abs($daysRemaining) .
                                                                            ' days (' .
                                                                            $dueDate->format('M d, Y') .
                                                                            ')</span>';
                                                                    }
                                                                @endphp
                                                            </p>
                                                            <p class="small mb-0">
                                                                <span
                                                                    class="badge bg-light text-dark">{{ $doc->activity_name }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                            </a>

                                        @empty
                                            <div class="list-group-item border-0 py-3 px-4">
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-muted mb-0">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            No overdue submissions
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Completed -->
                                <div class="tab-pane fade" id="completed-pane" role="tabpanel"
                                    aria-labelledby="completed-tab">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item border-0 py-3 px-4">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0 pt-1">
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success rounded-circle p-2">
                                                        <i class="fas fa-check fs-6"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-1">Chapter 1 Submission</h6>
                                                    <p class="text-muted small mb-1">
                                                        <i class="far fa-calendar-alt me-1"></i> Submitted on May 28, 2023
                                                    </p>
                                                    <p class="small text-success mb-0">
                                                        <i class="fas fa-check-circle me-1"></i> Approved by supervisor
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ Reminder ] end -->

                    <!-- [ TBS ] start -->
                    <div class="card border-0 shadow-sm">
                        <!-- Card Header -->
                        <div class="card-header bg-white border-bottom-0 pb-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="ti ti-chart-arcs me-2"></i>Student Progress
                                </h5>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-2">
                            <div class="fst-italic text-muted text-center">Coming soon..</div>
                        </div>
                    </div>
                    <!-- [ TBS ] end -->

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
                                        <i class="ti ti-edit text-primary me-2"></i>
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
