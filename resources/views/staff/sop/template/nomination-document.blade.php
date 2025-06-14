@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Crypt;

    $currentMode = strtolower($mode);
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

        .value-input {
            width: 100%;
            border: none;
            padding: 0;
            background: transparent;
        }

        .value-input input[type="text"],
        .value-input input[type="number"],
        .value-input input[type="date"],
        .value-input input[type="datetime-local"],
        .value-input select,
        .value-input textarea {
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
            width: 100% !important;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        .value-input select {
            background-image: none !important;
        }

        .option-group label {
            display: block;
            margin: 2px 0;
        }

        /* HIDE FORM CONTROLS IN PDF */
        input,
        select,
        textarea,
        button,
        canvas {
            border: none !important;
            background: transparent !important;
            pointer-events: none;
        }

        .signature-clear-btn,
        .field-disabled-note,
        .signature-disabled-note {
            display: none !important;
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

                    // Determine required role
                    $requiredRole =
                        $ff->ff_component_required_role == 1
                            ? 'supervisors'
                            : ($ff->ff_component_required_role == 2
                                ? 'administrators'
                                : 'all');

                    // Check if field is required for current mode
                    $isRequired =
                        $ff->ff_component_required == 1 && ($requiredRole === 'all' || $requiredRole === $currentMode);

                    // Determine if field should be disabled
                    $shouldDisable = $requiredRole !== 'all' && $requiredRole !== $currentMode;
                    $disabledAttr = $shouldDisable ? 'disabled' : '';
                    $requiredAttr = $isRequired && !$shouldDisable ? 'required' : '';

                    $options = [];

                    if (str_contains($ff->ff_value_options, '{"table":')) {
                        $optionsData = json_decode($ff->ff_value_options, true);
                        $options = DB::table($optionsData['table'])
                            ->select($optionsData['column'])
                            ->distinct()
                            ->pluck($optionsData['column'])
                            ->toArray();
                    } else {
                        $options = json_decode($ff->ff_value_options, true);
                        if (!is_array($options)) {
                            $options = preg_split('/\r\n|\r|\n/', $ff->ff_value_options);
                        }
                    }
                @endphp
                <tr data-required-role="{{ $requiredRole }}" class="{{ $shouldDisable ? 'disabled-field' : '' }}">
                    <td class="label">
                        {{ $ff->ff_label }}
                        <span class="isrequired">{{ $isRequired && !$shouldDisable ? '*' : '' }}</span>
                        <small class="append-text">{{ $ff->ff_append_text ?? '' }}</small>
                    </td>
                    <td class="colon">:</td>
                    <td class="value-input">
                        @switch($component)
                            @case('text')
                                <input type="text" id="{{ $key }}" name="{{ $key }}"
                                    value="{{ e($value) }}" placeholder="{{ $placeholder }}" {{ $requiredAttr }}
                                    {{ $disabledAttr }}>
                            @break

                            @case('textarea')
                                <textarea id="{{ $key }}" name="{{ $key }}" placeholder="{{ $placeholder }}" {{ $requiredAttr }}
                                    {{ $disabledAttr }}>{{ e($value) }}</textarea>
                            @break

                            @case('select')
                                @if ($options && count($options) > 0)
                                    <select id="{{ $key }}" name="{{ $key }}" {{ $requiredAttr }}
                                        {{ $disabledAttr }}>
                                        <option value="">-- Select --</option>
                                        @foreach ($options as $opt)
                                            <option value="{{ e($opt) }}" @selected($opt == $value)>
                                                {{ e($opt) }}
                                            </option>
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
                                            <label class="{{ $shouldDisable ? 'disabled-option' : '' }}">
                                                <input type="radio" name="{{ $key }}" value="{{ e($opt) }}"
                                                    @checked($opt == $value) {{ $requiredAttr }} {{ $disabledAttr }}>
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
                                    $checkedValues = is_array($value) ? $value : explode(', ', $value);
                                @endphp
                                @if ($options && count($options) > 0)
                                    <div class="option-group"
                                        data-required-group="{{ $isRequired && !$shouldDisable ? 'true' : 'false' }}">
                                        @foreach ($options as $opt)
                                            <label class="{{ $shouldDisable ? 'disabled-option' : '' }}">
                                                <input type="checkbox" name="{{ $key }}[]"
                                                    value="{{ e($opt) }}" @checked(in_array(trim($opt), $checkedValues))
                                                    {{ $disabledAttr }}>
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
                                    value="{{ e($value) }}" {{ $requiredAttr }} {{ $disabledAttr }}>
                            @break

                            @case('datetime-local')
                                <input type="datetime-local" id="{{ $key }}" name="{{ $key }}"
                                    value="{{ e($value) }}" {{ $requiredAttr }} {{ $disabledAttr }}>
                            @break

                            @default
                                <input type="text" id="{{ $key }}" name="{{ $key }}"
                                    value="{{ e($value) }}" placeholder="{{ $placeholder }}" {{ $requiredAttr }}
                                    {{ $disabledAttr }}>
                        @endswitch

                        @if ($shouldDisable)
                            <div class="field-disabled-note mt-1">
                                <small class="text-muted">
                                    <i class="ti ti-lock me-1"></i>
                                    This field can only be filled by {{ $requiredRole }}
                                </small>
                            </div>
                        @endif
                    </td>
                </tr>
                @php $i++; @endphp
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
