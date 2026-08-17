<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\AnnouncementFilter;
use App\Filters\ClaimHeaderFilter;
use App\Filters\LeaveRequestFilter;
use App\Filters\UpcomingEventFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementIndexRequest;
use App\Http\Requests\ClaimHeaderIndexRequest;
use App\Http\Requests\DashboardSummaryRequest;
use App\Http\Requests\LeaveRequestIndexRequest;
use App\Http\Requests\UpcomingEventIndexRequest;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\ClaimHeaderResource;
use App\Http\Resources\LeaveRequestResource;
use App\Http\Resources\UpcomingEventResource;
use App\Models\Announcement;
use App\Models\ClaimHeader;
use App\Models\LeaveEntitlement;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\UpcomingEvent;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function __construct()
    {
    }

    public function dashboardSummary(DashboardSummaryRequest $request)
    {
        $user = User::findByUuid(self::auth()->uuid);

        // $leave_entitlement = LeaveEntitlement::active();

        // if ($request->relevant_to_me)
        // {
        //     $leave_entitlement->where('user_id', $user->id);
        // }

        // $leave_balance = $leave_entitlement->get()
        //     ->sum(function ($leave_entitlement) {
        //         return ($leave_entitlement->entitled_days + $leave_entitlement->carried_forward_days) - $leave_entitlement->used_days;
        //     });

        $leave_request = LeaveRequest::where(function ($query) {
                $query->where('manager_approved', '!=', StatusCodeConstants::ACTIVE)
                    ->orWhere('handover_approved', '!=', StatusCodeConstants::ACTIVE)
                    ->orWhere('director_approved', '!=', StatusCodeConstants::ACTIVE);
            })
            ->active();

        if ($request->relevant_to_me)
        {
            $leave_request->where('user_id', $user->id);
        }

        $pending_leave = $leave_request->count();

        $leave_request_current_month = LeaveRequest::whereMonth('created_at', self::currentDateTime()->format('m'))
            ->whereYear('created_at', self::currentDateTime()->format('Y'))
            ->active();

        if ($request->relevant_to_me)
        {
            $leave_request_current_month->where('user_id', $user->id);
        }

        $leave_request_current_month = $leave_request_current_month->count();

        $claim_header = ClaimHeader::where(function ($query) {
                $query->whereNull('manager_reviewed_at')
                    ->orWhereNull('director_reviewed_at');
            })
            ->active();

        if ($request->relevant_to_me)
        {
            $claim_header->where('user_id', $user->id);
        }

        $pending_claim = $claim_header->count();

        $total_users = User::where('is_active', StatusCodeConstants::ACTIVE)
            ->count();

        $payroll = Payroll::where('user_id', $user->id)
            ->where('is_published', StatusCodeConstants::ACTIVE)
            ->active()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        $data = [
            // 'leave_balance' => $leave_balance,
            'pending_leave' => $pending_leave,
            'leave_request_current_month' => $leave_request_current_month,
            'pending_claim' => $pending_claim,
            'total_users' => $total_users,
            'latest_payroll' => $payroll ? [
                'date' => $payroll->year . '-' . str_pad($payroll->month, 2, '0', STR_PAD_LEFT) . '-01',
                'attachment_url' => $payroll->attachment_path ? asset(Storage::url($payroll->attachment_path)) : null,
            ] : null,
        ];

        return self::response($data);
    }
}
