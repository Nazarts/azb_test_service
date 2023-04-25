<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TokenController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $token = base64_encode(Str::uuid());

        Session::put('Token', $token);

        Session::save();

        return response()->json([
            'success' => true,
            'token' => $token
        ]);
    }
}
