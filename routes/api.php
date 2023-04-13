<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CasketController;
use App\Http\Controllers\Api\V1\PersonController;
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

Route::post('/register', [AuthController::class, 'register']);
Route::post('v1/login', [AuthController::class, 'login']);

// api/v1
Route::group(['prefix' => 'v1', 'mamespace' => 'App\Http\Controllers\Api\V1', 'middleware' => 'auth:sanctum'], function() {
  Route::get('/logout', [AuthController::class, 'logout']);
  Route::apiResource('caskets', CasketController::class);
  Route::apiResource('people', PersonController::class);
});