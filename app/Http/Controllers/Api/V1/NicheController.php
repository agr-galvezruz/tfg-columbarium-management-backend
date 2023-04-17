<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Niche;
use Illuminate\Http\Request;
use App\Filters\V1\NicheFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreNicheRequest;
use App\Http\Requests\V1\UpdateNicheRequest;
use App\Http\Resources\V1\Niche\NicheResource;
use App\Http\Resources\V1\Niche\NicheCollection;

class NicheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new NicheFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $niches = Niche::where($AndFilterItems)->where($OrFilterItems);

      $includeRow = $request->query('includeRow');
      $includeRoom = $request->query('includeRoom');
      if ($includeRow && $includeRoom) {
        $niches = $niches->with('row.room'); // Get relationship of relationship (cascade)
      } else if ($includeRow) {
        $niches = $niches->with('row');
      }

      $includeUrns = $request->query('includeUrns');
      if ($includeUrns) {
        $niches = $niches->with('urns');
      }

      return new NicheCollection($niches->orderBy('internal_code')->paginate(25)->appends($request->query()));
    }

    /**
     * Display a listing of the resource filter by dependency id.
     */
    public function getNichesFromRow(Request $request)
    {
      $rowId = $request->query('rowId');
      if (!$rowId) {
        return [];
      }

      $filter = new NicheFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $niches = Niche::where('row_id', '=', $rowId)->where($AndFilterItems)->where($OrFilterItems);

      $includeRow = $request->query('includeRow');
      $includeRoom = $request->query('includeRoom');
      if ($includeRow && $includeRoom) {
        $niches = $niches->with('row.room'); // Get relationship of relationship (cascade)
      } else if ($includeRow) {
        $niches = $niches->with('row');
      }

      $includeUrns = $request->query('includeUrns');
      if ($includeUrns) {
        $niches = $niches->with('urns');
      }

      return new NicheCollection($niches->orderBy('internal_code')->paginate(25)->appends($request->query()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNicheRequest $request)
    {
      return new NicheResource(Niche::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Niche $niche)
    {
      $includeRow = request()->query('includeRow');
      $includeRoom = request()->query('includeRoom');
      if ($includeRow && $includeRoom) {
        $niche = $niche->loadMissing('row.room'); // Get relationship of relationship (cascade)
      } else if ($includeRow) {
        $niche = $niche->loadMissing('row');
      }

      $includeUrns = request()->query('includeUrns');
      if($includeUrns) {
        $niche = $niche->loadMissing('urns');
      }

      return new NicheResource($niche);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNicheRequest $request, Niche $niche)
    {
      $niche->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Niche $niche)
    {
      $niche->delete();
    }
}
