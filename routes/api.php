<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/positions', [\App\Http\Controllers\PositionController::class, 'index']);

Route::middleware('api-session')->group(function () {
    Route::get('/token', [\App\Http\Controllers\TokenController::class, 'index']);
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'create']);
});

Route::get('/users', [\App\Http\Controllers\UserController::class, 'index']);
