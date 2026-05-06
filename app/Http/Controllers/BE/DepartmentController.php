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

class DepartmentController extends Controller
{
    public function __construct(private DepartmentFilter $department_filter)
    {
    }

    public function index(DepartmentIndexRequest $request)
    {
        $department = Department::query()->active();

        $department = $this->department_filter->apply($request, $request->size, $department);

        return self::responsePaginated(DepartmentResource::collection($department), $department);
    }

    public function store(DepartmentStoreRequest $request)
    {
        $department = Department::create([
            'uuid' => self::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => StatusCodeConstants::ACTIVE,
            'created_by' => self::auth()->uuid,
            'created_at' => self::currentDateTime(),
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new DepartmentResource($department));
    }

    public function update(DepartmentUpdateRequest $request, string $uuid)
    {
        $department = Department::findByUuid($uuid);
        
        $department->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => StatusCodeConstants::ACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new DepartmentResource($department));
    }

    public function updateStatus(DepartmentUpdateStatusRequest $request, string $uuid)
    {
        $department = Department::findByUuid($uuid);
        
        $department->update([
            'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new DepartmentResource($department));
    }

    public function show(DepartmentShowRequest $request, string $uuid)
    {
        $department = Department::findByUuid($uuid);
        
        return self::response(new DepartmentResource($department));
    }
}
