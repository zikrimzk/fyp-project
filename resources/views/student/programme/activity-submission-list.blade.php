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
                                <li class="breadcrumb-item"><a href="{{ route('student-programme-overview') }}">Programme
                                        Overview</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">{{ $act->act_name }} Submission List</li>

                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h2 class="mb-0">{{ $act->act_name }} Submission List</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <div class="d-flex justify-content-start align-items-center mb-3">
                <a href="{{ route('student-programme-overview') }}"
                    class="btn btn-sm btn-light-primary d-flex align-items-center justify-content-center me-2">
                    <i class="ti ti-arrow-left me-2"></i>
                    <span class="me-2">Back</span>
                </a>
            </div>

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

                <!-- [ Submission Document ] start -->

                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-4">
                            @forelse($docs as $doc)
                                <div class="card mb-4 border-2 shadow-sm">
                                    <div class="card-body">

                                        {{-- Header --}}
                                        <div
                                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                                            <div>
                                                <h5 class="mb-1">{{ $doc->document_name }}</h5>
                                                <span class="badge bg-warning">Pending Submission</span>
                                            </div>
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
                                            <a href=""
                                                class="btn btn-sm btn-outline-primary mt-2 mt-md-0 d-flex align-items-center gap-1">
                                                <span>Add Submission</span>
                                                <i class="ti ti-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-info">
                                    No documents available for submission yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- [ Submission Document ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
@endsection
