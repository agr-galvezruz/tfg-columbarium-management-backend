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
      if($includeRoom) {
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Row $row)
    {
      $row->delete();
    }
}
