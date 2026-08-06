<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OvertimeResource extends JsonResource
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
            'user' => new UserResource($this->whenLoaded('user')),
            'director_action_by' => new UserResource($this->whenLoaded('directorActionBy')),
            'director_action_at' => $this->director_action_at ? Carbon::parse($this->director_action_at)->utc() : null,
            'director_approved' => $this->director_approved,
            'director_remark' => $this->director_remark,
            'description' => $this->description,
            'total_days' => $this->total_days,
            'attachment_path' => $this->attachment_path ? asset(Storage::url($this->attachment_path)) : null,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_by' => $this->updated_by,
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
        ];

        return $data;
    }
}
