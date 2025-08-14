@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Crypt;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        /* ===== GLOBAL RESET ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: rgba(52, 58, 64, 255);
            background: #fff;
            margin: 20px 25px;
        }

        /* ===== HEADER ===== */
        .header {
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(52, 58, 64, 255);
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 100px;
            text-align: center;
        }

        .faculty-logo {
            max-width: 120px;
            max-height: 120px;
            object-fit: contain;
        }

        .text-cell {
            text-align: center;
            padding-left: 10px;
        }

        .faculty-name {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            color: rgba(52, 58, 64, 255);
            margin-bottom: 3px;
        }

        .university-name {
            font-size: 11pt;
            font-weight: bold;
            color: rgba(52, 58, 64, 255);
            text-transform: uppercase;
        }

        .form-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            color: rgba(52, 58, 64, 255);
            margin-top: 12px;
            text-transform: uppercase;
            padding: 6px 0;
        }

        /* ===== META INFO ===== */
        .report-meta {
            margin: 20px 0;
            padding: 10px;
            background-color: #f9fafb;
        }

        .report-meta-table {
            width: 100%;
        }

        .report-meta-table td {
            padding: 3px 8px;
            font-size: 10pt;
        }

        .meta-label {
            font-weight: bold;
            color: rgba(52, 58, 64, 255);
            width: 25%;
        }

        /* ===== SECTION HEADER ===== */
        .section-header {
            font-size: 11.5pt;
            font-weight: bold;
            color: rgba(52, 58, 64, 255);
            margin: 20px 0 8px;
            padding: 6px 10px;
            background-color: #eef1f3;
            text-transform: uppercase;
        }

        /* ===== DATA TABLE ===== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th {
            background-color: rgba(52, 58, 64, 255);
            color: #fff;
            padding: 8px;
            font-size: 10pt;
            text-align: left;
        }

        .data-table td {
            padding: 6px 8px;
            font-size: 10pt;
            border-bottom: 1px solid #eaeaea;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9pt;
            color: rgba(52, 58, 64, 255);
        }

        .no-signature-notice {
            margin-top: 6px;
            font-weight: bold;
            font-size: 9.5pt;
            color: rgba(52, 58, 64, 255);
            text-transform: uppercase;
        }

        /* ===== PAGE BREAK ===== */
        .page-break {
            page-break-before: always;
        }

        @page {
            margin: 2cm 1.5cm;
        }
    </style>
</head>

<body>
    <!-- ========================================
         DOCUMENT HEADER SECTION
         ======================================== -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if ($faculty->fac_logo && file_exists(public_path('storage/' . $faculty->fac_logo)))
                        <img src="{{ public_path('storage/' . $faculty->fac_logo) }}" alt="{{ $faculty->fac_code }} LOGO"
                            class="faculty-logo">
                    @endif
                </td>
                <td class="text-cell">
                    <div class="faculty-name">{{ $faculty->fac_name }}</div>
                    <div class="university-name">Universiti Teknikal Malaysia Melaka</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="form-title">{{ $title }}</div>

    <!-- ========================================
         REPORT METADATA SECTION
         ======================================== -->
    <div class="report-meta">
        <table class="report-meta-table">
            <tr>
                <td class="meta-label">Generated Date:</td>
                <td class="meta-value">{{ Carbon::now()->format('d/m/Y H:i:s') }}</td>
                <td class="meta-label">Generated By:</td>
                <td class="meta-value">{{ auth()->user()->staff_name }}</td>
            </tr>
            <tr>
                <td class="meta-label">Total Records:</td>
                <td class="meta-value">{{ collect($groupedData)->flatten(1)->count() }}</td>
                <td class="meta-label">Report Type:</td>
                <td class="meta-value">{{ $report_type }}</td>
            </tr>
        </table>
    </div>

    <!-- ========================================
         MAIN CONTENT SECTION
         ======================================== -->
    @if ($report_module == 1)
        @foreach ($groupedData as $activity => $items)
            <h3 class="section-header">{{ $activity }}</h3>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Student Name</th>
                        <th style="width: 15%;">Matric No</th>
                        <th style="width: 25%;">Document</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;">Due Date</th>
                        <th style="width: 15%;">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $row)
                        <tr>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ $row->student_matricno }}</td>
                            <td>{{ $row->doc_name }}</td>
                            <td>
                                {{ $row->submission_status_label }}
                            </td>
                            <td>{{ Carbon::parse($row->submission_duedate)->format('d/m/Y') }}</td>
                            <td>{{ $row->sem_label }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="section-break"></div>
        @endforeach
    @endif

    <!-- ========================================
         DOCUMENT FOOTER SECTION
         ======================================== -->
    <div class="footer">
        <div class="footer-content">
            <div class="generated-info">
                Document generated on {{ Carbon::now()->format('l, F j, Y \a\t g:i A') }}
                <br>
                e-PostGrad System | Faculty: {{ $faculty->fac_code }}
            </div>
            <div class="no-signature-notice">
                This is a computer-generated document and does not require a signature
            </div>
        </div>
    </div>

</body>

</html>
