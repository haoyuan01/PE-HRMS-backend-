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
            'approved_by' => new UserResource($this->whenLoaded('approvedBy')),
            'approved_at' => $this->approved_at ? Carbon::parse($this->approved_at)->utc() : null,
            'released_by' => new UserResource($this->whenLoaded('releasedBy')),
            'released_at' => $this->released_at ? Carbon::parse($this->released_at)->utc() : null,
            'rejected_by' => new UserResource($this->whenLoaded('rejectedBy')),
            'rejected_at' => $this->rejected_at ? Carbon::parse($this->rejected_at)->utc() : null,
            'claim_header' => new ClaimHeaderResource($this->whenLoaded('claimHeader')),
        ];

        return $data;
    }
}
