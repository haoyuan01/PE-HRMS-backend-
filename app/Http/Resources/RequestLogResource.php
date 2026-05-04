<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class RequestLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'uuid'                  => $this->uuid,
            'method'                => $this->method,
            'path'                  => $this->path,
            'files'                 => $this->files,
            'request_payload'       => $this->request_payload,
            'response_payload'      => $this->response_payload,
            'ip'                    => $this->ip,
            'url'                   => $this->url,
            'scheme'                => $this->scheme,
            'host'                  => $this->host,
            'port'                  => $this->port,
            'cookies'               => $this->cookies,
            'user_agent'            => $this->user_agent,
            'status_code'           => $this->status_code,
            'success'               => $this->success,
            'performance'           => $this->performance,
            'created_at'            => Carbon::parse($this->created_at)->utc(),
            'updated_at'            => Carbon::parse($this->updated_at)->utc(),
            'user'                  => new UserResource($this->whenLoaded('user')),
            'activity_logs'         => ActivityLogResource::collection($this->whenLoaded('activityLogs')),
            'error_logs'            => ErrorLogResource::collection($this->whenLoaded('errorLogs')),
        ];

        return $data;
    }
}
