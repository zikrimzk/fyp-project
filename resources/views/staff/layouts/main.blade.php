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
    <!--[jQuery] -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- [DataTables Scripts] -->
    <script src="../assets/js/plugins/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.5.1/js/dataTables.rowGroup.js"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.5.1/js/rowGroup.dataTables.js"></script>
    <script src="../assets/js/plugins/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/js/plugins/dataTables.responsive.min.js"></script>
    <script src="../assets/js/plugins/responsive.bootstrap5.min.js"></script>
    <!-- [DataTables Style Links] -->
    <link rel="stylesheet" href="../assets/css/plugins/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="../assets/css/plugins/responsive.bootstrap5.min.css" />
    <link href="../assets/css/plugins/animate.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/rowgroup/1.3.1/css/rowGroup.dataTables.min.css">
    <!-- [Flatpickr Style Links] -->
    <link rel="stylesheet" href="../assets/css/plugins/flatpickr.min.css" />
    <!-- [Flatpickr Scripts] -->
    <script src="../assets/js/plugins/flatpickr.min.js"></script>

    <script>
        $.extend(true, $.fn.dataTable.defaults, {
            processing: true,
            serverSide: true,
            responsive: true,
            language: {
                emptyTable: "No data available",
                zeroRecords: "No matching records found",
                search: "",
                searchPlaceholder: "Search records..."
            },
            order: [],
            pageLength: 50,
            lengthMenu: [5, 10, 25, 50, 100],
            columnDefs: [{
                targets: '_all',
                defaultContent: '-'
            }]
        });
    </script>

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

        table.dataTable thead th {
            background-color: rgba(52, 58, 64, 255) !important;
            color: white !important;
        }

        /* button,
        .btn {
            border-radius: 6px !important;
        } */

        @media (max-width: 768px) {
            .nav-tabs.profile-tabs .nav-item {
                flex: 1 1 auto;
                text-align: center;
            }

            .nav-tabs.profile-tabs .nav-link {
                display: block;
                width: 100%;
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
    @include('staff.layouts.sidebar')
    <!-- [ Sidebar Menu ] end -->

    <!-- [ Header Topbar ] start -->
    @include('staff.layouts.header')
    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    @yield('content')
    <!-- [ Main Content ] end -->

    <!-- [ Footer ] start -->
    @include('staff.layouts.footer')
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

    <script>
        main_layout_change('vertical');
    </script>

</body>
<!-- [Body] end -->

</html>
