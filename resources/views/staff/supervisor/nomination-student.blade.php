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
                                <li class="breadcrumb-item"><a href="javascript: void(0)">My Supervision</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0)">Nomination</a></li>
                                <li class="breadcrumb-item"><a
                                        href="{{ route('my-supervision-nomination', Crypt::encrypt($act->id)) }}">{{ $act->act_name }}</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">{{ $data->student_name }}</li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <a href="{{ route('my-supervision-nomination', Crypt::encrypt($act->id)) }}"
                                    class="btn me-2 d-flex align-items-center">
                                    <span class="f-18">
                                        <i class="ti ti-arrow-left me-2"></i>
                                    </span>
                                    Back
                                </a>
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

                <!-- [ Nomination Student ] start -->
                <div class="col-sm-12">
                    <form action="" method="POST">
                        <div class="card p-3">
                            <div class="card-body">
                                <div class="container">
                                    <div id="formContainer"></div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Confirm & Submit Nomination</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- [ Nomination Student ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <script type="text/javascript">
        $(window).on("load", function() {

            function getNominationForm() {
                $.ajax({
                    url: "{{ route('view-nomination-form-get') }}",
                    type: "GET",
                    data: {
                        _token: "{{ csrf_token() }}",
                        actid: "{{ $act->id }}",
                        afid: "{{ $actform->id }}",
                        studentid: "{{ $data->student_id }}"
                    },
                    success: function(response) {
                        $('#formContainer').html(response.html);
                    },
                    error: function() {
                        alert("Something went wrong! {{ $act->id }}");
                    }
                });
            }
            
            getNominationForm();

        });

        $(document).ready(function() {

        });
    </script>
@endsection
