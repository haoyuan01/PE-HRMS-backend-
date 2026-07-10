<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'description' => $this->description,
            'start_datetime' => Carbon::parse($this->start_datetime)->utc(),
            'end_datetime' => Carbon::parse($this->end_datetime)->utc(),
            'total_days' => $this->total_days,
            'attachment_path' => $this->attachment_path,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_by' => $this->updated_by,
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
        ];

        return $data;
    }
}
