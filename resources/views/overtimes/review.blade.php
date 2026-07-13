<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PE Portal - Overtime Review</title>
<link rel="icon" href="https://www.petro-excel.com.my/wp-content/uploads/2018/09/Oil-Drop-Out-line-e1736841035299.png" type="image/png">
<style>
    body { margin: 0; padding: 0; background-color: #eef1f5; font-family: Arial, sans-serif; }
    .wrapper { display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 10px; }
    .container { width: 100%; max-width: 600px; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; }
    .header { text-align: center; padding: 32px 20px 20px; }
    .header img { width: 50px; margin-bottom: 12px; }
    .header-title { font-size: 22px; font-weight: 700; color: #111827; }
    .divider { height: 1px; background-color: #f3f4f6; margin: 0 40px; }
    .content { padding: 20px 40px 40px 40px; color: #374151; }
    .content h2 { text-align: center; font-size: 20px; margin-bottom: 20px; color: #111827; }
    .detail-box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 18px; margin-bottom: 20px; background: #f9fafb; }
    .detail-box div { margin-bottom: 8px; font-size: 14px; }
    .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .button { display: block; width: 100%; padding: 14px; color: white; text-align: center; font-weight: 600; border: none; border-radius: 5px; cursor: pointer; font-size: 15px; }
    .accept { background-color: #16a34a; }
    .reject { background-color: #dc2626; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 8px; }
    .form-group textarea { width: 100%; min-height: 110px; padding: 12px; border: 1px solid #d1d5db; border-radius: 5px; font-size: 14px; font-family: Arial, sans-serif; box-sizing: border-box; resize: vertical; }
    .error { margin-top: 6px; color: red; font-size: 12px; text-align: right; }
    .footer { text-align: center; padding: 32px 40px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; }
    @media only screen and (max-width: 620px) { .container { border-radius: 0; } .content { padding: 10px 20px 20px 20px; } .footer { padding: 20px; } }
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
            <h2>Overtime Review</h2>

            <p>Hello <strong>{{ $name }}</strong>,</p>
            <p>Please accept or reject the overtime request below.</p>

            <div class="detail-box">
                <div><strong>Applicant:</strong> {{ $overtime->user->email }}</div>
                <div><strong>Start Date Time:</strong> {{ \Carbon\Carbon::parse($overtime->start_datetime)->format('Y-m-d h:i:s A') }}</div>
                <div><strong>End Date Time:</strong> {{ \Carbon\Carbon::parse($overtime->end_datetime)->format('Y-m-d h:i:s A') }}</div>
                <div><strong>Total Days:</strong> {{ number_format($overtime->total_days, 2) }}</div>
                <div><strong>Description:</strong> {{ $overtime->description ?: '-' }}</div>
            </div>

            <form method="POST" action="{{ $action_url }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="overtime_uuid" value="{{ $overtime_uuid }}">

                <div class="form-group">
                    <label for="remark">Manager Remark</label>
                    <textarea id="remark" name="remark">{{ old('remark') }}</textarea>
                    @error('remark')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="actions">
                    <button class="button accept" name="approve" value="1">Accept</button>
                    <button class="button reject" name="approve" value="0">Reject</button>
                </div>
            </form>
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
