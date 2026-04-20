<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'uuid'          => $this->uuid,
            'name'          => $this->name,
            'description'   => $this->description,
            'is_active'     => $this->is_active,
            'created_by'    => $this->created_by,
            'created_at'    => Carbon::parse($this->created_at)->utc(),
            'updated_by'    => $this->updated_by,
            'updated_at'    => Carbon::parse($this->updated_at)->utc(),
        ];

        return $data;
    }
}
