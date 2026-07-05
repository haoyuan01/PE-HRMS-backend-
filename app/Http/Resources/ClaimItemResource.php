<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ClaimItemResource extends JsonResource
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
            'amount' => $this->amount,
            'date' => $this->date ? Carbon::parse($this->date)->utc() : null,
            'attachment_path' => $this->attachment_path ? asset(Storage::url($this->attachment_path)) : null,
            'remark' => $this->remark,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_by' => $this->updated_by,
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
            'manager_action_by' => new UserResource($this->whenLoaded('managerActionBy')),
            'manager_action_at' => $this->manager_action_at ? Carbon::parse($this->manager_action_at)->utc() : null,
            'manager_approved' => $this->manager_approved,
            'director_action_by' => new UserResource($this->whenLoaded('directorActionBy')),
            'director_action_at' => $this->director_action_at ? Carbon::parse($this->director_action_at)->utc() : null,
            'director_approved' => $this->director_approved,
            'claim_header' => new ClaimHeaderResource($this->whenLoaded('claimHeader')),
        ];

        return $data;
    }
}
