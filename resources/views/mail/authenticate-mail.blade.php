<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-PostGrad Account Notification</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Roboto:wght@300;400;500;700&display=swap');

        /* Base Body and Wrapper Styles */
        body {
            font-family: 'Merriweather', serif;
            background-color: #f0f2f5;
            color: #2c3e50;
            line-height: 1.6;
            padding: 40px 0;
            margin: 0;
        }

        .email-wrapper {
            max-width: 700px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid #e8ecef;
        }

        /* Official Letterhead */
        .letterhead {
            background-color: rgba(52, 58, 64, 255);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        
        .letterhead-divider {
            height: 5px;
            background: linear-gradient(90deg, #c9b037 0%, #f4e467 50%, #c9b037 100%);
        }

        .university-name {
            font-family: 'Roboto', sans-serif;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #f8f9fa;
        }

        .system-name {
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            font-weight: 300;
            opacity: 0.9;
            color: #ced4da;
        }

        /* Letter Content */
        .letter-content {
            padding: 40px 50px;
            background: #ffffff;
        }

        .letter-header {
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 25px;
            margin-bottom: 30px;
        }

        .letter-date {
            font-size: 13px;
            color: #7f8c8d;
            text-align: right;
            margin-bottom: 20px;
            font-family: 'Roboto', sans-serif;
        }

        .subject-line {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            font-size: 18px;
            color: rgba(52, 58, 64, 255);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .activity-name {
            font-size: 15px;
            color: #7f8c8d;
            font-style: italic;
            font-family: 'Merriweather', serif;
        }

        /* Letter Body */
        .letter-body {
            margin: 25px 0;
        }

        .greeting {
            margin-bottom: 20px;
            font-size: 15px;
            color: #000000;
        }

        .recipient-name {
            font-weight: bold;
            color: rgba(52, 58, 64, 255);
        }

        .letter-paragraph {
            margin-bottom: 18px;
            text-align: justify;
            font-size: 15px;
            line-height: 1.8;
        }

        /* Notification Boxes */
        .box {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 14px;
            line-height: 1.6;
        }

        .credentials-box {
            background: #f8f9fa;
            border-left: 5px solid rgba(52, 58, 64, 255);
            padding: 20px 25px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .credentials-box h4 {
            color: rgba(52, 58, 64, 255);
            margin-bottom: 15px;
            font-size: 15px;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
        }

        .credentials-box p {
            margin-bottom: 10px;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
        }

        .credentials-box code {
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #34495e;
            font-weight: bold;
        }

        .highlight-box {
            background-color: #fffbe6;
            border-left: 4px solid #f4d974;
            color: #8c7437;
        }

        .success-box {
            background-color: #e8f5e9;
            border-left: 4px solid #43a047;
            color: #2e7d32;
        }

        .warning-box {
            background-color: #fbe9e7;
            border-left: 4px solid #e53935;
            color: #d32f2f;
        }

        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #1e88e5;
            color: #1565c0;
        }
        
        /* Action Button */
        .action-section {
            text-align: center;
            margin: 40px 0;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px dashed #ced4da;
        }

        .action-button {
            display: inline-block;
            background-color: rgba(52, 58, 64, 255);
            color: #ffffff !important;
            text-decoration: none;
            padding: 15px 35px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 58, 64, 0.3);
        }

        .action-button:hover {
            background-color: #2b3035;
        }

        /* Signature */
        .signature-section {
            margin-top: 50px;
            padding-top: 25px;
            border-top: 1px solid #e0e0e0;
        }

        .closing {
            margin-bottom: 15px;
            font-size: 15px;
        }

        .signature {
            font-size: 15px;
            font-weight: bold;
            color: rgba(52, 58, 64, 255);
            font-family: 'Roboto', sans-serif;
        }

        .signature-title {
            font-size: 13px;
            color: #7f8c8d;
            margin-top: 5px;
            font-family: 'Roboto', sans-serif;
        }

        /* Footer */
        .letter-footer {
            background: rgba(52, 58, 64, 255);
            color: #ecf0f1;
            padding: 30px 50px;
            font-size: 12px;
            font-family: 'Roboto', sans-serif;
            text-align: center;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .contact-info a {
            color: #f4e467;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 20px 0;
            }
            .email-wrapper {
                margin: 0 10px;
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
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="letterhead">
            <div class="university-name">UNIVERSITI TEKNIKAL MALAYSIA MELAKA</div>
            <div class="system-name">e-PostGrad System</div>
        </div>
        <div class="letterhead-divider"></div>

        <div class="letter-content">
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

            <div class="letter-body">
                <div class="greeting">
                    <strong>Assalamualaikum Warahmatullahi Wabarakatuh</strong><br>
                    Peace be upon you and good day,
                </div>

                <div class="greeting">
                    <span class="recipient-name">Dear {{ $data['name'] }}, </span><br>
                </div>

                @if ($data['eType'] == 1)
                    {{-- ACCOUNT REGISTRATION --}}
                    <p class="letter-paragraph">
                        We are pleased to inform you that your account has been successfully created in the e-PostGrad
                        system. This system will serve as your primary platform for managing all postgraduate-related
                        submissions and communications.
                    </p>

                    <div class="success-box box">
                        <strong>ACCOUNT SUCCESSFULLY CREATED</strong><br>
                        Registration Date: {{ date('l, d F Y') }}<br>
                        Account Status: <span style="color: #27ae60; font-weight: bold;">ACTIVE</span>
                    </div>

                    <div class="credentials-box">
                        <h4>LOGIN CREDENTIALS:</h4>
                        @if ($data['uType'] == 1)
                            <p><strong>Username:</strong> Your registered student email address</p>
                            <p><strong>Default Password:</strong> <code>pg@matricno</code></p>
                            <p style="font-size: 12px; color: #7f8c8d; margin-top: 10px;">
                                <em>Note: Please replace "matricno" with your actual matriculation number</em>
                            </p>
                        @elseif($data['uType'] == 2)
                            <p><strong>Username:</strong> Your registered staff email address</p>
                            <p><strong>Default Password:</strong> <code>pg@staffid</code></p>
                            <p style="font-size: 12px; color: #7f8c8d; margin-top: 10px;">
                                <em>Note: Please replace "staffid" with your actual staff identification number</em>
                            </p>
                        @endif
                    </div>

                    <div class="highlight-box box">
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

                    <div class="warning-box box">
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

                    <div class="info-box box">
                        <strong>NEXT STEPS:</strong> Please contact the postgraduate administrator for assistance with
                        account
                        reactivation procedures if you believe this action was taken in error.
                    </div>
                @elseif ($data['eType'] == 3)
                    {{-- PASSWORD RESET REQUEST --}}
                    <p class="letter-paragraph">
                        We have received a request to reset the password for your e-PostGrad system account. This
                        request was initiated from your registered email address.
                    </p>

                    <div class="info-box box">
                        <strong>Reset Request Details:</strong><br>
                        Request Date: {{ date('l, d F Y \a\t g:i A') }}<br>
                        Account: {{ $data['name'] }}<br>
                        Status: Pending your action
                    </div>

                    <p class="letter-paragraph">
                        If you did not initiate this password reset request, please disregard this email and ensure your
                        account security by monitoring for any unauthorized access attempts.
                    </p>

                    <div class="highlight-box box">
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

                    <div class="success-box box">
                        <strong>PASSWORD SUCCESSFULLY UPDATED</strong><br>
                        Update Date: {{ date('l, d F Y \a\t g:i A') }}<br>
                        Account: {{ $data['name'] }}<br>
                        Status: <span style="color: #27ae60; font-weight: bold;">SECURE & READY</span>
                    </div>

                    <p class="letter-paragraph">
                        You can now log in to the e-PostGrad system using your new password. Please ensure that you keep
                        your login credentials secure and do not share them with unauthorized individuals.
                    </p>

                    <div class="info-box box">
                        <strong>SECURITY BEST PRACTICES:</strong>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>Use a strong, unique password</li>
                            <li>Do not share your credentials with others</li>
                            <li>Log out properly after each session</li>
                            <li>Report any suspicious account activity immediately</li>
                        </ul>
                    </div>
                @endif

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

        <div class="letter-footer">
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