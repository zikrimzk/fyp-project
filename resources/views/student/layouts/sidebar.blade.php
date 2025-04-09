@php
    use App\Models\Semester;
@endphp
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="p-2 bg-light-secondary text-center d-flex justify-content-center align-items-center border border-bottom border-2">
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
                    <img src="{{ empty(auth()->user()->student_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . auth()->user()->student_directory . '/photo/' . auth()->user()->student_photo) }}"
                        alt="Profile Photo" />
                </div>
                <div class="flex-grow-1 text-center">
                    <h6 class="mb-0">{{ auth()->user()->student_name }}</h6>
                    <small class="d-block">
                        {{ auth()->user()->programmes->prog_code }}
                        ({{ auth()->user()->programmes->prog_mode }})
                    </small>
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
