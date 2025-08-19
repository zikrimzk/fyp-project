@extends('staff.layouts.main')

@section('content')

    <style>
        .greeting-card {
            background: #ffffff;
            border: 1px solid #e0e6ed;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            transition: box-shadow 0.3s ease;
        }

        .greeting-card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .weather-icon {
            font-size: 2.5rem;
            transition: transform 0.3s ease;
            cursor: default;
        }

        .weather-icon:hover {
            transform: scale(1.1);
        }

        .greeting-title {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.3rem;
            line-height: 1.3;
        }

        .welcome-text {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 0;
            font-weight: 400;
        }

        .quote-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-left: 3px solid #3b82f6;
            padding: 1.2rem 1.4rem;
            border-radius: 0 8px 8px 0;
            margin-top: 1.5rem;
            position: relative;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .quote-section:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .quote-section::before {
            content: '"';
            position: absolute;
            top: 0.5rem;
            left: 1rem;
            font-size: 2rem;
            color: #3b82f6;
            opacity: 0.3;
            font-family: Georgia, serif;
        }

        .quote-text {
            font-style: italic;
            color: #374151;
            margin-bottom: 0.7rem;
            font-size: 1rem;
            line-height: 1.5;
            padding-left: 1.5rem;
        }

        .quote-author {
            font-size: 0.8rem;
            color: #6b7280;
            text-align: right;
            margin-bottom: 0;
            font-weight: 500;
        }

        .info-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.4rem 1.2rem;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .info-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .time-display {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            text-align: center;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .weather-info {
            text-align: center;
        }

        .weather-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: #475569;
        }

        .weather-detail:last-child {
            margin-bottom: 0;
        }

        .weather-detail .label {
            font-weight: 500;
            color: #334155;
        }

        .weather-detail .value {
            font-weight: 600;
            color: #3b82f6;
        }

        .main-content-section {
            padding-right: 1rem;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .greeting-title {
                font-size: 1.3rem;
            }

            .weather-icon {
                font-size: 2.2rem;
            }

            .quote-section {
                margin-top: 1.2rem;
                padding: 1rem 1.2rem;
            }

            .info-box {
                margin-top: 1rem;
                padding: 1.2rem 1rem;
            }

            .main-content-section {
                padding-right: 0;
            }
        }

        @media (max-width: 576px) {
            .greeting-title {
                font-size: 1.2rem;
            }

            .weather-icon {
                font-size: 2rem;
            }

            .quote-text {
                font-size: 0.88rem;
            }

            .time-display {
                font-size: 0.95rem;
            }
        }

        /* Subtle animations */
        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .action-card {
            border: 1px solid #f5f5f5;
            transition: all 0.25s ease;
            border-radius: 10px;
            background-color: #fff;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #e0e0e0;
        }

        .icon-wrapper {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .hover-indicator {
            color: #6c757d;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .action-card:hover .hover-indicator {
            opacity: 1;
        }

        /* Soft color variants */
        .bg-warning-soft {
            background-color: rgba(255, 193, 7, 0.1)
        }

        .text-warning-dark {
            color: #ffc107
        }

        .bg-info-soft {
            background-color: rgba(23, 162, 184, 0.1)
        }

        .text-info-dark {
            color: #17a2b8
        }

        .bg-purple-soft {
            background-color: rgba(111, 66, 193, 0.1)
        }

        .text-purple-dark {
            color: #6f42c1
        }

        .bg-danger-soft {
            background-color: rgba(220, 53, 69, 0.1)
        }

        .text-danger-dark {
            color: #dc3545
        }
    </style>

    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item" aria-current="page">Dashboard</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Dashboard</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- [ Alert ] start -->
            <div>
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="alert-heading">
                                <i class="fas fa-check-circle"></i>
                                Success
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <p class="mb-0">{{ session('success') }}</p>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="alert-heading">
                                <i class="fas fa-info-circle"></i>
                                Error
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <p class="mb-0">{{ session('error') }}</p>
                    </div>
                @endif
            </div>
            <!-- [ Alert ] end -->

            <!-- [ Main Content ] start -->

            <!-- Dashboard Greeting start -->
            <div class="row">
                <div class="col-12">
                    <div class="card greeting-card">
                        <div class="card-body p-4">
                            <div class="row align-items-stretch">
                                <div class="col-lg-8 main-content-section">
                                    <div class="d-flex align-items-center mb-2">
                                        <div id="weather-icon" class="weather-icon me-3">☀️</div>
                                        <div class="flex-grow-1">
                                            <h2 class="greeting-title mb-1" id="time-greeting">
                                                Good Morning, <span id="user-name">Dr. Sarah Johnson</span> !
                                            </h2>
                                            <p class="welcome-text">Welcome back to e-PostGrad System</p>
                                        </div>
                                    </div>

                                    <div class="quote-section fade-in">
                                        <p class="quote-text mb-2" id="motivational-quote">
                                            Education is the most powerful weapon which you can use to change the world.
                                        </p>
                                        <p class="quote-author" id="quote-author">— Nelson Mandela</p>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="info-box">
                                        <div class="time-display" id="live-clock">
                                            <i class="fas fa-clock me-2 text-primary"></i>Loading time...
                                        </div>

                                        <div class="weather-info">
                                            <div class="weather-detail">
                                                <span class="label">
                                                    <i class="fas fa-thermometer-half me-1 text-danger"></i>Temperature
                                                </span>
                                                <span class="value" id="temperature">24°C</span>
                                            </div>
                                            <div class="weather-detail">
                                                <span class="label">
                                                    <i class="fas fa-cloud-sun me-1 text-warning"></i>Condition
                                                </span>
                                                <span class="value" id="weather-condition">Clear & Pleasant</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Dashboard Greeting end -->

            <!-- [ Supervisor Dashboard ] start -->
            @if ($isSupervisor)
                <div class="supervisor-dashboard">
                    @if (
                        $pendingSupervisorSubmission > 0 ||
                            ($pendingSupervisorNomination && $pendingSupervisorNomination->isNotEmpty()) ||
                            ($pendingSupervisorEvaluation && $pendingSupervisorEvaluation->isNotEmpty()) ||
                            ($pendingSupervisorCorrection && $pendingSupervisorCorrection->isNotEmpty()))
                        <!-- Dashboard Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Supervisor Actions</h5>
                                <p class="small text-muted mb-0">Pending items requiring your attention</p>
                            </div>
                            <div>
                                <span class="badge bg-light text-dark border"> Last Updated :
                                    {{ now()->format('M j, Y g:i A') }}</span>
                            </div>
                        </div>

                        <!-- Action Card  -->
                        <div class="row g-4">
                            <!-- PENDING SUBMISSION APPROVAL -->
                            @if ($pendingSupervisorSubmission > 0)
                                <div class="col-md-6 col-lg-3">
                                    <a href="{{ route('my-supervision-submission-approval') }}"
                                        class="card action-card text-decoration-none">
                                        <div class="card-body p-3 text-center d-flex flex-column">
                                            <div class="icon-wrapper bg-light-danger rounded-circle mx-auto mb-3">
                                                <i class="fas fa-file-upload text-danger"></i>
                                            </div>
                                            <h6 class="mb-0 text-dark fw-semibold">Submissions</h6>
                                            <small class="mb-1 text-muted">All Activity</small>
                                            <h3 class="text-dark">{{ number_format($pendingSupervisorSubmission) }}</h3>
                                            <small class="text-muted">Pending Approval</small>
                                            <div class="hover-indicator mt-2">
                                                <span class="small">View details</span>
                                                <i class="fas fa-chevron-right small ms-1"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endif

                            <!-- PENDING NOMINATION -->
                            @foreach ($pendingSupervisorNomination as $psn)
                                <div class="col-md-6 col-lg-3">
                                    <a href="{{ route('my-supervision-nomination', strtolower(str_replace(' ', '-', $psn->activity_name))) }}"
                                        class="card action-card text-decoration-none">
                                        <div class="card-body p-3 text-center d-flex flex-column">
                                            <div class="icon-wrapper bg-light-danger rounded-circle mx-auto mb-3">
                                                <i class="fas fa-user-plus text-danger"></i>
                                            </div>
                                            <h6 class="mb-0 text-dark fw-semibold">Nominations</h6>
                                            <small class="mb-1 text-muted">{{ $psn->activity_name }}</small>
                                            <h3 class="text-dark">{{ number_format($psn->total_pending) }}</h3>
                                            <small class="text-muted">Pending Action</small>
                                            <div class="hover-indicator mt-2">
                                                <span class="small">View details</span>
                                                <i class="fas fa-chevron-right small ms-1"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach

                            <!-- PENDING EVALUATION APPROVAL -->
                            @foreach ($pendingSupervisorEvaluation as $pse)
                                <div class="col-md-6 col-lg-3">
                                    <a href="{{ route('my-supervision-evaluation-approval', strtolower(str_replace(' ', '-', $pse->activity_name))) }}"
                                        class="card action-card text-decoration-none">
                                        <div class="card-body p-3 text-center d-flex flex-column">
                                            <div class="icon-wrapper bg-light-danger rounded-circle mx-auto mb-3">
                                                <i class="fas fa-user-edit text-danger"></i>
                                            </div>
                                            <h6 class="mb-0 text-dark fw-semibold">Evaluations</h6>
                                            <small class="mb-1 text-muted">{{ $pse->activity_name }}</small>
                                            <h3 class="text-dark">{{ number_format($pse->total_pending) }}</h3>
                                            <small class="text-muted">Waiting Approval</small>
                                            <div class="hover-indicator mt-2">
                                                <span class="small">View details</span>
                                                <i class="fas fa-chevron-right small ms-1"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach

                            <!-- PENDING CORRECTION APPROVAL -->
                            @foreach ($pendingSupervisorCorrection as $psc)
                                <div class="col-md-6 col-lg-3">
                                    <a href="{{ route('my-supervision-correction-approval') }}"
                                        class="card action-card text-decoration-none">
                                        <div class="card-body p-3 text-center d-flex flex-column">
                                            <div class="icon-wrapper bg-light-danger rounded-circle mx-auto mb-3">
                                                <i class="fas fa-edit text-danger"></i>
                                            </div>
                                            <h6 class="mb-0 text-dark fw-semibold">Corrections</h6>
                                            <small class="mb-1 text-muted">{{ $psc->activity_name }}</small>
                                            <h3 class="text-dark">{{ number_format($psc->total_pending) }}</h3>
                                            <small class="text-muted">Needs Review</small>
                                            <div class="hover-indicator mt-2">
                                                <span class="small">View details</span>
                                                <i class="fas fa-chevron-right small ms-1"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
            <!-- [ Supervisor Dashboard ] end -->

            <!-- [ Dashboard ] [FOR PSM PURPOSE] start -->
            {{-- <div class="row">
                    <!-- Total Students -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar bg-light-primary">
                                            <i class="fas fa-user-graduate f-24"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-1">Total Students</p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h4 class="mb-0">{{ number_format($totalStudents) }}</h4>
                                            <a href="{{ route('student-management') }}">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Staff -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar bg-light-warning">
                                            <i class="fas fa-user-tie f-24"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-1">Total Staff</p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h4 class="mb-0">{{ number_format($totalStaff) }}</h4>
                                            <a href="{{ route('staff-management') }}">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Programmes -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar bg-light-success">
                                            <i class="fas fa-book-open f-24"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-1">Total Programmes</p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h4 class="mb-0">{{ number_format($totalProgrammes) }}</h4>
                                            <a href="{{ route('programme-setting') }}">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar bg-light-danger">
                                            <i class="ti ti-user-off f-24"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-1">Unassigned Supervision</p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h4 class="mb-0">{{ number_format($unassignedStudentsCount) }}</h4>
                                            <a href="{{ route('supervision-arrangement') }}" class="link-danger">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-4">Total Students by Semester and Status</h5>

                                @if ($studentBySemester->isEmpty())
                                    <div class="alert alert-warning">
                                        No data available to display.
                                    </div>
                                @else
                                    <canvas id="studentBySemesterStatusChart" style="height: 450px;"></canvas>
                                @endif

                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-4">Total Students by Programme & Mode (by Semester)</h5>

                                @if ($studentByProgrammeBySemester->isEmpty())
                                    <div class="alert alert-warning">
                                        No data available to display.
                                    </div>
                                @else
                                    <canvas id="studentByProgrammeBySemesterChart" style="height: 450px;"></canvas>
                                @endif
                            </div>
                        </div>
                    </div>
                </div> --}}
            <!-- [ Dashboard ] [FOR PSM PURPOSE] end -->

            <!-- [FOR PSM PURPOSE] -->

            {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

                @if (!$studentBySemester->isEmpty())
                    <script>
                        const ctx = document.getElementById('studentBySemesterStatusChart').getContext('2d');

                        const rawData3 = {!! json_encode($studentBySemester) !!};

                        const labels3 = [...new Set(rawData3.map(item => item.sem_label))];

                        const statusLabels = {
                            1: 'Active',
                            2: 'Inactive',
                            3: 'Barred/Withdrawn',
                            4: 'Completed'
                        };

                        const statusColors = {
                            1: 'rgba(54, 162, 235, 0.8)',
                            2: 'rgba(255, 205, 86, 0.8)',
                            3: 'rgba(255, 99, 132, 0.8)',
                            4: 'rgba(75, 192, 192, 0.8)'
                        };

                        const datasets3 = Object.keys(statusLabels).map(status => {
                            return {
                                label: statusLabels[status],
                                data: labels3.map(sem => {
                                    const record = rawData3.find(item =>
                                        item.sem_label === sem && item.ss_status == status
                                    );
                                    return record ? record.total_students : 0;
                                }),
                                backgroundColor: statusColors[status],
                                borderColor: statusColors[status],
                                borderWidth: 1
                            }
                        });

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels3,
                                datasets: datasets3
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return `${context.dataset.label}: ${context.parsed.y} students`;
                                            }
                                        }
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                },
                                scales: {
                                    x: {
                                        stacked: true
                                    },
                                    y: {
                                        stacked: true,
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                @endif

                @if (!$studentByProgrammeBySemester->isEmpty())
                    <script>
                        const ctx2 = document.getElementById('studentByProgrammeBySemesterChart').getContext('2d');

                        const labels2 = {!! json_encode($semesters->pluck('sem_label')) !!};

                        const rawData2 = {!! json_encode($studentByProgrammeBySemester) !!};

                        const programmeModes2 = [...new Set(rawData2.map(item => item.prog_code + ' (' + item.prog_mode + ')'))];

                        const datasets2 = programmeModes2.map((progLabel, index) => {
                            // Generate consistent blue shades
                            const hue = 210; // blue
                            const saturation = 60;
                            const lightness = 40 + (index * 8); // slightly lighter for each
                            const color = `hsl(${hue}, ${saturation}%, ${lightness}%)`;

                            return {
                                label: progLabel,
                                data: labels2.map(sem => {
                                    const record = rawData2.find(item =>
                                        (item.prog_code + ' (' + item.prog_mode + ')') === progLabel &&
                                        item.sem_label === sem
                                    );
                                    return record ? record.total_students : 0;
                                }),
                                backgroundColor: color,
                                borderColor: color,
                                borderWidth: 1
                            };
                        });

                        new Chart(ctx2, {
                            type: 'bar',
                            data: {
                                labels: labels2,
                                datasets: datasets2
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return `${context.dataset.label}: ${context.parsed.y} students`;
                                            }
                                        }
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                @endif --}}

            <!-- [FOR PSM PURPOSE] -->
            <!-- [ Main Content ] end -->

            <script>
                $(document).ready(function() {
                    // Professional academic quotes for university lecturers
                    const academicQuotes = [{
                            quote: "Education is the most powerful weapon which you can use to change the world.",
                            author: "Nelson Mandela"
                        },
                        {
                            quote: "The mediocre teacher tells. The good teacher explains. The superior teacher demonstrates. The great teacher inspires.",
                            author: "William Arthur Ward"
                        },
                        {
                            quote: "Teaching is not about answering questions but about raising questions.",
                            author: "Yawar Baig"
                        },
                        {
                            quote: "A good teacher can inspire hope, ignite the imagination, and instill a love of learning.",
                            author: "Brad Henry"
                        },
                        {
                            quote: "The art of teaching is the art of assisting discovery.",
                            author: "Mark Van Doren"
                        },
                        {
                            quote: "Education is not preparation for life; education is life itself.",
                            author: "John Dewey"
                        },
                        {
                            quote: "Knowledge is power. Information is liberating. Education is the premise of progress.",
                            author: "Kofi Annan"
                        },
                        {
                            quote: "The best teachers show you where to look but don't tell you what to see.",
                            author: "Alexandra K. Trenfor"
                        }
                    ];

                    // Weather conditions based on time of day
                    const weatherConditions = {
                        morning: {
                            icon: '☀️',
                            temp: '22°C',
                            condition: 'Clear & Pleasant'
                        },
                        afternoon: {
                            icon: '⛅',
                            temp: '28°C',
                            condition: 'Partly Cloudy'
                        },
                        evening: {
                            icon: '🌅',
                            temp: '25°C',
                            condition: 'Calm & Clear'
                        },
                        night: {
                            icon: '🌙',
                            temp: '20°C',
                            condition: 'Cool & Quiet'
                        }
                    };

                    // Set user name (replace with actual Laravel blade syntax)
                    const userName = "{{ auth()->user()->staff_name }}"; // Replace with {{ auth()->user()->staff_name }}
                    $('#user-name').text(userName);

                    // Update greeting and weather based on time of day
                    function updateGreeting() {
                        const hour = new Date().getHours();
                        let timeOfDay, greeting;

                        if (hour >= 5 && hour < 12) {
                            timeOfDay = 'morning';
                            greeting = 'Good Morning';
                        } else if (hour >= 12 && hour < 17) {
                            timeOfDay = 'afternoon';
                            greeting = 'Good Afternoon';
                        } else if (hour >= 17 && hour < 21) {
                            timeOfDay = 'evening';
                            greeting = 'Good Evening';
                        } else {
                            timeOfDay = 'night';
                            greeting = 'Good Evening';
                        }

                        const weather = weatherConditions[timeOfDay];

                        // Update greeting and weather
                        $('#time-greeting').html(`${greeting}, <span id="user-name">${userName}</span> !`);
                        $('#weather-icon').text(weather.icon);
                        $('#temperature').text(weather.temp);
                        $('#weather-condition').text(weather.condition);
                    }

                    // Update live clock
                    function updateClock() {
                        const now = new Date();
                        const options = {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        };

                        $('#live-clock').text(now.toLocaleDateString('en-US', options));
                    }

                    // Update motivational quote
                    function updateMotivationalQuote() {
                        const randomIndex = Math.floor(Math.random() * academicQuotes.length);
                        const selectedQuote = academicQuotes[randomIndex];

                        $('#motivational-quote').text(selectedQuote.quote);
                        $('#quote-author').text(`- ${selectedQuote.author}`);
                    }

                    // Initialize functions
                    updateGreeting();
                    updateClock();
                    updateMotivationalQuote();

                    // Update clock every second
                    setInterval(updateClock, 1000);

                    // Update quote every 5 minutes
                    setInterval(updateMotivationalQuote, 300000);

                    // Update greeting every hour
                    setInterval(updateGreeting, 3600000);
                });
            </script>
        </div>
    </div>



@endsection
