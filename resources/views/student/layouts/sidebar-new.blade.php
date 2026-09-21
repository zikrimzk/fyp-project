@php
    use App\Models\Semester;
    $student = auth()->user();
    $programme = $student->programmes;
    $currentSemester = Semester::where('sem_status', 1)->value('sem_label') ?? 'No current semester';
    $programmeName = $programme?->prog_name ?? 'Programme not assigned';
    $programmeCode = $programme?->prog_code ?? 'Programme';
    $programmeMode = $programme?->prog_mode ?? '-';
    $programmeModeLabel = $programmeMode === 'FT' ? 'Full Time' : ($programmeMode === 'PT' ? 'Part Time' : $programmeMode);
@endphp
{{-- <style>
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
</style> --}}

<style media="not all">
    /* Theme Color Variables for Consistency and New Design */
    :root {
        --color-primary: #245a91;
        --color-primary-dark: #194e83;
        --color-secondary: #64748b;
        --color-success: #198754;
        --color-danger: #dc3545;
        --color-white: #ffffff;
        --color-light-gray: #f8fafc;
        --color-medium-gray: #dce4ec;
        --color-dark-gray: #1f2d3d;
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
        --gradient-active: linear-gradient(90deg, #245a91 0%, #194e83 100%);
        --gradient-hover: linear-gradient(90deg, #eaf2fa 0%, #f8fafc 100%);
    }

    /* Enhanced Sidebar Styles */
    .pc-sidebar {
        border-right: 1px solid var(--color-medium-gray);
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        background-color: var(--color-light-gray);
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
        background: var(--color-light-gray);
        border-bottom: 2px solid var(--color-medium-gray);
        padding: 1.5rem 1rem;
        flex-shrink: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .sidebar-header .b-brand {
        transition: transform 0.2s ease;
    }

    .sidebar-header .b-brand:hover {
        transform: scale(1.02);
    }

    .semester-info {
        background: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid var(--color-medium-gray);
        margin-bottom: 0;
        padding: 0.75rem 1rem;
        flex-shrink: 0;
    }

    .semester-info h6 {
        color: var(--color-secondary);
        font-weight: 600;
        margin: 0;
        font-size: 0.875rem;
    }

    .navbar-content {
        background: var(--color-light-gray);
        flex: 1;
        overflow-y: unset;
        padding-bottom: 2rem;
    }

    .user-profile-section {
        padding: 1.5rem 1rem;
        border-bottom: 1px solid var(--color-medium-gray);
        margin-bottom: 1rem;
    }

    .avatar-sidebar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        overflow: hidden;
        transition: transform 0.2s ease;
        border: 2px solid var(--color-medium-gray);
    }

    .avatar-sidebar:hover {
        transform: scale(1.05);
        border-color: var(--color-primary);
    }

    .avatar-sidebar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-info h6 {
        color: var(--color-dark-gray);
        font-weight: 600;
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }

    .user-role {
        color: var(--color-secondary);
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
        margin: 0;
        list-style: none;
    }

    .pc-item.pc-caption {
        margin: 1.5rem 0 0.75rem 0;
        padding: 0 1rem;
    }

    .pc-item.pc-caption label {
        color: var(--color-secondary);
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
        color: var(--color-dark-gray);
        text-decoration: none;
        transition: all 0.2s ease;
        border-radius: 0;
        position: relative;
    }

    .pc-link:hover {
        background: rgba(0, 0, 0, 0.05);
        color: var(--color-primary);
        text-decoration: none;
        padding-left: calc(1rem - 3px);
    }

    .pc-link.active {
        background: var(--gradient-active);
        color: var(--color-white);
        border-left: 3px solid var(--color-primary-dark);
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
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
        border-left: 2px solid var(--color-medium-gray);
        margin-left: 1rem;
    }

    .pc-submenu .pc-item {
        margin-bottom: 0;
    }

    .pc-submenu .pc-link {
        padding: 0.6rem 1rem 0.6rem 2rem;
        font-size: 0.8rem;
        color: var(--color-secondary);
    }

    .pc-submenu .pc-link:hover {
        background: rgba(0, 0, 0, 0.05);
        color: var(--color-dark-gray);
        border-left: 2px solid var(--color-primary);
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

<nav class="pc-sidebar student-work-sidebar" aria-label="Student navigation">
    <div class="navbar-wrapper">
        <div class="student-sidebar-heading">
            <a href="{{ route('student-home') }}" class="student-sidebar-brand">
                <img src="{{ asset('assets/images/logo-utem.PNG') }}" alt="UTeM" width="54">
                <span>
                    <strong>e-Pasca</strong>
                    <small>{{ $currentSemester }}</small>
                </span>
            </a>

            <div class="student-sidebar-person">
                <img src="{{ empty($student->student_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . $student->student_directory . '/photo/' . $student->student_photo) }}"
                    alt="" width="34" height="34">
                <span>
                    <strong>{{ $student->student_name ?? 'Student' }}</strong>
                    <small>{{ $programmeCode }} · {{ $programmeModeLabel }}</small>
                </span>
            </div>

            <div class="student-programme-summary">
                <span>Programme</span>
                <strong>{{ $programmeName }}</strong>
            </div>
        </div>

        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label>Main</label>
                </li>

                <li class="pc-item">
                    <a href="{{ route('student-home') }}" class="pc-link" @if(request()->routeIs('student-home')) aria-current="page" @endif>
                        <span class="pc-micon">
                            <i class="fas fa-home pc-icon"></i>
                        </span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                <li class="pc-item pc-caption">
                    <label>Academic</label>
                </li>

                <li class="pc-item">
                    <a href="{{ route('student-programme-overview') }}" class="pc-link"
                        @if(request()->routeIs('student-programme-overview', 'student-document-submission')) aria-current="page" @endif>
                        <span class="pc-micon">
                            <i class="fas fa-book-open pc-icon"></i>
                        </span>
                        <span class="pc-mtext">Programme Overview</span>
                    </a>
                </li>

                <li class="pc-item">
                    <a href="{{ route('student-journal-publication') }}" class="pc-link"
                        @if(request()->routeIs('student-journal-publication')) aria-current="page" @endif>
                        <span class="pc-micon">
                            <i class="fas fa-bookmark pc-icon"></i>
                        </span>
                        <span class="pc-mtext">Journal Publication</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
