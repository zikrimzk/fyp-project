@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Crypt;

    $currentMode = $mode;
    $examinerKeys = $formfields->where('ff_signature_role', 8)->pluck('ff_signature_key')->toArray() ?? [];
@endphp

<title>{{ $title }}</title>

<style>
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
        font-size: 12pt;
        word-wrap: break-word;
        white-space: pre-line;
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
        border-bottom: 1px solid #000;
        margin-bottom: 10px;
        display: flex;
        justify-content: center;
        align-items: flex-end;
    }

    .signature-img-clean {
        max-height: 80px;
        max-width: 100%;
        object-fit: contain;
    }

    .signature-label-clean {
        font-weight: bold;
        font-size: 11pt;
        margin-top: 5px;
    }

    .signature-date-clean {
        font-size: 10.5pt;
        margin-top: 3px;
    }

    .date-label,
    .name-label {
        display: block;
    }

    p {
        margin: 0;
    }

    .disabled-field {
        opacity: 0.7;
        background-color: #f9f9f9;
    }

    .disabled-field .label,
    .disabled-field .colon,
    .disabled-field .value-input {
        color: #6c757d;
    }

    .disabled-option {
        color: #6c757d;
        cursor: not-allowed;
    }

    input:disabled,
    select:disabled,
    textarea:disabled {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    .field-disabled-note {
        font-size: 0.85rem;
    }

    .disabled-signature {
        opacity: 0.7;
    }

    .disabled-signature canvas {
        background-color: #f8f9fa;
        cursor: not-allowed;
        pointer-events: none;
    }

    .disabled-signature .signature-clear-btn {
        opacity: 0.6;
        pointer-events: none;
    }

    .signature-disabled-note {
        font-size: 0.85rem;
        text-align: center;
        margin-top: 5px;
    }

    .special-label {
        display: block;
        font-size: 12pt;
    }

    .special-label label {
        margin-bottom: 10px;
    }


    .notebook-container {
        position: relative;
        width: 100%;
        height: 600px;
        border: 1px solid #ccc;
        overflow: auto;
        background-color: #fff;
    }

    .notebook-lines {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 100%;
        pointer-events: none;
    }

    .notebook-textarea {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        padding: 10px;
        border: none;
        background: transparent;
        font-family: Arial, sans-serif;
        font-size: 12pt;
        line-height: 40px;
        resize: none;
        overflow: auto;
    }

    .notebook-textarea:focus {
        outline: none;
    }

    @media only screen and (max-width: 768px) {

        .header-table,
        .info-table,
        .signature-table {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .header-table thead,
        .info-table thead,
        .signature-table thead,
        .header-table tbody,
        .info-table tbody,
        .signature-table tbody,
        .header-table tr,
        .info-table tr,
        .signature-table tr,
        .header-table td,
        .info-table td,
        .signature-table td {
            display: block;
            width: 100%;
        }

        .logo-cell,
        .text-cell {
            text-align: center;
            display: block;
            width: 100%;
        }

        .faculty-logo {
            width: 80px;
            margin-bottom: 10px;
        }

        .form-title {
            font-size: 11pt;
        }


        .colon {
            display: none !important;
        }

        .label,
        .colon,
        .value,
        .value-input {
            display: block;
            width: 100% !important;
        }

        .value-input input,
        .value-input textarea,
        .value-input select {
            width: 100% !important;
        }

        .option-group label {
            display: block;
            margin: 4px 0;
        }

        .signature-cell {
            width: 100%;
            padding: 10px 0;
        }

        .signature-box-clean {
            height: 80px;
        }

        .signature-label-clean,
        .signature-date-clean {
            font-size: 10pt;
        }

        .signature-canvas {
            display: block;
            touch-action: none;
            width: 100% !important;
            height: 150px !important;
        }

        .section-header {
            font-size: 11pt;
        }

        .label span.isrequired {
            display: inline-block;
            margin-left: 3px;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        input[type="datetime-local"],
        input[type="password"],
        textarea,
        select {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            font-size: 11pt;
            padding: 10px;
        }

        .value-input input,
        .value-input textarea,
        .value-input select {
            width: 100% !important;
            max-width: 100% !important;
        }

        /* For radio and checkbox - make tap-friendly */
        input[type="radio"],
        input[type="checkbox"] {
            display: inline-block;
            margin: 0;
        }

        .option-group label {
            font-size: 11pt;
            margin: 4px 0;
            display: flex;
            align-items: center;
        }

        /* For labels before inputs */
        label {
            font-size: 11pt;
            margin-bottom: 5px;
        }

        /* Specific for signature canvas */
        .signature-canvas {
            width: 100% !important;
            height: 150px !important;
        }

        /* Notebook textarea mobile adjustments */
        .notebook-textarea {
            min-height: 300px;
            font-size: 11pt;
            line-height: 2.2;
            background-size: 100% 36px;
            padding-left: 15px;
        }
    }
</style>

<!-- Header -->
<div class="header">
    <table class="header-table" width="100%">
        <tr>
            <td class="logo-cell">
                @if ($faculty->fac_logo && file_exists(public_path('storage/' . $faculty->fac_logo)))
                    <img src="{{ '../storage/' . urlencode($faculty->fac_logo) }}" alt="{{ $faculty->fac_code }} LOGO"
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
        @if ($ff->ff_category == 1 && $ff->ff_component_type == 'longtextarea')
            <!-- CATEGORY : INPUT -->
            @php
                $component = strtolower($ff->ff_component_type);
                $key = str_replace(' ', '_', strtolower($ff->ff_label));
                $value = old($key, $userData[$key] ?? '');
                $placeholder = $ff->ff_placeholder ?? '';

                // Get required role as integer
                $requiredRole = $ff->ff_component_required_role;

                // Check if field is required for current mode
                $isRequired = $ff->ff_component_required == 1 && ($requiredRole == 0 || $requiredRole == $currentMode);

                // Determine if field should be disabled
                $shouldDisable = $requiredRole != 0 && $requiredRole != $currentMode;
                $disabledAttr = $shouldDisable ? 'disabled' : '';
                $requiredAttr = $isRequired && !$shouldDisable ? 'required' : '';

                // Role names for display
                $roleNames = [
                    0 => 'all',
                    1 => 'supervisors',
                    2 => 'committee',
                    3 => 'deputy dean',
                    4 => 'dean',
                    5 => 'examiner/panel',
                    6 => 'chairman',
                ];

            @endphp
            <tr data-required-role="{{ $ff->ff_component_required_role }}"
                class="{{ $shouldDisable ? 'disabled-field' : '' }}">
                <td colspan="3">
                    <div class="long-textarea-field special-label">
                        <label for="{{ $key }}">
                            {{ $ff->ff_label }}
                            <span class="isrequired">{{ $ff->ff_component_required == 1 ? '*' : '' }}</span>
                            <small class="append-text">{{ $ff->ff_append_text ?? '' }}</small>
                        </label>
                        <div class="notebook-container">
                            <div class="notebook-lines"></div>
                            <textarea class="notebook-textarea" id="{{ $key }}" name="{{ $key }}"
                                placeholder="{{ $placeholder }}" {{ $requiredAttr }} {{ $disabledAttr }}>{{ e($value) }}</textarea>
                        </div>
                        @if ($shouldDisable)
                            <div class="field-disabled-note mt-1">
                                <small class="text-muted">
                                    <i class="ti ti-lock me-1"></i>
                                    This field can only be filled by
                                    {{ $roleNames[$ff->ff_component_required_role] ?? 'authorized role' }}
                                </small>
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
            @php $i++; @endphp
        @elseif ($ff->ff_category == 1)
            <!-- CATEGORY : INPUT -->
            @php
                $component = strtolower($ff->ff_component_type);
                $key = str_replace(' ', '_', strtolower($ff->ff_label));
                $value = old($key, $userData[$key] ?? '');
                $placeholder = $ff->ff_placeholder ?? '';

                // Get required role as integer
                $requiredRole = $ff->ff_component_required_role;

                // Check if field is required for current mode
                $isRequired = $ff->ff_component_required == 1 && ($requiredRole == 0 || $requiredRole == $currentMode);

                // Determine if field should be disabled
                $shouldDisable = $requiredRole != 0 && $requiredRole != $currentMode;
                $disabledAttr = $shouldDisable ? 'disabled' : '';
                $requiredAttr = $isRequired && !$shouldDisable ? 'required' : '';
                // dd($shouldDisable, $disabledAttr, $isRequired, $requiredAttr);

                // Role names for display
                $roleNames = [
                    0 => 'all',
                    1 => 'supervisors',
                    2 => 'committee',
                    3 => 'deputy dean',
                    4 => 'dean',
                    5 => 'examiner/panel',
                    6 => 'chairman',
                ];

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
                                            <input type="checkbox" name="{{ $key }}[]" value="{{ e($opt) }}"
                                                @checked(in_array(trim($opt), $checkedValues)) {{ $disabledAttr }}>
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
                                This field can only be filled by {{ $roleNames[$requiredRole] }}
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
                <td class="value">{!! nl2br(e($value)) !!}</td>
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
            <!-- Signature Table Display -->
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
                                    @php
                                        $sigId = $sig->id;
                                        $sigKey = $sig->ff_signature_key;
                                        $sigDateKey = $sig->ff_signature_date_key;
                                        $isApproved =
                                            $signatureData &&
                                            $sigKey &&
                                            isset($signatureData->{$sigKey . '_is_cross_approval'}) &&
                                            $signatureData->{$sigKey . '_is_cross_approval'};

                                        // Determine if signature should be disabled based on mode and key
                                        $shouldDisableSig = true;
                                        $role = 0;

                                        if (
                                            $currentMode == 1 &&
                                            in_array($sigKey, ['sv_signature', 'cosv_signature'])
                                        ) {
                                            $shouldDisableSig = false;
                                        } elseif ($currentMode == 2 && $sigKey == 'comm_signature') {
                                            $shouldDisableSig = false;
                                        } elseif ($currentMode == 3 && $sigKey == 'deputy_dean_signature') {
                                            $shouldDisableSig = false;
                                        } elseif ($currentMode == 4 && $sigKey == 'dean_signature') {
                                            $shouldDisableSig = false;
                                        } elseif ($currentMode == 5 && in_array($sigKey, $examinerKeys)) {
                                            $shouldDisableSig = false;
                                        } elseif ($currentMode == 6) {
                                            $shouldDisableSig = false;
                                        }

                                        $sigRoleNames = [
                                            'sv_signature' => 'Supervisors',
                                            'cosv_signature' => 'Supervisors',
                                            'comm_signature' => 'Committee',
                                            'deputy_dean_signature' => 'Deputy Dean',
                                            'dean_signature' => 'Dean',
                                            'chairman_signature' => 'Chairman',
                                        ];

                                        if (!isset($sigRoleNames[$sigKey]) && in_array($sigKey, $examinerKeys)) {
                                            $sigRoleNames[$sigKey] = 'Examiner / Panel';
                                        }
                                    @endphp
                                    <td
                                        class="signature-cell @if ($shouldDisableSig) disabled-signature @endif">
                                        @if ($isApproved)
                                            <div class="signature-box-clean-text">
                                                Approved by {{ $signatureData->{$sigKey . '_role'} ?? '-' }}
                                            </div>
                                            <div class="signature-label-clean">{{ $sig->ff_label }}</div>
                                            <div class="signature-date-clean">
                                                <span
                                                    class="name-label">{{ $signatureData->{$sigKey . '_name'} ?? '( NAME_OF_APPROVER )' }}</span>
                                                <span
                                                    class="date-label">{{ $signatureData->{$sigDateKey} ?? '( DATE_OF_APPROVAL )' }}</span>
                                            </div>
                                        @else
                                            <div class="signature-canvas-container">
                                                @if ($signatureData && $sigKey && isset($signatureData->{$sigKey}))
                                                    <div class="signature-box-clean">
                                                        <img src="{{ $signatureData->{$sigKey} }}"
                                                            class="signature-img-clean">
                                                    </div>
                                                @else
                                                    <div class="signature-canvas-wrapper">
                                                        <canvas class="signature-canvas"
                                                            style="border:1px solid #000000; border-radius:10px; width:100%; height:200px;"
                                                            data-id="{{ $sigId }}"
                                                            data-role="{{ $sigKey }}"
                                                            @if ($shouldDisableSig) disabled @endif>
                                                        </canvas>
                                                        <input type="hidden"
                                                            name="signatureData[{{ $sigKey }}]"
                                                            id="signatureData-{{ $sigId }}"
                                                            @if ($shouldDisableSig) disabled @endif>
                                                    </div>
                                                    <button type="button"
                                                        class="btn btn-sm btn-light mt-2 w-100 signature-clear-btn"
                                                        data-id="{{ $sigId }}"
                                                        @if ($shouldDisableSig) disabled @endif>
                                                        <i class="ti ti-eraser me-2"></i> Clear Signature
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="signature-label-clean">{{ $sig->ff_label }}</div>
                                            <div class="signature-date-clean">
                                                <span
                                                    class="name-label">{{ $signatureData->{$sigKey . '_name'} ?? '( NAME_OF_APPROVER )' }}</span>
                                                <span
                                                    class="date-label">{{ $signatureData->{$sigDateKey} ?? '( DATE_OF_APPROVAL )' }}</span>
                                            </div>
                                            @if ($shouldDisableSig)
                                                <div class="signature-disabled-note mt-1">
                                                    <small class="text-muted">
                                                        <i class="ti ti-lock me-1"></i>
                                                        This signature can only be provided by
                                                        {{ $sigRoleNames[$sigKey] ?? 'authorized role' }}
                                                    </small>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    </td>
                </tr>
            @endforeach
        @endif
    @endwhile
</table>

<script></script>


{{-- @php
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
                                    @php
                                        $sigId = $sig->id;
                                        $sigKey = $sig->ff_signature_key;
                                        $sigDateKey = $sig->ff_signature_date_key;
                                        $isApproved =
                                            $signatureData &&
                                            $sigKey &&
                                            isset($signatureData->{$sigKey . '_is_cross_approval'}) &&
                                            $signatureData->{$sigKey . '_is_cross_approval'};
                                    @endphp
                                    <td class="signature-cell">
                                        @if ($isApproved)
                                            <div class="signature-box-clean-text">
                                                Approved by {{ $signatureData->{$sigKey . '_role'} ?? '-' }}
                                            </div>
                                            <div class="signature-label-clean">{{ $sig->ff_label }}</div>
                                            <div class="signature-date-clean">
                                                <span
                                                    class="name-label">{{ $signatureData->{$sigKey . '_name'} ?? '( NAME_OF_APPROVER )' }}</span>
                                                <span
                                                    class="date-label">{{ $signatureData->{$sigDateKey} ?? '( DATE_OF_APPROVAL )' }}</span>
                                            </div>
                                        @else
                                            <div class="signature-canvas-container">
                                                @if ($signatureData && $sigKey && isset($signatureData->{$sigKey}))
                                                    <div class="signature-box-clean">
                                                        <img src="{{ $signatureData->{$sigKey} }}"
                                                            class="signature-img-clean">
                                                    </div>
                                                @else
                                                    <div class="signature-canvas-wrapper">
                                                        <canvas class="signature-canvas"
                                                            style="border:1px solid #000000; border-radius:10px; width:100%; height:200px;"
                                                            data-id="{{ $sigId }}"
                                                            data-role="{{ $sigKey }}"></canvas>
                                                        <input type="hidden" name="signatureData[{{ $sigKey }}]"
                                                            id="signatureData-{{ $sigId }}">
                                                    </div>
                                                    <button type="button"
                                                        class="btn btn-sm btn-light mt-2 w-100 signature-clear-btn"
                                                        data-id="{{ $sigId }}">
                                                        <i class="ti ti-eraser me-2"></i> Clear Signature
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="signature-label-clean">{{ $sig->ff_label }}</div>
                                            <div class="signature-date-clean">
                                                <span
                                                    class="name-label">{{ $signatureData->{$sigKey . '_name'} ?? '( NAME_OF_APPROVER )' }}</span>
                                                <span
                                                    class="date-label">{{ $signatureData->{$sigDateKey} ?? '( DATE_OF_APPROVAL )' }}</span>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    </td>
                </tr>
            @endforeach --}}
