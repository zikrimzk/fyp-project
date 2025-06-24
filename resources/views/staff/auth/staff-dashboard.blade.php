@extends('staff.layouts.main')

@section('content')
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
            <div class="row">

                <!-- [ Dashboard ] start -->
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-4">Total Students by Semester</h5>

                            @if ($studentBySemester->isEmpty())
                                <div class="alert alert-warning">
                                    No student data available to display.
                                </div>
                            @else
                                <canvas id="studentBySemesterChart" style="height: 400px;"></canvas>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- [ Dashboard ] end -->

            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @if (!$studentBySemester->isEmpty())
        <script>
            const ctx = document.getElementById('studentBySemesterChart').getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(54, 162, 235, 0.8)');
            gradient.addColorStop(1, 'rgba(75, 192, 192, 0.3)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($studentBySemester->pluck('sem_label')) !!},
                    datasets: [{
                        label: 'Total Students',
                        data: {!! json_encode($studentBySemester->pluck('total_students')) !!},
                        backgroundColor: gradient,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.parsed.y + ' students';
                                }
                            }
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
@endsection
