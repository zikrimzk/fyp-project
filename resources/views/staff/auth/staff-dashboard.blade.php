@extends('staff.layouts.main')

@section('content')

    <style>
        /* --- Professional Dashboard Theme --- */
        :root {
            --theme-primary: #343a40;
            /* rgba(52, 58, 64, 1) */
            --theme-secondary: #6c757d;
            --theme-background: #f8f9fa;
            --theme-card-bg: #ffffff;
            --theme-text-dark: #212529;
            --theme-text-light: #495057;
            --theme-border: #dee2e6;
            --theme-accent: #0d6efd;
            --theme-success: #198754;
            --theme-danger: #dc3545;
            --theme-warning: #ffc107;
            --theme-info: #0dcaf0;
            /* A professional blue for links and highlights */
            --theme-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            --theme-shadow-hover: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: var(--theme-background);
            color: var(--theme-text-light);
        }

        /* --- Dashboard Header --- */
        .dashboard-header {
            background: var(--theme-card-bg);
            border: 1px solid var(--theme-border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--theme-shadow);
        }

        .greeting-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--theme-text-dark);
        }

        .greeting-title span {
            font-weight: 400;
        }

        .info-bar {
            font-size: 0.9rem;
            color: var(--theme-secondary);
        }

        .info-bar .fas {
            color: var(--theme-primary);
        }

        .print-btn {
            background-color: var(--theme-accent);
            border: none;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s ease;
        }

        .print-btn:hover {
            background-color: #0b5ed7;
        }

        /* --- Stat Cards --- */
        .stat-card {
            background-color: var(--theme-card-bg);
            border: 1px solid var(--theme-border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: var(--theme-shadow);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--theme-shadow-hover);
        }

        .stat-card .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background-color: var(--theme-background);
            color: var(--theme-primary);
        }

        .stat-card .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--theme-primary);
        }

        .stat-card .card-label {
            font-size: 0.9rem;
            color: var(--theme-text-light);
            margin-bottom: 0.5rem;
        }

        .stat-card .card-link {
            text-decoration: none;
            font-weight: 500;
            color: var(--theme-accent);
        }

        .stat-card.danger .card-icon {
            color: #dc3545;
        }

        .stat-card.danger .card-link {
            color: #dc3545;
        }

        /* --- Chart Cards --- */
        .chart-card {
            background-color: var(--theme-card-bg);
            border: 1px solid var(--theme-border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--theme-shadow);
        }

        .chart-card .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--theme-text-dark);
        }

        /* --- Print Styles --- */
        @media print {
            body {
                background-color: #fff;
            }

            .no-print {
                display: none !important;
            }

            .container,
            .row,
            .col {
                padding: 0 !important;
                margin: 0 !important;
            }

            .stat-card,
            .chart-card,
            .dashboard-header {
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }

            .chart-card {
                margin-top: 2rem;
            }
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

            <!-- [ Main Content ] start -->

            <div class="dashboard-header no-print">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h1 class="greeting-title mb-1">
                            <span>Welcome back,</span> {{ Auth::user()->staff_name }}!
                        </h1>
                        <p class="mb-0 text-muted">Here's your dashboard overview.</p>
                    </div>
                    <div class="mt-3 mt-md-0 d-flex flex-column align-items-md-end text-start text-md-end">
                        <div class="info-bar d-flex gap-3 mb-2">
                            <span><i class="fas fa-calendar-alt me-2"></i>{{ now()->format('l, F j, Y') }}</span>
                            <span id="live-clock"><i class="fas fa-clock me-2"></i>Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- [ Supervisor Dashboard ] start -->
            @if ($isSupervisor)
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h5 class="fw-bold text-dark mb-0">Supervisor Overview</h5>
                    <span class="badge bg-light text-dark border no-print">
                        Last Updated: {{ now()->format('M j, Y g:i A') }}
                    </span>
                </div>

                <div class="row g-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="d-flex align-items-center mb-2">
                                <div class="card-icon me-3">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <p class="card-label mb-0">Total Supervisions (Current Semester)</p>
                            </div>
                            <h3 class="card-value">{{ number_format($supervisorStudentCount) }}</h3>
                            <a href="{{ route('my-supervision-student-list') }}" class="card-link">View Details <i
                                    class="fas fa-arrow-right fa-xs ms-1"></i></a>
                        </div>
                    </div>

                    @if ($pendingSupervisorSubmission > 0)
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card danger">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="card-icon me-3">
                                        <i class="fas fa-file-upload"></i>
                                    </div>
                                    <p class="card-label mb-0">Submissions</p>
                                </div>
                                <h3 class="card-value">{{ number_format($pendingSupervisorSubmission) }}</h3>
                                <a href="{{ route('my-supervision-submission-approval') }}" class="card-link">Pending
                                    Approval <i class="fas fa-arrow-right fa-xs ms-1"></i></a>
                            </div>
                        </div>
                    @endif

                    @foreach ($pendingSupervisorNomination as $psn)
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card danger">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="card-icon me-3">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <p class="card-label mb-0">Nominations ({{ $psn->activity_name }})</p>
                                </div>
                                <h3 class="card-value">{{ number_format($psn->total_pending) }}</h3>
                                <a href="{{ route('my-supervision-nomination', strtolower(str_replace(' ', '-', $psn->activity_name))) }}"
                                    class="card-link">Pending Action <i class="fas fa-arrow-right fa-xs ms-1"></i></a>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($pendingSupervisorEvaluation as $pse)
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card danger">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="card-icon me-3">
                                        <i class="fas fa-user-edit"></i>
                                    </div>
                                    <p class="card-label mb-0">Evaluations ({{ $pse->activity_name }})</p>
                                </div>
                                <h3 class="card-value">{{ number_format($pse->total_pending) }}</h3>
                                <a href="{{ route('my-supervision-evaluation-approval', strtolower(str_replace(' ', '-', $pse->activity_name))) }}"
                                    class="card-link">Waiting Approval <i class="fas fa-arrow-right fa-xs ms-1"></i></a>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($pendingSupervisorCorrection as $psc)
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card danger">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="card-icon me-3">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <p class="card-label mb-0">Corrections ({{ $psc->activity_name }})</p>
                                </div>
                                <h3 class="card-value">{{ number_format($psc->total_pending) }}</h3>
                                <a href="{{ route('my-supervision-correction-approval') }}" class="card-link">Needs
                                    Review <i class="fas fa-arrow-right fa-xs ms-1"></i></a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif(!$isSupervisor && !$isHigherUps)
                <div class="col-12">
                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-start gap-2"
                        role="alert">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>
                            <strong>Oops!</strong> There’s nothing to display on your dashboard yet.
                            A role hasn’t been assigned to your account. Please wait until your role is assigned in the
                            system.
                        </div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif
            <!-- [ Supervisor Dashboard ] end -->

            <!-- [ Higher Ups Dashboard ] start -->
            @if ($isHigherUps)
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h5 class="fw-bold text-dark mb-0">System Overview</h5>
                    <span class="badge bg-light text-dark border no-print">
                        Last Updated: {{ now()->format('M j, Y g:i A') }}
                    </span>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="d-flex align-items-center mb-2">
                                <div class="card-icon me-3">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <p class="card-label mb-0">Total Students</p>
                            </div>
                            <h3 class="card-value">{{ number_format($totalStudents) }}</h3>
                            <a href="{{ route('student-management') }}" class="card-link">View Details <i
                                    class="fas fa-arrow-right fa-xs ms-1"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="d-flex align-items-center mb-2">
                                <div class="card-icon me-3">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <p class="card-label mb-0">Total Staff</p>
                            </div>
                            <h3 class="card-value">{{ number_format($totalStaff) }}</h3>
                            <a href="{{ route('staff-management') }}" class="card-link">View Details <i
                                    class="fas fa-arrow-right fa-xs ms-1"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="d-flex align-items-center mb-2">
                                <div class="card-icon me-3">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <p class="card-label mb-0">Total Programmes</p>
                            </div>
                            <h3 class="card-value">{{ number_format($totalProgrammes) }}</h3>
                            <a href="{{ route('programme-setting') }}" class="card-link">View Details <i
                                    class="fas fa-arrow-right fa-xs ms-1"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card danger">
                            <div class="d-flex align-items-center mb-2">
                                <div class="card-icon me-3">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                <p class="card-label mb-0">Unassigned Supervision</p>
                            </div>
                            <h3 class="card-value">{{ number_format($unassignedStudentsCount) }}</h3>
                            <a href="{{ route('supervision-arrangement') }}" class="card-link">View Details <i
                                    class="fas fa-arrow-right fa-xs ms-1"></i></a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="chart-card">
                            <h5 class="card-title mb-4">Students by Semester and Status</h5>

                            @if (!$studentBySemester->isEmpty())
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="studentBySemesterStatusChart"
                                        data-chart-data='@json($studentBySemester)'>
                                    </canvas>
                                </div>
                            @else
                                <div class="alert alert-warning">No data available to display.</div>
                            @endif

                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="chart-card">
                            <h5 class="card-title mb-4">Students by Programme & Mode (per Semester)</h5>

                            @if (!$studentByProgrammeBySemester->isEmpty())
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="studentByProgrammeBySemesterChart"
                                        data-labels='@json($semesters->pluck('sem_label'))'
                                        data-chart-data='@json($studentByProgrammeBySemester)'>
                                    </canvas>
                                </div>
                            @else
                                <div class="alert alert-warning">No data available to display.</div>
                            @endif

                        </div>
                    </div>
                </div>
            @endif
            <!-- [ Higher Ups Dashboard ] end -->

        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            // Live Clock Functionality
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
                const clockElement = document.getElementById('live-clock');
                if (clockElement) {
                    clockElement.innerHTML = `<i class="fas fa-clock me-2"></i>${timeString}`;
                }
            }
            setInterval(updateClock, 1000);
            updateClock();

            // --- Chart.js Global Configuration ---
            Chart.defaults.font.family = "'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif";
            Chart.defaults.plugins.legend.position = 'bottom';

            // Get CSS variables for consistent colors
            const getCssVariable = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
            const primaryColor = getCssVariable('--theme-primary');
            const accentColor = getCssVariable('--theme-accent');
            const secondaryColor = getCssVariable('--theme-secondary');
            const successColor = getCssVariable('--theme-success');
            const dangerColor = getCssVariable('--theme-danger');
            const warningColor = getCssVariable('--theme-warning');
            const infoColor = getCssVariable('--theme-info');

            /* --- Chart 1: Students by Semester and Status --- */
            const semesterChartCanvas = document.getElementById('studentBySemesterStatusChart');
            if (semesterChartCanvas) {
                const rawData1 = JSON.parse(semesterChartCanvas.dataset.chartData);
                const labels1 = [...new Set(rawData1.map(item => item.sem_label))];

                const statusConfig = {
                    '1': {
                        label: 'Active',
                        color: accentColor // Uses the professional blue for active status
                    },
                    '2': {
                        label: 'Inactive',
                        color: warningColor // Warning color for inactive students
                    },
                    '4': {
                        label: 'Completed',
                        color: successColor // Muted secondary color for completed status
                    }
                };

                const datasets1 = Object.keys(statusConfig).map(status => ({
                    label: statusConfig[status].label,
                    data: labels1.map(sem => {
                        const record = rawData1.find(item => item.sem_label === sem && item.ss_status ==
                            status);
                        return record ? record.total_students : 0;
                    }),
                    backgroundColor: statusConfig[status].color,
                }));

                new Chart(semesterChartCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels1,
                        datasets: datasets1
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: true,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: context => `${context.dataset.label}: ${context.parsed.y} students`
                                }
                            }
                        }
                    }
                });
            }

            /* --- Chart 2: Students by Programme & Mode --- */
            const programmeChartCanvas = document.getElementById('studentByProgrammeBySemesterChart');
            if (programmeChartCanvas) {
                const labels2 = JSON.parse(programmeChartCanvas.dataset.labels);
                const rawData2 = JSON.parse(programmeChartCanvas.dataset.chartData);

                const programmeModes2 = [...new Set(rawData2.map(item => `${item.prog_code} (${item.prog_mode})`))];

                const baseColors = [
                    primaryColor,
                    dangerColor,
                    warningColor,
                    secondaryColor,
                    accentColor,
                    '#4a5568' // A custom dark gray for additional variation
                ];

                const datasets2 = programmeModes2.map((progLabel, index) => ({
                    label: progLabel,
                    data: labels2.map(sem => {
                        const record = rawData2.find(item => `${item.prog_code} (${item.prog_mode})` ===
                            progLabel && item.sem_label === sem);
                        return record ? record.total_students : 0;
                    }),
                    backgroundColor: baseColors[index % baseColors.length],
                }));

                new Chart(programmeChartCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels2,
                        datasets: datasets2
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: context => `${context.dataset.label}: ${context.parsed.y} students`
                                }
                            }
                        }
                    }
                });
            }
        </script>
        <!-- [ Main Content ] end -->
    </div>
@endsection
