<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-PostGrad Account Notification</title>
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

        .credentials-box {
            background: #f8f9fa;
            border-left: 4px solid #1e3c72;
            padding: 15px 20px;
            margin: 20px 0;
        }

        .credentials-box h4 {
            color: #000000;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .credentials-box code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #1e3c72;
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

        .info-box {
            background: #e8f4f8;
            border: 1px solid #74b9ff;
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
                <div class="letter-date">{{ date('d F Y') }}</div>

                <div class="subject-line">
                    @if ($data['eType'] == 1)
                        ACCOUNT REGISTRATION CONFIRMATION
                    @elseif($data['eType'] == 2)
                        ACCOUNT INACTIVATION NOTICE
                    @elseif($data['eType'] == 3)
                        PASSWORD RESET REQUEST
                    @elseif($data['eType'] == 4)
                        PASSWORD RESET CONFIRMATION
                    @endif
                </div>
                <div class="activity-name">e-PostGrad System Account Management</div>
            </div>

            <!-- Letter Body -->
            <div class="letter-body">
                <div class="greeting">
                    <strong>Assalamualaikum Warahmatullahi Wabarakatuh</strong><br>
                    Peace be upon you and good day,
                </div>

                <div class="greeting">
                    <span class="recipient-name">Dear {{ $data['name'] }},</span><br>
                    Universiti Teknikal Malaysia Melaka
                </div>

                @if ($data['eType'] == 1)
                    {{-- ACCOUNT REGISTRATION --}}
                    <p class="letter-paragraph">
                        We are pleased to inform you that your account has been successfully created in the e-PostGrad
                        system. This system will serve as your primary platform for managing all postgraduate-related
                        submissions and communications.
                    </p>

                    <div class="success-box">
                        <strong>ACCOUNT SUCCESSFULLY CREATED</strong><br>
                        Registration Date: {{ date('l, d F Y') }}<br>
                        Account Status: <span style="color: #27ae60; font-weight: bold;">ACTIVE</span>
                    </div>

                    <div class="credentials-box">
                        <h4>LOGIN CREDENTIALS:</h4>
                        @if ($data['uType'] == 1)
                            <p><strong>Username:</strong> Your registered student email address</p>
                            <p><strong>Default Password:</strong> <code>pg@matricno</code></p>
                            <p style="font-size: 12px; color: #666; margin-top: 10px;">
                                <em>Note: Please replace "matricno" with your actual matriculation number</em>
                            </p>
                        @elseif($data['uType'] == 2)
                            <p><strong>Username:</strong> Your registered staff email address</p>
                            <p><strong>Default Password:</strong> <code>pg@staffid</code></p>
                            <p style="font-size: 12px; color: #666; margin-top: 10px;">
                                <em>Note: Please replace "staffid" with your actual staff identification number</em>
                            </p>
                        @endif
                    </div>

                    <div class="highlight-box">
                        <strong>SECURITY REMINDER:</strong> For your account security, we strongly recommend that you
                        change your password immediately after your first login. Please ensure your new password is
                        strong and unique.
                    </div>

                    <p class="letter-paragraph">
                        Welcome to the e-PostGrad system! This platform will facilitate efficient management of your
                        postgraduate documentation and communication processes.
                    </p>
                @elseif ($data['eType'] == 2)
                    {{-- ACCOUNT DEACTIVATION --}}
                    <p class="letter-paragraph">
                        We regret to inform you that your e-PostGrad system account has been inactivated effective
                        immediately.
                    </p>

                    <div class="warning-box">
                        <strong>ACCOUNT STATUS:</strong> <span
                            style="color: #e74c3c; font-weight: bold;">INACTIVATED</span><br>
                        Effective Date: {{ date('l, d F Y') }}<br>
                        Access: No longer available
                    </div>

                    <p class="letter-paragraph">
                        If you believe this inactivation has been made in error or if you require clarification
                        regarding this matter, please contact the system administrator or the postgraduate office
                        immediately.
                    </p>

                    <div class="info-box">
                        <strong>NEXT STEPS:</strong> Please contact the postgraduate administrator for assistance with account
                        reactivation procedures if you believe this action was taken in error.
                    </div>
                @elseif ($data['eType'] == 3)
                    {{-- PASSWORD RESET REQUEST --}}
                    <p class="letter-paragraph">
                        We have received a request to reset the password for your e-PostGrad system account. This
                        request was initiated from your registered email address.
                    </p>

                    <div class="info-box">
                        <strong>Reset Request Details:</strong><br>
                        Request Date: {{ date('l, d F Y \a\t g:i A') }}<br>
                        Account: {{ $data['name'] }}<br>
                        Status: Pending your action
                    </div>

                    <p class="letter-paragraph">
                        If you did not initiate this password reset request, please disregard this email and ensure your
                        account security by monitoring for any unauthorized access attempts.
                    </p>

                    <div class="highlight-box">
                        <strong>IMPORTANT SECURITY NOTICE:</strong> This password reset link will expire in <strong>1
                            hour</strong> from the time this email was sent. If you need to reset your password after
                        this time, you will need to submit a new request.
                    </div>
                @elseif ($data['eType'] == 4)
                    {{-- PASSWORD RESET CONFIRMATION --}}
                    <p class="letter-paragraph">
                        We are pleased to confirm that your password has been successfully updated in the e-PostGrad
                        system. Your account security has been enhanced with the new credentials.
                    </p>

                    <div class="success-box">
                        <strong>PASSWORD SUCCESSFULLY UPDATED</strong><br>
                        Update Date: {{ date('l, d F Y \a\t g:i A') }}<br>
                        Account: {{ $data['name'] }}<br>
                        Status: <span style="color: #27ae60; font-weight: bold;">SECURE & READY</span>
                    </div>

                    <p class="letter-paragraph">
                        You can now log in to the e-PostGrad system using your new password. Please ensure that you keep
                        your login credentials secure and do not share them with unauthorized individuals.
                    </p>

                    <div class="info-box">
                        <strong>SECURITY BEST PRACTICES:</strong>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>Use a strong, unique password</li>
                            <li>Do not share your credentials with others</li>
                            <li>Log out properly after each session</li>
                            <li>Report any suspicious account activity immediately</li>
                        </ul>
                    </div>
                @endif

                <!-- Action Section -->
                @if (isset($data['link']))
                    <div class="action-section">
                        <p style="margin-bottom: 15px; font-size: 14px; color: #666;">
                            @if ($data['eType'] == 1 || $data['eType'] == 4)
                                Access your e-PostGrad account using the link below:
                            @elseif ($data['eType'] == 3)
                                Click the button below to reset your password:
                            @endif
                        </p>
                        <a href="{{ $data['link'] }}" class="action-button">
                            @if ($data['eType'] == 1 || $data['eType'] == 4)
                                Log in to e-PostGrad System
                            @elseif ($data['eType'] == 3)
                                Reset My Password
                            @endif
                        </a>
                    </div>
                @endif

                <!-- Signature Section -->
                <div class="signature-section">
                    <p class="closing">
                        Thank you for your attention to this matter. Should you require any assistance or have questions
                        regarding your account, please do not hesitate to contact our support team.
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
