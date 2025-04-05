<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="p-2 bg-light-secondary text-center">
            <a href="../dashboard/index.html" class="b-brand text-primary">
                <img src="../assets/images/logo-utem.PNG" alt="logo" width="80" />
            </a>
        </div>
        <div class="navbar-content">
            <div class="card pc-user-card">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <div class="flex-shrink-0 mb-3">
                            <img src="{{ empty(auth()->user()->staff_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . auth()->user()->staff_photo) }}"
                            alt="Profile Photo" width="80" height="80" class="rounded-circle" />
                        </div>
                        <div class="flex-grow-1 text-center">
                            <h5 class="mb-0">{{ auth()->user()->staff_name }}</h5>
                            <small>Committee</small>
                        </div>
                    </div>
                </div>
            </div>

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
                        <li class="pc-item"><a class="pc-link" href="{{ route('faculty-setting') }}">Faculty Setting</a>
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
