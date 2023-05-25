<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UrnController;
use App\Http\Controllers\Api\V1\RowController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\NicheController;
use App\Http\Controllers\Api\V1\CasketController;
use App\Http\Controllers\Api\V1\PersonController;
use App\Http\Controllers\Api\V1\BuildingController;
use App\Http\Controllers\Api\V1\DepositController;
use App\Http\Controllers\Api\V1\ProvinceController;
use App\Http\Controllers\Api\V1\RelocationController;
use App\Http\Controllers\Api\V1\ReservationController;

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

Route::post('v1/register', [AuthController::class, 'register']);
Route::post('v1/login', [AuthController::class, 'login']);

// api/v1
Route::group(['prefix' => 'v1', 'mamespace' => 'App\Http\Controllers\Api\V1', 'middleware' => 'auth:sanctum'], function() {
  Route::get('/logout', [AuthController::class, 'logout']);

  Route::post('/buildings/getAllBuildingsById', [BuildingController::class, 'getAllBuildingsById']);
  Route::get('/buildings/getAllBuildingsNoPaginated', [BuildingController::class, 'getAllBuildingsNoPaginated']);
  Route::apiResource('buildings', BuildingController::class);

  Route::post('/rooms/getAllRoomsByIdAndBuilding', [RoomController::class, 'getAllRoomsByIdAndBuilding']);
  Route::get('/rooms/getRoomsFromBuilding', [RoomController::class, 'getRoomsFromBuilding']);
  Route::get('/rooms/getAllRoomsFromBuildingNoPagination/{buildingId}', [RoomController::class, 'getAllRoomsFromBuildingNoPagination']);
  Route::apiResource('rooms', RoomController::class);

  Route::post('/rows/getAllRowsByIdAndRoom', [RowController::class, 'getAllRowsByIdAndRoom']);
  Route::get('/rows/getRowsFromRoom', [RowController::class, 'getRowsFromRoom']);
  Route::get('/rows/getAllRowsFromRoomNoPagination/{roomId}', [RowController::class, 'getAllRowsFromRoomNoPagination']);
  Route::apiResource('rows', RowController::class);

  Route::post('/niches/getAllNichesByIdAndRow', [NicheController::class, 'getAllNichesByIdAndRow']);
  Route::get('/niches/getNichesFromRow', [NicheController::class, 'getNichesFromRow']);
  Route::get('/niches/getNichesStatus', [NicheController::class, 'getNichesStatus']);
  Route::get('/niches/getNicheWithUrns/{nicheId}', [NicheController::class, 'getNicheWithUrns']);
  Route::get('/niches/getAllNichesFromRowNoPagination/{rowId}', [NicheController::class, 'getAllNichesFromRowNoPagination']);
  Route::apiResource('niches', NicheController::class);

  Route::post('/urns/getAllUrnsByIdAndNiche', [UrnController::class, 'getAllUrnsByIdAndNiche']);
  Route::get('/urns/getUrnsFromNiche', [UrnController::class, 'getUrnsFromNiche']);
  Route::get('/urns/getUrnById/{urnId}', [UrnController::class, 'getUrnById']);
  Route::apiResource('urns', UrnController::class);

  Route::apiResource('provinces', ProvinceController::class);

  Route::put('/users/updateUser', [UserController::class, 'updateUser']);
  Route::apiResource('users', UserController::class);

  Route::get('/caskets/getCasketById/{casketId}', [CasketController::class, 'getCasketById']);
  Route::get('/caskets/getAllCasketsWithNoDeposit', [CasketController::class, 'getAllCasketsWithNoDeposit']);
  Route::get('/caskets/getAllCasketsWithExpiredReservation', [CasketController::class, 'getAllCasketsWithExpiredReservation']);
  Route::post('/caskets/createCasketWithPeople', [CasketController::class, 'createCasketWithPeople']);
  Route::post('/caskets/updateCasketWithPeople', [CasketController::class, 'updateCasketWithPeople']);
  Route::apiResource('caskets', CasketController::class);

  Route::get('/people/getAllPeopleNoInCasket', [PersonController::class, 'getAllPeopleNoInCasket']);
  Route::get('/people/getAllPeopleNoInCasketNoUsers', [PersonController::class, 'getAllPeopleNoInCasketNoUsers']);
  Route::get('/people/getAllPeopleInCasket/{casketId}', [PersonController::class, 'getAllPeopleInCasket']);
  Route::get('/people/checkExistDni/{dni}', [PersonController::class, 'checkExistDni']);
  Route::get('/people/getPersonById/{personId}', [PersonController::class, 'getPersonById']);
  Route::apiResource('people', PersonController::class);

  Route::get('/reservations/getAllReservationsWithNoDeposit/', [ReservationController::class, 'getAllReservationsWithNoDeposit']);
  Route::post('/reservations/createReservation/', [ReservationController::class, 'createReservation']);
  Route::post('/reservations/updateReservation/', [ReservationController::class, 'updateReservation']);
  Route::get('/reservations/getReservationById/{reservationId}', [ReservationController::class, 'getReservationById']);
  Route::get('/reservations/getAllReservationsFromPerson/', [ReservationController::class, 'getAllReservationsFromPerson']);
  Route::get('/reservations/getAllReservationsWithDepositInDate/', [ReservationController::class, 'getAllReservationsWithDepositInDate']);
  Route::get('/reservations/getAllReservationsFromUrn/', [ReservationController::class, 'getAllReservationsFromUrn']);
  Route::get('/reservations/getAllAvailableResources/', [ReservationController::class, 'getAllAvailableResources']);
  Route::apiResource('reservations', ReservationController::class);

  Route::post('/deposits/createDeposit/', [DepositController::class, 'createDeposit']);
  Route::post('/deposits/updateDeposit/', [DepositController::class, 'updateDeposit']);
  Route::get('/deposits/getDepositByReservationId/{reservationId}', [DepositController::class, 'getDepositByReservationId']);
  Route::get('/deposits/getDepositByPersonId/{personId}', [DepositController::class, 'getDepositByPersonId']);
  Route::get('/deposits/getDepositByCasketId/{casketId}', [DepositController::class, 'getDepositByCasketId']);
  Route::get('/deposits/getDepositByUrnId/{urnId}', [DepositController::class, 'getDepositByUrnId']);
  Route::apiResource('deposits', DepositController::class);

  Route::get('/relocations/getAllRelocationsByCasket/{casketId}', [RelocationController::class, 'getAllRelocationsByCasket']);
  Route::get('/relocations/getAllRelocationsByUrn/{urnId}', [RelocationController::class, 'getAllRelocationsByUrn']);
  Route::post('/relocations/createRelocation/', [RelocationController::class, 'createRelocation']);
  Route::post('/relocations/updateRelocation/', [RelocationController::class, 'updateRelocation']);
  Route::apiResource('relocations', RelocationController::class);
});
