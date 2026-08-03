<?php

namespace App\Http\Controllers\FE;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollPreviewActionFE;
use App\Mail\ForgotPasscodeMail;
use App\Models\Payroll;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class PayrollControllerFE extends Controller
{
    public function __construct(private AuthService $auth_service)
    {
    }

    public function preview(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email)
        {
            return view('payrolls.preview-invalid');
        }

        $user = User::findByEmail($email, false);

        if (!$user || !self::tokenExists($user, $token))
        {
            return view('payrolls.preview-invalid');
        }

        $payroll_uuid = $request->query('payroll_uuid');
        $payroll = Payroll::findByUuid($payroll_uuid, false);

        if (!$payroll || $payroll->user_id != $user->id || !$payroll->is_published)
        {
            return view('payrolls.preview-invalid');
        }

        return view('payrolls.preview-passcode', [
            'token' => $token,
            'email' => $email,
            'payroll_uuid' => $payroll_uuid,
            'name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
            'payroll' => $payroll,
            'action_url' => url('/payroll-preview'),
        ]);
    }

    public function previewAction(PayrollPreviewActionFE $request)
    {
        $user = User::findByEmail($request->email, false);

        if (!$user || !self::tokenExists($user, $request->token))
        {
            return view('payrolls.preview-invalid');
        }

        $payroll = Payroll::findByUuid($request->payroll_uuid, false);

        if (!$payroll || $payroll->user_id != $user->id || !$payroll->is_published)
        {
            return view('payrolls.preview-invalid');
        }

        try {
            $this->auth_service->validatePasscode($user, $request->passcode);
        } catch (\Exception $exception) {
            return view('payrolls.preview-passcode-error', [
                'email' => $user->email,
                'name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
                'forgot_passcode_url' => url('/payroll-forgot-passcode'),
                'retry_url' => url('/payroll-preview?token=' . $request->token . '&email=' . urlencode($user->email) . '&payroll_uuid=' . $payroll->uuid),
            ]);
        }

        return view('payrolls.preview-success', [
            'name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
            'payroll' => $payroll,
            'attachment_url' => $payroll->attachment_path ? asset(Storage::url($payroll->attachment_path)) : null,
        ]);
    }

    public function forgotPasscode(Request $request)
    {
        $user = User::findByEmail($request->email, false);

        if (!$user)
        {
            return view('payrolls.preview-invalid');
        }

        Password::deleteToken($user);

        $token = Password::createToken($user);

        $reset_passcode_link = url('/reset-passcode?token=' . $token . '&email=' . urlencode($user->email));

        $data = [
            'name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
            'subject' => 'PE Portal - Reset System PIN',
            'reset_passcode_link' => $reset_passcode_link,
        ];

        Mail::to($user->email)->send(new ForgotPasscodeMail($data));

        return view('payrolls.preview-passcode-email-sent', [
            'name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
        ]);
    }

    private function tokenExists($user, $token)
    {
        $token_record = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        return $token_record && Hash::check($token, $token_record->token);
    }
}
