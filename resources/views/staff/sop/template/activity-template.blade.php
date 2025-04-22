@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12pt;
            margin: 40px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header img {
            width: 140px;
            margin-bottom: 10px;
        }

        .header h2,
        .header h3 {
            margin: 0;
            font-weight: bold;
        }

        .line-title {
            border-top: 1px solid #000;
            margin-top: 5px;
        }

        .form-title {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 12px;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0 20px;
        }

        .info-table td {
            padding: 10px 4px;
            vertical-align: top;
        }

        .label {
            width: 35%;
            font-weight: bold;
        }

        .colon {
            width: 2%;
        }

        .value {
            width: 63%;
            border-bottom: 1px solid #000;
            text-transform: uppercase;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }

        .signature-table td {
            vertical-align: center;
            padding: 0 10px;
        }

        .signature-user {
            height: 50px;
        }

        .signature-label {
            font-weight: bold;
            font-size: 11pt;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .date-label {
            font-size: 10.5pt;
            margin-top: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <!-- Logo & Faculty based on system default faculty -->
        <img src="../../assets/images/logo-faculty/ftmk.png" alt="FTMK Logo">
        <h2>FAKULTI TEKNOLOGI MAKLUMAT DAN KOMUNIKASI</h2>
        <h3>UNIVERSITI TEKNIKAL MALAYSIA MELAKA</h3>
        <div class="line-title"></div>
        <!-- Activity Name will be based on user selection [Activity Table] -->
        <div class="form-title">First Stage Submission Form</div>
    </div>

    <!-- Student Info [ Dynamic Field Here ] -->
    <table class="info-table">
        <tr>
            <td class="label">Student Name</td>
            <td class="colon">:</td>
            <td class="value">Muhammad Zikri Bin Kashim</td>
        </tr>
        <tr>
            <td class="label">Matric No.</td>
            <td>:</td>
            <td class="value">B032320063</td>
        </tr>
        <tr>
            <td class="label">Programme of Study</td>
            <td>:</td>
            <td class="value">PITA (Full-Time | Part-Time)</td>
        </tr>
        <tr>
            <td class="label">Main Supervisor</td>
            <td>:</td>
            <td class="value">Prof. Dr. Zainal Abidin</td>
        </tr>
        <tr>
            <td class="label">Co-Supervisor</td>
            <td>:</td>
            <td class="value">Dr. Zahriah Othman</td>
        </tr>
        <tr>
            <td class="label">Journal / Conference Name</td>
            <td>:</td>
            <td class="value">&nbsp;</td>
        </tr>
    </table>

    <!-- Signature Section -->
    <table class="signature-table">
        <tr>
            <td style="width: 33.33%; height: 120px;  border: 1px solid #000; border-bottom: none;"></td>
            <td style="width: 33.33%; height: 120px;  border: 1px solid #000; border-bottom: none;"></td>
            <td style="width: 33.33%; height: 120px;  border: 1px solid #000; border-bottom: none;"></td>
        </tr>
        <tr class="signature-user">
            <td class="signature-label">Student’s Signature</td>
            <td class="signature-label">Supervisor’s Signature & Stamp</td>
            <td class="signature-label">Deputy Dean (Research & Postgraduate)</td>
        </tr>
        <tr>
            <td class="signature-label">
                <div class="date-label">Date:</div>
            </td>
            <td class="signature-label">
                <div class="date-label">Date:</div>
            </td>
            <td class="signature-label">
                <div class="date-label">Date:</div>
            </td>
        </tr>
    </table>

</body>

</html>
