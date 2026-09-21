@php
    use App\Models\Semester;
@endphp
            @php

                /* KEEP SUPERVISION ACCESS INCLUDING OUTSTANDING WORK FROM EARLIER SEMESTERS */
                $supervision = DB::table('supervisions')->where('staff_id', auth()->user()->id)->exists();

                /* CHECK EACH ROLE */
                $iscommittee = auth()->user()->staff_role == 1;
                $isDD = auth()->user()->staff_role == 3;
                $isDean = auth()->user()->staff_role == 4;

                $showDeputyDeanNomination = false;
                $deputyDeanNominations = collect();

                if (auth()->user()->staff_role == 3) {
                    $deputyDeanNominations = DB::table('activity_forms as af')
                        ->join('form_fields as ff', 'af.id', '=', 'ff.af_id')
                        ->join('procedures as p', 'af.activity_id', '=', 'p.activity_id')
                        ->join('activities as a', 'p.activity_id', '=', 'a.id')
                        ->where('af.af_target', 3)
                        ->where('ff.ff_category', 6)
                        ->where('ff.ff_signature_role', 5)
                        ->where('p.is_haveEva', 1)
                        ->select('a.id as activity_id', 'a.act_name as activity_name')
                        ->distinct()
                        ->get();

                    $showDeputyDeanNomination = $deputyDeanNominations->isNotEmpty();
                }

                $showDeanNomination = false;
                $deanNominations = collect();

                if (auth()->user()->staff_role == 4) {
                    $deanNominations = DB::table('activity_forms as af')
                        ->join('form_fields as ff', 'af.id', '=', 'ff.af_id')
                        ->join('procedures as p', 'af.activity_id', '=', 'p.activity_id')
                        ->join('activities as a', 'p.activity_id', '=', 'a.id')
                        ->where('af.af_target', 3)
                        ->where('ff.ff_category', 6)
                        ->where('ff.ff_signature_role', 6)
                        ->where('p.is_haveEva', 1)
                        ->select('a.id as activity_id', 'a.act_name as activity_name')
                        ->distinct()
                        ->get();

                    $showDeanNomination = $deanNominations->isNotEmpty();
                }

                /* LOAD NOMINATION DATA */
                $nomination = DB::table('procedures as a')
                    ->join('activities as b', 'a.activity_id', '=', 'b.id')
                    ->where('a.is_haveEva', 1)
                    ->select('b.id as activity_id', 'b.act_name as activity_name')
                    ->distinct()
                    ->get();

                /* LOAD EXAMINER/PANEL ACTIVITIES */
                $examinerpanelActivity = DB::table('evaluators as a')
                    ->join('staff as b', 'a.staff_id', '=', 'b.id')
                    ->join('nominations as c', 'a.nom_id', '=', 'c.id')
                    ->join('activities as d', 'c.activity_id', '=', 'd.id')
                    ->where('b.id', auth()->user()->id)
                    ->where('a.eva_status', 3)
                    ->where('a.eva_role', 1)
                    ->select('d.id as activity_id', 'd.act_name as activity_name')
                    ->distinct()
                    ->get();

                /* LOAD CHAIRMAN ACTIVITIES */
                $chairmanActivity = DB::table('evaluators as a')
                    ->join('staff as b', 'a.staff_id', '=', 'b.id')
                    ->join('nominations as c', 'a.nom_id', '=', 'c.id')
                    ->join('activities as d', 'c.activity_id', '=', 'd.id')
                    ->where('b.id', auth()->user()->id)
                    ->where('a.eva_status', 3)
                    ->where('a.eva_role', 2)
                    ->select('d.id as activity_id', 'd.act_name as activity_name')
                    ->distinct()
                    ->get();

                /* CHECK IF USER IS HIGHER UP */
                $higherUps = DB::table('staff')
                    ->where('id', auth()->user()->id)
                    ->whereIn('staff_role', [1, 3, 4])
                    ->exists();

                /* EVALUATION APPROVAL CONFIG */
                $evalConfig = DB::table('procedures as p')
                    ->join('activities as a', 'p.activity_id', '=', 'a.id')
                    ->join('activity_forms as af', 'a.id', '=', 'af.activity_id')
                    ->join('form_fields as ff', 'af.id', '=', 'ff.af_id')
                    ->where('p.is_haveEva', 1)
                    ->where('p.evaluation_mode', 2)
                    ->whereIn('af.af_target', [4, 5])
                    ->where('ff.ff_category', 6)
                    ->whereIn('ff.ff_signature_role', [2, 3, 4, 5, 6])
                    ->select('a.id as activity_id', 'a.act_name as activity_name', 'ff.ff_signature_role as role')
                    ->distinct()
                    ->get()
                    ->groupBy('activity_id')
                    ->map(function ($rows) {
                        $first = $rows->first();
                        return [
                            'activity_id' => $first->activity_id,
                            'activity_name' => $first->activity_name,
                            'roles' => $rows->pluck('role')->unique()->values()->all(),
                        ];
                    })
                    ->values();

                $slugify = fn($name) => Str::of($name)->lower()->replace(' ', '-');

                $supervisorActs = $evalConfig->filter(
                    fn($x) => collect($x['roles'])
                        ->intersect([2, 3])
                        ->isNotEmpty(),
                );
                $committeeActs = $evalConfig->filter(fn($x) => in_array(4, $x['roles']));
                $ddActs = $evalConfig->filter(fn($x) => in_array(5, $x['roles']));
                $deanActs = $evalConfig->filter(fn($x) => in_array(6, $x['roles']));

                $sidebarRoles = [];
                if ($higherUps) $sidebarRoles['administrator'] = 'Administrator';
                if ($supervision) $sidebarRoles['supervisor'] = 'Supervisor';
                if ($chairmanActivity->isNotEmpty()) $sidebarRoles['chairman'] = 'Chairman';
                if ($examinerpanelActivity->isNotEmpty()) $sidebarRoles['examiner'] = 'Examiner / Panel';
                $storedSidebarRole = session('staff_dashboard_role');
                $requestedSidebarRole = request('dashboard_role');
                $initialSidebarRole = is_string($requestedSidebarRole) && array_key_exists($requestedSidebarRole, $sidebarRoles)
                    ? $requestedSidebarRole
                    : (is_string($storedSidebarRole) && array_key_exists($storedSidebarRole, $sidebarRoles) ? $storedSidebarRole : array_key_first($sidebarRoles));

                if (request()->routeIs('my-supervision-*') && array_key_exists('supervisor', $sidebarRoles)) {
                    $initialSidebarRole = 'supervisor';
                } elseif (request()->routeIs('chairman-*') && array_key_exists('chairman', $sidebarRoles)) {
                    $initialSidebarRole = 'chairman';
                } elseif (request()->routeIs('examiner-panel-*') && array_key_exists('examiner', $sidebarRoles)) {
                    $initialSidebarRole = 'examiner';
                } elseif (request()->routeIs('submission-eligibility') && array_key_exists('administrator', $sidebarRoles)) {
                    $initialSidebarRole = 'administrator';
                }
                $sidebarCountsAvailable = true;
                try {
                    $sidebarCounts = app(\App\Services\StaffWorkCounts::class)->forStaff(auth()->user());
                } catch (\Throwable $error) {
                    report($error);
                    $sidebarCounts = [];
                    $sidebarCountsAvailable = false;
                }
            @endphp
