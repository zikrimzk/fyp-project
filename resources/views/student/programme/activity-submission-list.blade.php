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
                                <li class="breadcrumb-item"><a href="{{ route('student-programme-overview') }}">Programme Overview</a>
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
                <a href="{{ route('student-programme-overview') }}" class="btn btn-sm btn-light-primary d-flex align-items-center justify-content-center me-2">
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
                            <h5 class="fst-italic">To be implemented</h5>
                        </div>
                    </div>
                </div>

                <!-- [ Submission Document ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
@endsection
