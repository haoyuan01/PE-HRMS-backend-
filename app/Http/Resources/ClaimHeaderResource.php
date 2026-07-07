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
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_by' => $this->updated_by,
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
            'manager_reviewed_by' => new UserResource($this->whenLoaded('managerReviewedBy')),
            'manager_reviewed_at' => $this->manager_reviewed_at ? Carbon::parse($this->manager_reviewed_at)->utc() : null,
            'director_reviewed_by' => new UserResource($this->whenLoaded('directorReviewedBy')),
            'director_reviewed_at' => $this->director_reviewed_at ? Carbon::parse($this->director_reviewed_at)->utc() : null,
            'user' => new UserResource($this->whenLoaded('user')),
            'manager_approver' => new UserResource($this->whenLoaded('managerApprover')),
            'claim_items' => ClaimItemResource::collection($this->whenLoaded('claimItems')),
        ];

        return $data;
    }
}
