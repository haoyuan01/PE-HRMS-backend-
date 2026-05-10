<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserPersonalUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserPersonalController extends Controller
{
    public function __construct()
    {
    }

    public function update(UserPersonalUpdateRequest $request)
    {
        $user = User::findByUuid($request->input('user_uuid'));
        
        $personal = [
            'full_name' => $request->input('full_name'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'identity_number' => $request->input('identity_number'),
            'passport_number' => $request->input('passport_number'),
            'passport_expiry_date' => $request->input('passport_expiry_date'),
            'blood_type' => $request->input('blood_type'),
            'gender' => $request->input('gender'),
            'is_married' => $request->input('is_married') ? StatusCodeConstants::ACTIVE : StatusCodeConstants::INACTIVE,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ];

        if ($request->hasFile('image'))
        {
            if ($user->personal && $user->personal->image_path)
            {
                Storage::disk('public')->delete($user->personal->image_path);
            }
            
            $file = $request->file('image');

            $filename = time() . '_' . self::uuid() . '.' . $file->getClientOriginalExtension();

            $personal['image_path'] = $file->storeAs('users', $filename, 'public');
        }
        
        if ($user->personal)
        {
            $user->personal->update($personal);
        }
        else
        {
            $user->personal()->create([
                ...$personal,
                'uuid' => self::uuid(),
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
            ]);
        }

        $user->load(['personal']);
        
        return self::response(new UserResource($user));
    }
}
