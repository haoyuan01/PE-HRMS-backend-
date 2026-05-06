<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\OfficeBranchFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeBranchIndexRequest;
use App\Http\Requests\OfficeBranchShowRequest;
use App\Http\Requests\OfficeBranchStoreRequest;
use App\Http\Requests\OfficeBranchUpdateRequest;
use App\Http\Requests\OfficeBranchUpdateStatusRequest;
use App\Http\Resources\OfficeBranchResource;
use App\Models\OfficeBranch;

class OfficeBranchController extends Controller
{
    public function __construct(private OfficeBranchFilter $office_branch_filter)
    {
    }

    public function index(OfficeBranchIndexRequest $request)
    {
        $office_branch = OfficeBranch::query()->active();

        $office_branch = $this->office_branch_filter->apply($request, $request->size, $office_branch);

        return self::responsePaginated(OfficeBranchResource::collection($office_branch), $office_branch);
    }

    public function store(OfficeBranchStoreRequest $request)
    {
        $office_branch = OfficeBranch::create([
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
            'phone_code' => $request->phone_code,
            'phone_number' => $request->phone_number,
            'phone_iso' => $request->phone_iso,
            'fax_code' => $request->fax_code,
            'fax_number' => $request->fax_number,
            'fax_iso' => $request->fax_iso,
            'email' => $request->email,
            'is_active' => StatusCodeConstants::ACTIVE,
            'created_by' => self::auth()->uuid,
            'created_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new OfficeBranchResource($office_branch));
    }

    public function update(OfficeBranchUpdateRequest $request, string $uuid)
    {
        $office_branch = OfficeBranch::findByUuid($uuid);
        
        $office_branch->update([
            'name' => $request->name,
            'description' => $request->description,
            'address_1' => $request->address_1,
            'address_2' => $request->address_2,
            'address_3' => $request->address_3,
            'city' => $request->city,
            'state' => $request->state,
            'postcode' => $request->postcode,
            'country' => $request->country,
            'phone_code' => $request->phone_code,
            'phone_number' => $request->phone_number,
            'phone_iso' => $request->phone_iso,
            'fax_code' => $request->fax_code,
            'fax_number' => $request->fax_number,
            'fax_iso' => $request->fax_iso,
            'email' => $request->email,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new OfficeBranchResource($office_branch));
    }

    public function updateStatus(OfficeBranchUpdateStatusRequest $request, string $uuid)
    {
        $office_branch = OfficeBranch::findByUuid($uuid);
        
        $office_branch->update([
            'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new OfficeBranchResource($office_branch));
    }

    public function show(OfficeBranchShowRequest $request, string $uuid)
    {
        $office_branch = OfficeBranch::findByUuid($uuid);

        return self::response(new OfficeBranchResource($office_branch));
    }


}
