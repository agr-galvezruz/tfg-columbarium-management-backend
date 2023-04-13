<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Casket;
use Illuminate\Http\Request;
use App\Filters\V1\CasketFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreCasketRequest;
use App\Http\Resources\V1\Casket\CasketResource;
use App\Http\Resources\V1\Casket\CasketCollection;

class CasketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new CasketFilter();
      $filterItems = $filter->transform($request); //[['column', 'operator', 'value']]
      $caskets = Casket::where($filterItems);

      $includePeople = $request->query('includePeople');
      if ($includePeople) {
        $caskets = $caskets->with('people');
      }

      return new CasketCollection($caskets->paginate()->appends($request->query()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCasketRequest $request)
    {
      return new CasketResource(Casket::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Casket $casket)
    {
      $includePeople = request()->query('includePeople');
      if($includePeople) {
        $casket = $casket->loadMissing('people');
      }

      return new CasketResource($casket);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCasketRequest $request, Casket $casket)
    {
      $casket->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Casket $casket)
    {
      $casket->delete();
    }
}
