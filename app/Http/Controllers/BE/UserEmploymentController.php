<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserEmploymentUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\Department;
use App\Models\Office;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;

class UserEmploymentController extends Controller
{
    public function __construct()
    {
    }

    public function update(UserEmploymentUpdateRequest $request)
    {
        $user = User::findByUuid($request->user_uuid);

        if ($request->department_uuid)
        {
            $department = Department::findByUuid($request->department_uuid);
        }

        if ($request->position_uuid)
        {
            $position = Position::findByUuid($request->position_uuid);
        }

        if ($request->office_uuid)
        {
            $office = Office::findByUuid($request->office_uuid);
        }
        
        $employment = [
            'user_id' => $user->id,
            'position_id' => $request->position_uuid ? $position->id : null,
            'department_id' => $request->department_uuid ? $department->id : null,
            'office_id' => $request->office_uuid ? $office->id : null,
            'joined_date' => $request->joined_date,
            'is_manager' => $request->is_manager ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'is_accountant' => $request->is_accountant ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ];

        if ($user->employment)
        {
            $user->employment->update($employment);
        }
        else
        {
            $user->employment()->create([
                ...$employment,
                'uuid' => self::uuid(),
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
            ]);
        }

        $user->load([
            'employment.office',
            'employment.department',
            'employment.position',
        ]);
        
        return self::response(new UserResource($user));
    }
}
