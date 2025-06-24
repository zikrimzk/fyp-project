@extends('staff.layouts.main')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            @if (auth()->user()->staff_role != 2)
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
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
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
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                            <p class="mb-0">{{ session('error') }}</p>
                        </div>
                    @endif
                </div>
                <!-- [ Alert ] end -->

                <!-- [ Main Content ] start -->


                <!-- [ Dashboard ] [FOR PSM PURPOSE] start -->
                <div class="row">
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
                </div>
                <!-- [ Dashboard ] [FOR PSM PURPOSE] end -->

                <!-- [FOR PSM PURPOSE] -->

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
                @endif

                <!-- [FOR PSM PURPOSE] -->
            @endif

            <!-- [ Main Content ] end -->
        </div>
    </div>



@endsection
