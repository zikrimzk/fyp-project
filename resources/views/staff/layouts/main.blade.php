<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!-- [Head] start -->

<head>
    <title>e-Pasca | {{ $title }}</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="-" />
    <meta name="keywords" content="-" />
    <meta name="author" content="ZikriMzk" />

    <!-- [Favicon] icon -->
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon" />
    <!-- [Font] Family -->
    <link rel="preload" href="{{ asset('assets/fonts/inter/Inter-roman.var.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}?v=1.0.0" id="main-font-link" />
    <!-- [phosphor Icons] https://phosphoricons.com/ -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/phosphor/duotone/style.css') }}" />
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" />
    <!-- [Feather Icons] https://feathericons.com -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" />
    <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" />
    <!-- [Material Icons] https://fonts.google.com/icons -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}" />
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link" />
    <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}" />
    <!--[jQuery] -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- [DataTables Scripts] -->
    <script src="{{ asset('assets/js/plugins/dataTables.min.js') }}"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.5.1/js/dataTables.rowGroup.js"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.5.1/js/rowGroup.dataTables.js"></script>
    <script src="{{ asset('assets/js/plugins/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/responsive.bootstrap5.min.js') }}"></script>
    <!-- [DataTables Style Links] -->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dataTables.bootstrap5.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/responsive.bootstrap5.min.css') }}" />
    <link href="{{ asset('assets/css/plugins/animate.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/rowgroup/1.3.1/css/rowGroup.dataTables.min.css">
    <!-- [Flatpickr Style Links] -->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}" />
    <!-- [Flatpickr Scripts] -->
    <script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>

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

        (function() {
            function readableLabel(element) {
                var id = element.id;
                var explicit = id ? document.querySelector('label[for="' + CSS.escape(id) + '"]') : null;
                if (explicit) return explicit.textContent.trim();

                var inputGroup = element.closest('.input-group');
                var container = inputGroup ? inputGroup.parentElement : element.parentElement;
                var nearby = container ? container.querySelector(':scope > .form-label, :scope > label') : null;
                if (nearby) return nearby.textContent.replace('*', '').trim();

                return (id || element.name || 'Filter')
                    .replace(/^fil_/, '')
                    .replace(/_/g, ' ')
                    .replace(/\b\w/g, function(char) { return char.toUpperCase(); });
            }

            function selectedFilters(table) {
                var scope = table.closest('.pc-content') || document;
                return Array.from(scope.querySelectorAll('select[id^="fil_"], input[id$="Filter"]'))
                    .filter(function(element) {
                        if (!element.offsetParent) return false;
                        var value = element.tagName === 'SELECT'
                            ? element.options[element.selectedIndex]?.text.trim()
                            : element.value.trim();
                        if (!value || /^--\s*select/i.test(value) || /^all\s/i.test(value)) return false;
                        element.dataset.emptyStateValue = value;
                        return true;
                    })
                    .map(function(element) {
                        return readableLabel(element) + ': ' + element.dataset.emptyStateValue;
                    });
            }

            $(document).on('init.dt draw.dt', function(event, settings) {
                var table = settings.nTable;
                var emptyCell = table.querySelector('tbody td.dt-empty, tbody td.dataTables_empty');
                if (!emptyCell) return;

                var filters = selectedFilters(table);
                emptyCell.textContent = '';

                var wrapper = document.createElement('div');
                wrapper.className = 'ep-empty-state py-4';

                var title = document.createElement('strong');
                title.className = 'd-block mb-1';
                title.textContent = filters.length
                    ? 'No records match the current filters.'
                    : 'No records are available for this module yet.';
                wrapper.appendChild(title);

                var guidance = document.createElement('span');
                guidance.className = 'text-muted d-block';
                guidance.textContent = filters.length
                    ? 'Active filters: ' + filters.join(' · ') + '. Clear one or more filters to widen the results.'
                    : 'Records will appear here when the related workflow step has been completed.';
                wrapper.appendChild(guidance);
                emptyCell.appendChild(wrapper);
            });
        })();
    </script>

    <style>
        :root {
            --success-color: #166534;
            --danger-color: #b91c1c;
            --border-color: #d1d5db;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: var(--success-color);
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: var(--danger-color);
            border: 1px solid #fecaca;
        }

        .data-table td {
            white-space: normal !important;
        }

        .ep-empty-state {
            max-width: 760px;
            margin: 0 auto;
            line-height: 1.5;
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

        .table-color {
            background-color: rgba(52, 58, 64, 255) !important;
        }

        /* Custom Button Styles (Management UI) */
        .btn {
            border-radius: 6px !important;
        }

        .btn-primary {
            background-color: rgba(52, 58, 64, 255) !important;
            border-color: rgba(52, 58, 64, 255) !important;
            color: #fff !important;
            transition: all 0.2s ease;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: rgba(33, 37, 41, 255) !important;
            border-color: rgba(33, 37, 41, 255) !important;
            color: #fff !important;
        }

        .btn-outline-primary {
            color: rgba(52, 58, 64, 255) !important;
            border-color: rgba(52, 58, 64, 255) !important;
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover, .btn-outline-primary:focus {
            background-color: rgba(52, 58, 64, 255) !important;
            color: #fff !important;
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
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/system-theme.css') }}?v=1.0.0" />

</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body class="ep-app ep-staff-app" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr"
    data-pc-theme_contrast="" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
    <div class="page-loader">
        <div class="bar"></div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ Sidebar Menu ] start -->
    @include('staff.layouts.sidebar-new')
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
    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

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

    <script>
        // Global Loading State for all form submissions
        // Added on: 2026-08-06
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                // If the form fails native validation, do not show loading
                if (this.checkValidity && !this.checkValidity()) {
                    return;
                }

                // Find the submit button
                let $form = $(this);
                let $btn = $form.find('button[type="submit"]');

                if ($btn.length && !$btn.prop('disabled')) {
                    // Check if it already has a loading state to prevent double execution
                    if ($btn.data('is-loading')) return;

                    // Set loading state
                    $btn.data('is-loading', true);
                    
                    // Store original HTML in case we need to revert
                    let originalHtml = $btn.html();
                    $btn.data('original-html', originalHtml);

                    // Add spinner
                    $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...');
                    
                    // Disable button to prevent double-click
                    // Use setTimeout so the form submission doesn't drop the button's name/value if it's required
                    setTimeout(function() {
                        $btn.prop('disabled', true);
                    }, 10);
                }
            });
        });
    </script>

</body>
<!-- [Body] end -->

</html>
