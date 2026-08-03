<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\UserFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveEntitlementShowRequest;
use App\Http\Requests\LeaveEntitlementUpdateRequest;
use App\Http\Requests\UserIndexRequest;
use App\Http\Resources\LeaveEntitlementResource;
use App\Http\Resources\UserResource;
use App\Models\LeaveEntitlement;
use App\Models\LeavePolicy;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveEntitlementController extends Controller
{
    public function __construct(private UserFilter $user_filter)
    {
    }

    public function index(UserIndexRequest $request)
    {
        $this->assignAllUser();

        $user = User::with([
            'personal',
            'contact',
            'employment.office',
            'employment.department',
            'employment.position',
            'emergency',
            'certificates',
            'leaveEntitlements' => function($query) {
                $query->where('is_active', StatusCodeConstants::ACTIVE);
            },
            'leaveEntitlements.leavePolicy.leavePolicyTiers',
        ])->active();

        $user = $this->user_filter->apply($request, $request->size, $user);

        return self::responsePaginated(UserResource::collection($user), $user);
    }

    public function update(LeaveEntitlementUpdateRequest $request, string $uuid)
    {
        $leave_entitlement = LeaveEntitlement::findByUuid($uuid);

        $leave_entitlement->update([
            'entitled_days' => $request->entitled_days,
            'carried_forward_days' => $request->carried_forward_days,
            'used_days' => $request->used_days,
            'balance_days' => $request->balance_days,
            'carry_forward_expiry_date' => $request->carry_forward_expiry_date,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        return self::response(new LeaveEntitlementResource($leave_entitlement));
    }

    public function show(LeaveEntitlementShowRequest $request, string $uuid)
    {
        $leave_entitlement = LeaveEntitlement::findByUuid($uuid);

        return self::response(new LeaveEntitlementResource($leave_entitlement));
    }

    public function assignAllUser()
    {
        DB::beginTransaction();

        try {
            $assigned_count = 0;
            $year = self::currentDateTime()->format('Y');
            $leave_policies = LeavePolicy::with(['leavePolicyTiers'])->active()->get();
            $users = User::with(['employment'])->active()->get();

            foreach($users as $user)
            {
                $years_of_service = $user->employment?->joined_date ? Carbon::parse($user->employment->joined_date)->diffInYears(self::currentDateTime()) : 0;

                foreach($leave_policies as $leave_policy)
                {
                    $leave_policy_tier = $leave_policy->leavePolicyTiers
                        ->where('service_year_from', '<=', $years_of_service)
                        ->filter(function($tier) use ($years_of_service) {
                            return $tier->service_year_to === null || $tier->service_year_to > $years_of_service;
                        })
                        ->first();

                    if (!$leave_policy_tier)
                    {
                        $leave_policy_tier = $leave_policy->leavePolicyTiers->first();
                    }

                    $entitled_days = $leave_policy_tier?->entitlement_days ?? 0;
                    $carry_forward_expiry_date = null;

                    // alternative solution
                    if ($leave_policy->carry_forward_expiry_month && $leave_policy->carry_forward_expiry_date)
                    {
                        $carry_forward_expiry_month = Carbon::create($year + 1, $leave_policy->carry_forward_expiry_month, 1);
                        $carry_forward_expiry_date = $carry_forward_expiry_month
                            ->copy()
                            ->day(min($leave_policy->carry_forward_expiry_date, $carry_forward_expiry_month->daysInMonth))
                            ->format('Y-m-d');
                    }

                    // if ($leave_policy->carry_forward_expiry_month && $leave_policy->carry_forward_expiry_date)
                    // {
                    //     $expiry_year = $year + 1;
                    //     $expiry_month = (int) $leave_policy->carry_forward_expiry_month;
                    //     $requested_day = (int) $leave_policy->carry_forward_expiry_date;

                    //     $last_day_of_month = Carbon::create($expiry_year, $expiry_month, 1)->endOfMonth()->day;

                    //     if ($requested_day > $last_day_of_month)
                    //     {
                    //         $requested_day = $last_day_of_month;
                    //     }

                    //     if ($requested_day < 1)
                    //     {
                    //         $requested_day = 1;
                    //     }

                    //     $carry_forward_expiry_date = Carbon::createSafe($expiry_year, $expiry_month, $requested_day)->format('Y-m-d');
                    // }

                    $leave_entitlement = LeaveEntitlement::where('user_id', $user->id)
                        ->where('leave_policy_id', $leave_policy->id)
                        ->where('year', $year)
                        ->first();

                    if (!$leave_entitlement)
                    {
                        LeaveEntitlement::create([
                            'uuid' => self::uuid(),
                            'user_id' => $user->id,
                            'leave_policy_id' => $leave_policy->id,
                            'year' => $year,
                            'entitled_days' => $entitled_days,
                            'used_days' => 0,
                            'balance_days' => $entitled_days,
                            'carried_forward_days' => 0,
                            'carry_forward_expiry_date' => $carry_forward_expiry_date,
                            'is_active' => StatusCodeConstants::ACTIVE,
                            'created_by' => self::auth()->uuid,
                            'created_at' => self::currentDateTime(),
                            'updated_by' => self::auth()->uuid,
                            'updated_at' => self::currentDateTime(),
                        ]);

                        $assigned_count++;
                    }
                }
            }

            DB::commit();

            return self::response([
                'assigned_count' => $assigned_count,
            ]);

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }
}
