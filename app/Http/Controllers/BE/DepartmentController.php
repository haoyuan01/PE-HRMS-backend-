<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\DepartmentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentIndexRequest;
use App\Http\Requests\DepartmentShowRequest;
use App\Http\Requests\DepartmentStoreRequest;
use App\Http\Requests\DepartmentUpdateRequest;
use App\Http\Requests\DepartmentUpdateStatusRequest;
use App\Models\Department;
use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;

class DepartmentController extends Controller
{
    public function __construct(private DepartmentFilter $department_filter, private DepartmentService $department_service)
    {
    }

    public function index(DepartmentIndexRequest $request)
    {
        $department = Department::query();

        $department = $this->department_filter->apply($request, $request->size, $department);

        return self::responsePaginated(DepartmentResource::collection($department), $department);
    }

    public function store(DepartmentStoreRequest $request)
    {
        $department = new Department();
        $department->uuid = self::uuid();
        $department->name = $request->name;
        $department->description = $request->description;
        $department->is_active = StatusCodeConstants::ACTIVE;
        $department->created_by = $department->updated_by = self::auth()->uuid;
        $department->created_at = $department->updated_at = self::currentDateTime();
        $department->save();
        
        return self::response(new DepartmentResource($department));
    }

    public function update(DepartmentUpdateRequest $request)
    {
        $department = $this->department_service->findByUUID($request->uuid);
        $department->name = $request->name;
        $department->description = $request->description;
        $department->is_active = StatusCodeConstants::ACTIVE;
        $department->updated_by = self::auth()->uuid;
        $department->updated_at = self::currentDateTime();
        $department->save();
        
        return self::response(new DepartmentResource($department));
    }

    public function updateStatus(DepartmentUpdateStatusRequest $request)
    {
        $department = $this->department_service->findByUUID($request->uuid);
        $department->is_active = $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE;
        $department->updated_by = self::auth()->uuid;
        $department->updated_at = self::currentDateTime();
        $department->save();
        
        return self::response(new DepartmentResource($department));
    }

    public function show(DepartmentShowRequest $request)
    {
        $department = $this->department_service->findByUUID($request->uuid);
        
        return self::response(new DepartmentResource($department));
    }
}
