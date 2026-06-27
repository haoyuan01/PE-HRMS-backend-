<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserEmploymentResource extends JsonResource
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
            'joined_date' => $this->joined_date ? Carbon::parse($this->joined_date)->utc() : null,
            'is_manager' => $this->is_manager,
            'is_accountant' => $this->is_accountant,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_by' => $this->updated_by,
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
            'user' => new UserResource($this->whenLoaded('user')),
            'position' => new PositionResource($this->whenLoaded('position')),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'office' => new OfficeResource($this->whenLoaded('office')),
        ];

        return $data;
    }
}
