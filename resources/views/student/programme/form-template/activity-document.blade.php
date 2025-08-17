@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    {{-- <style>
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
            margin: 15px 0 5px 0;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .signature-table.clean-signature {
            width: 100%;
            border-collapse: separate;
        }

        .signature-cell {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 10px 15px;
        }

        .signature-box-clean {
            height: 100px;
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
            align-items: flex-end;
        }

        .signature-img-clean {
            max-height: 120px;
            max-width: 100%;
            object-fit: contain;
            margin-bottom: 5px;
        }

        .signature-label-clean {
            border-top: 1px solid #000;
            font-weight: bold;
            font-size: 11pt;
            padding-top: 5px;
        }

        .signature-date-clean {
            font-size: 10.5pt;
            margin-top: 3px;
        }

        .date-label,
        .name-label {
            display: block;
        }

        .signature-box-clean-text {
            height: 100px;
            line-height: 100px;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            color: red;
            margin-bottom: 10px;
            overflow: hidden;
            white-space: nowrap;
        }

        p {
            margin: 0;
        }
    </style> --}}

    {{-- <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            margin: 30px;
            color: #000;
            background: #fff;
        }

        .header {
            margin-bottom: 30px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .logo-cell {
            width: 120px;
            text-align: center;
            vertical-align: middle;
        }

        .faculty-logo {
            width: 100px;
            height: auto;
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
            margin: 5px 0;
            padding: 0;
        }

        .line-title {
            border-top: 2px solid #000;
            margin: 15px 0;
        }

        .form-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 15px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .info-table td {
            padding: 8px 5px;
            vertical-align: top;
        }

        .label {
            width: 35%;
            font-size: 11pt;
            font-weight: normal;
            text-transform: capitalize;
        }

        .label span.isrequired {
            color: #000;
            font-size: 10pt;
            margin-left: 3px;
        }

        .colon {
            width: 2%;
        }

        .value {
            width: 63%;
            border-bottom: 1px solid #000;
            word-wrap: break-word;
            white-space: pre-line;
            /* color: transparent; */
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            min-height: 18px;
        }

        .value-input {
            width: 63%;
        }

        .append-text {
            display: block;
            font-size: 9pt;
            color: #666;
            margin-top: 2px;
        }

        .value-input input[type="text"],
        .value-input input[type="number"],
        .value-input input[type="date"],
        .value-input input[type="datetime-local"],
        .value-input select,
        .value-input textarea {
            width: 70%;
            padding: 6px 8px;
            font-size: 11pt;
            border: 1px solid #000;
            background-color: #fff;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            text-transform: uppercase;
        }

        .value-input select {
            background-image: none;
        }

        .value-input textarea {
            resize: none;
            height: 60px;
        }

        .value-input input[type="radio"],
        .value-input input[type="checkbox"] {
            margin-right: 5px;
            transform: scale(1.1);
        }

        .option-group {
            margin-top: 5px;
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
            margin: 20px 0 10px 0;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .signature-table.clean-signature {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .signature-cell {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 15px 10px;
        }

        .signature-box-clean {
            height: 80px;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
            display: flex;
            justify-content: center;
            align-items: flex-end;
        }

        .signature-img-clean {
            max-height: 70px;
            max-width: 100%;
            object-fit: contain;
        }

        .signature-label-clean {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .signature-date-clean {
            font-size: 10pt;
            line-height: 1.4;
        }

        .date-label,
        .name-label {
            display: block;
            margin-bottom: 2px;
        }

        p {
            margin: 0;
        }

        .long-textarea-notebook {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 16px;
            border: 1px solid #000;
            padding: 10px;
            background-color: #fff;
            white-space: pre-wrap;
            word-wrap: break-word;
            min-height: 60px;
            box-sizing: border-box;
            position: relative;
        }

        .long-textarea-notebook::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: repeating-linear-gradient(to bottom,
                    transparent,
                    transparent 15px,
                    #ccc 16px);
            z-index: 0;
        }

        .long-textarea-notebook {
            position: relative;
            z-index: 1;
        }

        .long-textarea-field {
            margin: 10px 0;
        }

        .long-textarea-field label {
            font-size: 11pt;
            font-weight: normal;
            display: block;
            margin-bottom: 5px;
        }

        .pub-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .pub-table thead th {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            background-color: #f0f0f0;
        }

        .pub-table tbody td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10pt;
        }

        .pub-table tbody td:first-child {
            text-align: center;
            font-weight: bold;
        }

        .pub-table tbody td:last-child {
            text-align: center;
        }
    </style> --}}

     <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            margin: 30px;
            color: #333;
            background: #fff;
        }

        /* ENHANCED HEADER DESIGN */
        .header {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 15px;
            background: #fafafa;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 120px;
            text-align: center;
            vertical-align: middle;
            padding-right: 15px;
            border-right: 1px solid #eee;
        }

        .faculty-logo {
            width: 100px;
            height: auto;
        }

        .text-cell {
            text-align: center;
            vertical-align: middle;
            padding-left: 15px;
        }

        .faculty-name {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 5px 0;
            letter-spacing: 0.5px;
            color: #000;
        }

        .university-name {
            font-size: 11pt;
            font-weight: normal;
            text-transform: uppercase;
            margin: 5px 0 0 0;
            color: #555;
            letter-spacing: 0.3px;
        }

        .line-title {
            border-top: 2px solid #333;
            margin: 12px -15px 8px -15px;
        }

        .form-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 8px 0 0 0;
            padding: 8px;
            background: #fff;
            letter-spacing: 1px;
            color: #000;
        }

        /* PROFESSIONAL FORM LAYOUT */
        .info-table {
            width: 100%;
            border: 1px solid #ccc;
            border-collapse: collapse;
            margin: 20px 0;
            background: #fff;
        }

        .info-table td {
            padding: 10px 12px;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }

        .info-table tr:nth-child(even) {
            background: #f8f8f8;
        }

        .label {
            width: 38%;
            font-size: 11pt;
            font-weight: 600;
            text-transform: none;
            background: #f5f5f5;
            border-right: 1px solid #ddd;
            color: #333;
        }

        .label span.isrequired {
            color: #d32f2f;
            font-size: 11pt;
            font-weight: bold;
            margin-left: 3px;
        }

        .colon {
            width: 2%;
            text-align: center;
            font-weight: bold;
            background: #f5f5f5;
            color: #666;
        }

        .value {
            width: 60%;
            border-bottom: 1px solid #333;
            word-wrap: break-word;
            white-space: pre-line;
            /* color: transparent; */
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            min-height: 20px;
            padding-left: 5px;
        }

        .value-input {
            width: 60%;
            padding-left: 5px;
        }

        .append-text {
            display: block;
            font-size: 9pt;
            color: #666;
            margin-top: 3px;
            font-style: italic;
        }

        /* ENHANCED FORM CONTROLS */
        .value-input input[type="text"],
        .value-input input[type="number"],
        .value-input input[type="date"],
        .value-input input[type="datetime-local"],
        .value-input select,
        .value-input textarea {
            width: 80%;
            padding: 6px 10px;
            font-size: 11pt;
            border: 1px solid #999;
            border-left: 3px solid #666;
            background-color: #fff;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            text-transform: uppercase;
            color: #333;
        }

        .value-input select {
            background: #fff;
            cursor: pointer;
        }

        .value-input textarea {
            resize: vertical;
            height: 65px;
            text-transform: none;
            line-height: 1.4;
        }

        .value-input input[type="radio"],
        .value-input input[type="checkbox"] {
            margin-right: 6px;
            transform: scale(1.2);
        }

        .option-group {
            margin-top: 6px;
            padding: 8px;
            background: #f9f9f9;
            border: 1px solid #eee;
        }

        .option-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 11pt;
            text-transform: uppercase;
            font-weight: normal;
            color: #333;
        }

        /* ENHANCED SECTION HEADERS */
        .section-header {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            background: #f0f0f0;
            margin: 20px 0 0 0;
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-bottom: 2px solid #666;
            letter-spacing: 0.5px;
        }

        /* PROFESSIONAL SIGNATURE SECTION */
        .signature-table.clean-signature {
            width: 100%;
            border: 1px solid #ccc;
            border-collapse: collapse;
            margin: 30px 0;
            background: #fff;
        }

        .signature-cell {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 20px 10px;
            border-right: 1px solid #ccc;
            background: #fafafa;
        }

        .signature-cell:last-child {
            border-right: none;
        }

        .signature-box-clean {
            height: 90px;
            border: 1px solid #999;
            border-bottom: 2px solid #333;
            margin-bottom: 12px;
            display: flex;
            justify-content: center;
            align-items: flex-end;
            background: #fff;
            position: relative;
        }

        .signature-box-clean::before {
            content: "Signature";
            position: absolute;
            top: 3px;
            left: 5px;
            font-size: 8pt;
            color: #bbb;
            font-style: italic;
        }

        .signature-img-clean {
            max-height: 75px;
            max-width: 90%;
            object-fit: contain;
        }

        .signature-label-clean {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 8px;
            text-transform: uppercase;
            color: #000;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
            letter-spacing: 0.3px;
        }

        .signature-date-clean {
            font-size: 9pt;
            line-height: 1.3;
            color: #666;
        }

        .date-label,
        .name-label {
            display: block;
            margin-bottom: 2px;
            font-weight: normal;
        }

        /* ENHANCED TEXT AREAS */
        .long-textarea-notebook {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 16px;
            border: 1px solid #999;
            border-left: 3px solid #666;
            padding: 12px 15px;
            background-color: #fff;
            white-space: pre-wrap;
            word-wrap: break-word;
            min-height: 70px;
            box-sizing: border-box;
            position: relative;
        }

        .long-textarea-notebook::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: repeating-linear-gradient(to bottom,
                    transparent,
                    transparent 15px,
                    #f0f0f0 16px);
            z-index: 0;
        }

        .long-textarea-field {
            margin: 15px 0;
            border: 1px solid #eee;
            padding: 12px;
            background: #f8f8f8;
        }

        .long-textarea-field label {
            font-size: 11pt;
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            color: #333;
        }

        /* PROFESSIONAL TABLE DESIGN */
        .pub-table {
            width: 100%;
            border: 1px solid #999;
            border-collapse: collapse;
            margin: 15px 0;
            background: #fff;
        }

        .pub-table thead th {
            border: 1px solid #666;
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            background: #f0f0f0;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .pub-table tbody tr {
            border-bottom: 1px solid #ccc;
        }

        .pub-table tbody tr:nth-child(even) {
            background: #f8f8f8;
        }

        .pub-table tbody td {
            border-right: 1px solid #ccc;
            padding: 8px 10px;
            font-size: 10pt;
            line-height: 1.3;
            color: #333;
        }

        .pub-table tbody td:last-child {
            border-right: none;
        }

        .pub-table tbody td:first-child {
            text-align: center;
            font-weight: bold;
            background: #f5f5f5;
        }

        .pub-table tbody td:last-child {
            text-align: center;
            font-weight: 600;
        }

        p {
            margin: 6px 0;
            color: #333;
        }

        /* CLEAN STYLING FOR SECTIONS */
        .info-table tr td[colspan="3"] {
            padding: 0;
            border: none;
            background: none;
        }

        .info-table tr td[colspan="3"]>* {
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

        <div class="line-title"></div>

        <div class="form-title">{{ $form_title }}</div>
    </div>

    <!-- Student Info [ Dynamic Field Here ] -->
    <table class="info-table">
        @php
            $i = 0;
            $total = count($formfields);
        @endphp

        @while ($i < $total)
            @php
                $ff = $formfields[$i];
            @endphp
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

                        @php $i++; @endphp

                    </td>
                </tr>
            @elseif($ff->ff_category == 2)
                <!-- CATEGORY : OUTPUT -->
                @php
                    $key = str_replace(' ', '_', strtolower($ff->ff_label));
                    $value = $userData[$key] ?? '-';
                @endphp
                <tr>
                    <td class="label">{{ $ff->ff_label }}</td>
                    <td class="colon">:</td>
                    <td class="value">{!! $value !!}</td>
                </tr>
                @php $i++; @endphp
            @elseif($ff->ff_category == 3)
                <!-- CATEGORY : SECTION -->
                <tr>
                    <td colspan ="3">
                        <div class="section-header">{{ $ff->ff_label }}</div>
                    </td>
                </tr>
                @php $i++; @endphp
            @elseif($ff->ff_category == 4)
                <!-- CATEGORY : TEXT -->
                <tr>
                    <td colspan ="3">
                        {!! $ff->ff_label !!}
                    </td>
                </tr>
                @php $i++; @endphp
            @elseif ($ff->ff_category == 6)
                @php
                    $signatureGroup = collect();
                    while ($i < $total && $formfields[$i]->ff_category == 6) {
                        $signatureGroup->push($formfields[$i]);
                        $i++;
                    }
                    $signatureChunks = $signatureGroup->chunk(3);
                @endphp

                @foreach ($signatureChunks as $chunk)
                    <tr>
                        <td colspan="3">
                            <table class="signature-table clean-signature">
                                <tr>
                                    @foreach ($chunk as $sig)
                                        @if (
                                            $signatureData &&
                                                $sig->ff_signature_key &&
                                                isset($signatureData->{$sig->ff_signature_key . '_is_cross_approval'}) &&
                                                $signatureData->{$sig->ff_signature_key . '_is_cross_approval'})
                                            <td class="signature-cell">
                                                <div class="signature-box-clean-text">
                                                    Approved by
                                                    {{ $signatureData->{$sig->ff_signature_key . '_role'} ?? '-' }}
                                                </div>
                                                <div class="signature-label-clean">
                                                    {{ $sig->ff_label }}
                                                </div>
                                                <div class="signature-date-clean">
                                                    <span class="name-label">
                                                        {{ $signatureData && $sig->ff_signature_key && isset($signatureData->{$sig->ff_signature_key . '_name'}) ? $signatureData->{$sig->ff_signature_key . '_name'} : '( NAME_OF_APPROVER )' }}
                                                    </span>
                                                    <span class="date-label">
                                                        {{ $signatureData && $sig->ff_signature_date_key && isset($signatureData->{$sig->ff_signature_date_key}) ? $signatureData->{$sig->ff_signature_date_key} : '( DATE_OF_APPROVAL )' }}
                                                    </span>
                                                </div>
                                            </td>
                                        @else
                                            <td class="signature-cell">
                                                <div class="signature-box-clean">
                                                    <img src="{{ $signatureData && $sig->ff_signature_key && isset($signatureData->{$sig->ff_signature_key}) ? $signatureData->{$sig->ff_signature_key} : '' }}"
                                                        class="signature-img-clean">
                                                </div>
                                                <div class="signature-label-clean">
                                                    {{ $sig->ff_label }}
                                                </div>
                                                <div class="signature-date-clean">
                                                    <span class="name-label">
                                                        {{ $signatureData && $sig->ff_signature_key && isset($signatureData->{$sig->ff_signature_key . '_name'}) ? $signatureData->{$sig->ff_signature_key . '_name'} : '( NAME_OF_APPROVER )' }}
                                                    </span>
                                                    <span class="date-label">
                                                        {{ $signatureData && $sig->ff_signature_date_key && isset($signatureData->{$sig->ff_signature_date_key}) ? $signatureData->{$sig->ff_signature_date_key} : '( DATE_OF_APPROVAL )' }}
                                                    </span>
                                                </div>
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endforeach
            @endif
        @endwhile
    </table>


</body>

</html>
