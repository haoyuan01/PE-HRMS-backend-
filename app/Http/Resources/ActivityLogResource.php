<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ActivityLogResource extends JsonResource
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
            'log_name'      => $this->log_name,
            'event'         => $this->event,
            'description'   => $this->description,
            'subject_type'  => $this->subject_type,
            'subject_id'    => $this->subject_id,
            'causer_type'   => $this->causer_type,
            'causer_id'     => $this->causer_id,
            'properties'    => $this->properties,
            'old_values'    => $this->old_values,
            'new_values'    => $this->new_values,
            'performance'   => $this->performance,
            'batch_uuid'    => $this->batch_uuid,
            'created_at'    => Carbon::parse($this->created_at)->utc(),
            'updated_at'    => Carbon::parse($this->updated_at)->utc(),
            'user'          => new UserResource($this->whenLoaded('user')),
        ];

        return $data;
    }
}
