@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-PostGrad System Notification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #f8f9fa;
            color: #2c3e50;
            line-height: 1.6;
            padding: 20px 0;
        }

        .email-wrapper {
            max-width: 700px;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #e8ecef;
        }

        /* Official Letterhead */
        .letterhead {
            color: white;
            padding: 30px 40px;
            text-align: center;
            position: relative;
        }

        .letterhead::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(90deg, #c9b037 0%, #f4e467 50%, #c9b037 100%);
        }

        .university-logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #000000;
            font-size: 24px;
        }

        .university-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 1px;
            color: #000000;

        }

        .system-name {
            font-size: 14px;
            opacity: 0.9;
            font-weight: normal;
            color: #000000;
        }

        /* Letter Content */
        .letter-content {
            padding: 40px 50px;
            background: white;
        }

        .letter-header {
            border-bottom: 2px solid #1e3c72;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .reference-number {
            font-size: 12px;
            color: #666;
            text-align: right;
            margin-bottom: 10px;
        }

        .letter-date {
            font-size: 14px;
            color: #666;
            text-align: right;
            margin-bottom: 20px;
        }

        .subject-line {
            font-weight: bold;
            font-size: 16px;
            color: #000000;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .activity-name {
            font-size: 14px;
            color: #666;
            font-style: italic;
        }

        /* Letter Body */
        .letter-body {
            margin: 25px 0;
        }

        .greeting {
            margin-bottom: 20px;
            font-size: 14px;
            color: #000000;
        }

        .recipient-name {
            font-weight: bold;
            color: #000000;
        }

        .letter-paragraph {
            margin-bottom: 18px;
            text-align: justify;
            font-size: 14px;
            line-height: 1.7;
        }

        .document-list {
            background: #f8f9fa;
            border-left: 4px solid #1e3c72;
            padding: 15px 20px;
            margin: 20px 0;
        }

        .document-list h4 {
            color: #000000;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .document-list ul {
            list-style: none;
            padding-left: 0;
        }

        .document-list li {
            padding: 8px 0;
            border-bottom: 1px dotted #ddd;
            font-size: 13px;
        }

        .document-list li:last-child {
            border-bottom: none;
        }

        .due-date {
            color: #e74c3c;
            font-weight: bold;
        }

        .highlight-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }

        .success-box {
            background: #d1edff;
            border: 1px solid #74b9ff;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }

        .warning-box {
            background: #ffe8e8;
            border: 1px solid #ff7675;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }

        /* Action Button */
        .action-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .action-button {
            display: inline-block;
            color: rgb(0, 0, 0) !important;
            text-decoration: none;
            padding: 12px 30px;
            border: 1px solid #1e3c72;
            border-radius: 25px;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 60, 114, 0.4);
        }

        /* Signature */
        .signature-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .closing {
            margin-bottom: 15px;
            font-size: 14px;
        }

        .signature {
            font-size: 14px;
            font-weight: bold;
            color: #000000;
        }

        .signature-title {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        /* Footer */
        .letter-footer {
            background: #f8f9fa;
            padding: 25px 50px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }

        .footer-divider {
            border-bottom: 2px solid #1e3c72;
            margin-bottom: 15px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .contact-info a {
            color: #1e3c72;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .email-wrapper {
                margin: 10px;
            }

            .letter-content {
                padding: 30px 25px;
            }

            .letter-footer {
                padding: 20px 25px;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
            }

            .email-wrapper {
                box-shadow: none;
                border: none;
            }

            .action-button {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <!-- Official Letterhead -->
        <div class="letterhead">
            <div class="university-name">UNIVERSITI TEKNIKAL MALAYSIA MELAKA</div>
            <div class="system-name">e-PostGrad System</div>
        </div>

        <!-- Letter Content -->
        <div class="letter-content">
            <!-- Letter Header -->
            <div class="letter-header">
                <div class="letter-date">{{ Carbon::now()->format('d F Y') }}</div>

                <div class="subject-line">
                    @if ($data['eType'] == 1)
                        OFFICIAL REMINDER: DOCUMENT SUBMISSION REQUIRED
                    @elseif($data['eType'] == 2)
                        NOTIFICATION: STUDENT SUBMISSION CONFIRMED
                    @elseif($data['eType'] == 3)
                        NOTIFICATION: SUBMISSION APPROVED
                    @elseif($data['eType'] == 4)
                        NOTIFICATION: SUBMISSION REQUIRES REVISION
                    @elseif($data['eType'] == 5)
                        NOTIFICATION: SUBMISSION REVERTED FOR ACTION
                    @elseif($data['eType'] == 6)
                        CONGRATULATIONS: ACTIVITY COMPLETED SUCCESSFULLY
                    @elseif($data['eType'] == 7)
                        NOTIFICATION: SUBMISSION PORTAL OPENED
                    @elseif($data['eType'] == 8)
                        NOTIFICATION: SUBMISSION PORTAL CLOSED
                    @endif
                </div>
                <div class="activity-name">Re: {{ $data['act_name'] }}</div>
            </div>

            <!-- Letter Body -->
            <div class="letter-body">
                <div class="greeting">
                    <strong>Assalamualaikum Warahmatullahi Wabarakatuh</strong><br>
                    Peace be upon you and good day,
                </div>

                <div class="greeting">
                    <span class="recipient-name">Dear {{ $data['name'] }}, </span><br>
                </div>

                @if ($data['eType'] == 1)
                    {{-- SUBMISSION REMINDER --}}
                    <p class="letter-paragraph">
                        We hope this official communication finds you in good health and spirits. This serves as a
                        formal reminder regarding your pending document submission for the above-referenced activity
                        through our e-PostGrad system.
                    </p>

                    <div class="document-list">
                        <h4>REQUIRED DOCUMENTS FOR SUBMISSION:</h4>
                        <ul>
                            @foreach ($data['document'] as $doc)
                                <li>
                                    <strong>{{ $doc->doc_name }}</strong><br>
                                    <span class="due-date">Submission Deadline:
                                        {{ Carbon::parse($doc->submission_duedate)->format('l, d F Y \a\t g:i A') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="highlight-box">
                        <strong>IMPORTANT:</strong> Please ensure that you click the <strong>"Confirm
                            Submission"</strong> button after uploading all required documents to complete the
                        submission process. Failure to do so will result in an incomplete submission.
                    </div>
                @elseif ($data['eType'] == 2)
                    {{-- STUDENT SUBMISSION CONFIRMED --}}
                    <p class="letter-paragraph">
                        We are writing to formally notify you that your supervisee,
                        <strong>{{ $data['student_name'] }}</strong> (Matric No: {{ $data['student_matricno'] }}), has
                        successfully confirmed the submission of their required documents for the above-referenced
                        activity.
                    </p>

                    <div class="success-box">
                        <strong>Submission Details:</strong><br>
                        Date & Time: {{ Carbon::parse($data['submission_date'])->format('l, d F Y \a\t g:i A') }}<br>
                        Activity: {{ $data['act_name'] }}<br>
                        Status: Confirmed and Awaiting Review
                    </div>

                    <p class="letter-paragraph">
                        We kindly request your prompt attention to review the submitted documents at your earliest
                        convenience through the e-PostGrad system.
                    </p>
                @elseif ($data['eType'] == 3)
                    {{-- STUDENT SUBMISSION APPROVED --}}
                    <p class="letter-paragraph">
                        We are delighted to inform you that your document submission for the above-referenced activity
                        has been <strong>APPROVED</strong>.
                    </p>

                    <div class="success-box">
                        <strong>Approval Details:</strong><br>
                        Approved by: <strong>{{ $data['approvalUser'] }}</strong><br>
                        Date & Time: {{ Carbon::parse($data['sa_date'])->format('l, d F Y \a\t g:i A') }}<br>
                        Activity: {{ $data['act_name'] }}<br>
                        Status: <span style="color: #27ae60; font-weight: bold;">APPROVED</span>
                    </div>

                    <p class="letter-paragraph">
                        Congratulations on this achievement. You may now proceed to the next phase of your postgraduate
                        journey as outlined in your study plan.
                    </p>
                @elseif ($data['eType'] == 4)
                    {{-- STUDENT SUBMISSION REJECTED --}}
                    <p class="letter-paragraph">
                        We regret to inform you that your document submission for the above-referenced activity requires
                        revision and has been returned for your attention.
                    </p>

                    <div class="warning-box">
                        <strong>Review Details:</strong><br>
                        Reviewed by: <strong>{{ $data['approvalUser'] }}</strong><br>
                        Date & Time: {{ Carbon::parse($data['sa_date'])->format('l, d F Y \a\t g:i A') }}<br>
                        Activity: {{ $data['act_name'] }}<br>
                        Status: <span style="color: #e74c3c; font-weight: bold;">REQUIRES REVISION</span>
                    </div>

                    <p class="letter-paragraph">
                        Please log in to the e-PostGrad system to review the feedback provided and take the necessary
                        corrective actions. We encourage you to consult with your supervisor if you require any
                        clarification.
                    </p>
                @elseif ($data['eType'] == 5)
                    {{-- STUDENT SUBMISSION REVERTED --}}
                    <p class="letter-paragraph">
                        This is to notify you that your submission for the above-referenced activity has been reverted
                        to allow for modifications and updates.
                    </p>

                    <div class="highlight-box">
                        <strong>REQUIRED ACTION:</strong> Please log in to the system, review the current status, make
                        the necessary updates to your documents, and reconfirm your submission.
                    </div>
                @elseif ($data['eType'] == 6)
                    {{-- ACTIVITY COMPLETED --}}
                    <p class="letter-paragraph">
                        It is with great pleasure that we congratulate you on the successful completion of your
                        <strong>{{ $data['act_name'] }}</strong> activity. This represents a significant milestone in
                        your postgraduate studies.
                    </p>

                    <div class="success-box">
                        <strong>🎉 CONGRATULATIONS! 🎉</strong><br>
                        Activity: {{ $data['act_name'] }}<br>
                        Status: <span style="color: #27ae60; font-weight: bold;">SUCCESSFULLY COMPLETED</span><br>
                        Completion Date: {{ Carbon::now()->format('d F Y') }}
                    </div>

                    <p class="letter-paragraph">
                        You are now eligible to proceed to the next activity in your study plan. If this represents the
                        completion of your final required activity, we extend our heartiest congratulations and wish you
                        continued success in your future endeavors.
                    </p>
                @elseif ($data['eType'] == 7)
                    {{-- STUDENT SUBMISSION OPENED --}}
                    <p class="letter-paragraph">
                        We are pleased to inform you that the submission portal for your
                        <strong>{{ $data['act_name'] }}</strong> activity has been officially opened and is now
                        available for your use.
                    </p>

                    <div class="success-box">
                        <strong>Portal Status:</strong> <span style="color: #27ae60; font-weight: bold;">OPEN FOR
                            SUBMISSION</span><br>
                        Activity: {{ $data['act_name'] }}<br>
                        Action Required: Upload documents and confirm submission
                    </div>
                @elseif ($data['eType'] == 8)
                    {{-- STUDENT SUBMISSION CLOSED --}}
                    <p class="letter-paragraph">
                        We regret to inform you that the submission portal for your
                        <strong>{{ $data['act_name'] }}</strong> activity has been closed and is no longer accepting
                        document uploads.
                    </p>

                    <div class="warning-box">
                        <strong>Portal Status:</strong> <span
                            style="color: #e74c3c; font-weight: bold;">CLOSED</span><br>
                        Activity: {{ $data['act_name'] }}<br>
                        Note: No further submissions can be processed at this time
                    </div>

                    <p class="letter-paragraph">
                        If you have any queries or require special consideration regarding this matter, please contact
                        the system administrator immediately.
                    </p>
                @endif

                <!-- Action Section -->
                <div class="action-section">
                    <p style="margin-bottom: 15px; font-size: 14px; color: #666;">
                        Access the e-PostGrad system using the link below:
                    </p>
                    <a href="{{ route('main-login') }}" class="action-button">
                        Log in to e-PostGrad System
                    </a>
                </div>

                <!-- Signature Section -->
                <div class="signature-section">
                    <p class="closing">
                        Thank you for your attention to this matter. Should you require any assistance, please do not
                        hesitate to contact our support team.
                    </p>

                    <p class="closing">
                        Yours sincerely,
                    </p>

                    <div class="signature">
                        e-PostGrad System Administrator
                    </div>
                    <div class="signature-title">
                        Universiti Teknikal Malaysia Melaka
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="letter-footer">
            <div class="footer-divider"></div>
            <div class="footer-content">
                <div>
                    &copy; {{ date('Y') }} e-PostGrad System, UTeM. All rights reserved.
                </div>
                <div class="contact-info">
                    Support: <a href="mailto:support@utem.edu.my">support@utem.edu.my</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
