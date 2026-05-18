<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9fafb;
        }
        .container {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 24px;
        }
        .alert {
            background-color: #fef3c7;
            border-left: 4px solid #d97706;
            padding: 12px 16px;
            margin: 16px 0;
            border-radius: 4px;
        }
        .details {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 12px 16px;
            margin: 16px 0;
        }
        .details p {
            margin: 6px 0;
        }
        .footer {
            margin-top: 24px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <p>Hello {{ $mailData['recipient_name'] ?? 'Facility Owner' }},</p>

        <div class="alert">
            <strong>Reminder:</strong> Your future timeslot schedule will run out in {{ $mailData['days_remaining'] ?? 30 }} days.
        </div>

        <div class="details">
            <p><strong>Timeslot end date:</strong> {{ $mailData['end_date'] }}</p>
            <p><strong>Reminder date:</strong> {{ $mailData['reminder_date'] }}</p>
        </div>

        <p>Please generate additional future timeslots to avoid booking interruptions.</p>

        <div class="footer">
            Best regards,<br>
            <strong>Sunshine Spot Team</strong>
        </div>
    </div>
</body>
</html>