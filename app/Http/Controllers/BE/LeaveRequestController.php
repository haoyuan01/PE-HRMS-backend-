<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Exceptions\AppException;
use App\Filters\LeaveRequestFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestCalendarSummaryRequest;
use App\Http\Requests\LeaveRequestDirectorApproveRequest;
use App\Http\Requests\LeaveRequestIndexRequest;
use App\Http\Requests\LeaveRequestManagerApproveRequest;
use App\Http\Requests\LeaveRequestShowRequest;
use App\Http\Requests\LeaveRequestStatusSummaryRequest;
use App\Http\Requests\LeaveRequestStoreRequest;
use App\Http\Requests\LeaveRequestUpdateRequest;
use App\Http\Requests\LeaveRequestUpdateStatusRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Mail\LeaveApplicationMail;
use App\Models\LeaveEntitlement;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class LeaveRequestController extends Controller
{
    public function __construct(private LeaveRequestFilter $leave_request_filter)
    {
    }

    public function index(LeaveRequestIndexRequest $request)
    {
        $leave_request = LeaveRequest::with([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',

            'managerApprover.personal',
            'managerApprover.contact',
            'managerApprover.employment.office',
            'managerApprover.employment.position',
            'managerApprover.employment.department',
            'managerApprover.emergency',

            'leaveEntitlement.leavePolicy.leavePolicyTiers',

            'managerActionBy.personal',
            'managerActionBy.contact',
            'managerActionBy.employment.office',
            'managerActionBy.employment.position',
            'managerActionBy.employment.department',
            'managerActionBy.emergency',

            'directorActionBy.personal',
            'directorActionBy.contact',
            'directorActionBy.employment.office',
            'directorActionBy.employment.position',
            'directorActionBy.employment.department',
            'directorActionBy.emergency',

            'handoverBy.personal',
            'handoverBy.contact',
            'handoverBy.employment.office',
            'handoverBy.employment.position',
            'handoverBy.employment.department',
            'handoverBy.emergency',
        ])->active();

        $leave_request = $this->leave_request_filter->apply($request, $request->size, $leave_request);

        return self::responsePaginated(LeaveRequestResource::collection($leave_request), $leave_request);
    }

    public function store(LeaveRequestStoreRequest $request)
    {
        $user = User::findByUuid(self::auth()->uuid);
        $manager_approver = User::findByUuid($request->manager_approver_uuid);
        $leave_entitlement = LeaveEntitlement::findByUuid($request->leave_entitlement_uuid);
    
        $handover_by = null;
        if ($request->handover_by_uuid)
        {
            $handover_by = User::findByUuid($request->handover_by_uuid);
        }

        $leave_policy = $leave_entitlement->leavePolicy;
        $is_handover_required = $leave_policy?->is_handover_required == StatusCodeConstants::ACTIVE && $request->total_days >= ($leave_policy->handover_min_days ?? 0);
        $notice_days = self::currentDateTime()->startOfDay()->diffInDays(\Carbon\Carbon::parse($request->start_date)->startOfDay(), false);

        throw_if($leave_entitlement->user_id != $user->id, AppException::class, 'Invalid leave entitlement');
        throw_if($leave_entitlement->balance_days < $request->total_days, AppException::class, 'Insufficient leave balance');
        throw_if($notice_days < ($leave_policy->min_notice_days ?? 0), AppException::class, 'Minimum notice days is not fulfilled');
        throw_if($is_handover_required && !$request->handover_by_uuid, AppException::class, 'Handover is required');
        throw_if($leave_policy->requires_attachment == StatusCodeConstants::ACTIVE && !$request->hasFile('attachment'), AppException::class, 'Attachment is required');

        DB::beginTransaction();

        try {
            $attachment_url = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_url = $file->storeAs('leaves', $filename, 'public');
            }

            $leave_request = LeaveRequest::create([
                'uuid' => self::uuid(),
                'user_id' => self::auth()->id,
                'manager_approver_id' => $manager_approver->id,
                'leave_entitlement_id' => $leave_entitlement->id,
                'handover_by' => $handover_by?->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'resume_date' => $request->resume_date,
                'total_days' => $request->total_days,
                'is_half_day' => $request->is_half_day ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'is_first_half' => $request->is_first_half ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'reason' => $request->reason,
                'attachment_url' => $attachment_url,
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            if ($handover_by)
            {
                $token = Password::createToken($handover_by);

                $data = [
                    'name' => trim(($handover_by->personal?->first_name ?? '') . ' ' . ($handover_by->personal?->last_name ?? '')) ?: $handover_by->email,
                    'applicant_name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
                    'applicant_email' => $user->email,
                    'applicant_phone_number' => $user->contact?->phone_number,
                    'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                    'subject' => 'PE Portal - Leave Pending Handover Approval',
                    'title' => 'Leave Handover Approval',
                    'leave_request' => $leave_request,
                    'leave_entitlement' => $leave_entitlement,
                    'action_url' => url('/leave-request-review?token=' . $token . '&email=' . urlencode($handover_by->email) . '&leave_request_uuid=' . $leave_request->uuid),
                    'action_label' => 'Review Handover',
                ];

                Mail::to($handover_by->email)->send(new LeaveApplicationMail($data));
            }
            else
            {
                $data = [
                    'name' => trim(($manager_approver->personal?->first_name ?? '') . ' ' . ($manager_approver->personal?->last_name ?? '')) ?: $manager_approver->email,
                    'applicant_name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
                    'applicant_email' => $user->email,
                    'applicant_phone_number' => $user->contact?->phone_number,
                    'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                    'subject' => 'PE Portal - Leave Pending Manager Approval',
                    'title' => 'Leave Manager Approval',
                    'leave_request' => $leave_request,
                    'leave_entitlement' => $leave_entitlement,
                    'action_url' => url('/leave-request-review?token=' . Password::createToken($manager_approver) . '&email=' . urlencode($manager_approver->email) . '&leave_request_uuid=' . $leave_request->uuid . '&type=manager'),
                    'action_label' => 'Review Leave',
                ];

                Mail::to($manager_approver->email)->send(new LeaveApplicationMail($data));
            }

            $leave_request->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',

                'managerApprover.personal',
                'managerApprover.contact',
                'managerApprover.employment.office',
                'managerApprover.employment.position',
                'managerApprover.employment.department',
                'managerApprover.emergency',

                'leaveEntitlement.leavePolicy.leavePolicyTiers',

                'handoverBy.personal',
                'handoverBy.contact',
                'handoverBy.employment.office',
                'handoverBy.employment.position',
                'handoverBy.employment.department',
                'handoverBy.emergency',
            ]);

            DB::commit();

            return self::response(new LeaveRequestResource($leave_request));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(LeaveRequestUpdateRequest $request, string $uuid)
    {
        $leave_request = LeaveRequest::findByUuid($uuid);

        $user = $leave_request->user;
        $old_manager_id = $leave_request->manager_approver_id;
        $new_manager = User::findByUuid($request->manager_approver_uuid);
        $leave_entitlement = LeaveEntitlement::findByUuid($request->leave_entitlement_uuid);

        $handover_by = null;
        if ($request->handover_by_uuid)
        {
            $handover_by = User::findByUuid($request->handover_by_uuid);
        }

        $leave_policy = $leave_entitlement->leavePolicy;
        $is_handover_required = $leave_policy?->is_handover_required == StatusCodeConstants::ACTIVE && $request->total_days >= ($leave_policy->handover_min_days ?? 0);
        $handover_changed = $handover_by?->id != $leave_request->handover_by;

        throw_if($leave_request->manager_action_at, AppException::class, 'Leave request already reviewed by manager');
        throw_if($leave_request->director_action_at, AppException::class, 'Leave request already reviewed by director');
        throw_if($handover_changed && $leave_request->handover_action_at, AppException::class, 'Handover already reviewed');
        throw_if($new_manager->id != $old_manager_id && $old_manager_id, AppException::class, 'Manager approver already assigned');
        throw_if($leave_entitlement->user_id != $leave_request->user_id, AppException::class, 'Invalid leave entitlement');
        throw_if($leave_entitlement->balance_days < $request->total_days, AppException::class, 'Insufficient leave balance');
        throw_if($is_handover_required && !$request->handover_by_uuid, AppException::class, 'Handover is required');

        DB::beginTransaction();

        try {
            $attachment_url = null;

            if ($request->hasFile('attachment'))
            {
                $file = $request->file('attachment');

                $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

                $attachment_url = $file->storeAs('leaves', $filename, 'public');
            }

            $leave_request->update([
                'manager_approver_id' => $new_manager->id,
                'leave_entitlement_id' => $leave_entitlement->id,
                'handover_by' => $handover_by?->id,
                'handover_action_at' => $handover_changed ? null : $leave_request->handover_action_at,
                'handover_approved' => $handover_changed ? StatusCodeConstants::INACTIVE : $leave_request->handover_approved,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'resume_date' => $request->resume_date,
                'total_days' => $request->total_days,
                'is_half_day' => $request->is_half_day ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'is_first_half' => $request->is_first_half ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'reason' => $request->reason,
                'attachment_url' => $attachment_url ? $attachment_url : $leave_request->attachment_url,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            if ($handover_by && $handover_changed)
            {
                $token = Password::createToken($handover_by);

                $data = [
                    'name' => trim(($handover_by->personal?->first_name ?? '') . ' ' . ($handover_by->personal?->last_name ?? '')) ?: $handover_by->email,
                    'applicant_name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
                    'applicant_email' => $user->email,
                    'applicant_phone_number' => $user->contact?->phone_number,
                    'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                    'subject' => 'PE Portal - Leave Pending Handover Approval',
                    'title' => 'Leave Handover Approval',
                    'leave_request' => $leave_request,
                    'leave_entitlement' => $leave_entitlement,
                    'action_url' => url('/leave-request-review?token=' . $token . '&email=' . urlencode($handover_by->email) . '&leave_request_uuid=' . $leave_request->uuid),
                    'action_label' => 'Review Handover',
                ];

                Mail::to($handover_by->email)->send(new LeaveApplicationMail($data));
            }
            else if (!$handover_by && $new_manager->id != $old_manager_id) // only send email if manager is updated
            {
                $data = [
                    'name' => trim(($new_manager->personal?->first_name ?? '') . ' ' . ($new_manager->personal?->last_name ?? '')) ?: $new_manager->email,
                    'applicant_name' => trim(($user->personal?->first_name ?? '') . ' ' . ($user->personal?->last_name ?? '')) ?: $user->email,
                    'applicant_email' => $user->email,
                    'applicant_phone_number' => $user->contact?->phone_number,
                    'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                    'subject' => 'PE Portal - Leave Pending Manager Approval',
                    'title' => 'Leave Manager Approval',
                    'leave_request' => $leave_request,
                    'leave_entitlement' => $leave_entitlement,
                    'action_url' => url('/leave-request-review?token=' . Password::createToken($new_manager) . '&email=' . urlencode($new_manager->email) . '&leave_request_uuid=' . $leave_request->uuid . '&type=manager'),
                    'action_label' => 'Review Leave',
                ];

                Mail::to($new_manager->email)->send(new LeaveApplicationMail($data));
            }

            $leave_request->load([
                'user.personal',
                'user.contact',
                'user.employment.office',
                'user.employment.position',
                'user.employment.department',
                'user.emergency',

                'managerApprover.personal',
                'managerApprover.contact',
                'managerApprover.employment.office',
                'managerApprover.employment.position',
                'managerApprover.employment.department',
                'managerApprover.emergency',

                'leaveEntitlement.leavePolicy.leavePolicyTiers',

                'managerActionBy.personal',
                'managerActionBy.contact',
                'managerActionBy.employment.office',
                'managerActionBy.employment.position',
                'managerActionBy.employment.department',
                'managerActionBy.emergency',

                'directorActionBy.personal',
                'directorActionBy.contact',
                'directorActionBy.employment.office',
                'directorActionBy.employment.position',
                'directorActionBy.employment.department',
                'directorActionBy.emergency',

                'handoverBy.personal',
                'handoverBy.contact',
                'handoverBy.employment.office',
                'handoverBy.employment.position',
                'handoverBy.employment.department',
                'handoverBy.emergency',
            ]);

            DB::commit();

            return self::response(new LeaveRequestResource($leave_request));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(LeaveRequestUpdateStatusRequest $request, string $uuid)
    {
        DB::beginTransaction();

        try {
            $leave_request = LeaveRequest::findByUuid($uuid);

            $leave_request->update([
                'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            DB::commit();

            return self::response(new LeaveRequestResource($leave_request));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function show(LeaveRequestShowRequest $request, string $uuid)
    {
        $leave_request = LeaveRequest::findByUuid($uuid);

        return self::response(new LeaveRequestResource($leave_request));
    }

    public function calendarSummaries(LeaveRequestCalendarSummaryRequest $request)
    {
        $start_date = Carbon::parse($request->start_date)->startOfDay();
        $end_date = Carbon::parse($request->end_date)->endOfDay();

        $leave_requests = LeaveRequest::with([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',

            'managerApprover.personal',
            'managerApprover.contact',
            'managerApprover.employment.office',
            'managerApprover.employment.position',
            'managerApprover.employment.department',
            'managerApprover.emergency',

            'leaveEntitlement.leavePolicy.leavePolicyTiers',

            'managerActionBy.personal',
            'managerActionBy.contact',
            'managerActionBy.employment.office',
            'managerActionBy.employment.position',
            'managerActionBy.employment.department',
            'managerActionBy.emergency',

            'directorActionBy.personal',
            'directorActionBy.contact',
            'directorActionBy.employment.office',
            'directorActionBy.employment.position',
            'directorActionBy.employment.department',
            'directorActionBy.emergency',

            'handoverBy.personal',
            'handoverBy.contact',
            'handoverBy.employment.office',
            'handoverBy.employment.position',
            'handoverBy.employment.department',
            'handoverBy.emergency',
        ])
            ->whereBetween('created_at', [$start_date, $end_date])
            ->active()
            ->get()
            ->groupBy(function ($leave_request) {
                return Carbon::parse($leave_request->created_at)->format('Y-m-d');
            });

        $data = [];
        $date = $start_date->copy();

        while($date->lte($end_date))
        {
            $date_key = $date->format('Y-m-d');
            $daily_leave_requests = $leave_requests->get($date_key, collect());

            $data[$date_key] = [
                'date' => $date_key,
                'total' => $daily_leave_requests->count(),
                'leave_requests' => LeaveRequestResource::collection($daily_leave_requests),
            ];

            $date->addDay();
        }

        return self::response($data);
    }

    public function statusSummaries(LeaveRequestStatusSummaryRequest $request)
    {
        $leave_request = LeaveRequest::active();

        if ($request->user_uuid)
        {
            $user = User::findByUuid($request->user_uuid);

            $leave_request->where('user_id', $user->id);
        }

        $data = [
            'total' => (clone $leave_request)->count(),
            'pending' => (clone $leave_request)
                ->whereNull('director_action_at')
                ->count(),
            'approved' => (clone $leave_request)
                ->whereNotNull('director_action_at')
                ->where('director_approved', StatusCodeConstants::ACTIVE)
                ->count(),
            'rejected' => (clone $leave_request)
                ->whereNotNull('director_action_at')
                ->where('director_approved', StatusCodeConstants::INACTIVE)
                ->count(),
        ];

        return self::response($data);
    }

    public function managerApprove(LeaveRequestManagerApproveRequest $request, string $uuid)
    {
        $leave_request = LeaveRequest::findByUuid($uuid);

        $manager = User::findByUuid(self::auth()->uuid);

        throw_if($manager->employment?->is_manager != StatusCodeConstants::ACTIVE, AppException::class, 'Manager access only');
        throw_if($leave_request->director_action_at != null, AppException::class, 'Director already approved or rejected');
        throw_if($leave_request->manager_approver_id != $manager->id, AppException::class, 'Invalid manager approver');
        throw_if($leave_request->handover_by && $leave_request->handover_action_at == null, AppException::class, 'Handover not yet approved or rejected');
        throw_if($leave_request->handover_by && $leave_request->handover_approved != StatusCodeConstants::ACTIVE, AppException::class, 'Handover not approved');
        throw_if($request->approve && $this->availableLeaveDays($leave_request->leaveEntitlement) < $leave_request->total_days, AppException::class, 'Insufficient leave balance');

        DB::beginTransaction();

        try {

            $leave_request->update([
                'manager_action_by' => $manager->id,
                'manager_action_at' => self::currentDateTime(),
                'manager_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'manager_remark' => $request->remark,
            ]);

            if ($request->approve)
            {
                $directors = User::whereHas('employment', function ($query) {
                    $query->where('is_director', '=', StatusCodeConstants::ACTIVE);
                })
                    ->where('is_active', StatusCodeConstants::ACTIVE)
                    ->get();

                if ($directors->isNotEmpty())
                {
                    foreach($directors as $director)
                    {
                        $data = [
                            'name' => trim(($director->personal?->first_name ?? '') . ' ' . ($director->personal?->last_name ?? '')) ?: $director->email,
                            'applicant_name' => trim(($leave_request->user->personal?->first_name ?? '') . ' ' . ($leave_request->user->personal?->last_name ?? '')) ?: $leave_request->user->email,
                            'applicant_email' => $leave_request->user->email,
                            'applicant_phone_number' => $leave_request->user->contact?->phone_number,
                            'submitted_at' => self::currentDateTime()->format('Y-m-d h:i:s A'),
                            'subject' => 'PE Portal - Leave Pending Director Approval',
                            'title' => 'Leave Pending Director Approval',
                            'leave_request' => $leave_request,
                            'leave_entitlement' => $leave_request->leaveEntitlement,
                            'handover_remark' => $leave_request->handover_remark,
                            'manager_remark' => $leave_request->manager_remark,
                            'action_url' => url('/leave-request-review?token=' . Password::createToken($director) . '&email=' . urlencode($director->email) . '&leave_request_uuid=' . $leave_request->uuid . '&type=director'),
                            'action_label' => 'Review Leave',
                        ];

                        Mail::to($director->email)->send(new LeaveApplicationMail($data));
                    }
                }
            }

            $leave_request = LeaveRequest::findByUuid($uuid);

            DB::commit();
            
            return self::response(new LeaveRequestResource($leave_request));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function directorApprove(LeaveRequestDirectorApproveRequest $request, string $uuid)
    {
        DB::beginTransaction();

        try {
            $leave_request = LeaveRequest::findByUuid($uuid);

            $director = User::findByUuid(self::auth()->uuid);

            throw_if($director->employment?->is_director != StatusCodeConstants::ACTIVE, AppException::class, 'Director access only');
            throw_if($leave_request->manager_action_at == null, AppException::class, 'Manager not yet approved or rejected');
            throw_if($leave_request->manager_approved != StatusCodeConstants::ACTIVE, AppException::class, 'Manager not approved');
            throw_if($leave_request->director_action_at, AppException::class, 'Leave request already reviewed by director');
            throw_if($request->approve && $this->availableLeaveDays($leave_request->leaveEntitlement) < $leave_request->total_days, AppException::class, 'Insufficient leave balance');

            $leave_request->update([
                'director_action_by' => $director->id,
                'director_action_at' => self::currentDateTime(),
                'director_approved' => $request->approve ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'director_remark' => $request->remark,
            ]);

            if ($request->approve)
            {
                $leave_entitlement = $leave_request->leaveEntitlement;

                $this->deductLeaveDays($leave_entitlement, $leave_request->total_days, self::auth()->uuid);
            }

            $leave_request = LeaveRequest::findByUuid($uuid);

            DB::commit();

            return self::response(new LeaveRequestResource($leave_request));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    private function availableLeaveDays($leave_entitlement)
    {
        $carried_forward_days = 0;

        if ($leave_entitlement->carry_forward_expiry_date && Carbon::parse($leave_entitlement->carry_forward_expiry_date)->endOfDay()->gte(self::currentDateTime()))
        {
            $carried_forward_days = $leave_entitlement->carried_forward_days;
        }

        return $leave_entitlement->balance_days + $carried_forward_days;
    }

    private function deductLeaveDays($leave_entitlement, $total_days, $updated_by)
    {
        $carried_forward_days = 0;

        if ($leave_entitlement->carry_forward_expiry_date && Carbon::parse($leave_entitlement->carry_forward_expiry_date)->endOfDay()->gte(self::currentDateTime()))
        {
            $carried_forward_days = $leave_entitlement->carried_forward_days;
        }

        $deduct_carried_forward_days = min($carried_forward_days, $total_days);
        $remaining_days = $total_days - $deduct_carried_forward_days;

        $leave_entitlement->update([
            'used_days' => $leave_entitlement->used_days + $total_days,
            'carried_forward_days' => $leave_entitlement->carried_forward_days - $deduct_carried_forward_days,
            'balance_days' => $leave_entitlement->balance_days - $remaining_days,
            'updated_by' => $updated_by,
            'updated_at' => self::currentDateTime(),
        ]);
    }
}
