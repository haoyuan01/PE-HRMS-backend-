<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class LeavePolicyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'allow_half_day' => $this->allow_half_day,
            'carry_forward_days' => $this->carry_forward_days,
            'carry_forward_expiry_month' => $this->carry_forward_expiry_month,
            'carry_forward_expiry_date' => $this->carry_forward_expiry_date,
            'is_handover_required' => $this->is_handover_required,
            'handover_min_days' => $this->handover_min_days,
            'min_notice_days' => $this->min_notice_days,
            'requires_attachment' => $this->requires_attachment,
            'is_paid' => $this->is_paid,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_by' => $this->updated_by,
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
            'leave_policy_tiers' => LeavePolicyTierResource::collection($this->whenLoaded('leavePolicyTiers')),
        ];

        return $data;
    }
}
