<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ConfigurationResource extends JsonResource
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
            'key' => $this->key,
            'value' => $this->value,
            'value_type' => $this->value_type,
            'description' => $this->description,
            'is_editable' => $this->is_editable,
            'is_viewable' => $this->is_viewable,
            'created_at'    => Carbon::parse($this->created_at)->utc(),
            'updated_at'    => Carbon::parse($this->updated_at)->utc(),
        ];

        return $data;
    }
}
