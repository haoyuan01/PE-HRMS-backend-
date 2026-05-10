<?php

namespace App\Http\Controllers\BE;

use App\Constants\StatusCodeConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserContactUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserContactController extends Controller
{
    public function __construct()
    {
    }

    public function update(UserContactUpdateRequest $request)
    {
        $user = User::findByUuid($request->user_uuid);
        
        $contact = [
            'user_id' => $user->id,
            'company_email' => $request->company_email,
            'phone_number' => $request->phone_number,
            'address_1' => $request->address_1,
            'address_2' => $request->address_2,
            'address_3' => $request->address_3,
            'city' => $request->city,
            'state' => $request->state,
            'postcode' => $request->postcode,
            'country' => $request->country,
            'updated_by' => self::auth()->uuid,
            'updated_at' => self::currentDateTime(),
        ];

        if ($user->contact)
        {
            $user->contact->update($contact);
        }
        else
        {
            $user->contact()->create([
                ...$contact,
                'uuid' => self::uuid(),
                'is_active' => StatusCodeConstants::ACTIVE,
                'created_by' => self::auth()->uuid,
                'created_at' => self::currentDateTime(),
            ]);
        }

        $user->load(['contact']);
        
        return self::response(new UserResource($user));
    }
}
