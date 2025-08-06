<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!-- [Head] start -->

<head>
    <title>e-PostGrad | {{ $title }}</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=0.9, maximum-scale=1.0, user-scalable=no, minimal-ui">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="-" />
    <meta name="keywords" content="-" />
    <meta name="author" content="ZikriMzk" />

    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/images/favicon.svg" type="image/x-icon" />
    <!-- [Font] Family -->
    <link rel="stylesheet" href="../assets/fonts/inter/inter.css" id="main-font-link" />
    <!-- [phosphor Icons] https://phosphoricons.com/ -->
    <link rel="stylesheet" href="../assets/fonts/phosphor/duotone/style.css" />
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <!-- [Feather Icons] https://feathericons.com -->
    <link rel="stylesheet" href="../assets/fonts/feather.css" />
    <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
    <link rel="stylesheet" href="../assets/fonts/fontawesome.css" />
    <!-- [Material Icons] https://fonts.google.com/icons -->
    <link rel="stylesheet" href="../assets/fonts/material.css" />
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/style-preset.css" />
    <!-- [DataTables Style Links] -->
    <link rel="stylesheet" href="../assets/css/plugins/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="../assets/css/plugins/responsive.bootstrap5.min.css" />
    <link href="../assets/css/plugins/animate.min.css" rel="stylesheet" type="text/css" />
    <!-- [DataTables Scripts] -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="../assets/js/plugins/dataTables.min.js"></script>
    <script src="../assets/js/plugins/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/js/plugins/dataTables.responsive.min.js"></script>
    <script src="../assets/js/plugins/responsive.bootstrap5.min.js"></script>
    <!-- [Flatpickr Style Links] -->
    <link rel="stylesheet" href="../assets/css/plugins/flatpickr.min.css" />
    <!-- [Flatpickr Scripts] -->
    <script src="../assets/js/plugins/flatpickr.min.js"></script>
    <!-- Uppy CSS -->
    <link rel="stylesheet" href="../assets/css/plugins/uppy.min.css" />
    <!-- Uppy JS -->
    <script src="../assets/js/plugins/uppy.min.js"></script>

    <style>
        .data-table td {
            white-space: normal !important;
        }

        .disabled-a {
            pointer-events: none;
            opacity: 0.6;
            text-decoration: none;
        }

        .avatar-s {
            width: 150px !important;
            height: 150px !important;
            overflow: hidden;
            border-radius: 50%;
        }

        .avatar-s img {
            width: 150px !important;
            height: 150px !important;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-sms {
            width: 50px !important;
            height: 50px !important;
            overflow: hidden;
            border-radius: 50%;
        }

        .avatar-sms img {
            width: 50px !important;
            height: 50px !important;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-sidebar {
            width: 80px !important;
            height: 80px !important;
            overflow: hidden;
            border-radius: 50%;
        }

        .avatar-sidebar img {
            width: 80px !important;
            height: 80px !important;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @keyframes flash {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        .badge-flash {
            animation: flash 1s infinite;
        }

        .uppy-container-wrapper {
            width: 100%;
            max-width: 100%;
            padding: 0 1rem;
            box-sizing: border-box;
        }

        #pc-uppy-1 .uppy-Dashboard-inner {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box;
        }

        table.dataTable thead th {
            background-color: rgba(52, 58, 64, 255) !important;
            color: white !important;
        }


        @media (max-width: 768px) {
            .nav-tabs.profile-tabs .nav-item {
                flex: 1 1 auto;
                text-align: center;
            }

            .nav-tabs.profile-tabs .nav-link {
                display: block;
                width: 100%;
            }

            .uppy-container-wrapper {
                padding: 0.5rem;
            }

            #pc-uppy-1 .uppy-Dashboard-inner {
                padding: 0.5rem !important;
            }

            .uppy-Dashboard-AddFiles {
                flex-direction: column !important;
                align-items: stretch !important;
            }

            .uppy-Dashboard-FileCard {
                width: 100% !important;
            }
        }
    </style>

</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr"
    data-pc-theme_contrast="" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
    <div class="page-loader">
        <div class="bar"></div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ Sidebar Menu ] start -->
    @include('student.layouts.sidebar-new')
    <!-- [ Sidebar Menu ] end -->

    <!-- [ Header Topbar ] start -->
    @include('student.layouts.header')
    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    @yield('content')
    <!-- [ Main Content ] end -->

    <!-- [ Footer ] start -->
    @include('student.layouts.footer')
    <!-- [ Footer ] end -->

    <!-- Required Js -->
    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../assets/js/fonts/custom-font.js"></script>
    <script src="../assets/js/pcoded.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>

    <script>
        // Prevent pinch-to-zoom
        document.addEventListener('gesturestart', function(e) {
            e.preventDefault();
        });

        // Prevent double-tap zoom
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            let now = new Date().getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>

    <script>
        $(document).ready(function() {
            $('[title]').tooltip({
                placement: 'bottom',
                trigger: 'hover'
            });
        });
    </script>
    <!-- [Uppy Scripts] -->
    <script src="../assets/js/plugins/uppy.min.js"></script>

    <script>
        main_layout_change('vertical');
    </script>

</body>
<!-- [Body] end -->

</html>
