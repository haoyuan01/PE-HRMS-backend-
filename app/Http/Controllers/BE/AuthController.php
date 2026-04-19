<?php

namespace App\Http\Controllers\BE;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private UserService $user_service, private AuthService $auth_service)
    {
    }

    public function login(AuthLoginRequest $request)
    {
        $user = $this->user_service->findByEmail($request->email);

        $this->auth_service->validatePassword($user, $request->password);

        $token = $user->createToken('api-access')->plainTextToken;

        $data = [
            'user' => new UserResource($user),
            'token' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ];

        return self::response($data, 'Login success');
    }

    public function logout(Request $request)
    {

        $request->user()->currentAccessToken()->delete();

        return self::response(null, 'Logout success');
    }
}
