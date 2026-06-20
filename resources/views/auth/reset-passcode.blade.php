<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PE Portal - Reset Passcode</title>
<link rel="icon" href="https://www.petro-excel.com.my/wp-content/uploads/2018/09/Oil-Drop-Out-line-e1736841035299.png" type="image/png">

<style>
    body {
        margin: 0;
        padding: 0;
        background-color: #eef1f5;
        font-family: Arial, sans-serif;
    }

    .wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 10px;
    }

    .container {
        width: 100%;
        max-width: 600px;
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .header {
        text-align: center;
        padding: 32px 20px 20px;
    }

    .header img {
        width: 50px;
        margin-bottom: 12px;
    }

    .header-title {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
    }

    .divider {
        height: 1px;
        background-color: #f3f4f6;
        margin: 0 40px;
    }

    .content {
        padding: 20px 40px 40px 40px;
        color: #374151;
    }

    .content h2 {
        text-align: center;
        font-size: 20px;
        margin-bottom: 20px;
        color: #111827;
    }

    .input-group {
        margin-bottom: 20px;
    }

    .input-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 14px;
        font-weight: 600;
    }

    .input-group input {
        width: 100%;
        padding: 12px;
        border-radius: 5px;
        border: 1px solid #d1d5db;
        font-size: 14px;
    }

    .passcode-inputs {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .passcode-inputs input {
        width: 44px;
        height: 48px;
        padding: 0;
        text-align: center;
        font-size: 22px;
        font-weight: 600;
    }

    .button {
        display: block;
        width: 100%;
        padding: 14px;
        background-color: #17a2b8;
        color: white;
        text-align: center;
        font-weight: 600;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 15px;
    }

    .button:hover {
        opacity: 0.9;
    }

    .footer {
        text-align: center;
        padding: 32px 40px;
        background-color: #f9fafb;
        border-top: 1px solid #e5e7eb;
        font-size: 12px;
    }

    @media only screen and (max-width: 620px) {
        .container {
            border-radius: 0;
        }

        .content {
            padding: 10px 20px 20px 20px;
        }

        .footer {
            padding: 20px;
        }
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
            <h2>Reset Passcode</h2>

            <p>Hello <strong>{{ $name }}</strong>,</p>
            <p>Please enter your new 6 digit passcode below to complete your passcode reset.</p>

            <form method="POST" action="{{ $action_url }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="passcode" id="passcode">
                
                <div class="input-group">
                    <label>New System PIN</label>
                    <div class="passcode-inputs">
                        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" class="passcode-digit" autofocus>
                        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" class="passcode-digit">
                        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" class="passcode-digit">
                        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" class="passcode-digit">
                        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" class="passcode-digit">
                        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" class="passcode-digit">
                    </div>
                    @error('passcode')
                        <small style="color:red;">{{ $message }}</small>
                    @enderror
                </div>

                <button class="button">Submit</button>
            </form>

        </div>

        <div class="footer">

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

        </div>

    </div>

</div>

</body>
<script>
    const passcode = document.getElementById('passcode');
    const digits = document.querySelectorAll('.passcode-digit');

    const updatePasscode = () => {
        passcode.value = Array.from(digits).map((input) => input.value).join('');
    };

    digits.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 1);
            updatePasscode();

            if (input.value && digits[index + 1])
            {
                digits[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !input.value && digits[index - 1])
            {
                digits[index - 1].focus();
            }
        });

        input.addEventListener('paste', (event) => {
            event.preventDefault();

            const value = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);

            value.split('').forEach((digit, digit_index) => {
                digits[digit_index].value = digit;
            });

            updatePasscode();

            if (digits[value.length - 1])
            {
                digits[value.length - 1].focus();
            }
        });
    });
</script>
</html>
