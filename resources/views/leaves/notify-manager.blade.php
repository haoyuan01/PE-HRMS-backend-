<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="https://www.petro-excel.com.my/img/Petro-Excel-logo-1.png" type="image/png">
<title>{{ $data['subject'] }}</title>
<style>
    /* Mobile responsiveness */
    @media only screen and (max-width: 620px) {
        .container {
            width: 100% !important;
            border-radius: 0 !important;
        }

        .content {
            padding: 20px !important;
        }

        .header,
        .footer {
            padding: 20px !important;
        }
    }
</style>

</head>

<body style="margin:0; padding:0; background-color:#eef1f5; font-family: Arial, sans-serif;">

<!-- FULL HEIGHT WRAPPER (centers vertically + horizontally) -->
<table width="100%" height="100%" cellpadding="0" cellspacing="0" style="background-color:#eef1f5; min-height:100vh;">
    <tr>
        <td align="center" valign="middle" style="padding:10px;">

            <!-- CARD -->
            <table cellpadding="0" cellspacing="0" class="container"
                   style="width:100%; max-width:600px; background:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">

                <!-- HEADER -->
                <tr>
                    <td align="center" style="padding: 32px 0 20px 0;">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" style="padding-bottom: 12px;">
                                    <img src="https://www.petro-excel.com.my/wp-content/uploads/2018/09/Oil-Drop-Out-line-e1736841035299.png" width="50">
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <span style="font-size: 22px; font-weight: 700; color: #111827;">
                                        Petro-Excel Sdn Bhd
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 0 40px;">
                        <div style="height: 1px; background-color: #f3f4f6; width: 100%;"></div>
                    </td>
                </tr>

                <!-- CONTENT -->
                <tr>
                    <td class="content" style="padding: 40px; color: #374151;">

                        <h2 style="margin: 0 0 20px 0; font-size: 20px; color: #111827; font-weight: 600; text-align: center;">
                            {{ $data['title'] }}
                        </h2>

                        <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.5;">
                            Hello <strong>{{ $data['name'] }}</strong>,
                        </p>

                        <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.6;">
                            A leave application has been submitted by <strong>{{ $data['applicant_name'] }}</strong> ({{ $data['applicant_email'] }}{{ $data['applicant_phone_number'] ? ', ' . $data['applicant_phone_number'] : '' }}) and is pending your approval.
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
                            <tr>
                                <td colspan="2" style="padding:14px 16px; background:#111827; color:#ffffff; font-size:15px; font-weight:600;">
                                    Leave Details
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px; background:#f9fafb; border-bottom:1px solid #e5e7eb; width:42%;">
                                    <div style="font-size:13px; color:#6b7280; margin-bottom:4px;">Leave Policy</div>
                                    <div style="font-size:15px; font-weight:600; color:#111827;">{{ $data['leave_entitlement']->leavePolicy?->name }}</div>
                                </td>
                                <td style="padding:14px 16px; background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                                    <div style="font-size:13px; color:#6b7280; margin-bottom:4px;">Submitted At</div>
                                    <div style="font-size:15px; font-weight:600; color:#111827;">{{ $data['submitted_at'] }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding:0; border-bottom:1px solid #e5e7eb;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td colspan="3" style="padding:12px 16px; background:#f9fafb; color:#6b7280; font-size:13px; font-weight:600;">
                                                Applied Dates
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:10px 16px; background:#ffffff; color:#6b7280; font-size:13px; font-weight:600; border-top:1px solid #e5e7eb;">
                                                Date
                                            </td>
                                            <td style="padding:10px 16px; background:#ffffff; color:#6b7280; font-size:13px; font-weight:600; border-top:1px solid #e5e7eb;">
                                                Duration
                                            </td>
                                            <td style="padding:10px 16px; background:#ffffff; color:#6b7280; font-size:13px; font-weight:600; border-top:1px solid #e5e7eb;">
                                                Session
                                            </td>
                                        </tr>
                                        @foreach($data['leave_request']->leaveRequestDates as $leave_request_date)
                                            <tr>
                                                <td style="padding:10px 16px; color:#111827; font-size:14px; border-top:1px solid #e5e7eb;">
                                                    {{ \Carbon\Carbon::parse($leave_request_date->date)->format('Y-m-d') }}
                                                </td>
                                                <td style="padding:10px 16px; color:#111827; font-size:14px; border-top:1px solid #e5e7eb;">
                                                    {{ $leave_request_date->is_half_day ? 'Half Day' : 'Full Day' }}
                                                </td>
                                                <td style="padding:10px 16px; color:#111827; font-size:14px; border-top:1px solid #e5e7eb;">
                                                    {{ $leave_request_date->is_half_day ? ($leave_request_date->is_first_half ? 'First Half' : 'Second Half') : '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px; border-bottom:1px solid #e5e7eb;">
                                    <div style="font-size:13px; color:#6b7280; margin-bottom:4px;">Resume Date</div>
                                    <div style="font-size:15px; font-weight:600; color:#111827;">{{ \Carbon\Carbon::parse($data['leave_request']->resume_date)->format('Y-m-d') }}</div>
                                </td>
                                <td style="padding:14px 16px; border-bottom:1px solid #e5e7eb;">
                                    <div style="font-size:13px; color:#6b7280; margin-bottom:4px;">Total Days</div>
                                    <div style="font-size:15px; font-weight:600; color:#111827;">{{ number_format($data['leave_request']->total_days, 2) }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding:14px 16px; background:#f9fafb;">
                                    <div style="font-size:13px; color:#6b7280; margin-bottom:4px;">Reason</div>
                                    <div style="font-size:14px; line-height:1.5; color:#111827;">{{ $data['leave_request']->reason ?: '-' }}</div>
                                </td>
                            </tr>
                            @if(isset($data['handover_remark']) && $data['handover_remark'])
                                <tr>
                                    <td colspan="2" style="padding:14px 16px; background:#ffffff; border-top:1px solid #e5e7eb;">
                                        <div style="font-size:13px; color:#6b7280; margin-bottom:4px;">Handover Remark</div>
                                        <div style="font-size:14px; line-height:1.5; color:#111827;">{{ $data['handover_remark'] }}</div>
                                    </td>
                                </tr>
                            @endif
                            @if(isset($data['manager_remark']) && $data['manager_remark'])
                                <tr>
                                    <td colspan="2" style="padding:14px 16px; background:#ffffff; border-top:1px solid #e5e7eb;">
                                        <div style="font-size:13px; color:#6b7280; margin-bottom:4px;">Manager Remark</div>
                                        <div style="font-size:14px; line-height:1.5; color:#111827;">{{ $data['manager_remark'] }}</div>
                                    </td>
                                </tr>
                            @endif
                        </table>

                        @if(isset($data['action_url']) && $data['action_url'])
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $data['action_url'] }}" style="display:inline-block; padding:14px 22px; background:#17a2b8; color:#ffffff; text-decoration:none; border-radius:5px; font-size:14px; font-weight:600;">
                                            {{ $data['action_label'] ?? 'Review Leave' }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        @else
                            <p style="margin: 0; font-size: 14px; color: #6b7280; line-height: 1.6;">
                                Please log in to PE Portal to review the leave application.
                            </p>
                        @endif

                    </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                    <td class="footer" style="padding: 32px 40px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center;">

                        <!-- Auto-generated notice -->
                        <p style="margin: 0 0 24px 0; font-size: 12px; color: #9ca3af;">
                            This is an automated system-generated email. Please do not reply to this message.
                        </p>

                        <p style="margin: 0 0 8px 0; font-size: 12px; color: #6b7280; font-weight: 600;">
                            Petro-Excel Sdn Bhd
                        </p>

                        <p style="margin: 0; font-size: 12px; color: #9ca3af; line-height: 18px;">
                            Lot 1236 & 1237,<br>
                            Senadin Venture Light Industrial Park,<br>
                            Jalan Lutong - Kuala Baram,<br>
                            98000 Miri, Sarawak.<br>
                        </p>

                        <p style="margin: 16px 0 0 0; font-size: 12px; color: #9ca3af;">
                            © {{ date('Y') }} Petro-Excel Sdn Bhd. All rights reserved.
                        </p>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
