<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserCertificateResource extends JsonResource
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
            'organization' => $this->organization,
            'description' => $this->description,
            'date_applied' => $this->date_applied ? Carbon::parse($this->date_applied)->utc() : null,
            'valid_until' => $this->valid_until ? Carbon::parse($this->valid_until)->utc() : null,
            'attachment_path' => $this->attachment_path ? asset(Storage::url($this->attachment_path)) : null,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_by' => $this->updated_by,
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
            'user' => new UserResource($this->whenLoaded('user')),
        ];

        return $data;
    }
}
