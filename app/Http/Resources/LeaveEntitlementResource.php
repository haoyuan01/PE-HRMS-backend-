<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveEntitlementResource extends JsonResource
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
            'year' => $this->year,
            'entitled_days' => $this->entitled_days,
            'used_days' => $this->used_days,
            'balance_days' => $this->balance_days,
            'carried_forward_days' => $this->carried_forward_days,
            'carry_forward_expiry_date' => $this->carry_forward_expiry_date ? Carbon::parse($this->carry_forward_expiry_date)->utc() : null,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_by' => $this->updated_by,
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
            'user' => new UserResource($this->whenLoaded('user')),
            'leave_policy' => new LeavePolicyResource($this->whenLoaded('leavePolicy')),
        ];

        return $data;
    }
}
