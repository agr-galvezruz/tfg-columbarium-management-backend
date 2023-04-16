<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Person;
use Illuminate\Http\Request;
use App\Filters\V1\PersonFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StorePersonRequest;
use App\Http\Resources\V1\Person\PersonResource;
use App\Http\Resources\V1\Person\PersonCollection;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new PersonFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $people = Person::where($AndFilterItems)->where($OrFilterItems);

      $includeCasket = $request->query('includeCasket');
      if ($includeCasket) {
        $people = $people->with('casket');
      }

      $includeUser = $request->query('includeUser');
      if ($includeUser) {
        $people = $people->with('user');
      }

      return new PersonCollection($people->paginate(2)->appends($request->query()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonRequest $request)
    {
      return new PersonResource(Person::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Person $person)
    {
      $includeCasket = request()->query('includeCasket');
      if($includeCasket) {
        $person = $person->loadMissing('casket');
      }

      $includeUser = request()->query('includeUser');
      if($includeUser) {
        $person = $person->loadMissing('user');
      }

      return new PersonResource($person);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePersonRequest $request, Person $person)
    {
      $person->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Person $person)
    {
      $person->delete();
    }
}
