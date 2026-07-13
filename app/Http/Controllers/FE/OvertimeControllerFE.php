<?php

namespace App\Http\Controllers\FE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeReviewActionFE;
use App\Mail\OvertimeApplicationMail;
use App\Models\Overtime;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class OvertimeControllerFE extends Controller
{
    public function __construct()
    {
    }

    public function review(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email)
        {
            return view('overtimes.review-invalid');
        }

        $user = User::findByEmail($email, false);

        if (!$user || !Password::tokenExists($user, $token))
        {
            return view('overtimes.review-invalid');
        }

        $overtime_uuid = $request->query('overtime_uuid');
        $overtime = Overtime::findByUuid($overtime_uuid, false);

        if (!$overtime)
        {
            return view('overtimes.review-invalid');
        }

        return view('overtimes.review', [
            'token' => $token,
            'email' => $email,
            'overtime_uuid' => $overtime_uuid,
            'name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
            'overtime' => $overtime,
            'action_url' => url('/overtime-review'),
        ]);
    }

    public function reviewAction(OvertimeReviewActionFE $request)
    {
        $user = User::findByEmail($request->email, false);

        if (!$user || !Password::tokenExists($user, $request->token))
        {
            return view('overtimes.review-invalid');
        }

        $overtime = Overtime::findByUuid($request->overtime_uuid, false);

        if (!$overtime)
        {
            return view('overtimes.review-invalid');
        }

        DB::beginTransaction();

        try {
            $overtime->update([
                'manager_action_by' => $user->id,
                'manager_action_at' => self::currentDateTime(),
                'manager_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'manager_remark' => $request->remark,
                'updated_by' => $user->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            if ($request->approve)
            {
                $accountants = User::whereHas('employment', function ($query) {
                    $query->where('is_accountant', '=', StatusCodeConstants::ACTIVE);
                })
                    ->where('is_active', StatusCodeConstants::ACTIVE)
                    ->get();

                foreach($accountants as $accountant)
                {
                    $data = [
                        'name' => trim(($accountant->personal?->first_name ?? '') . ' ' . ($accountant->personal?->last_name ?? '')) ?: $accountant->email,
                        'applicant_name' => trim(($overtime->user->personal?->first_name ?? '') . ' ' . ($overtime->user->personal?->last_name ?? '')) ?: $overtime->user->email,
                        'applicant_email' => $overtime->user->email,
                        'applicant_phone_number' => $overtime->user->contact?->phone_number,
                        'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                        'subject' => 'PE Portal - Overtime Approved',
                        'title' => 'Overtime Approved',
                        'overtime' => $overtime,
                    ];

                    Mail::to($accountant->email)->send(new OvertimeApplicationMail($data));
                }
            }

            Password::deleteToken($user);

            DB::commit();

            return redirect('/overtime-review-success');

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function reviewSuccess()
    {
        return view('overtimes.review-success');
    }
}
