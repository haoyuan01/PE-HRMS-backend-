<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Filters\RoleFilter;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleIndexRequest;
use App\Http\Requests\RoleShowRequest;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Requests\RoleUpdateStatusRequest;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuthService;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct(private RoleFilter $role_filter, private AuthService $auth_service)
    {
    }

    public function index(RoleIndexRequest $request)
    {
        $role = Role::with([
            'permissions',
        ])->active();

        $role = $this->role_filter->apply($request, $request->size, $role);

        return self::responsePaginated(RoleResource::collection($role), $role);
    }

    public function store(RoleStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $role = Role::create([
                'uuid' => self::uuid(),
                'name' => $request->name,
                'guard_name' => 'web',
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

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

    public function update(RoleUpdateRequest $request, string $uuid)
    {
        $role = Role::findByUuid($uuid);

        DB::beginTransaction();

        try {
            $role->update([
                'name' => $request->name,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

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

    public function updateStatus(RoleUpdateStatusRequest $request, string $uuid)
    {
        DB::beginTransaction();

        try {
            $role = Role::findByUuid($uuid);

            $this->auth_service->validatePasscode(self::auth(), $request->passcode);

            throw_if($request->is_active == StatusCodeConstants::INACTIVE && $role->users()->exists(), new AppException('Role is assigned to user'));

            $role->update([
                'is_active' => $request->is_active ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
                'updated_by' => self::auth()->uuid,
                'updated_at' => self::currentDateTime(),
            ]);

            DB::commit();

            return self::response(new RoleResource($role));

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function show(RoleShowRequest $request, string $uuid)
    {
        $role = Role::findByUuid($uuid);

        return self::response(new RoleResource($role));
    }
}
