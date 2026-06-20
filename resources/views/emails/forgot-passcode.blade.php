<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="https://www.petro-excel.com.my/wp-content/uploads/2018/09/Oil-Drop-Out-line-e1736841035299.png" type="image/png">
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
                            System PIN Reset Request
                        </h2>

                        <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.5;">
                            Hello <strong>{{ $data['name'] }}</strong>,
                        </p>

                        <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.6;">
                            A request has been made to reset the system PIN for your account associated with <strong>PE Portal</strong>.
                        </p>

                        <!-- BUTTON -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin: 32px 0;">
                            <tr>
                                <td align="center">
                                    <a
                                        href="{{ $data['reset_passcode_link'] }}"
                                        style="display:inline-block; padding:14px 40px; font-size:15px; font-weight:600; color:#ffffff; background-color:#17a2b8; text-decoration:none; border-radius:5px;"
                                    >
                                        Reset System PIN
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0; font-size: 14px; color: #6b7280; line-height: 1.6;">
                            If you did not initiate this request, please disregard this email.
                        </p>

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
