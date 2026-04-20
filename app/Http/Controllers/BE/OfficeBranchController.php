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
use App\Services\OfficeBranchService;

class OfficeBranchController extends Controller
{
    public function __construct(private OfficeBranchFilter $office_branch_filter, private OfficeBranchService $office_branch_service)
    {
    }

    public function index(OfficeBranchIndexRequest $request)
    {
        $office_branch = OfficeBranch::query();

        $office_branch = $this->office_branch_filter->apply($request, $request->size, $office_branch);

        return self::responsePaginated(OfficeBranchResource::collection($office_branch), $office_branch);
    }

    public function store(OfficeBranchStoreRequest $request)
    {
        $office_branch = new OfficeBranch();
        $office_branch->uuid = self::uuid();
        $office_branch->name = $request->name;
        $office_branch->description = $request->description;
        $office_branch->address_1 = $request->address_1;
        $office_branch->address_2 = $request->address_2;
        $office_branch->address_3 = $request->address_3;
        $office_branch->city = $request->city;
        $office_branch->state = $request->state;
        $office_branch->postcode = $request->postcode;
        $office_branch->country = $request->country;
        $office_branch->phone_code = $request->phone_code;
        $office_branch->phone_number = $request->phone_number;
        $office_branch->phone_iso = $request->phone_iso;
        $office_branch->fax_code = $request->fax_code;
        $office_branch->fax_number = $request->fax_number;
        $office_branch->fax_iso = $request->fax_iso;
        $office_branch->email = $request->email;
        $office_branch->is_active = StatusCodeConstants::ACTIVE;
        $office_branch->created_by = $office_branch->updated_by = self::auth()->uuid;
        $office_branch->created_at = $office_branch->updated_at = self::currentDateTime();
        $office_branch->save();
        
        return self::response(new OfficeBranchResource($office_branch));
    }

    public function update(OfficeBranchUpdateRequest $request, $uuid)
    {
        $office_branch = $this->office_branch_service->findByUUID($uuid);
        $office_branch->name = $request->name;
        $office_branch->description = $request->description;
        $office_branch->address_1 = $request->address_1;
        $office_branch->address_2 = $request->address_2;
        $office_branch->address_3 = $request->address_3;
        $office_branch->city = $request->city;
        $office_branch->state = $request->state;
        $office_branch->postcode = $request->postcode;
        $office_branch->country = $request->country;
        $office_branch->phone_code = $request->phone_code;
        $office_branch->phone_number = $request->phone_number;
        $office_branch->phone_iso = $request->phone_iso;
        $office_branch->fax_code = $request->fax_code;
        $office_branch->fax_number = $request->fax_number;
        $office_branch->fax_iso = $request->fax_iso;
        $office_branch->email = $request->email;
        $office_branch->updated_by = self::auth()->uuid;
        $office_branch->updated_at = self::currentDateTime();
        $office_branch->save();
        
        return self::response(new OfficeBranchResource($office_branch));
    }

    public function updateStatus(OfficeBranchUpdateStatusRequest $request, $uuid)
    {
        $office_branch = $this->office_branch_service->findByUUID($uuid);
        $office_branch->is_active = $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE;
        $office_branch->updated_by = self::auth()->uuid;
        $office_branch->updated_at = self::currentDateTime();
        $office_branch->save();
        
        return self::response(new OfficeBranchResource($office_branch));
    }

    public function show(OfficeBranchShowRequest $request, $uuid)
    {
        $office_branch = $this->office_branch_service->findByUUID($uuid);

        return self::response(new OfficeBranchResource($office_branch));
    }


}
