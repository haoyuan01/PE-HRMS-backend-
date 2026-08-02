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

        $type = $request->query('type', 'manager');

        if (
            ($type == 'manager' && $overtime->manager_action_at) ||
            ($type == 'director' && ($overtime->manager_action_at == null || $overtime->manager_approved != StatusCodeConstants::ACTIVE || $overtime->director_action_at))
        )
        {
            return view('overtimes.review-invalid');
        }

        return view('overtimes.review', [
            'title' => $type == 'manager' ? 'Manager Overtime Review' : 'Director Overtime Review',
            'token' => $token,
            'email' => $email,
            'overtime_uuid' => $overtime_uuid,
            'type' => $type,
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

        $type = $request->type ?? 'manager';

        if (
            ($type == 'manager' && $overtime->manager_action_at) ||
            ($type == 'director' && ($overtime->manager_action_at == null || $overtime->manager_approved != StatusCodeConstants::ACTIVE || $overtime->director_action_at))
        )
        {
            return view('overtimes.review-invalid');
        }

        DB::beginTransaction();

        try {

            if ($type == 'manager')
            {
                $overtime->update([
                    'manager_action_by' => $user->id,
                    'manager_action_at' => self::currentDateTime(),
                    'manager_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'manager_remark' => $request->remark,
                    'updated_by' => $user->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);

                Password::deleteToken($user);

                if ($request->approve)
                {
                    $this->sendDirectorEmail($overtime);
                }
                else
                {
                    $this->sendApplicantEmail($overtime, false, false, $user);
                }
            }
            else
            {
                $overtime->update([
                    'director_action_by' => $user->id,
                    'director_action_at' => self::currentDateTime(),
                    'director_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'director_remark' => $request->remark,
                    'updated_by' => $user->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);

                if ($request->approve)
                {
                    $this->sendApplicantEmail($overtime, true, true, $user);
                }
                else
                {
                    $this->sendApplicantEmail($overtime, false, false, $user);
                }

                Password::deleteToken($user);
            }

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

    private function sendDirectorEmail($overtime)
    {
        $directors = User::whereHas('employment', function ($query) {
            $query->where('is_director', '=', StatusCodeConstants::ACTIVE);
        })
            ->where('is_active', StatusCodeConstants::ACTIVE)
            ->get();

        foreach($directors as $director)
        {
            Password::deleteToken($director);

            $token = Password::createToken($director);

            $data = [
                'name' => trim(($director->personal?->first_name ?? '') . ' ' . ($director->personal?->last_name ?? '')) ?: $director->email,
                'applicant_name' => trim(($overtime->user->personal?->first_name ?? '') . ' ' . ($overtime->user->personal?->last_name ?? '')) ?: $overtime->user->email,
                'applicant_email' => $overtime->user->email,
                'applicant_phone_number' => $overtime->user->contact?->phone_number,
                'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                'subject' => 'PE Portal - Overtime Pending Director Approval',
                'title' => 'Overtime Director Approval',
                'manager_remark' => $overtime->manager_remark,
                'overtime' => $overtime,
                'action_url' => url('/overtime-review?token=' . $token . '&email=' . urlencode($director->email) . '&overtime_uuid=' . $overtime->uuid . '&type=director'),
                'action_label' => 'Review Overtime',
            ];

            Mail::to($director->email)->send(new OvertimeApplicationMail($data));
        }
    }

    private function sendApplicantEmail($overtime, $approved = true, $cc_accountant = true, $reviewer = null)
    {
        $accountants = User::whereHas('employment', function ($query) {
            $query->where('is_accountant', '=', StatusCodeConstants::ACTIVE);
        })
            ->where('is_active', StatusCodeConstants::ACTIVE)
            ->get();

        $data = [
            'name' => trim(($overtime->user->personal?->first_name ?? '') . ' ' . ($overtime->user->personal?->last_name ?? '')) ?: $overtime->user->email,
            'applicant_name' => trim(($overtime->user->personal?->first_name ?? '') . ' ' . ($overtime->user->personal?->last_name ?? '')) ?: $overtime->user->email,
            'applicant_email' => $overtime->user->email,
            'applicant_phone_number' => $overtime->user->contact?->phone_number,
            'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
            'subject' => $approved ? 'PE Portal - Overtime Approved' : 'PE Portal - Overtime Rejected',
            'title' => $approved ? 'Overtime Approved' : 'Overtime Rejected',
            'status_text' => $approved ? 'approved' : 'rejected',
            'reviewed_by' => $reviewer ? trim(($reviewer->personal?->first_name ?? '') . ' ' . ($reviewer->personal?->last_name ?? '')) ?: $reviewer->email : null,
            'manager_remark' => $overtime->manager_remark,
            'director_remark' => $overtime->director_remark,
            'footer_message' => 'Please log in to PE Portal to view the overtime application.',
            'is_applicant_notification' => true,
            'overtime' => $overtime,
        ];

        $mail = Mail::to($overtime->user->email);

        if ($cc_accountant)
        {
            $mail->cc($accountants->pluck('email')->filter()->values()->toArray());
        }

        $mail->send(new OvertimeApplicationMail($data));
    }
}
