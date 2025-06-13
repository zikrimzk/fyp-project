@php
    use App\Models\Semester;
@endphp
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div
            class="p-2 bg-light-secondary text-center d-flex justify-content-center align-items-center border border-bottom border-2">
            <a href="../dashboard/index.html" class="b-brand text-primary">
                <img src="../assets/images/logo-utem.PNG" alt="logo" width="100" />
            </a>
        </div>
        <div class="navbar-content bg-light-secondary">

            <div class="text-center border-bottom border-2 mb-3">
                <h6 class="p-2">{{ Semester::where('sem_status', 1)->first()->sem_label }}</h6>
            </div>

            <div class="d-flex flex-column align-items-center justify-content-center m-2 mb-3">
                <div class="flex-shrink-0 mb-3 avatar-sidebar">
                    <img src="{{ empty(auth()->user()->staff_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . auth()->user()->staff_photo) }}"
                        alt="Profile Photo" />
                </div>
                <div class="flex-grow-1 text-center">
                    <h6 class="mb-0">{{ auth()->user()->staff_name ?? '-' }}</h6>
                    @if (auth()->user()->staff_role == 1)
                        {{-- Committee --}}
                        <small>Committee</small>
                    @elseif(auth()->user()->staff_role == 2)
                        {{-- Lecturer --}}
                        <small>Lecturer</small>
                    @elseif(auth()->user()->staff_role == 3)
                        {{-- DD --}}
                        <small>Deputy Dean</small>
                    @elseif(auth()->user()->staff_role == 4)
                        {{-- Dean --}}
                        <small>Dean</small>
                    @else
                        {{-- N/A --}}
                        <small>N/A</small>
                    @endif
                </div>
            </div>

            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label>Main</label>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="{{ route('staff-dashboard') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="fas fa-home pc-icon"></i>
                        </span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                @php
                    $supervision = DB::table('supervisions')
                        ->where('staff_id', auth()->user()->id)
                        ->exists();

                    $higherUps = DB::table('staff')
                        ->where('id', auth()->user()->id)
                        ->whereIn('staff_role', [1, 3, 4])
                        ->exists();

                    $nomination = DB::table('procedures as a')
                        ->join('activities as b', 'a.activity_id', '=', 'b.id')
                        ->where('a.is_haveEva', 1)
                        ->select('b.id as activity_id', 'b.act_name as activity_name')
                        ->distinct()
                        ->get();

                @endphp

                @if ($supervision)
                    <li class="pc-item pc-caption">
                        <label>Supervisor</label>
                    </li>

                    <li class="pc-item pc-hasmenu">
                        <a href="{{ route('my-supervision-student-list') }}" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-user-graduate pc-icon"></i>
                            </span>
                            <span class="pc-mtext">My Student</span>
                        </a>
                    </li>

                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-upload pc-icon"></i>
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

                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-user-friends pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Nomination</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            @foreach ($nomination as $nom)
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('my-supervision-nomination', Crypt::encrypt($nom->activity_id)) }}">
                                        {{ $nom->activity_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif

                @if ($higherUps)
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
                                    <span class="pc-mtext">Student</span>
                                    <span class="pc-arrow">
                                        <i data-feather="chevron-right"></i>
                                    </span>
                                </a>
                                <ul class="pc-submenu">
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('student-management') }}">Student
                                            Management</a>
                                    </li>
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('semester-enrollment') }}">Semester
                                            Enrollment</a>
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
                                <i class="fas fa-upload pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Submission</span>
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
                                <i class="fas fa-bezier-curve pc-icon"></i>
                            </span>
                            <span class="pc-mtext">SOP</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            <li class="pc-item"><a class="pc-link" href="{{ route('procedure-setting') }}">Procedure
                                    Setting</a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('activity-setting') }}">Activity Setting</a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('form-setting') }}">Form Setting</a>
                            </li>
                        </ul>
                    </li>

                    <li class="pc-item pc-hasmenu">
                        <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-cogs pc-icon"></i>
                            </span>
                            <span class="pc-mtext">Setting</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            <li class="pc-item"><a class="pc-link" href="{{ route('faculty-setting') }}">Faculty
                                    Setting</a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('department-setting') }}">Department Setting</a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('programme-setting') }}">Programme Setting</a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="{{ route('semester-setting') }}">Semester Setting</a>
                            </li>
                        </ul>
                    </li>
                @endif

            </ul>

            <br /> <br /> <br /> <br />
            <br /> <br /> <br /> <br />

        </div>
    </div>
</nav>
