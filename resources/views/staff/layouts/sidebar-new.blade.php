@php
    use App\Models\Semester;
@endphp
<style>
    /* Enhanced Sidebar Styles */
    .pc-sidebar {
        border-right: 1px solid #dee2e6;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
    }

    .navbar-wrapper {
        height: 100vh;
        overflow-y: hidden;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
    }

    .navbar-wrapper::-webkit-scrollbar {
        width: 4px;
    }

    .navbar-wrapper::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
    }

    .navbar-wrapper::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 2px;
    }

    .navbar-wrapper::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 0, 0, 0.3);
    }

    .sidebar-header {
        background: var(--bs-light-secondary, #f8f9fa);
        border-bottom: 2px solid #dee2e6;
        padding: 1rem;
        flex-shrink: 0;
    }

    .sidebar-header .b-brand {
        transition: transform 0.2s ease;
    }

    .sidebar-header .b-brand:hover {
        transform: scale(1.02);
    }

    .semester-info {
        background: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 0;
        padding: 0.75rem 1rem;
        flex-shrink: 0;
    }

    .semester-info h6 {
        color: #495057;
        font-weight: 600;
        margin: 0;
        font-size: 0.875rem;
    }

    .navbar-content {
        background: var(--bs-light-secondary, #f8f9fa);
        flex: 1;
        overflow-y: unset;
        padding-bottom: 2rem;
    }

    .user-profile-section {
        padding: 1.5rem 1rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }

    .avatar-sidebar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #dee2e6;
        transition: border-color 0.2s ease;
    }

    .avatar-sidebar:hover {
        border-color: #adb5bd;
    }

    .avatar-sidebar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-info h6 {
        color: #212529;
        font-weight: 600;
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }

    .user-role {
        color: #6c757d;
        font-size: 0.8rem;
        background: rgba(0, 0, 0, 0.05);
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        display: inline-block;
    }

    .pc-navbar {
        padding: 0 0 14rem 0;
        /* Added bottom padding for proper spacing */
        margin: 0;
        list-style: none;
    }

    .pc-item.pc-caption {
        margin: 1.5rem 0 0.75rem 0;
        padding: 0 1rem;
    }

    .pc-item.pc-caption label {
        color: #6c757d;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .pc-item {
        margin-bottom: 0.25rem;
    }

    .pc-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: #495057;
        text-decoration: none;
        transition: all 0.2s ease;
        border-radius: 0;
        position: relative;
    }

    .pc-link:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #212529;
        text-decoration: none;
        border-left: 3px solid var(--bs-primary, #0d6efd);
        padding-left: calc(1rem - 3px);
    }

    .pc-link.active {
        background: rgba(13, 110, 253, 0.1);
        color: var(--bs-primary, #0d6efd);
        border-left: 3px solid var(--bs-primary, #0d6efd);
        padding-left: calc(1rem - 3px);
    }

    .pc-micon {
        width: 20px;
        height: 20px;
        margin-right: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .pc-icon {
        font-size: 16px;
        color: inherit;
    }

    .pc-mtext {
        flex: 1;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .pc-arrow {
        margin-left: 0.5rem;
        transition: transform 0.2s ease;
    }

    .pc-item.pc-hasmenu.active .pc-arrow {
        transform: rotate(90deg);
    }

    .pc-submenu {
        list-style: none;
        padding: 0;
        margin: 0;
        background: rgba(0, 0, 0, 0.03);
        border-left: 2px solid #dee2e6;
        margin-left: 1rem;
    }

    .pc-submenu .pc-item {
        margin-bottom: 0;
    }

    .pc-submenu .pc-link {
        padding: 0.6rem 1rem 0.6rem 2rem;
        font-size: 0.8rem;
        color: #6c757d;
    }

    .pc-submenu .pc-link:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #495057;
        border-left: 2px solid var(--bs-primary, #0d6efd);
        padding-left: calc(2rem - 2px);
    }

    .pc-submenu .pc-submenu {
        margin-left: 2rem;
        background: rgba(0, 0, 0, 0.05);
    }

    .pc-submenu .pc-submenu .pc-link {
        padding-left: 2.5rem;
        font-size: 0.75rem;
    }

    .pc-submenu .pc-submenu .pc-link:hover {
        padding-left: calc(2.5rem - 2px);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .user-profile-section {
            padding: 1rem;
        }

        .avatar-sidebar {
            width: 48px;
            height: 48px;
        }

        .pc-link {
            padding: 0.6rem 0.75rem;
        }
    }
</style>

<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <!-- Header Section -->
        <div class="sidebar-header">
            <a href="https://utem.edu.my" target="_blank" class="b-brand text-primary d-flex justify-content-center">
                <img src="../assets/images/logo-utem.PNG" alt="UTEM Logo" width="100" />
            </a>
        </div>

        <!-- Semester Info -->
        <div class="semester-info text-center">
            <h6>{{ Semester::where('sem_status', 1)->first()->sem_label }}</h6>
        </div>

        <div class="navbar-content">
            <!-- User Profile Section -->
            <div class="user-profile-section d-flex flex-column align-items-center">
                <div class="avatar-sidebar mb-3">
                    <img src="{{ empty(auth()->user()->staff_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . auth()->user()->staff_photo) }}"
                        alt="Profile Photo" />
                </div>
                <div class="user-info text-center">
                    <h6>{{ auth()->user()->staff_name ?? '-' }}</h6>
                    @if (auth()->user()->staff_role == 1)
                        <span class="user-role">Committee</span>
                    @elseif(auth()->user()->staff_role == 2)
                        <span class="user-role">Lecturer</span>
                    @elseif(auth()->user()->staff_role == 3)
                        <span class="user-role">Deputy Dean</span>
                    @elseif(auth()->user()->staff_role == 4)
                        <span class="user-role">Dean</span>
                    @else
                        <span class="user-role">N/A</span>
                    @endif
                </div>
            </div>

            @php
                $supervision = DB::table('supervisions')
                    ->where('staff_id', auth()->user()->id)
                    ->exists();

                $iscommittee = auth()->user()->staff_role == 1;

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

                $nomination = DB::table('procedures as a')
                    ->join('activities as b', 'a.activity_id', '=', 'b.id')
                    ->where('a.is_haveEva', 1)
                    ->select('b.id as activity_id', 'b.act_name as activity_name')
                    ->distinct()
                    ->get();

                // Examiner/Panel Activities
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

                // Chairman Activities
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

                $higherUps = DB::table('staff')
                    ->where('id', auth()->user()->id)
                    ->whereIn('staff_role', [1, 3, 4])
                    ->exists();
            @endphp

            <ul class="pc-navbar">
                <!-- Main Section -->
                <li class="pc-item pc-caption">
                    <label>Main</label>
                </li>
                <li class="pc-item">
                    <a href="{{ route('staff-dashboard') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="fas fa-tachometer-alt pc-icon"></i>
                        </span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                @if ($supervision)
                    <!-- Supervisor Section -->
                    <li class="pc-item pc-caption">
                        <label>Supervisor</label>
                    </li>

                    <li class="pc-item">
                        <a href="{{ route('my-supervision-student-list') }}" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-user-graduate pc-icon"></i>
                            </span>
                            <span class="pc-mtext">My Students</span>
                        </a>
                    </li>

                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-file-upload pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Submissions</span>
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

                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-clipboard-list pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Nominations</span>
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
                @endif

                @if ($iscommittee)
                    <!-- Committee Section -->
                    <li class="pc-item pc-caption">
                        <label>Committee</label>
                    </li>
                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-clipboard-list pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Nominations</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            @foreach ($nomination as $nom)
                                <li class="pc-item">
                                    <a class="pc-link"
                                        href="{{ route('committee-nomination', strtolower(str_replace(' ', '-', $nom->activity_name))) }}">
                                        {{ $nom->activity_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>

                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-pen pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Evaluation</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            @foreach ($nomination as $nom)
                                <li class="pc-item">
                                    <a class="pc-link"
                                        href="{{ route('committee-evaluation', strtolower(str_replace(' ', '-', $nom->activity_name))) }}">
                                        {{ $nom->activity_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif

                @if ($showDeputyDeanNomination)
                    <!-- Deputy Dean Section -->
                    <li class="pc-item pc-caption">
                        <label>Deputy Dean</label>
                    </li>
                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-clipboard-list pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Nominations</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            @foreach ($deputyDeanNominations as $nom)
                                <li class="pc-item">
                                    <a class="pc-link"
                                        href="{{ route('deputydean-nomination', strtolower(str_replace(' ', '-', $nom->activity_name))) }}">
                                        {{ $nom->activity_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif

                @if ($showDeanNomination)
                    <!-- Dean Section -->
                    <li class="pc-item pc-caption">
                        <label>Dean</label>
                    </li>
                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-clipboard-list pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Nominations</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            @foreach ($deanNominations as $nom)
                                <li class="pc-item">
                                    <a class="pc-link"
                                        href="{{ route('dean-nomination', strtolower(str_replace(' ', '-', $nom->activity_name))) }}">
                                        {{ $nom->activity_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif

                @if ($chairmanActivity->isNotEmpty())
                    <!-- Chairman Section -->
                    <li class="pc-item pc-caption">
                        <label>Chairman</label>
                    </li>
                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-pen pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Evaluations</span>
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
                    <li class="pc-item pc-caption">
                        <label>Examiner / Panel</label>
                    </li>
                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-pen pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Evaluations</span>
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
                        </ul>
                    </li>
                @endif

                @if ($higherUps)
                    <!-- Administrator Section -->
                    <li class="pc-item pc-caption">
                        <label>Administrator</label>
                    </li>

                    <li class="pc-item pc-hasmenu">
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
                                    <span class="pc-mtext">Students</span>
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

                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-file-upload pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Submissions</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
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
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('submission-suggestion') }}">
                                    Submission Suggestion
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-project-diagram pc-icon"></i>
                            </span>
                            <span class="pc-mtext">SOP Management</span>
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

                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-cog pc-icon"></i>
                            </span>
                            <span class="pc-mtext">System Settings</span>
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
