<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Building;
use Illuminate\Http\Request;
use App\Filters\V1\BuildingFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreBuildingRequest;
use App\Http\Requests\V1\UpdateBuildingRequest;
use App\Http\Resources\V1\Building\BuildingResource;
use App\Http\Resources\V1\Building\BuildingCollection;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new BuildingFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $buildings = Building::where($AndFilterItems)->where($OrFilterItems);

      $includeRooms = $request->query('includeRooms');
      if ($includeRooms) {
        $buildings = $buildings->with('rooms');
      }

      return new BuildingCollection($buildings->orderBy('internal_code')->paginate(25)->appends($request->query()));
    }

    /**
     * Display a listing of the resource without pagination.
     */
    public function getAllBuildingsNoPaginated()
    {
      $buildings = Building::orderBy('internal_code')->get();
      return new BuildingCollection($buildings);
    }

    /**
     * Display a listing of the resource without pagination.
     */
    public function getAllBuildingsById(Request $request)
    {
      $building_ids = $request->buildingIds;
      $buildings = Building::whereIn('id', $building_ids)->orderBy('internal_code')->get();
      return new BuildingCollection($buildings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBuildingRequest $request)
    {
      return new BuildingResource(Building::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Building $building)
    {
      $includeRooms = request()->query('includeRooms');
      if($includeRooms) {
        // $building = $building->loadMissing('rooms');
        return new BuildingResource($building->loadMissing('rooms'));
      }

      return new BuildingResource($building);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBuildingRequest $request, Building $building)
    {
      $building->update($request->all());
      $this->updateChilds($building);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Building $building)
    {
      $building->delete();
    }

    public function updateChilds($building) {
      $buildingWithRooms = new BuildingResource(Building::with(array('rooms' => function($query) {
        $query->orderBy('internal_code', 'ASC');
      }))->find($building->id));

      $rooms = $buildingWithRooms->rooms;

      // Check if building (parent) internal code has changed to update all rooms (child) internal code
      if (count($rooms) > 0) {
        $roomsUpdate = [];

        foreach ($rooms as $index => $room) {
          $rowRoomInternalCode = substr($room->internal_code, 0, strrpos($room->internal_code, '-', 0)); // Get building internal code from room 01-01-01
          $rowSingleInternalCode = substr($room->internal_code, strrpos($room->internal_code, '-') + 1); // Get room single part internal code 01

          if ($rowRoomInternalCode != $building->internal_code) { // If are diferent we need to update to the new building internal code
            $roomsUpdate[] = [
              'id' => $room->id,
              'internal_code' => $building->internal_code.'-'.$rowSingleInternalCode,
              'building_id' => $room->building_id,
              'description' => $room->description
            ];
          }
        }
        (new RoomController)->bulkUpdate((object) $roomsUpdate);
      }
    }
}
