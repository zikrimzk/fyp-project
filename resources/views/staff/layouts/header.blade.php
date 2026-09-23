@php
    $staffRoleLabel = match ((int) auth()->user()->staff_role) {
        1 => 'Committee',
        2 => 'Lecturer',
        3 => 'Deputy Dean',
        4 => 'Dean',
        default => 'Staff',
    };
@endphp
<header class="pc-header">
    <div class="header-wrapper"> <!-- [Mobile Media Block] start -->
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <!-- ======= Menu collapse Icon ===== -->
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide" aria-label="Collapse navigation">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse" aria-label="Open navigation">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item d-inline-flex">
                  <span class="staff-header-title">e-Pasca · {{ $title ?? 'Staff' }}</span>
                </li>
            </ul>
        </div>
        <!-- [Mobile Media Block end] -->
        <div class="ms-auto">
            <ul class="list-unstyled">
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0 staff-header-user" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="true" aria-expanded="false" aria-label="Open account menu">
                        <img src="{{ empty(auth()->user()->staff_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . auth()->user()->staff_photo) }}"
                            alt="" width="34" height="34">
                        <span class="staff-header-user-copy">
                            <strong>{{ auth()->user()->staff_name ?? 'Staff' }}</strong>
                            <small>{{ $staffRoleLabel }}</small>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        <a href="{{ route('staff-profile') }}" class="dropdown-item">
                            <i class="ti ti-user"></i>
                            <span>My Profile</span>
                        </a>
                        <form method="POST" action="{{ route('user-logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="ti ti-power"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>
