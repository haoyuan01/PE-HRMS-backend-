<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PE Portal - Incorrect PIN</title>
<link rel="icon" href="https://www.petro-excel.com.my/wp-content/uploads/2018/09/Oil-Drop-Out-line-e1736841035299.png" type="image/png">
<style>
    body { margin: 0; padding: 0; background-color: #eef1f5; font-family: Arial, sans-serif; }
    .wrapper { display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 10px; }
    .container { width: 100%; max-width: 600px; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; }
    .header { text-align: center; padding: 32px 20px 20px; }
    .header img { width: 50px; margin-bottom: 12px; }
    .header-title { font-size: 22px; font-weight: 700; color: #111827; }
    .divider { height: 1px; background-color: #f3f4f6; margin: 0 40px; }
    .content { padding: 20px 40px 40px 40px; color: #374151; text-align: center; }
    .content h2 { text-align: center; font-size: 20px; margin-bottom: 20px; color: #111827; }
    .content p { font-size: 15px; line-height: 1.6; margin: 0 0 12px 0; }
    .status-icon { width: 54px; height: 54px; line-height: 54px; margin: 0 auto 18px auto; border-radius: 50%; background-color: #fef2f2; color: #dc2626; font-size: 28px; font-weight: 700; }
    .action-group { display: flex; gap: 12px; justify-content: center; margin-top: 24px; flex-wrap: wrap; }
    .button { display: inline-block; min-width: 150px; padding: 14px 22px; color: #ffffff; text-decoration: none; border: 0; border-radius: 5px; font-weight: 600; cursor: pointer; font-size: 15px; box-sizing: border-box; }
    .button-dark { background-color: #111827; }
    .button-info { background-color: #17a2b8; }
    .button:hover { opacity: 0.9; }
    .footer { text-align: center; padding: 32px 40px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; }
    @media only screen and (max-width: 620px) {
        .container { border-radius: 0; }
        .content { padding: 10px 20px 20px 20px; }
        .button { width: 100%; }
        .action-group { display: block; }
        .action-group a { margin-bottom: 12px; }
        .footer { padding: 20px; }
    }
</style>
</head>
<body>
<div class="wrapper">
    <div class="container">
        <div class="header">
            <img src="https://www.petro-excel.com.my/wp-content/uploads/2018/09/Oil-Drop-Out-line-e1736841035299.png">
            <div class="header-title">Petro-Excel Sdn Bhd</div>
        </div>
        <div class="divider"></div>
        <div class="content">
            <div class="status-icon">!</div>
            <h2>Incorrect System PIN</h2>
            <p>Hello <strong>{{ $name }}</strong>,</p>
            <p>The system PIN entered is incorrect. Please try again or reset your passcode.</p>
            <div class="action-group">
                <a href="{{ $retry_url }}" class="button button-dark">Try Again</a>
                <form method="POST" action="{{ $forgot_passcode_url }}" style="margin:0;">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button class="button button-info">Forgot Passcode</button>
                </form>
            </div>
        </div>
        <div class="footer">
            <p style="margin: 0 0 24px 0; font-size: 12px; color: #9ca3af;">This is an automated system-generated email. Please do not reply to this message.</p>
            <p style="margin: 0 0 8px 0; font-size: 12px; color: #6b7280; font-weight: 600;">Petro-Excel Sdn Bhd</p>
            <p style="margin: 0; font-size: 12px; color: #9ca3af; line-height: 18px;">Lot 1236 & 1237,<br>Senadin Venture Light Industrial Park,<br>Jalan Lutong - Kuala Baram,<br>98000 Miri, Sarawak.<br></p>
            <p style="margin: 16px 0 0 0; font-size: 12px; color: #9ca3af;">© {{ date('Y') }} Petro-Excel Sdn Bhd. All rights reserved.</p>
        </div>
    </div>
</div>
</body>
</html>
