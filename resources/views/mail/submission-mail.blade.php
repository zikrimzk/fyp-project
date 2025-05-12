@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>e-PostGrad Submission Notification</title>
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
                {{-- FOR STUDENTS --}}
                <h2>Submission Reminder</h2>
            @elseif($data['eType'] == 2)
                {{-- FOR SUPERVISOR --}}
                <h2>Student Submission Confirmed</h2>
            @elseif($data['eType'] == 3)
                {{-- FOR STUDENT --}}
                <h2>Submission Approved</h2>
            @elseif($data['eType'] == 4)
                {{-- FOR STUDENT --}}
                <h2>Submission Rejected</h2>
            @elseif($data['eType'] == 5)
                {{-- FOR STUDENT --}}
                <h2>Submission Reverted</h2>
            @elseif($data['eType'] == 6)
                {{-- FOR STUDENT --}}
                <h2>Activity Completed</h2>
            @endif
        </div>

        <div class="email-body">
            <p>Assalamualaikum and Good Day,</p>
            <p>Dear <strong>{{ $data['name'] }}</strong>,</p>
            <p>We hope this message finds you well.</p>

            @if ($data['eType'] == 1)
                {{-- SUBMISSION REMINDER --}}
                <p>This is a gentle reminder to submit your <strong>{{ $data['act_name'] }}</strong> documents via the
                    e-PostGrad system. The documents required are as follows:</p>
                {{-- <ul>
                    @foreach ($data['document'] as $doc)
                        <li>{{ $doc->doc_name }} – Due by
                            {{ Carbon::parse($doc->submission_duedate)->format('d-m-Y g:i A') }}</li>
                    @endforeach
                </ul> --}}
                <p>Please ensure that you click the <strong>Confirm Submission</strong> button after uploading your
                    documents to complete the process.</p>
            @elseif ($data['eType'] == 2)
                {{-- STUDENT SUBMISSION CONFIRMED --}}
                <p>This is to inform you that the student <strong>{{ $data['student_name'] }}</strong> (Matric No:
                    {{ $data['student_matricno'] }}) has confirmed submission of their
                    <strong>{{ $data['act_name'] }}</strong> documents on
                    {{ Carbon::parse($data['submission_date'])->format('d-m-Y g:i A') }} via the e-PostGrad system.
                </p>
            @elseif ($data['eType'] == 3)
                {{-- STUDENT SUBMISSION APPROVED --}}
                <p>We are pleased to inform you that your <strong>{{ $data['act_name'] }}</strong> document has been
                    approved by <strong>{{ $data['approvalUser'] }}</strong> on
                    {{ Carbon::parse($data['sa_date'])->format('d-m-Y g:i A') }} through the e-PostGrad system.</p>
            @elseif ($data['eType'] == 4)
                {{-- STUDENT SUBMISSION REJECTED --}}
                <p>We regret to inform you that your <strong>{{ $data['act_name'] }}</strong> document was rejected by
                    <strong>{{ $data['approvalUser'] }}</strong> on
                    {{ Carbon::parse($data['sa_date'])->format('d-m-Y g:i A') }}. Kindly log in to the system to review
                    and
                    take the necessary actions.
                </p>
            @elseif ($data['eType'] == 5)
                {{-- STUDENT SUBMISSION REVERTED --}}
                <p>Your <strong>{{ $data['act_name'] }}</strong> document has been reverted for further action. Please
                    log in to the system, make the necessary updates, and reconfirm your submission.</p>
            @elseif ($data['eType'] == 6)
                {{-- ACTIVITY COMPLETED --}}
                <p>Congratulations! You have successfully completed your <strong>{{ $data['act_name'] }}</strong>
                    activity. You are now eligible to proceed to the next activity. If this is your final activity, we
                    wish you the very best in your future endeavors!</p>
            @endif

            <a href="{{ route('main-login') }}" class="btn">Log in to e-PostGrad</a>

            <p>Thank you.</p>
            <p>Best regards,<br><strong>e-PostGrad Team</strong></p>
        </div>

        <div class="email-footer">
            <p>&copy; {{ date('Y') }} e-PostGrad System. All rights reserved.</p>
            <p>Need help? <a href="mailto:utem.edu.my">Contact Support</a></p>
        </div>
    </div>
</body>

</html>
