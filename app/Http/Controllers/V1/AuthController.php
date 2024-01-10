<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * @group Authentication management
 *
 * APIs for autenticating user
 */

class AuthController extends Controller
{

    /**
        * Login
        *
        * This endpoint allows you to login to the application.
        * @bodyParam email string required the email of the user. Example: mahusay@gmail.com
        * @bodyParam password string required the password of the user. Example: password
        * @response {
        *   "data": {
        *   "access_token": "26|XbEfRWazh4Nh5tBdNZlUgZCq8MltnIeNgrvHQzZhe6f55227",
        *   "token_type": "Bearer",
        *   "user": {
        *       "id": 1,
        *       "name": "Juana Hayes",
        *       "email": "tbednar@example.com",
        *       "email_verified_at": "2024-01-09T04:12:17.000000Z",
        *       "is_admin": 1,
        *       "created_at": "2024-01-09T04:12:18.000000Z",
        *       "updated_at": "2024-01-09T04:12:18.000000Z"
        *       }
        *   }
        * }
    */
    public function login(Request $request){

        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if(!Auth::attempt($validated)){
            return response()->json([
                'errors' => 'Login information invalid.'
            ], 401);
        }

        $user = User::where('email', $validated['email'])->first();

        $data = [
            'access_token' => $user->createToken('api_token')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user
        ];

        return response()->json([
            'data' => $data
        ], 200);
    }

    /**
        * Logout
        *
        * This endpoint allows you to logout to the application.
        * @response {
        *   "data": {
        *       "message": "logout",
        *       }
        * }
        * @authenticated
    */
    public function logout(Request $request){

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'logout'
        ], 200);
    }
}