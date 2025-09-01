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
        transition: transform 0.2s ease;
    }

    .avatar-sidebar:hover {
        transform: scale(1.02);
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
        font-size: 0.6rem;
        background: rgba(0, 0, 0, 0.05);
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        display: inline-block;
        margin-bottom: 0.25rem;
    }

    .programme-info {
        font-size: 0.6rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .mode-badge {
        font-size: 0.6rem;
        padding: 0.25rem 0.5rem;
        border-radius: 8px;
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
                    <img src="{{ empty(auth()->user()->student_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . auth()->user()->student_directory . '/photo/' . auth()->user()->student_photo) }}"
                        alt="Profile Photo" />
                </div>
                <div class="user-info text-center">
                    <h6 class="text-uppercase">{{ auth()->user()->student_name ?? '-' }}</h6>
                    <div class="programme-info text-uppercase">{{ auth()->user()->programmes->prog_name }}</div>
                    @if (auth()->user()->programmes->prog_mode == 'FT')
                        <span class="badge bg-dark text-uppercase" style="font-size: 0.6rem;">Full Time</span>
                    @else
                        <span class="badge bg-secondary text-uppercase" style="font-size: 0.6rem;">Part Time</span>
                    @endif
                </div>
            </div>

            <ul class="pc-navbar">
                <!-- Main Section -->
                <li class="pc-item pc-caption">
                    <label>Main</label>
                </li>

                <li class="pc-item">
                    <a href="{{ route('student-home') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="fas fa-home pc-icon"></i>
                        </span>
                        <span class="pc-mtext">Home</span>
                    </a>
                </li>

                <!-- Course Section -->
                <li class="pc-item pc-caption">
                    <label>
                        {{ auth()->user()->programmes->prog_code }}
                        ({{ auth()->user()->programmes->prog_mode }})
                    </label>
                </li>

                <li class="pc-item">
                    <a href="{{ route('student-programme-overview') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="fas fa-book-open pc-icon"></i>
                        </span>
                        <span class="pc-mtext">
                            Programme Overview
                        </span>
                    </a>
                </li>

                <li class="pc-item">
                    <a href="{{ route('student-journal-publication') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="fas fa-bookmark pc-icon"></i>
                        </span>
                        <span class="pc-mtext">
                            Journal Publication
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
