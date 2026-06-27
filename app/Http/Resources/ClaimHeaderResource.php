<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ClaimHeaderResource extends JsonResource
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
            'remark' => $this->remark,
            'total_amount' => $this->total_amount,
            'start_date' => $this->start_date ? Carbon::parse($this->start_date)->utc() : null,
            'end_date' => $this->end_date ? Carbon::parse($this->end_date)->utc() : null,
            'approved_at' => $this->approved_at ? Carbon::parse($this->approved_at)->utc() : null,
            'paid_at' => $this->paid_at ? Carbon::parse($this->paid_at)->utc() : null,
            'rejected_at' => $this->rejected_at ? Carbon::parse($this->rejected_at)->utc() : null,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_by' => $this->updated_by,
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
            'user' => new UserResource($this->whenLoaded('user')),
            'manager_approver' => new UserResource($this->whenLoaded('managerApprover')),
            'claim_items' => ClaimItemResource::collection($this->whenLoaded('claimItems')),
        ];

        return $data;
    }
}
