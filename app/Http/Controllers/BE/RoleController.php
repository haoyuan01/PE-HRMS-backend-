<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Exceptions\AppException;
use App\Filters\RoleFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleIndexRequest;
use App\Http\Requests\RoleShowRequest;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Requests\RoleUpdateStatusRequest;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct(private RoleFilter $role_filter, private RoleService $role_service)
    {
    }

    public function index(RoleIndexRequest $request)
    {
        $role = Role::with([
            'permissions',
        ]);

        $role = $this->role_filter->apply($request, $request->size, $role);

        return self::responsePaginated(RoleResource::collection($role), $role);
    }

    public function store(RoleStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $role = new Role();
            $role->uuid = self::uuid();
            $role->name = $request->name;
            $role->guard_name = 'web';
            $role->is_active = StatusCodeConstants::ACTIVE;
            $role->created_by = $role->updated_by = self::auth()->uuid;
            $role->created_at = $role->updated_at = self::currentDateTime();
            $role->save();

            if ($request->filled('permissions'))
            {
                $uuids = collect($request->permissions)
                    ->pluck('uuid')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                $permissions = Permission::query()
                    ->whereIn('uuid', $uuids)
                    ->where('guard_name', 'web')
                    ->get();

                $role->syncPermissions($permissions);
            }

            $role->load(['permissions']);

            DB::commit();

            return self::response(new RoleResource($role));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function update(RoleUpdateRequest $request, $uuid)
    {
        $role = $this->role_service->findByUUID($uuid);

        DB::beginTransaction();

        try {
            $role->name = $request->name;
            $role->updated_by = self::auth()->uuid;
            $role->updated_at = self::currentDateTime();
            $role->save();

            if ($request->filled('permissions'))
            {
                $uuids = collect($request->permissions)
                    ->pluck('uuid')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                $permissions = Permission::query()
                    ->whereIn('uuid', $uuids)
                    ->where('guard_name', 'web')
                    ->get();

                $role->syncPermissions($permissions);
            }

            $role->load(['permissions']);

            DB::commit();

            return self::response(new RoleResource($role));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function updateStatus(RoleUpdateStatusRequest $request, $uuid)
    {
        $role = $this->role_service->findByUUID($uuid);

        $role->is_active = $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE;
        $role->updated_by = self::auth()->uuid;
        $role->updated_at = self::currentDateTime();
        $role->save();

        DB::commit();

        return self::response(new RoleResource($role));
    }

    public function show(RoleShowRequest $request, $uuid)
    {
        $role = $this->role_service->findByUUID($uuid);

        return self::response(new RoleResource($role));
    }
}
