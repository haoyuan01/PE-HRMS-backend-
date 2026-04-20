<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class OfficeBranchResource extends JsonResource
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
            'address_1'     => $this->address_1,
            'address_2'     => $this->address_2,
            'address_3'     => $this->address_3,
            'city'          => $this->city,
            'state'         => $this->state,
            'postcode'      => $this->postcode,
            'country'       => $this->country,
            'phone_code'    => $this->phone_code,
            'phone_number'  => $this->phone_number,
            'phone_iso'     => $this->phone_iso,
            'fax_code'      => $this->fax_code,
            'fax_number'    => $this->fax_number,
            'fax_iso'       => $this->fax_iso,
            'email'         => $this->email,
            'is_active'     => $this->is_active,
            'created_by'    => $this->created_by,
            'created_at'    => Carbon::parse($this->created_at)->utc(),
            'updated_by'    => $this->updated_by,
            'updated_at'    => Carbon::parse($this->updated_at)->utc(),
        ];

        return $data;
    }
}
