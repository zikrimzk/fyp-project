<!DOCTYPE html>
<html>

<head>
    <title>e-PostGrad Account Notification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            padding: 32px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
        }

        .email-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .email-header h1 {
            font-size: 20px;
            margin: 0;
            color: #1a237e;
        }

        .email-header h2 {
            font-size: 16px;
            margin-top: 8px;
            color: #555;
            font-weight: normal;
        }

        .email-body p {
            line-height: 1.6;
            margin: 16px 0;
        }

        .btn {
            display: inline-block;
            margin-top: 16px;
            background-color: #1a237e;
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 14px;
        }

        .email-footer {
            margin-top: 40px;
            font-size: 12px;
            text-align: center;
            color: #888;
        }

        .email-footer a {
            color: #1a237e;
            text-decoration: none;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background-color: #121212;
                color: #f0f0f0;
            }

            .email-container {
                background-color: #1e1e1e;
                border-color: #333;
            }

            .email-header h1 {
                color: #90caf9;
            }

            .btn {
                background-color: #3949ab;
            }

            .email-footer {
                color: #aaaaaa;
            }

            .email-footer a {
                color: #90caf9;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h1>e-PostGrad System</h1>
            @if ($data['eType'] == 1)
                <h2>Account Registration</h2>
            @elseif($data['eType'] == 2)
                <h2>Account Deactivation</h2>
            @elseif($data['eType'] == 3)
                <h2>Password Reset Request</h2>
            @elseif($data['eType'] == 4)
                <h2>Password Reset Confirmation</h2>
            @endif
        </div>

        <div class="email-body">
            <p>Dear <strong>{{ $data['name'] }}</strong>,</p>

            @if ($data['eType'] == 1)
                <p>Welcome! Your account has been successfully created.</p>
                @if ($data['uType'] == 1)
                    <p>You may log in using your <strong>student email</strong>. Your default password is in the format:
                        <code>pg@matricno</code>.</p>
                @elseif($data['uType'] == 2)
                    <p>You may log in using your <strong>staff email</strong>. Your default password is in the format:
                        <code>pg@staffid</code>.</p>
                @endif
                <a href="{{ $data['link'] }}" class="btn">Log In</a>
            @elseif ($data['eType'] == 2)
                <p>Your account has been deactivated. If you think this is an error, please contact the administrator or
                    postgraduate office.</p>
            @elseif ($data['eType'] == 3)
                <p>We received a request to reset your password. If this was not you, feel free to ignore this email.
                </p>
                <p>Otherwise, click the link below to reset your password. This link will expire in <strong>1
                        hour</strong>.</p>
                <a href="{{ $data['link'] }}" class="btn">Reset Password</a>
            @elseif ($data['eType'] == 4)
                <p>Your password has been successfully updated. You can now log in using your new password.</p>
                <a href="{{ $data['link'] }}" class="btn">Log In</a>
            @endif

            <p>If you have any questions, feel free to reach out to our support team.</p>
            <p>Best regards,<br>e-PostGrad Team</p>
        </div>

        <div class="email-footer">
            <p>&copy; {{ date('Y') }} e-PostGrad System. All rights reserved.</p>
            <p>Need help? <a href="mailto:zikrimzk@gmail.com">Contact Support</a></p>
        </div>
    </div>
</body>

</html>
