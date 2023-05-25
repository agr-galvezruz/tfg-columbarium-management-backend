<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Filters\V1\RoomFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRoomRequest;
use App\Http\Requests\V1\UpdateRoomRequest;
use App\Http\Resources\V1\Room\RoomResource;
use App\Http\Resources\V1\Room\RoomCollection;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new RoomFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $rooms = Room::where($AndFilterItems)->where($OrFilterItems);

      $includeBuilding = $request->query('includeBuilding');
      if ($includeBuilding) {
        $rooms = $rooms->with('building');
      }

      $includeRows = $request->query('includeRows');
      if ($includeRows) {
        $rooms = $rooms->with('rows');
      }

      return new RoomCollection($rooms->orderBy('internal_code')->paginate(25)->appends($request->query()));
    }

    /**
     * Display a listing of the resource filter by dependency id.
     */
    public function getRoomsFromBuilding(Request $request)
    {
      $buildingId = $request->query('buildingId');
      if (!$buildingId) {
        return [];
      }

      $filter = new RoomFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $rooms = Room::where('building_id', '=', $buildingId)->where($AndFilterItems)->where($OrFilterItems);

      $includeBuilding = $request->query('includeBuilding');
      if ($includeBuilding) {
        $rooms = $rooms->with('building');
      }

      $includeRows = $request->query('includeRows');
      if ($includeRows) {
        $rooms = $rooms->with('rows');
      }

      return new RoomCollection($rooms->orderBy('internal_code')->paginate(25)->appends($request->query()));
    }

    /**
     * Display a listing of the resource by dependency id.
     */
    public function getAllRoomsFromBuildingNoPagination($buildingId)
    {
      $rooms = Room::where('building_id', '=', $buildingId)->orderBy('internal_code')->get();
      return new RoomCollection($rooms);
    }

    /**
     * Display a listing of the resource without pagination.
     */
    public function getAllRoomsByIdAndBuilding(Request $request)
    {
      $building_id = $request->buildingId;
      $room_ids = $request->roomIds;

      $rooms = Room::where('building_id', '=', $building_id)->whereIn('id', $room_ids)->orderBy('internal_code')->get();
      return new RoomCollection($rooms);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoomRequest $request)
    {
      return new RoomResource(Room::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
      $includeBuilding = request()->query('includeBuilding');
      if($includeBuilding) {
        $room = $room->loadMissing('building');
      }

      $includeRows = request()->query('includeRows');
      if($includeRows) {
        $room = $room->loadMissing('rows');
      }

      return new RoomResource($room);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoomRequest $request, Room $room)
    {
      $room->update($request->all());
      $this->updateChilds($room);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
      $room->delete();
    }

    public function bulkUpdate($rooms)
    {
      foreach ($rooms as $room) {
        Room::find($room['id'])->update($room);
        $this->updateChilds((object) $room);
      }
    }

    public function updateChilds($room) {
      $roomWithRows = new RoomResource(Room::with(array('rows' => function($query) {
        $query->orderBy('internal_code', 'ASC');
      }))->find($room->id));

      $rows = $roomWithRows->rows;

      // Check if room (parent) internal code has changed to update all rows (child) internal code
      if (count($rows) > 0) {
        $rowsUpdate = [];

        foreach ($rows as $index => $row) {
          $rowRoomInternalCode = substr($row->internal_code, 0, strrpos($row->internal_code, '-', 0)); // Get room internal code from row 01-01-01
          $rowSingleInternalCode = substr($row->internal_code, strrpos($row->internal_code, '-') + 1); // Get row single part internal code 01

          if ($rowRoomInternalCode != $room->internal_code) { // If are diferent we need to update to the new room internal code
            $rowsUpdate[] = [
              'id' => $row->id,
              'internal_code' => $room->internal_code.'-'.$rowSingleInternalCode,
              'room_id' => $row->room_id,
              'description' => $row->description
            ];
          }
        }
        (new RowController)->bulkUpdate((object) $rowsUpdate);
      }
    }
}
