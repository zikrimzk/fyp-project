<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header justify-content-center">
            {{-- <a href="../dashboard/index.html" class="b-brand text-primary">
                <img src="../assets/images/logo-dark.svg" class="img-fluid logo-lg" alt="logo" />
                <span class="badge bg-light-success rounded-pill ms-2 theme-version">v9.4.1</span>
            </a> --}}
            <div class="text-muted text-center fw-semibold">e-PostGrad System (e-PGS)</div>
        </div>
        <div class="navbar-content">

            <div class="card pc-user-card">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <div class="flex-shrink-0 mb-3">
                            <i class="fas fa-user-circle f-50"></i>
                        </div>
                        <div class="flex-grow-1 text-center">
                            <h5 class="mb-0">Dr. Zahriah</h5>
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
                    <a href="#!" class="pc-link">
                        <span class="pc-micon">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-status-up"></use>
                            </svg>
                        </span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                <li class="pc-item pc-caption">
                    <label>System Setting</label>
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
