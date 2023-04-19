<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Urn;
use Illuminate\Http\Request;
use App\Filters\V1\UrnFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreUrnRequest;
use App\Http\Requests\V1\UpdateUrnRequest;
use App\Http\Resources\V1\Urn\UrnCollection;
use App\Http\Resources\V1\Urn\UrnResource;

class UrnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new UrnFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $urns = Urn::where($AndFilterItems)->where($OrFilterItems);

      $includeNiche = $request->query('includeNiche');
      $includeRow = $request->query('includeRow');
      $includeRoom = $request->query('includeRoom');
      if ($includeNiche && $includeRow && $includeRoom) {
        $urns = $urns->with('niche.row.room'); // Get relationship of relationship (cascade)
      } else if ($includeNiche && $includeRow) {
        $urns = $urns->with('niche.row'); // Get relationship of relationship (cascade)
      } else if ($includeNiche) {
        $urns = $urns->with('nichen');
      }

      $includeReservations = $request->query('includeReservations');
      if ($includeReservations) {
        $urns = $urns->with('reservations');
      }

      $includeRelocations = $request->query('includeRelocations');
      if ($includeRelocations) {
        $urns = $urns->with('relocations');
      }

      return new UrnCollection($urns->orderBy('internal_code')->paginate(25)->appends($request->query()));
    }

    /**
     * Display a listing of the resource filter by dependency id.
     */
    public function getUrnsFromNiche(Request $request)
    {
      $nicheId = $request->query('nicheId');
      if (!$nicheId) {
        return [];
      }

      $filter = new UrnFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $urns = Urn::where('niche_id', '=', $nicheId)->where($AndFilterItems)->where($OrFilterItems);

      $includeNiche = $request->query('includeNiche');
      $includeRow = $request->query('includeRow');
      $includeRoom = $request->query('includeRoom');
      if ($includeNiche && $includeRow && $includeRoom) {
        $urns = $urns->with('niche.row.room'); // Get relationship of relationship (cascade)
      } else if ($includeNiche && $includeRow) {
        $urns = $urns->with('niche.row'); // Get relationship of relationship (cascade)
      } else if ($includeNiche) {
        $urns = $urns->with('nichen');
      }

      $includeReservations = $request->query('includeReservations');
      if ($includeReservations) {
        $urns = $urns->with('reservations');
      }

      $includeRelocations = $request->query('includeRelocations');
      if ($includeRelocations) {
        $urns = $urns->with('relocations');
      }

      return new UrnCollection($urns->orderBy('internal_code')->paginate(25)->appends($request->query()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUrnRequest $request)
    {
      return new UrnResource(Urn::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Urn $urn)
    {
      $includeNiche = request()->query('includeNiche');
      $includeRow = request()->query('includeRow');
      $includeRoom = request()->query('includeRoom');
      if ($includeNiche && $includeRow && $includeRoom) {
        $urn = $urn->loadMissing('niche.row.room'); // Get relationship of relationship (cascade)
      } else if ($includeNiche && $includeRow) {
        $urn = $urn->loadMissing('niche.row'); // Get relationship of relationship (cascade)
      } else if ($includeNiche) {
        $urn = $urn->loadMissing('nichen');
      }

      $includeReservations = request()->query('includeReservations');
      if ($includeReservations) {
        $urn = $urn->loadMissing('reservations');
      }

      $includeRelocations = request()->query('includeRelocations');
      if ($includeRelocations) {
        $urn = $urn->loadMissing('relocations');
      }

      return new UrnResource($urn);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUrnRequest $request, Urn $urn)
    {
      $urn->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Urn $urn)
    {
      $urn->delete();
    }

    public function bulkStore($urns)
    {
      Urn::insert($urns);
    }

    public function bulkUpdate($urns)
    {
      foreach ($urns as $urn) {
        Urn::find($urn['id'])->update($urn);
      }
    }

    public function bulkDelete($urns)
    {
      Urn::destroy($urns);
    }
}
