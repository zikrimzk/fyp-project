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
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: rgba(52, 58, 64, 255);
            background: #fff;
            margin: 20px 25px;
        }

        /* ===== HEADER ===== */
        /* .header {
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
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            color: rgba(52, 58, 64, 255);
            margin-bottom: 3px;
        }

        .university-name {
            font-size: 10pt;
            font-weight: bold;
            color: rgba(52, 58, 64, 255);
            text-transform: uppercase;
        }

        .form-title {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            color: rgba(52, 58, 64, 255);
            margin-top: 12px;
            text-transform: uppercase;
            padding: 6px 0;
        } */

        /* ===== HEADER ===== */
        .header {
            margin-top: 20px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #343a40;
        }

        .header-inner {
            text-align: center;
        }

        .faculty-logo {
            max-width: 90px;
            max-height: 90px;
            margin-bottom: 8px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .header-text {
            margin-top: 5px;
        }

        .faculty-name {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #343a40;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }

        .university-name {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #343a40;
            letter-spacing: 0.5px;
        }

        .form-title {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            color: rgba(52, 58, 64, 255);
            /* margin-top: 5px; */
            text-transform: uppercase;
            /* padding: 6px 0; */
            letter-spacing: 0.5px;
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
            font-size: 8pt;
        }

        .meta-label {
            font-weight: bold;
            color: rgba(52, 58, 64, 255);
            width: 25%;
        }

        /* ===== SECTION HEADER ===== */
        .section-header {
            font-size: 10pt;
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
            font-size: 8pt;
            text-align: left;
        }

        .data-table td {
            padding: 6px 8px;
            font-size: 8pt;
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
            font-size: 8pt;
            color: rgba(52, 58, 64, 255);
        }

        .no-signature-notice {
            margin-top: 6px;
            font-weight: bold;
            font-size: 8pt;
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
        <div class="header-inner">
            @if ($faculty->fac_logo && file_exists(public_path('storage/' . $faculty->fac_logo)))
                <img src="{{ public_path('storage/' . $faculty->fac_logo) }}" alt="{{ $faculty->fac_code }} LOGO"
                    class="faculty-logo">
            @endif

            <div class="header-text">
                <div class="faculty-name">{{ $faculty->fac_name }}</div>
                <div class="university-name">Universiti Teknikal Malaysia Melaka</div>
            </div>
        </div>
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
                        <th style="width: 5%;">No.</th>
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
                            <td>{{ $loop->iteration }}</td>
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
    @elseif($report_module == 2)
        @foreach ($groupedData as $activity => $items)
            <h3 class="section-header">{{ $activity }}</h3>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 10%;">Confirmed Date</th>
                        <th style="width: 25%;">Student Name</th>
                        <th style="width: 15%;">Matric No</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 15%;">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ Carbon::parse($row->confirmation_date)->format('d/m/Y') }}</td>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ $row->student_matricno }}</td>
                            <td>
                                {{ $row->submission_status_label }}
                            </td>
                            <td>{{ $row->sem_label }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="section-break"></div>
        @endforeach
    @elseif($report_module == 3)
        @foreach ($groupedData as $activity => $items)
            <h3 class="section-header">{{ $activity }}</h3>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 10%;">Nomination Date</th>
                        <th style="width: 15%;">Student Name</th>
                        <th style="width: 10%;">Matric No</th>
                        <th style="width: 20%;">Evaluators</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $row)
                        @php
                            $evaluators = DB::table('evaluators as a')
                                ->join('staff as b', 'a.staff_id', '=', 'b.id')
                                ->where('a.nom_id', $row->nom_id)
                                ->where('a.eva_status', 3)
                                ->get();
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ Carbon::parse($row->nom_date)->format('d/m/Y') }}</td>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ $row->student_matricno }}</td>
                            <td>
                                @forelse($evaluators as $eva)
                                    <b>
                                        @if ($eva->eva_role == 1)
                                            Examiner/Panel:
                                        @else
                                            Chairman:
                                        @endif
                                    </b>
                                    </br>
                                    {{ $eva->staff_name }} </br>
                                    ({{ $eva->staff_email }})
                                    <br><br>
                                @empty
                                    No Confirmed Evaluators
                                @endforelse
                            </td>
                            <td>
                                {{ $row->nom_status_label }}
                            </td>
                            <td>{{ $row->sem_label }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="section-break"></div>
        @endforeach
    @elseif($report_module == 4)
        @foreach ($groupedData as $activity => $items)
            <h3 class="section-header">{{ $activity }}</h3>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 10%;">Evaluation Date</th>
                        <th style="width: 15%;">Student Name</th>
                        <th style="width: 5%;">Matric No</th>
                        <th style="width: 15%;">Evaluators</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;">Semester</th>
                        <th style="width: 10%;">Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $row)
                        @php
                            $meta = json_decode($row->evaluation_meta_data, true);
                            $scores = $meta['Score'] ?? [];

                            $evaluators = DB::table('nominations as a')
                                ->join('evaluators as b', 'a.id', '=', 'b.nom_id')
                                ->join('staff as c', 'b.staff_id', '=', 'c.id')
                                ->where('a.student_id', $row->student_id)
                                ->where('a.activity_id', $row->activity_id)
                                ->where('b.staff_id', $row->staff_id)
                                ->where('b.eva_status', 3)
                                ->first();
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ Carbon::parse($row->evaluation_date)->format('d/m/Y') }}</td>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ $row->student_matricno }}</td>
                            <td>
                                <b>
                                    @if ($evaluators->eva_role == 1)
                                        Examiner/Panel:
                                    @else
                                        Chairman:
                                    @endif
                                </b>
                                </br>
                                {{ $evaluators->staff_name }} </br>
                                ({{ $evaluators->staff_email }})
                            </td>
                            <td>{{ $row->evaluation_status_label }}</td>
                            <td>{{ $row->sem_label }}</td>
                            <td>
                                @foreach ($scores as $label => $value)
                                    @if ($evaluators->eva_role == 2)
                                        <div>
                                            <strong>{{ ucwords(str_replace('_', ' ', $label)) }}:</strong>
                                            {{ $value }}
                                        </div>
                                    @else
                                        @if (Str::contains(strtolower($label), ['total']))
                                            <div>
                                                <strong>{{ ucwords(str_replace('_', ' ', $label)) }}:</strong>
                                                {{ $value }}
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="section-break"></div>
        @endforeach
    @elseif($report_module == 5)
        @foreach ($groupedData as $activity => $items)
            <h3 class="section-header">{{ $activity }}</h3>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 15%;">Student Name</th>
                        <th style="width: 5%;">Matric No</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Start Date</th>
                        <th style="width: 15%;">Due Date</th>
                        <th style="width: 10%;">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ $row->student_matricno }}</td>
                            <td>{{ $row->correction_status_label }}</td>
                            <td>{{ Carbon::parse($row->ac_startdate)->format('d/m/Y g:i A') }}</td>
                            <td>{{ Carbon::parse($row->ac_duedate)->format('d/m/Y g:i A') }}</td>
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
