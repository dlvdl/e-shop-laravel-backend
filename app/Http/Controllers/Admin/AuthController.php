<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $remember = $credentials["remember"] ?? null;
        unset($credentials["remember"]);

        if (!Auth::attempt($credentials, $remember)) {
            return response()->json(['message' => ["Provided email or password is incorrect"]], 422);
        }

        /** @var \App\Models\User */
        $user = Auth::user();

        if (!$user->is_admin) {
            return response()->json(['message' => ["You dont have permission to authenticate as admin"]], 403);
        }

        $token = $user->createToken("main")->plainTextToken;
        return response()->json(compact("token", "user"), 200)->header('Authorization', $token);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(["message" => [""]], 204);
    }

    public function getUser()
    {
        return new UserResource(Auth::user());
    }

}
