<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ErrorLogResource extends JsonResource
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
            'level' => $this->level,
            'exception_class' => $this->exception_class,
            'message' => $this->message,
            'exception_code' => $this->exception_code,
            'source_file' => $this->source_file,
            'source_line' => $this->source_line,
            'stack_trace' => $this->stack_trace,
            'previous_exception' => $this->previous_exception,
            'performance' => $this->performance,
            'hostname' => $this->hostname,
            'created_at' => Carbon::parse($this->created_at)->utc(),
            'updated_at' => Carbon::parse($this->updated_at)->utc(),
            'user' => new UserResource($this->whenLoaded('user')),
            'request_log' => new RequestLogResource($this->whenLoaded('requestLog')),
        ];

        return $data;
    }
}
