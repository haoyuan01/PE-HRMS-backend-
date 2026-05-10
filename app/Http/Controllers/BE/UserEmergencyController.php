<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserEmergencyUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserEmergencyController extends Controller
{
    public function __construct()
    {
    }

    public function update(UserEmergencyUpdateRequest $request)
    {
        $user = User::findByUuid($request->user_uuid);

        $emergency = [
            'user_id' => $user->id,
            'name' => $request->name,
            'relationship' => $request->relationship,
            'phone_number' => $request->phone_number,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ];

        if ($user->emergency)
        {
            $user->emergency->update($emergency);
        }
        else
        {
            $user->emergency()->create([
                ...$emergency,
                'uuid' => self::uuid(),
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
            ]);
        }

        $user->load(['emergency']);

        return self::response(new UserResource($user));
    }
}
