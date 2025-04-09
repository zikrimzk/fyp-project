@extends('student.layouts.main')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">{{ auth()->user()->programmes->prog_code }}</li>
                                <li class="breadcrumb-item" aria-current="page">Programme Overview</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">Programme Overview</h2>
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

                <!-- [ Programme Overview ] start -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-4">

                            @forelse($acts as $act)
                                <div class="card mb-4 border-2 shadow-sm">
                                    <div class="card-body">

                                        {{-- Header --}}
                                        <div
                                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                                            <div>
                                                <h5 class="mb-1">{{ $act->act_name }}</h5>
                                                @if ($act->init_status == 1)
                                                    <span class="badge bg-success badge-flash">Open for Submission</span>
                                                @elseif($act->init_status == 2)
                                                    <span class="badge bg-danger">Locked</span>
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
                                            </div>

                                            <a href="{{ route('student-activity-submission-list', Crypt::encrypt($act->activity_id)) }}"
                                                class="btn btn-sm btn-outline-primary mt-2 mt-md-0 d-flex align-items-center gap-1">
                                                <span>View Activity</span>
                                                <i class="ti ti-arrow-right"></i>
                                            </a>
                                        </div>

                                        {{-- Flowchart --}}
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Flowchart</h6>
                                            @if ($act->material)
                                                <a href="{{ URL::signedRoute('student-view-material-get', ['filename' => Crypt::encrypt($act->material)]) }}"
                                                    class="text-decoration-none" target="_blank">
                                                    <i class="fas fa-download me-2"></i>
                                                    View Flowchart
                                                </a>
                                            @else
                                                <p class="text-muted fst-italic">No material uploaded</p>
                                            @endif
                                        </div>

                                        {{-- Document Submissions --}}
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Submission Involve</h6>

                                            @php
                                                $activityDocs = $docs->get($act->id); // Grouped by activity_id
                                            @endphp

                                            @if ($activityDocs && $activityDocs->first()->document_name)
                                                <ul class="list-group list-group-flush">
                                                    @foreach ($activityDocs as $item)
                                                        <li
                                                            class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                            {{ $item->document_name }}
                                                            <span
                                                                class="badge {{ $item->isRequired == 1 ? 'bg-danger' : 'bg-secondary' }}">
                                                                {{ $item->isRequired == 1 ? 'Required' : 'Optional' }}
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-muted fst-italic">No documents required</p>
                                            @endif
                                        </div>

                                    </div>

                                    {{-- Footer --}}
                                    <div
                                        class="card-footer bg-light d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                        <div class="mb-2">
                                            <h6 class="fw-semibold mb-0">Submission Date</h6>
                                            <small>15 December 2025 12:00 PM</small>
                                        </div>

                                        <div>
                                            <h6 class="fw-semibold mb-0">Time Remaining</h6>
                                            <small>1 Month 13 Days 12 Hours</small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-info">
                                    No activities found for your programme.
                                </div>
                            @endforelse

                        </div>
                    </div>
                </div>
                <!-- [ Programme Overview ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
@endsection