<link rel="stylesheet" href="{{ asset('assets/css/staff-sidebar.css') }}?v=1.0.0">
<nav class="pc-sidebar staff-work-sidebar" id="staff-work-sidebar" aria-label="Staff navigation"
    data-staff-id="{{ auth()->user()->id }}"
    data-counts-url="{{ route('staff-sidebar-work-counts') }}"
    data-dashboard-url="{{ route('staff-dashboard') }}"
    data-route-role="{{ $initialSidebarRole }}"
    data-initial-role="{{ $initialSidebarRole }}">
    <div class="navbar-wrapper">
        <div class="staff-sidebar-heading">
            <a href="{{ route('staff-dashboard') }}" class="staff-sidebar-brand">
                <img src="{{ asset('assets/images/logo-utem.PNG') }}" alt="UTeM" width="54">
                <span><strong>e-Pasca</strong><small>{{ Semester::where('sem_status', 1)->value('sem_label') ?? 'No current semester' }}</small></span>
            </a>
            <div class="staff-sidebar-person">
                <img src="{{ empty(auth()->user()->staff_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . auth()->user()->staff_photo) }}" alt="" width="34" height="34">
                <span>{{ auth()->user()->staff_name ?? 'Staff' }}</span>
            </div>
            @if (count($sidebarRoles))
                <label for="staff-sidebar-role" class="staff-role-label">My Role</label>
                <select id="staff-sidebar-role" class="form-select" aria-controls="staff-role-navigation">
                    @foreach ($sidebarRoles as $key => $label)
                        <option value="{{ $key }}" data-label="{{ $label }}" @selected($key === $initialSidebarRole)>{{ $label }}</option>
                    @endforeach
                </select>
                @unless ($sidebarCountsAvailable)
                    <div class="staff-work-summary" role="status">Pending counts temporarily unavailable</div>
                @endunless
                <div id="staff-other-roles" class="staff-other-roles" aria-label="Pending work in other roles"></div>
            @endif
        </div>
        <div class="navbar-content" id="staff-role-navigation">
            <ul class="pc-navbar">
                <!-- Main Section -->
                <li class="pc-item pc-caption">
                    <label>Main</label>
                </li>
                <li class="pc-item">
                    <a href="{{ route('staff-dashboard') }}" class="pc-link" data-dashboard-link>
                        <span class="pc-micon">
                            <i class="fas fa-home pc-icon"></i>
                        </span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                @if ($supervision)
                    <!-- Supervisor Section -->
                    <li data-sidebar-role="supervisor" class="pc-item pc-caption">
                        <label>Supervisor</label>
                    </li>

                    <li data-sidebar-role="supervisor" class="pc-item">
                        <a href="{{ route('my-supervision-student-list') }}" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-user-graduate pc-icon"></i>
                            </span>
                            <span class="pc-mtext">My Student</span>
                        </a>
                    </li>

                    <li data-sidebar-role="supervisor" class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-file-upload pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Submission</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('my-supervision-submission-management') }}">
                                    Submission Management
                                </a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('my-supervision-submission-approval') }}">
                                    Submission Approval
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li data-sidebar-role="supervisor" class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-clipboard-list pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Nomination</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            @foreach ($nomination as $nom)
                                <li class="pc-item">
                                    <a class="pc-link"
                                        href="{{ route('my-supervision-nomination', strtolower(str_replace(' ', '-', $nom->activity_name))) }}">
                                        {{ $nom->activity_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>

                    @if ($supervisorActs->isNotEmpty() || ($sidebarCounts[route('my-supervision-correction-approval')] ?? 0) > 0)
                        <li data-sidebar-role="supervisor" class="pc-item pc-hasmenu">
                            <a href="javascript:void(0)" class="pc-link">
                                <span class="pc-micon"><i class="fas fa-pen pc-icon"></i></span>
                                <span class="pc-mtext">Evaluation</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu">
                                <!-- Submodule: Approval (role-based) -->
                                @foreach ($supervisorActs as $act)
                                    <li class="pc-item">
                                        <a class="pc-link"
                                            href="{{ route('my-supervision-evaluation-approval', $slugify($act['activity_name'])) }}">
                                            {{ $act['activity_name'] }}
                                        </a>
                                    </li>
                                @endforeach
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('my-supervision-correction-approval') }}">
                                        Correction Approval
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                @endif

                @if ($chairmanActivity->isNotEmpty())
                    <!-- Chairman Section -->
                    <li data-sidebar-role="chairman" class="pc-item pc-caption">
                        <label>Chairman</label>
                    </li>
                    <li data-sidebar-role="chairman" class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-pen pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Evaluation</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            @foreach ($chairmanActivity as $eval)
                                <li class="pc-item">
                                    <a class="pc-link"
                                        href="{{ route('chairman-evaluation', strtolower(str_replace(' ', '-', $eval->activity_name))) }}">
                                        {{ $eval->activity_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif

                @if ($examinerpanelActivity->isNotEmpty())
                    <!-- Examiner Section -->
                    <li data-sidebar-role="examiner" class="pc-item pc-caption">
                        <label>Examiner / Panel</label>
                    </li>
                    <li data-sidebar-role="examiner" class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-pen pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Evaluation</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            @foreach ($examinerpanelActivity as $eval)
                                <li class="pc-item">
                                    <a class="pc-link"
                                        href="{{ route('examiner-panel-evaluation', strtolower(str_replace(' ', '-', $eval->activity_name))) }}">
                                        {{ $eval->activity_name }}
                                    </a>
                                </li>
                            @endforeach

                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('examiner-panel-correction-approval') }}">
                                    Correction Approval
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($higherUps)
                    <!-- Administrator Section -->
                    <li data-sidebar-role="administrator" class="pc-item pc-caption">
                        <label>Administrator</label>
                    </li>

                    <li data-sidebar-role="administrator" class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-users-cog pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Supervision</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('supervision-arrangement') }}">
                                    Supervision Arrangement
                                </a>
                            </li>
                            <li class="pc-item pc-hasmenu">
                                <a class="pc-link" href="javascript:void(0)">
                                    <span class="pc-mtext">Student</span>
                                    <span class="pc-arrow">
                                        <i data-feather="chevron-right"></i>
                                    </span>
                                </a>
                                <ul class="pc-submenu">
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('student-management') }}">
                                            Student Management
                                        </a>
                                    </li>
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('semester-enrollment') }}">
                                            Semester Enrollment
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('staff-management') }}">
                                    Staff Management
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li data-sidebar-role="administrator" class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-file-upload pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Submission</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('submission-final-overview') }}">
                                    Final Overview
                                </a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('submission-management') }}">
                                    Submission Management
                                </a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('submission-approval') }}">
                                    Submission Approval
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li data-sidebar-role="administrator" class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-clipboard-list pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Nomination</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">

                            <li class="pc-item pc-hasmenu">
                                <a href="javascript:void(0)" class="pc-link">
                                    <span class="pc-mtext">Final Overview</span>
                                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="pc-submenu">
                                    @foreach ($nomination as $nom)
                                        <li class="pc-item">
                                            <a class="pc-link"
                                                href="{{ route('nomination-final-overview', strtolower(str_replace(' ', '-', $nom->activity_name))) }}">
                                                {{ $nom->activity_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>

                            @if ($iscommittee)
                                <li class="pc-item pc-hasmenu">
                                    <a href="javascript:void(0)" class="pc-link">
                                        <span class="pc-mtext">Approval</span>
                                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                    </a>
                                    <ul class="pc-submenu">
                                        @foreach ($nomination as $nom)
                                            <li class="pc-item">
                                                <a class="pc-link"
                                                    href="{{ route('nomination-approval', strtolower(str_replace(' ', '-', $nom->activity_name))) }}">
                                                    {{ $nom->activity_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                            @if ($isDD && $showDeputyDeanNomination)
                                <li class="pc-item pc-hasmenu">
                                    <a href="javascript:void(0)" class="pc-link">
                                        <span class="pc-mtext">Approval</span>
                                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                    </a>
                                    <ul class="pc-submenu">
                                        @foreach ($deputyDeanNominations as $nom)
                                            <li class="pc-item">
                                                <a class="pc-link"
                                                    href="{{ route('nomination-approval', strtolower(str_replace(' ', '-', $nom->activity_name))) }}">
                                                    {{ $nom->activity_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                            @if ($isDean && $showDeanNomination)
                                <li class="pc-item pc-hasmenu">
                                    <a href="javascript:void(0)" class="pc-link">
                                        <span class="pc-mtext">Approval</span>
                                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                    </a>
                                    <ul class="pc-submenu">
                                        @foreach ($deanNominations as $nom)
                                            <li class="pc-item">
                                                <a class="pc-link"
                                                    href="{{ route('nomination-approval', strtolower(str_replace(' ', '-', $nom->activity_name))) }}">
                                                    {{ $nom->activity_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        </ul>
                    </li>

                    <li data-sidebar-role="administrator" class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-pen pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Evaluation</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">

                            <li class="pc-item pc-hasmenu">
                                <a href="javascript:void(0)" class="pc-link">
                                    <span class="pc-mtext">Final Overview</span>
                                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="pc-submenu">
                                    @foreach ($nomination as $nom)
                                        <li class="pc-item">
                                            <a class="pc-link"
                                                href="{{ route('evaluation-final-overview', strtolower(str_replace(' ', '-', $nom->activity_name))) }}">
                                                {{ $nom->activity_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('correction-final-overview') }}">
                                            Correction
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('correction-approval') }}">
                                    Correction Approval
                                </a>
                            </li>

                            @if ($iscommittee && $committeeActs->isNotEmpty())
                                <li class="pc-item pc-hasmenu">
                                    <a href="javascript:void(0)" class="pc-link">
                                        <span class="pc-mtext">Approval</span>
                                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                    </a>
                                    <ul class="pc-submenu">
                                        @foreach ($committeeActs as $act)
                                            <li class="pc-item">
                                                <a class="pc-link"
                                                    href="{{ route('evaluation-approval', $slugify($act['activity_name'])) }}">
                                                    {{ $act['activity_name'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                            @if ($isDD && $ddActs->isNotEmpty())
                                <li class="pc-item pc-hasmenu">
                                    <a href="javascript:void(0)" class="pc-link">
                                        <span class="pc-mtext">Approval</span>
                                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                    </a>
                                    <ul class="pc-submenu">
                                        @foreach ($ddActs as $act)
                                            <li class="pc-item">
                                                <a class="pc-link"
                                                    href="{{ route('evaluation-approval', $slugify($act['activity_name'])) }}">
                                                    {{ $act['activity_name'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                            @if ($isDean && $deanActs->isNotEmpty())
                                <li class="pc-item pc-hasmenu">
                                    <a href="javascript:void(0)" class="pc-link">
                                        <span class="pc-mtext">Approval</span>
                                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                    </a>
                                    <ul class="pc-submenu">
                                        @foreach ($deanActs as $act)
                                            <li class="pc-item">
                                                <a class="pc-link"
                                                    href="{{ route('evaluation-approval', $slugify($act['activity_name'])) }}">
                                                    {{ $act['activity_name'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                        </ul>
                    </li>

                    <li data-sidebar-role="administrator" class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-project-diagram pc-icon"></i>
                            </span>
                            <span class="pc-mtext">SOP</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('procedure-setting') }}">
                                    Procedure Setting
                                </a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('activity-setting') }}">
                                    Activity Setting
                                </a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('form-setting') }}">
                                    Form Setting
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li data-sidebar-role="administrator" class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-cog pc-icon"></i>
                            </span>
                            <span class="pc-mtext">System Setting</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('faculty-setting') }}">
                                    Faculty Setting
                                </a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('department-setting') }}">
                                    Department Setting
                                </a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('programme-setting') }}">
                                    Programme Setting
                                </a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('semester-setting') }}">
                                    Semester Setting
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
<script type="application/json" id="staff-work-counts">@json($sidebarCounts)</script>
<script src="{{ asset('assets/js/staff-sidebar.js') }}?v=1.0.0" defer></script>
