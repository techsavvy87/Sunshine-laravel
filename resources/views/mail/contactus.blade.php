<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>New Contact Us Message</title>
    <style>
      body {
        background: #f6f8fa;
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        padding: 0;
      }
      .email-container {
        max-width: 600px;
        margin: 40px auto;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 24px 24px 28px 24px;
      }
      h2 {
        color: #2563eb;
        margin-bottom: 8px;
        font-size: 24px;
        font-weight: 700;
      }
      p {
        color: #374151;
        font-size: 15px;
        margin-bottom: 10px;
      }
      .meta {
        background: #f9fafb;
        border-radius: 6px;
        padding: 14px 16px;
        margin: 18px 0;
      }
      .meta-row {
        display: flex;
        margin-bottom: 6px;
        font-size: 14px;
      }
      .meta-row span.label {
        width: 80px;
        font-weight: 600;
        color: #4b5563;
      }
      .meta-row span.value {
        color: #111827;
      }
      .message-box {
        margin-top: 10px;
        padding: 14px 16px;
        background: #f3f4f6;
        border-radius: 6px;
        white-space: pre-wrap;
        font-size: 14px;
        color: #111827;
      }
      .footer {
        color: #6b7280;
        font-size: 13px;
        margin-top: 22px;
      }
    </style>
  </head>
  <body>
    <div class="email-container">
      <h2>New Contact Us Message</h2>
      <p>You received a new message from the mobile app contact form.</p>

      <div class="meta">
        <div class="meta-row">
          <span class="label">Name:</span>
          <span class="value">{{ $data['name'] ?? '' }}</span>
        </div>
        <div class="meta-row">
          <span class="label">Email:</span>
          <span class="value">{{ $data['email'] ?? '' }}</span>
        </div>
        <div class="meta-row">
          <span class="label">Subject:</span>
          <span class="value">{{ $data['subject'] ?? '' }}</span>
        </div>
      </div>

      <p><strong>Message:</strong></p>
      <div class="message-box">
        {{ $data['message'] ?? '' }}
      </div>

      <p class="footer">
        This email was sent automatically from the Contact Us form. You can reply directly to the sender's email address.
      </p>
    </div>
  </body>
</html>

