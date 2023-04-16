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
      $buildings = Building::get();
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Building $building)
    {
      $building->delete();
    }
}
