<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Filters\V1\RoomFilter;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\V1\StoreRoomRequest;
use App\Http\Requests\V1\UpdateRoomRequest;
use App\Http\Resources\V1\Room\RoomResource;
use App\Http\Resources\V1\Room\RoomCollection;
use Illuminate\Support\Facades\DB;

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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
      $room->delete();
    }
}
