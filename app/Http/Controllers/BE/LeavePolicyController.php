<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Exceptions\AppException;
use App\Filters\LeavePolicyFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeavePolicyIndexRequest;
use App\Http\Requests\LeavePolicyShowRequest;
use App\Http\Requests\LeavePolicyStoreRequest;
use App\Http\Requests\LeavePolicyUpdateRequest;
use App\Http\Requests\LeavePolicyUpdateStatusRequest;
use App\Http\Resources\LeavePolicyResource;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyTier;
use Illuminate\Support\Facades\DB;

class LeavePolicyController extends Controller
{
    public function __construct(private LeavePolicyFilter $leave_policy_filter)
    {
    }

    public function index(LeavePolicyIndexRequest $request)
    {
        $leave_policy = LeavePolicy::with([
            'leavePolicyTiers',
        ])->active();

        $leave_policy = $this->leave_policy_filter->apply($request, $request->size, $leave_policy);

        return self::responsePaginated(LeavePolicyResource::collection($leave_policy), $leave_policy);
    }

    public function store(LeavePolicyStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $leave_policy = LeavePolicy::create([
                'uuid' => self::uuid(),
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'allow_half_day' => $request->allow_half_day ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'carry_forward_days' => $request->carry_forward_days ?? 0,
                'carry_forward_expiry_month' => $request->carry_forward_expiry_month,
                'carry_forward_expiry_date' => $request->carry_forward_expiry_date,
                'is_handover_required' => $request->is_handover_required ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'handover_min_days' => $request->handover_min_days ?? 0,
                'min_notice_days' => $request->min_notice_days ?? 0,
                'requires_attachment' => $request->requires_attachment ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'is_paid' => $request->is_paid ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            if ($request->filled('leave_policy_tiers') && !empty($request->leave_policy_tiers))
            {
                foreach($request->leave_policy_tiers as $tier)
                {
                    $this->validateLeavePolicyTier($leave_policy->id, $tier['service_year_from'], $tier['service_year_to'] ?? null);

                    LeavePolicyTier::create([
                        'uuid' => self::uuid(),
                        'leave_policy_id' => $leave_policy->id,
                        'service_year_from' => $tier['service_year_from'],
                        'service_year_to' => $tier['service_year_to'] ?? null,
                        'entitlement_days' => $tier['entitlement_days'],
                        'is_active' => StatusCodeConstants::ACTIVE,
                        'created_by' => self::auth()->uuid,
                        'created_at' => self::currentDateTime(),
                        'updated_by' => self::auth()->uuid,
                        'updated_at' => self::currentDateTime(),
                    ]);
                }
            }

            $leave_policy->load(['leavePolicyTiers']);

            DB::commit();

            return self::response(new LeavePolicyResource($leave_policy));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(LeavePolicyUpdateRequest $request, string $uuid)
    {
        DB::beginTransaction();

        try {
            $leave_policy = LeavePolicy::findByUuid($uuid);

            if ($request->filled('leave_policy_tiers') && !empty($request->leave_policy_tiers))
            {
                $tier_uuids = collect($request->leave_policy_tiers)
                    ->pluck('uuid')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                $leave_policy->leavePolicyTiers()
                    ->whereNotIn('uuid', $tier_uuids)
                    ->update([
                        'is_active' => StatusCodeConstants::INACTIVE,
                        'updated_by' => self::auth()->uuid,
                        'updated_at' => self::currentDateTime(),
                    ]);

                foreach($request->leave_policy_tiers as $tier)
                {
                    if (isset($tier['uuid']) && $tier['uuid'])
                    {
                        $leave_policy_tier = LeavePolicyTier::where('uuid', $tier['uuid'])
                            ->where('leave_policy_id', $leave_policy->id)
                            ->active()
                            ->firstOrFail();

                        $this->validateLeavePolicyTier($leave_policy->id, $tier['service_year_from'], $tier['service_year_to'] ?? null, $leave_policy_tier->uuid);

                        $leave_policy_tier->update([
                            'service_year_from' => $tier['service_year_from'],
                            'service_year_to' => $tier['service_year_to'] ?? null,
                            'entitlement_days' => $tier['entitlement_days'],
                            'updated_by' => self::auth()->uuid,
                            'updated_at' => self::currentDateTime(),
                        ]);
                    }
                    else
                    {
                        $this->validateLeavePolicyTier($leave_policy->id, $tier['service_year_from'], $tier['service_year_to'] ?? null);

                        LeavePolicyTier::create([
                            'uuid' => self::uuid(),
                            'leave_policy_id' => $leave_policy->id,
                            'service_year_from' => $tier['service_year_from'],
                            'service_year_to' => $tier['service_year_to'] ?? null,
                            'entitlement_days' => $tier['entitlement_days'],
                            'is_active' => StatusCodeConstants::ACTIVE,
                            'created_by' => self::auth()->uuid,
                            'created_at' => self::currentDateTime(),
                            'updated_by' => self::auth()->uuid,
                            'updated_at' => self::currentDateTime(),
                        ]);
                    }
                }
            }

            $leave_policy->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'allow_half_day' => $request->allow_half_day ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'carry_forward_days' => $request->carry_forward_days ?? 0,
                'carry_forward_expiry_month' => $request->carry_forward_expiry_month,
                'carry_forward_expiry_date' => $request->carry_forward_expiry_date,
                'is_handover_required' => $request->is_handover_required ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'handover_min_days' => $request->handover_min_days ?? 0,
                'min_notice_days' => $request->min_notice_days ?? 0,
                'requires_attachment' => $request->requires_attachment ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'is_paid' => $request->is_paid ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            $leave_policy->load(['leavePolicyTiers']);

            DB::commit();

            return self::response(new LeavePolicyResource($leave_policy));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(LeavePolicyUpdateStatusRequest $request, string $uuid)
    {
        $leave_policy = LeavePolicy::findByUuid($uuid);

        $leave_policy->update([
            'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);

        return self::response(new LeavePolicyResource($leave_policy));
    }

    public function show(LeavePolicyShowRequest $request, string $uuid)
    {
        $leave_policy = LeavePolicy::findByUuid($uuid);

        return self::response(new LeavePolicyResource($leave_policy));
    }

    private function validateLeavePolicyTier(int $leave_policy_id, int $service_year_from, ?int $service_year_to, ?string $ignore_uuid = null)
    {
        throw_if($service_year_to !== null && $service_year_to <= $service_year_from, AppException::class, 'Service year to must be greater than service year from');

        $service_year_to = $service_year_to ?? 999999;

        $leave_policy_tiers = LeavePolicyTier::where('leave_policy_id', $leave_policy_id)
            ->active();

        if ($ignore_uuid)
        {
            $leave_policy_tiers->where('uuid', '!=', $ignore_uuid);
        }

        $leave_policy_tiers = $leave_policy_tiers->get();

        foreach($leave_policy_tiers as $leave_policy_tier)
        {
            $existing_service_year_to = $leave_policy_tier->service_year_to ?? 999999;

            throw_if(
                $leave_policy_tier->service_year_from < $service_year_to && $existing_service_year_to > $service_year_from,
                AppException::class,
                'Leave policy tier service year range is overlapping'
            );
        }
    }
}
