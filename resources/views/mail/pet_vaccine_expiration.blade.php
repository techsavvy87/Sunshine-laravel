<!DOCTYPE html>
<html>
  <head>
    <title>{{ $mailData['subject'] }}</title>
    <meta charset="UTF-8">
    <style>
      body {
        background: #f6f8fa;
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        padding: 0;
      }
      .email-container {
        max-width: 640px;
        margin: 40px auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 32px 24px;
      }
      h2 {
        color: #1f2937;
        margin-bottom: 8px;
        font-size: 28px;
        font-weight: 700;
      }
      p {
        color: #374151;
        font-size: 16px;
        margin-bottom: 12px;
      }
      .alert-box {
        border-left: 4px solid {{ $mailData['alert_type'] === 'expired' ? '#dc2626' : '#d97706' }};
        background: {{ $mailData['alert_type'] === 'expired' ? '#fef2f2' : '#fffbeb' }};
        padding: 16px;
        margin: 20px 0;
        border-radius: 4px;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
      }
      th {
        background: #f3f4f6;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e7eb;
      }
      td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
      }
      .footer {
        color: #6b7280;
        font-size: 14px;
        margin-top: 32px;
        text-align: center;
        border-top: 1px solid #e5e7eb;
        padding-top: 20px;
      }
    </style>
  </head>
  <body>
    <div class="email-container">
      <p>Dear {{ $mailData['recipient_name'] ?? 'Customer' }},</p>

      @if ($mailData['alert_type'] === 'expired')
      <div class="alert-box">
        <p><strong>{{ $mailData['pet_name'] }}</strong> has one or more vaccines that are already expired.</p>
      </div>
      @else
      <div class="alert-box">
        <p><strong>{{ $mailData['pet_name'] }}</strong> has one or more vaccines that will expire within 1 month.</p>
      </div>
      @endif

      <table>
        <thead>
          <tr>
            <th>Vaccine</th>
            <th>Vaccination Date</th>
            <th>Validity (Months)</th>
            <th>Expires On</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($mailData['vaccines'] as $vaccine)
          <tr>
            <td>{{ $vaccine['type'] }}</td>
            <td>{{ $vaccine['date'] }}</td>
            <td>{{ $vaccine['months'] }}</td>
            <td>{{ $vaccine['expires_on'] }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <p>Please review and update the vaccination records for {{ $mailData['pet_name'] }} as needed.</p>

      <div class="footer">
        Best regards,<br>
        <strong>PawPrints Team</strong>
      </div>
    </div>
  </body>
</html>