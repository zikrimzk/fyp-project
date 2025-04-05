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
                            <i class="fas fa-user-circle f-50"></i>
                        </div>
                        <div class="flex-grow-1 text-center">
                            <h5 class="mb-0">{{ auth()->user()->student_name }}</h5>
                            <small>PITA (Full Time)</small>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label>Main</label>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="{{ route('student-home') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="fas fa-home pc-icon"></i>
                        </span>
                        <span class="pc-mtext">Home</span>
                    </a>
                </li>

                <li class="pc-item pc-caption">
                    <label>Course</label>
                </li>

                <li class="pc-item pc-hasmenu">
                    <a href="javascript:void(0)" class="pc-link">
                        <span class="pc-micon">
                            <i class="fas fa-book-open pc-icon"></i>
                        </span>
                        <span class="pc-mtext">[ Course Registered ]</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item">
                            <a class="pc-link" href="">
                                Action 1
                            </a>
                        </li>
                        <li class="pc-item">
                            <a class="pc-link" href="">
                                Action 2
                            </a>
                        </li>
                        <li class="pc-item">
                            <a class="pc-link" href="">
                                Action 3
                            </a>
                        </li>
                       

                    </ul>
                </li>

            </ul>
        </div>
    </div>
</nav>
