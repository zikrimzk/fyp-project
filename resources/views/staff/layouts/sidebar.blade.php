@php
    use App\Models\Semester;
@endphp
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="p-2 bg-light-secondary text-center">
            <a href="../dashboard/index.html" class="b-brand text-primary">
                <img src="../assets/images/logo-utem.PNG" alt="logo" width="100" />
            </a>
            <hr>
            <h6>{{ Semester::where('sem_status', 1)->first()->sem_label }}</h6>
            <hr>

            <div class="d-flex flex-column align-items-center justify-content-center m-2">
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
                        {{-- TDP --}}
                        <small>Timbalan Dekan Pendidikan</small>
                    @elseif(auth()->user()->staff_role == 4)
                        {{-- Dekan --}}
                        <small>Dekan</small>
                    @else
                        {{-- N/A --}}
                        <small>N/A</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label>Main</label>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="{{ route('staff-dashboard') }}" class="pc-link">
                        <span class="pc-micon">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-status-up"></use>
                            </svg>
                        </span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                <li class="pc-item pc-caption">
                    <label>Committee</label>
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
                        <li class="pc-item">
                            <a class="pc-link" href="{{ route('student-management') }}">
                                Student Management
                            </a>
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
                    </ul>
                </li>


                <li class="pc-item pc-caption">
                    <label>Setting</label>
                </li>

                <li class="pc-item pc-hasmenu">
                    <a href="javascript:void(0)" class="pc-link">
                        <span class="pc-micon">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-document"></use>
                            </svg>
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

            </ul>
        </div>
    </div>
</nav>
