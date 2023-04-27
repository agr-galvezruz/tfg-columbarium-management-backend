<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Row;
use Illuminate\Http\Request;
use App\Filters\V1\RowFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRowRequest;
use App\Http\Requests\V1\UpdateRowRequest;
use App\Http\Resources\V1\Row\RowResource;
use App\Http\Resources\V1\Row\RowCollection;

class RowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new RowFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $rows = Row::where($AndFilterItems)->where($OrFilterItems);

      $includeRoom = $request->query('includeRoom');
      if ($includeRoom) {
        $rows = $rows->with('room');
      }

      $includeNiches = $request->query('includeNiches');
      if ($includeNiches) {
        $rows = $rows->with('niches');
      }

      return new RowCollection($rows->orderBy('internal_code')->paginate(25)->appends($request->query()));
    }

    /**
     * Display a listing of the resource filter by dependency id.
     */
    public function getRowsFromRoom(Request $request)
    {
      $roomId = $request->query('roomId');
      if (!$roomId) {
        return [];
      }

      $filter = new RowFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $rows = Row::where('room_id', '=', $roomId)->where($AndFilterItems)->where($OrFilterItems);

      $includeRoom = $request->query('includeRoom');
      if ($includeRoom) {
        $rows = $rows->with('room');
      }

      $includeNiches = $request->query('includeNiches');
      if ($includeNiches) {
        $rows = $rows->with('niches');
      }

      return new RowCollection($rows->orderBy('internal_code')->paginate(25)->appends($request->query()));
    }

    /**
     * Display a listing of the resource by dependency id.
     */
    public function getAllRowsFromRoomNoPagination($roomId)
    {
      $rows = Row::where('room_id', '=', $roomId)->orderBy('internal_code')->get();
      return new RowCollection($rows);
    }

    /**
     * Display a listing of the resource without pagination.
     */
    public function getAllRowsByIdAndRoom(Request $request)
    {
      $room_id = $request->roomId;
      $row_ids = $request->rowIds;

      $rows = Row::where('room_id', '=', $room_id)->whereIn('id', $row_ids)->orderBy('internal_code')->get();
      return new RowCollection($rows);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRowRequest $request)
    {
      return new RowResource(Row::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Row $row)
    {
      $includeRoom = request()->query('includeRoom');
      $includeBuilding = request()->query('includeBuilding');
      if($includeRoom && $includeBuilding) {
        $row = $row->loadMissing('room.building'); // Get relationship of relationship (cascade)
      } else if ($includeRoom) {
        $row = $row->loadMissing('room');
      }

      $includeNiches = request()->query('includeNiches');
      if($includeNiches) {
        $row = $row->loadMissing('niches');
      }

      return new RowResource($row);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRowRequest $request, Row $row)
    {
      $row->update($request->all());
      $this->updateChilds($row);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Row $row)
    {
      $row->delete();
    }

    public function bulkUpdate($rows)
    {
      foreach ($rows as $row) {
        Row::find($row['id'])->update($row);
        $this->updateChilds((object) $row);
      }
    }

    public function updateChilds($row) {
      $rowWithNiches = new RowResource(Row::with(array('niches' => function($query) {
        $query->orderBy('internal_code', 'ASC');
      }))->find($row->id));

      $niches = $rowWithNiches->niches;

      // Check if row (parent) internal code has changed to update all niches (child) internal code
      if (count($niches) > 0) {
        $nichesUpdate = [];

        foreach ($niches as $index => $niche) {
          $nicheRowInternalCode = substr($niche->internal_code, 0, strrpos($niche->internal_code, '-', 0)); // Get row internal code from niche 01-01-01
          $nicheSingleInternalCode = substr($niche->internal_code, strrpos($niche->internal_code, '-') + 1); // Get niche single part internal code 01

          if ($nicheRowInternalCode != $row->internal_code) { // If are diferent we need to update to the new row internal code
            $nichesUpdate[] = [
              'id' => $niche->id,
              'internal_code' => $row->internal_code.'-'.$nicheSingleInternalCode,
              'storage_quantity' => $niche->storage_quantity,
              'row_id' => $niche->row_id,
              'description' => $niche->description
            ];
          }
        }
        (new NicheController)->bulkUpdate((object) $nichesUpdate);
      }
    }
}
