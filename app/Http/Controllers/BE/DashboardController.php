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
    public function __construct(
        private UpcomingEventFilter $upcoming_event_filter,
        private LeaveRequestFilter $leave_request_filter,
        private ClaimHeaderFilter $claim_header_filter,
        private AnnouncementFilter $announcement_filter
    )
    {
    }

    public function upcomingEvent(UpcomingEventIndexRequest $request)
    {
        $upcoming_event = UpcomingEvent::with([
            'upcomingEventImages',
            'upcomingEventDepartments.department',
            'upcomingEventOffices.office',
        ])->active();

        $upcoming_event = $this->upcoming_event_filter->apply($request, $request->size, $upcoming_event);

        return self::responsePaginated(UpcomingEventResource::collection($upcoming_event), $upcoming_event);
    }

    public function leaveRequest(LeaveRequestIndexRequest $request)
    {
        $leave_request = LeaveRequest::with([
            'leaveRequestDates',

            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
            'user.certificates',

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

    public function claimHeader(ClaimHeaderIndexRequest $request)
    {
        $claim_header = ClaimHeader::with([
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
            'user.certificates',

            'managerApprover.personal',
            'managerApprover.contact',
            'managerApprover.employment.office',
            'managerApprover.employment.position',
            'managerApprover.employment.department',
            'managerApprover.emergency',

            'managerReviewedBy.personal',
            'managerReviewedBy.contact',
            'managerReviewedBy.employment.office',
            'managerReviewedBy.employment.position',
            'managerReviewedBy.employment.department',
            'managerReviewedBy.emergency',

            'directorReviewedBy.personal',
            'directorReviewedBy.contact',
            'directorReviewedBy.employment.office',
            'directorReviewedBy.employment.position',
            'directorReviewedBy.employment.department',
            'directorReviewedBy.emergency',

            'claimItems.managerActionBy.personal',
            'claimItems.managerActionBy.contact',
            'claimItems.managerActionBy.employment.office',
            'claimItems.managerActionBy.employment.position',
            'claimItems.managerActionBy.employment.department',
            'claimItems.managerActionBy.emergency',

            'claimItems.directorActionBy.personal',
            'claimItems.directorActionBy.contact',
            'claimItems.directorActionBy.employment.office',
            'claimItems.directorActionBy.employment.position',
            'claimItems.directorActionBy.employment.department',
            'claimItems.directorActionBy.emergency',
        ])->active();

        $claim_header = $this->claim_header_filter->apply($request, $request->size, $claim_header);

        return self::responsePaginated(ClaimHeaderResource::collection($claim_header), $claim_header);
    }

    public function announcement(AnnouncementIndexRequest $request)
    {
        $announcement = Announcement::with([
            'announcementImages',
        ])->active();

        $announcement = $this->announcement_filter->apply($request, $request->size, $announcement);

        return self::responsePaginated(AnnouncementResource::collection($announcement), $announcement);
    }

    public function dashboardSummary()
    {
        $user = User::findByUuid(self::auth()->uuid);

        $leave_balance = LeaveEntitlement::where('user_id', $user->id)
            ->active()
            ->get()
            ->sum(function ($leave_entitlement) {
                return ($leave_entitlement->entitled_days + $leave_entitlement->carried_forward_days) - $leave_entitlement->used_days;
            });

        $pending_leave = LeaveRequest::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('manager_approved', '!=', StatusCodeConstants::ACTIVE)
                    ->orWhere('handover_approved', '!=', StatusCodeConstants::ACTIVE)
                    ->orWhere('director_approved', '!=', StatusCodeConstants::ACTIVE);
            })
            ->active()
            ->count();

        $pending_claim = ClaimHeader::where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('manager_reviewed_at')
                    ->orWhereNull('director_reviewed_at');
            })
            ->active()
            ->count();

        $total_users = User::where('is_active', StatusCodeConstants::ACTIVE)
            ->count();

        $payroll = Payroll::where('user_id', $user->id)
            ->where('is_published', StatusCodeConstants::ACTIVE)
            ->active()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        $data = [
            'leave_balance' => $leave_balance,
            'pending_leave' => $pending_leave,
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
