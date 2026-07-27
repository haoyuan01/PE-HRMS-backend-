<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class LeaveRequestResource extends JsonResource
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
            'handover_action_at' => $this->handover_action_at ? Carbon::parse($this->handover_action_at)->utc() : null,
            'handover_approved' => $this->handover_approved,
            'manager_action_at' => $this->manager_action_at ? Carbon::parse($this->manager_action_at)->utc() : null,
            'manager_approved' => $this->manager_approved,
            'manager_remark' => $this->manager_remark,
            'director_action_at' => $this->director_action_at ? Carbon::parse($this->director_action_at)->utc() : null,
            'director_approved' => $this->director_approved,
            'director_remark' => $this->director_remark,
            'handover_remark' => $this->handover_remark,
            'resume_date' => $this->resume_date ? Carbon::parse($this->resume_date)->utc() : null,
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'attachment_url' => $this->attachment_url ? asset(Storage::url($this->attachment_url)) : null,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_by' => $this->updated_by,
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
            'user' => new UserResource($this->whenLoaded('user')),
            'manager_approver' => new UserResource($this->whenLoaded('managerApprover')),
            'leave_entitlement' => new LeaveEntitlementResource($this->whenLoaded('leaveEntitlement')),
            'manager_action_by' => new UserResource($this->whenLoaded('managerActionBy')),
            'director_action_by' => new UserResource($this->whenLoaded('directorActionBy')),
            'handover_by' => new UserResource($this->whenLoaded('handoverBy')),
            'leave_request_dates' => LeaveRequestDateResource::collection($this->whenLoaded('leaveRequestDates')),
        ];

        return $data;
    }
}
