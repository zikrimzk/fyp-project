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
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12pt;
            margin: 40px;
            color: #000;
        }

        .header {
            margin-bottom: 20px;
            
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 100px;
            text-align: center;
            vertical-align: top;
        }

        .faculty-logo {
            width: 120px;
        }

        .text-cell {
            text-align: center;
            vertical-align: middle;
        }

        .faculty-name,
        .university-name {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
        }

        .line-title {
            border-top: 1px solid #000;
            margin: 10px 0;
        }

        .form-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0 20px;
        }

        .info-table td {
            padding: 6px;
            vertical-align: top;
        }

        .label {
            width: 35% !important;
            font-size: 12pt;
            /* font-weight: bold; */
            text-transform: capitalize;
        }

        .label span.isrequired {
            color: red;
            font-size: 10pt;
            margin-left: 5px;
        }

        .colon {
            width: 2% !important;
        }

        .value {
            width: 63% !important;
            border-bottom: 1px solid #000;
            word-wrap: break-word;
            white-space: pre-line;
            color: transparent;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .value-input {
            width: 63%;
        }

        .append-text {
            display: block;
            font-size: 9pt;
            color: #555;
            margin-top: 2px;
        }

        .value-input input[type="text"],
        .value-input input[type="number"],
        .value-input input[type="date"],
        .value-input input[type="datetime-local"],
        .value-input select,
        .value-input textarea {
            width: 70%;
            padding: 8px 10px;
            font-size: 11pt;
            border: 1px solid #000;
            border-radius: 4px;
            text-transform: uppercase;
            background-color: #f9f9f9;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        .value-input select {
            background-image: none;
        }

        .value-input textarea {
            resize: none;
            height: 80px;
        }

        .value-input input[type="radio"],
        .value-input input[type="checkbox"] {
            margin-right: 6px;
            transform: scale(1.1);
        }

        .option-group {
            margin-top: 4px;
        }

        .option-group label {
            display: inline-block;
            margin-right: 15px;
            font-size: 11pt;
            text-transform: uppercase;
        }

        .section-header {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin: 0px 0 5px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            display: none;
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

        p {
            margin: 0;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <table class="header-table" width="100%">
            <tr>
                <td class="logo-cell">
                    @if ($faculty->fac_logo && file_exists(public_path('storage/' . $faculty->fac_logo)))
                        <img src="{{ public_path('storage/' . $faculty->fac_logo) }}"
                            alt="{{ $faculty->fac_code }} LOGO" class="faculty-logo">
                    @endif
                </td>
                <td class="text-cell">
                    <div class="faculty-name">{{ $faculty->fac_name }}</div>
                    <div class="university-name">Universiti Teknikal Malaysia Melaka</div>
                </td>
            </tr>
        </table>

        <div class="line-title"></div>

        <div class="form-title">{{ $form_title }}</div>
    </div>

    <!-- Student Info [ Dynamic Field Here ] -->
    <table class="info-table">
        @foreach ($formfields as $ff)
            @if ($ff->ff_category == 1)
                <!-- CATEGORY : INPUT -->
                @php
                    $component = strtolower($ff->ff_component_type);
                    $key = str_replace(' ', '_', strtolower($ff->ff_label));
                    $value = old($key, $userData[$key] ?? '');
                    $placeholder = $ff->ff_placeholder ?? '';
                    $required = $ff->ff_component_required == 1 ? 'required' : '';
                    $options = json_decode($ff->ff_value_options, true);

                    if (!is_array($options)) {
                        $options = preg_split('/\r\n|\r|\n/', $ff->ff_value_options);
                    }

                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                @endphp
                <tr>
                    <td class="label">
                        {{ $ff->ff_label }}
                        <span class="isrequired">{{ $ff->ff_component_required == 1 ? '*' : '' }}</span>
                        <small class="append-text">{{ $ff->ff_append_text ?? '' }}</small>
                    </td>
                    <td class="colon">:</td>
                    <td class="value-input">
                        @switch($component)
                            @case('text')
                                <input type="text" id="{{ $key }}" name="{{ $key }}"
                                    value="{{ e($value) }}" placeholder="{{ $placeholder }}" {{ $required }}>
                            @break

                            @case('textarea')
                                <textarea id="{{ $key }}" name="{{ $key }}" placeholder="{{ $placeholder }}" {{ $required }}>{{ e($value) }}</textarea>
                            @break

                            @case('select')
                                @if ($options && count($options) > 0)
                                    <select id="{{ $key }}" name="{{ $key }}" {{ $required }}>
                                        <option value="">-- Select --</option>
                                        @foreach ($options as $opt)
                                            <option value="{{ e($opt) }}"
                                                @if ($opt == $value) selected @endif>{{ e($opt) }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <p>No options available</p>
                                @endif
                            @break

                            @case('radio')
                                @if ($options && count($options) > 0)
                                    <div class="option-group">
                                        @foreach ($options as $opt)
                                            <label>
                                                <input type="radio" name="{{ $key }}" value="{{ e($opt) }}"
                                                    @if ($opt == $value) checked @endif {{ $required }}>
                                                {{ e($opt) }}
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <p>No options available</p>
                                @endif
                            @break

                            @case('checkbox')
                                @php
                                    $checkedValues = explode(', ', $value);
                                @endphp
                                @if ($options && count($options) > 0)
                                    <div class="option-group">
                                        @foreach ($options as $opt)
                                            <label>
                                                <input type="checkbox" name="{{ $key }}[]"
                                                    value="{{ e($opt) }}"
                                                    @if (in_array($opt, $checkedValues)) checked @endif {{ $required }}>
                                                {{ e($opt) }}
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <p>No options available</p>
                                @endif
                            @break

                            @case('date')
                                <input type="date" id="{{ $key }}" name="{{ $key }}"
                                    value="{{ e($value) }}" {{ $required }}>
                            @break

                            @case('datetime-local')
                                <input type="datetime-local" id="{{ $key }}" name="{{ $key }}"
                                    value="{{ e($value) }}" {{ $required }}>
                            @break

                            @default
                                <input type="text" id="{{ $key }}" name="{{ $key }}"
                                    value="{{ e($value) }}" placeholder="{{ $placeholder }}" {{ $required }}>
                        @endswitch
                    </td>
                </tr>
            @elseif($ff->ff_category == 2)
                <!-- CATEGORY : OUTPUT -->
                <tr>
                    <td class="label">{{ $ff->ff_label }}</td>
                    <td class="colon">:</td>
                    <td class="value">{{ Str::limit(Crypt::encryptString($ff->ff_datakey), 30) }}</td>
                </tr>
            @elseif($ff->ff_category == 3)
                <!-- CATEGORY : SECTION -->
                <tr>
                    <td colspan ="3">
                        <div class="section-header">{{ $ff->ff_label }}</div>
                    </td>
                </tr>
            @elseif($ff->ff_category == 4)
                <!-- CATEGORY : TEXT -->
                <tr>
                    <td colspan ="3">
                        {!! $ff->ff_label !!}
                    </td>
                </tr>
            @endif
        @endforeach
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
