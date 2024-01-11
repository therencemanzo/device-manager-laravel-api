<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    /**
        * Get User List
        *
        * This endpoint allows you to get all the users
        * @subgroup Helpers
        * @subgroupDescription get users
        * 
        * @authenticated
    */

    public function __invoke(Request $request){

        $users = User::when(isset($request->q), function($query) use ($request){

            return $query->where('name', 'like', '%'. $request->q. '%')
            ->orWhere('email', 'like', '%'. $request->q. '%');

        })
        ->get();

        return UserResource::collection($users);
    }
}
