<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MovementResource extends JsonResource
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
            'movement_type' => new MovementTypeResource($this->whenLoaded('movement_type')),
            'location' => $this->location,
            'start_date' => $this->start_date ? Carbon::parse($this->start_date)->utc() : null,
            'end_date' => $this->end_date ? Carbon::parse($this->end_date)->utc() : null,
            'description' => $this->description,
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
