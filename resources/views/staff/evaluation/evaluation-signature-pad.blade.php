<!DOCTYPE html>
<html lang="en">

<head>
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
    <!-- [jQuery Files] -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/style-preset.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>{{ $title }}</title>
</head>

<body>

    <!-- [ Alert ] start -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div id="toastContainer"></div>
    </div>
    <!-- [ Alert ] end -->

    <!-- [ Main Content ] start -->
    <div class="row">

        <!-- [ Evaluation Student ] start -->
        <div class="col-sm-12">
            <form
                action="{{ route('submit-evaluation-signature-data-post', ['evaluationID' => Crypt::encrypt($evaluationID), 'mode' => $mode]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card p-3">
                    <div class="card-body">
                        <div class="container">
                            <div id="formContainer"></div>
                        </div>
                    </div>
                    <div class="card-footer d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" id="confirmBtn" class="btn btn-danger">Confirmed & Sign</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- [ Evaluation Student ] end -->

    </div>
    <!-- [ Main Content ] end -->

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>

    <script type="text/javascript">
        /*********************************************************
         ***************GLOBAL FUNCTION & VARIABLES***************
         *********************************************************/
        function showToast(type, message) {
            const toastId = 'toast-' + Date.now();
            const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-info-circle';
            const bgClass = type === 'success' ? 'bg-light-success' : 'bg-light-danger';
            const txtClass = type === 'success' ? 'text-success' : 'text-danger';
            const colorClass = type === 'success' ? 'success' : 'danger';
            const title = type === 'success' ? 'Success' : 'Error';

            const toastHtml = `
                    <div id="${toastId}" class="toast border-0 shadow-sm mb-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                        <div class="toast-body text-white ${bgClass} rounded d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0 ${txtClass}">
                                    <i class="${iconClass} me-2"></i> ${title}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <p class="mb-0 ${txtClass}">${message}</p>
                        </div>
                    </div>
                `;

            $('#toastContainer').append(toastHtml);
            const toastEl = new bootstrap.Toast(document.getElementById(toastId));
            toastEl.show();
        }

        /*********************************************************/
        /***************GLOBAL FUNCTION & VARIABLES***************/
        /*********************************************************/

        initSignaturePads();
        getEvaluationForm();


        /*********************************************************/
        /***************SIGNATURE PADS FUNCTION*******************/
        /*********************************************************/

        const signaturePads = {};

        function initSignaturePads() {
            document.querySelectorAll('.signature-canvas').forEach(canvas => {
                const sigId = canvas.getAttribute('data-id');

                // Skip already initialized canvases
                if (signaturePads[sigId]) return;

                // High-DPI setup
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);
                ctx.lineWidth = 2;

                // Initialize signature pad
                signaturePads[sigId] = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255,255,255,1)',
                    penColor: 'black',
                    minWidth: 1,
                    maxWidth: 3,
                    throttle: 16
                });

                console.log('Initialized signature pad:', sigId);
            });
        }

        $(document).ajaxComplete(function() {
            setTimeout(initSignaturePads, 100);
        });

        $(document).on('click', '.signature-clear-btn', function() {
            const sigId = $(this).data('id');
            if (signaturePads[sigId]) {
                signaturePads[sigId].clear();
                console.log('Cleared signature:', sigId);
            }
        });

        /*********************************************************/
        /**********************GETTERS FUNCTION*******************/
        /*********************************************************/
        function getEvaluationForm() {
            $.ajax({
                url: "{{ route('get-evaluation-signature-form-get') }}",
                type: "GET",
                data: {
                    _token: "{{ csrf_token() }}",
                    evaid: "{{ $evaluationID }}",
                    sigkey: "{{ $signature_key }}",
                    afid: "{{ $form->id }}",
                    mode: "{{ $mode }}"
                },
                beforeSend: function() {
                    $('#formContainer').html(
                        '<div class="text-center py-4"><i class="ti ti-loader spin me-2"></i>Loading form...</div>'
                    );
                },
                success: function(response) {
                    $('#formContainer').html(response.html);
                },
                error: function() {
                    $('#formContainer').html(
                        '<div class="alert alert-danger">Error loading form</div>');
                }
            });
        }


        /*********************************************************/
        /******************FORM SUBMIT FUNCTION*******************/
        /*********************************************************/

        $('#confirmBtn').on('click', function(e) {
            e.preventDefault();
            if (confirm(
                    "Are you sure?\n\nBy submitting this, you confirm that the signature provided is valid, created by you, and you fully acknowledge its authenticity."
                    )) {
                $('form').submit();
            }
        });

        $('.notebook-container').each(function() {
            const container = $(this);
            const linesContainer = container.find('.notebook-lines');
            const textarea = container.find('.notebook-textarea');

            function generateLines() {
                const lineHeight = parseInt(textarea.css('line-height'));
                const containerHeight = container.height();
                const lineCount = Math.ceil(containerHeight / lineHeight) + 20; // extra lines
                linesContainer.empty();
                for (let i = 0; i < lineCount; i++) {
                    $('<div>')
                        .css({
                            'border-bottom': '1px solid #000',
                            'height': (lineHeight - 1) + 'px'
                        })
                        .appendTo(linesContainer);
                }
            }

            // Initial render
            generateLines();

            // Regenerate on window resize
            $(window).on('resize', generateLines);

            // Sync scroll
            container.on('scroll', function() {
                linesContainer.css('transform', `translateY(-${container.scrollTop()}px)`);
            });
        });

        $('form').on('submit', function(e) {
            const mode = "{{ $mode }}";
            let isValid = true;
            const errorMessages = [];
            const errorFields = [];

            // ==============================================
            // 1. Validate regular required fields (UPDATED)
            // ==============================================
            $('[required]').each(function() {
                const $field = $(this);
                const fieldType = $field.attr('type');
                let isEmpty = false;

                if (fieldType === 'checkbox') {
                    const groupName = $field.attr('name');
                    const checkedCount = $(`input[name="${groupName}"]:checked`).length;
                    isEmpty = checkedCount === 0;
                } else if (fieldType === 'radio') {
                    const groupName = $field.attr('name');
                    const checkedCount = $(`input[name="${groupName}"]:checked`).length;
                    isEmpty = checkedCount === 0;
                } else {
                    isEmpty = !$field.val().trim();
                }

                if (isEmpty) {
                    isValid = false;
                    $field.addClass('error-field');
                    const fieldLabel = $field.closest('tr').find('.label').text().replace('*', '').trim();
                    if (fieldType === 'radio') {
                        if (!errorFields.some(f => f.includes(fieldLabel))) {
                            errorFields.push(fieldLabel);
                        }
                    } else {
                        errorFields.push(fieldLabel);
                    }
                }
            });

            const examinerSignatureKeys = @json($examinerSign->pluck('ff_signature_key')->toArray());

            // ==============================================
            // 2. Validate signatures (unchanged)
            // ==============================================
            $('.signature-canvas').each(function() {
                const $canvas = $(this);
                const sigId = $canvas.data('id');
                const sigRole = $canvas.data('role');
                const pad = signaturePads[sigId];
                const $input = $('#signatureData-' + sigId);
                let isSignatureRequired = false;

                if (mode == 1 && (sigRole == "sv_signature" || sigRole == "cosv_signature")) {
                    isSignatureRequired = true;
                } else if (mode == 2 && sigRole == "comm_signature") {
                    isSignatureRequired = true;
                } else if (mode == 3 && sigRole == "deputy_dean_signature") {
                    isSignatureRequired = true;
                } else if (mode == 4 && sigRole == "dean_signature") {
                    isSignatureRequired = true;
                } else if (mode == 5 && examinerSignatureKeys.includes(sigRole)) {
                    isSignatureRequired = true;
                } else if (mode == 6) {
                    isSignatureRequired = true;
                }

                if (pad && !pad.isEmpty()) {
                    $input.val(pad.toDataURL('image/png'));
                } else if (isSignatureRequired) {
                    isValid = false;
                    $canvas.css('border-color', 'red');
                    const signatureLabel = $canvas.closest('.signature-cell').find('.signature-label-clean')
                        .text().trim();
                    errorMessages.push(`${signatureLabel} signature is required`);
                }
            });

            // ==============================================
            // 3. Prevent submission if validation fails
            // ==============================================
            if (!isValid) {
                e.preventDefault();

                let fullMessage = '';

                if (errorFields.length > 0) {
                    fullMessage += 'Please complete these required fields:\n- ' + errorFields.join('\n- ');
                }

                if (errorMessages.length > 0) {
                    if (fullMessage) fullMessage += '\n\n';
                    fullMessage += 'Signature:\n- ' + errorMessages.join('\n- ');
                }

                if (fullMessage) {
                    if (typeof showToast === 'function') {
                        showToast('danger', fullMessage, {
                            duration: 10000,
                            position: 'top-right'
                        });

                        $('html, body').animate({
                            scrollTop: $('.error-field').first().offset().top - 100
                        }, 500);
                    } else {
                        alert(fullMessage);
                    }
                }
            }

            $(document).on('change', '.error-field', function() {
                $(this).removeClass('error-field');
            });
        });
    </script>

</body>

</html>
