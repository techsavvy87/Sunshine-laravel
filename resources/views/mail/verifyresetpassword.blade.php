<!DOCTYPE html>
<html>
  <head>
    <title>Reset Your PetManage Password</title>
    <meta charset="UTF-8">
    <style>
      body {
        background: #f6f8fa;
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        padding: 0;
      }
      .email-container {
        max-width: 480px;
        margin: 40px auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 32px 24px;
      }
      h4 {
        color: #2563eb;
        margin-bottom: 16px;
        font-size: 22px;
        font-weight: 700;
      }
      p {
        color: #374151;
        font-size: 16px;
        margin-bottom: 18px;
      }
      .verify-btn {
        display: inline-block;
        padding: 14px 32px;
        font-size: 16px;
        font-weight: bold;
        color: #fff !important;
        background-color: #2563eb;
        border-radius: 6px;
        text-decoration: none;
        text-align: center;
        margin: 24px 0 16px 0;
        box-shadow: 0 1px 4px rgba(37,99,235,0.12);
        letter-spacing: 0.5px;
      }
      .link-box {
        background: #f3f4f6;
        border-radius: 4px;
        padding: 10px;
        font-size: 14px;
        word-break: break-all;
        color: #2563eb;
        margin-bottom: 24px;
      }
      .footer {
        color: #6b7280;
        font-size: 14px;
        margin-top: 32px;
        text-align: center;
      }
    </style>
  </head>
  <body>
    <div class="email-container">
      <h4>Reset Your Password</h4>
      <p>
        You requested to reset your PetManage account password. Click the button below to set a new password.
      </p>
      <center>
        <a href="{{ $mailData['reset_link'] }}" class="verify-btn">
          Reset Password
        </a>
      </center>
      <p>If the button doesn’t work, you can copy and paste this link into your browser:</p>
      <div class="link-box">
        {{ $mailData['reset_link'] }}
      </div>
      <p>If you did not request a password reset, you can safely ignore this email.</p>
      <div class="footer">
        Warm regards<br>
        <strong>petmanage.com</strong>
      </div>
    </div>
  </body>
</html>