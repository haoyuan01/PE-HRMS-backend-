<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UserPersonalResource extends JsonResource
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
            'full_name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'identity_number' => $this->identity_number,
            'passport_number' => $this->passport_number,
            'passport_expiry_date' => $this->passport_expiry_date ? Carbon::parse($this->passport_expiry_date)->utc() : null,
            'blood_type' => $this->blood_type,
            'image_path' => $this->image_path ? asset(Storage::url($this->image_path)) : null,
            'gender' => $this->gender,
            'is_married' => $this->is_married,
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
