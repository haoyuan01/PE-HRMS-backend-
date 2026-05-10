<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\OfficeFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeIndexRequest;
use App\Http\Requests\OfficeShowRequest;
use App\Http\Requests\OfficeStoreRequest;
use App\Http\Requests\OfficeUpdateRequest;
use App\Http\Requests\OfficeUpdateStatusRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;

class OfficeController extends Controller
{
    public function __construct(private OfficeFilter $office_filter)
    {
    }

    public function index(OfficeIndexRequest $request)
    {
        $office = Office::query()->active();

        $office = $this->office_filter->apply($request, $request->size, $office);

        return self::responsePaginated(OfficeResource::collection($office), $office);
    }

    public function store(OfficeStoreRequest $request)
    {
        $office = Office::create([
            'uuid' => self::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'address_1' => $request->address_1,
            'address_2' => $request->address_2,
            'address_3' => $request->address_3,
            'city' => $request->city,
            'state' => $request->state,
            'postcode' => $request->postcode,
            'country' => $request->country,
            'phone_number' => $request->phone_number,
            'fax_number' => $request->fax_number,
            'email' => $request->email,
            'is_active' => StatusCodeConstants::ACTIVE,
            'created_by' => self::auth()->uuid,
            'created_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new OfficeResource($office));
    }

    public function update(OfficeUpdateRequest $request, string $uuid)
    {
        $office = Office::findByUuid($uuid);
        
        $office->update([
            'name' => $request->name,
            'description' => $request->description,
            'address_1' => $request->address_1,
            'address_2' => $request->address_2,
            'address_3' => $request->address_3,
            'city' => $request->city,
            'state' => $request->state,
            'postcode' => $request->postcode,
            'country' => $request->country,
            'phone_number' => $request->phone_number,
            'fax_number' => $request->fax_number,
            'email' => $request->email,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new OfficeResource($office));
    }

    public function updateStatus(OfficeUpdateStatusRequest $request, string $uuid)
    {
        $office = Office::findByUuid($uuid);
        
        $office->update([
            'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new OfficeResource($office));
    }

    public function show(OfficeShowRequest $request, string $uuid)
    {
        $office = Office::findByUuid($uuid);

        return self::response(new OfficeResource($office));
    }


}
