<?php

namespace App\Helpers;
use Illuminate\Http\Response;
use App\Http\Utility\ApiResponse;

class ResponseHelper {

    public function unauthorized() {
        $unAuthResponse = new ApiResponse('Unauthorized', '401');
        return response()->json($unAuthResponse->toArray(), $unAuthResponse->code());
    }
}