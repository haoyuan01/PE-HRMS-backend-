<?php

namespace App\Http\Controllers\FE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestHandoverActionFE;
use App\Mail\LeaveApplicationMail;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class LeaveRequestControllerFE extends Controller
{
    public function __construct()
    {
    }

    public function handover(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email)
        {
            return view('leaves.leave-review-invalid');
        }

        $user = User::findByEmail($email, false);

        if (!$user || !Password::tokenExists($user, $token))
        {
            return view('leaves.leave-review-invalid');
        }

        $leave_request_uuid = $request->query('leave_request_uuid');
        $type = $request->query('type', 'handover');
        $leave_request = LeaveRequest::findByUuid($leave_request_uuid, false);

        if (!$leave_request)
        {
            return view('leaves.leave-review-invalid');
        }

        return view('leaves.leave-review', [
            'token' => $token,
            'email' => $email,
            'leave_request_uuid' => $leave_request_uuid,
            'type' => $type,
            'name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
            'leave_request' => $leave_request,
            'action_url' => url('/leave-request-review'),
            'remark_name' => 'remark',
            'remark_label' => $type == 'handover' ? 'Handover Remark' : ($type == 'manager' ? 'Manager Remark' : 'Director Remark'),
        ]);
    }

    public function handoverAction(LeaveRequestHandoverActionFE $request)
    {
        $user = User::findByEmail($request->email, false);

        if (!$user || !Password::tokenExists($user, $request->token))
        {
            return view('leaves.leave-review-invalid');
        }

        $leave_request = LeaveRequest::findByUuid($request->leave_request_uuid, false);

        if (!$leave_request)
        {
            return view('leaves.leave-review-invalid');
        }

        DB::beginTransaction();

        try {
            $type = $request->type ?? 'handover';

            if ($type == 'handover')
            {
                $leave_request->update([
                    'handover_action_at' => self::currentDateTime(),
                    'handover_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'handover_remark' => $request->remark,
                    'updated_by' => $user->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);

                if ($request->approve)
                {
                    $data = [
                        'name' => trim(($leave_request->managerApprover->personal?->first_name ?? '') . ' ' . ($leave_request->managerApprover->personal?->last_name ?? '')) ?: $leave_request->managerApprover->email,
                        'applicant_name' => trim(($leave_request->user->personal?->first_name ?? '') . ' ' . ($leave_request->user->personal?->last_name ?? '')) ?: $leave_request->user->email,
                        'applicant_email' => $leave_request->user->email,
                        'applicant_phone_number' => $leave_request->user->contact?->phone_number,
                        'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                        'subject' => 'PE Portal - Leave Pending Manager Approval',
                        'leave_request' => $leave_request,
                        'leave_entitlement' => $leave_request->leaveEntitlement,
                        'action_url' => url('/leave-request-review?token=' . Password::createToken($leave_request->managerApprover) . '&email=' . urlencode($leave_request->managerApprover->email) . '&leave_request_uuid=' . $leave_request->uuid . '&type=manager'),
                        'action_label' => 'Review Leave',
                    ];

                    Mail::to($leave_request->managerApprover->email)->send(new LeaveApplicationMail($data));
                }
            }
            else if ($type == 'manager')
            {
                $leave_request->update([
                    'manager_action_by' => $user->id,
                    'manager_action_at' => self::currentDateTime(),
                    'manager_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'manager_remark' => $request->remark,
                    'updated_by' => $user->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);

                if ($request->approve)
                {
                    $directors = User::whereHas('employment', function ($query) {
                        $query->where('is_director', '=', StatusCodeConstants::ACTIVE);
                    })
                        ->where('is_active', StatusCodeConstants::ACTIVE)
                        ->get();

                    foreach($directors as $director)
                    {
                        $data = [
                            'name' => trim(($director->personal?->first_name ?? '') . ' ' . ($director->personal?->last_name ?? '')) ?: $director->email,
                            'applicant_name' => trim(($leave_request->user->personal?->first_name ?? '') . ' ' . ($leave_request->user->personal?->last_name ?? '')) ?: $leave_request->user->email,
                            'applicant_email' => $leave_request->user->email,
                            'applicant_phone_number' => $leave_request->user->contact?->phone_number,
                            'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                            'subject' => 'PE Portal - Leave Pending Director Approval',
                            'leave_request' => $leave_request,
                            'leave_entitlement' => $leave_request->leaveEntitlement,
                            'action_url' => url('/leave-request-review?token=' . Password::createToken($director) . '&email=' . urlencode($director->email) . '&leave_request_uuid=' . $leave_request->uuid . '&type=director'),
                            'action_label' => 'Review Leave',
                        ];

                        Mail::to($director->email)->send(new LeaveApplicationMail($data));
                    }
                }
            }
            else
            {
                $leave_request->update([
                    'director_action_by' => $user->id,
                    'director_action_at' => self::currentDateTime(),
                    'director_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                    'director_remark' => $request->remark,
                    'updated_by' => $user->uuid,
                    'updated_at' => self::currentDateTime(),
                ]);

                if ($request->approve)
                {
                    $leave_entitlement = $leave_request->leaveEntitlement;

                    $leave_entitlement->update([
                        'used_days' => $leave_entitlement->used_days + $leave_request->total_days,
                        'balance_days' => $leave_entitlement->balance_days - $leave_request->total_days,
                        'updated_by' => $user->uuid,
                        'updated_at' => self::currentDateTime(),
                    ]);
                }
            }

            Password::deleteToken($user);

            DB::commit();

            return redirect('/leave-request-review-success');

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function handoverSuccess()
    {
        return view('leaves.leave-review-success');
    }
}
