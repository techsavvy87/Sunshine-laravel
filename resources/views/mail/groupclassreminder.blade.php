<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #4F46E5;
        }
        .details p {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #4F46E5;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Group Class Reminder</h1>
    </div>

    <div class="content">
        <p>Hello {{ $appointment->customer->profile->first_name ?? 'there' }},</p>

        <p>This is a friendly reminder that you have a group class scheduled for <strong>tomorrow</strong>!</p>

        <div class="details">
            <p><span class="label">Service:</span> {{ $appointment->service->name }}</p>

            @if($appointment->pet)
                <p><span class="label">Pet:</span> {{ $appointment->pet->name }}</p>
            @endif

            <p><span class="label">Class:</span> {{ $appointment->class_name }}</p>

            <p><span class="label">Date:</span> {{ \Carbon\Carbon::parse($appointment->date)->format('l, F j, Y') }}</p>

            <p><span class="label">Time:</span> {{ \Carbon\Carbon::parse($appointment->start_time)->format('g:i A') }}
                @if($appointment->end_time)
                    - {{ \Carbon\Carbon::parse($appointment->end_time)->format('g:i A') }}
                @endif
            </p>

            @if($appointment->staff)
                <p><span class="label">Instructor:</span> {{ $appointment->staff->profile->first_name ?? '' }} {{ $appointment->staff->profile->last_name ?? '' }}</p>
            @endif
        </div>

        <p>Please arrive 10 minutes early to check in. If you need to cancel or reschedule, please contact us as soon as possible.</p>

        <p>We look forward to seeing you and {{ $appointment->pet->name ?? 'your pet' }} tomorrow!</p>

        <p>Best regards,<br>
        <strong>PawPrints Team</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated reminder. Please do not reply to this email.</p>
    </div>
</body>
</html>