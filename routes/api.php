<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UrnController;
use App\Http\Controllers\Api\V1\RowController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\NicheController;
use App\Http\Controllers\Api\V1\CasketController;
use App\Http\Controllers\Api\V1\PersonController;
use App\Http\Controllers\Api\V1\BuildingController;

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

  Route::get('/buildings/getAllBuildingsNoPaginated', [BuildingController::class, 'getAllBuildingsNoPaginated']);
  Route::apiResource('buildings', BuildingController::class);

  Route::get('/rooms/getRoomsFromBuilding', [RoomController::class, 'getRoomsFromBuilding']);
  Route::get('/rooms/getAllRoomsFromBuildingNoPagination/{buildingId}', [RoomController::class, 'getAllRoomsFromBuildingNoPagination']);
  Route::apiResource('rooms', RoomController::class);

  Route::get('/rows/getRowsFromRoom', [RowController::class, 'getRowsFromRoom']);
  Route::get('/rows/getAllRowsFromRoomNoPagination/{roomId}', [RowController::class, 'getAllRowsFromRoomNoPagination']);
  Route::apiResource('rows', RowController::class);

  Route::get('/niches/getNichesFromRow', [NicheController::class, 'getNichesFromRow']);
  Route::get('/rows/getAllNichesFromRowNoPagination/{rowId}', [NicheController::class, 'getAllNichesFromRowNoPagination']);
  Route::apiResource('niches', NicheController::class);

  Route::get('/urns/getUrnsFromNiche', [UrnController::class, 'getUrnsFromNiche']);
  Route::apiResource('urns', UrnController::class);

  Route::apiResource('caskets', CasketController::class);

  Route::apiResource('people', PersonController::class);
});
