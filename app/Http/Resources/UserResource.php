<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_by' => $this->updated_by,
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
            'personal' => new UserPersonalResource($this->whenLoaded('personal')),
            'contact' => new UserContactResource($this->whenLoaded('contact')),
            'employment' => new UserEmploymentResource($this->whenLoaded('employment')),
            'emergency' => new UserEmergencyResource($this->whenLoaded('emergency')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
        ];
        
        return $data;
    }
}
