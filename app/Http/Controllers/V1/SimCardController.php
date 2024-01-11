<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\SimCardResource;
use App\Models\SimCard;

class SimCardController extends Controller
{
    /**
        * Get Sim Card List
        *
        * This endpoint allows you to get all the sim card that was not yet assigned to a device
        * @subgroup Helpers
        * @subgroupDescription get available sim cards
        * @authenticated
    */

    public function __invoke(Request $request){

        $users = SimCard::when(isset($request->q), function($query) use ($request){

            return $query->where('name', 'like', '%'. $request->q. '%');

        })
        ->doesntHave('device')
        ->get();

        return SimCardResource::collection($users);
    }
}
