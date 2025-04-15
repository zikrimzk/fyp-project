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
                    <div class="card border-0 bg-light">
                        <div class="card-body p-4">

                            @forelse($acts as $act)
                                <div class="card mb-4 border-2 shadow-md rounded-4">
                                    <div class="card-body">

                                        {{-- Activity Header --}}
                                        <div
                                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                                            <div>
                                                <h5 class="fw-bold mb-1">{{ $act->act_name }}</h5>
                                                @if ($act->init_status == 1)
                                                    <span class="badge bg-success">Open for Submission</span>
                                                @elseif($act->init_status == 2)
                                                    <span class="badge bg-danger">Locked</span>
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Flowchart Section --}}
                                        <div class="mb-4">
                                            <h6 class="fw-semibold mb-2 text-dark">Flowchart</h6>
                                            @if ($act->material)
                                                <a href="{{ URL::signedRoute('student-view-material-get', ['filename' => Crypt::encrypt($act->material)]) }}"
                                                    target="_blank"
                                                    class="text-decoration-none d-inline-flex align-items-center gap-2 text-primary">
                                                    <i class="ti ti-download"></i> Download Flowchart
                                                </a>
                                            @else
                                                <p class="text-muted fst-italic mb-0">No material uploaded</p>
                                            @endif
                                        </div>

                                        {{-- Document Submission Section --}}
                                        <div class="mb-2">
                                            <h6 class="fw-semibold mb-3">Documents for Submission</h6>

                                            @php
                                                $activityDocs = $docs->get($act->id);
                                            @endphp

                                            @if ($activityDocs && $activityDocs->first()->document_name)
                                                <div class="row g-3">
                                                    @foreach ($activityDocs as $item)
                                                        <div class="col-12">
                                                            <div
                                                                class="bg-light p-3 rounded-3 shadow-sm border-start border-4 border-secondary">
                                                                <div
                                                                    class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                                                                    <div>
                                                                        <h6 class="fw-bold mb-1 text-dark">
                                                                            {{ $item->document_name }}</h6>
                                                                        <span
                                                                            class="badge {{ $item->isRequired == 1 ? 'bg-danger' : 'bg-secondary' }}">
                                                                            {{ $item->isRequired == 1 ? 'Required' : 'Optional' }}
                                                                        </span>
                                                                    </div>
                                                                    <span class="badge bg-warning mt-2 mt-md-0">No
                                                                        Attempt</span>
                                                                </div>

                                                                <div class="row mt-3">
                                                                    <div class="col-6 col-md-4">
                                                                        <small class="text-muted">Submission Date</small>
                                                                        <div class="fw-semibold">15 December 2025, 12:00 PM
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 col-md-4">
                                                                        <small class="text-muted">Time Remaining</small>
                                                                        <div class="fw-semibold">1 Month 13 Days 12 Hours
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                                                                        <a href="{{ route('student-activity-submission-list', Crypt::encrypt($act->activity_id)) }}"
                                                                            class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                                                            <i class="ti ti-upload"></i> Submit
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted fst-italic">No documents required for this activity.
                                                </p>
                                            @endif
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
