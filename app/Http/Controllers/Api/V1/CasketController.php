<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Casket;
use Illuminate\Http\Request;
use App\Filters\V1\CasketFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreCasketRequest;
use App\Http\Resources\V1\Casket\CasketResource;
use App\Http\Resources\V1\Casket\CasketCollection;
use App\Models\Person;

class CasketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new CasketFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $caskets = Casket::where($AndFilterItems)->where($OrFilterItems);

      $includePeople = $request->query('includePeople');
      $peopleFilter = $request->query('people');
      $anyFilter = $request->query('any');

      if ($includePeople) {
        if ($peopleFilter || $anyFilter) {
          $filterByPeopleString = $peopleFilter['like'] ?? $anyFilter;

          $caskets = $caskets->whereHas('people', function($query) use ($filterByPeopleString) {
            $query->where(function($query) use ($filterByPeopleString) {
              $name_wildcard = str_replace(' ', '%', $filterByPeopleString);
              $name_wildcard = '%' . $name_wildcard . '%';
              $query->whereRaw('CONCAT(first_name," ",last_name_1," ",last_name_2) LIKE ?', [$name_wildcard]);
            });
          })->with('people');
        } else {
          $caskets = $caskets->with('people');
        }
      }

      if ($anyFilter) {
        $caskets = $caskets->orWhere('description', 'like', '%'.$anyFilter.'%');
      }

      return new CasketCollection($caskets->paginate(25)->appends($request->query()));
    }

    public function getAllCasketsWithNoDeposit(Request $request) {
      $includeCasketId = $request->query('includeCasketId');
      if ($includeCasketId) {
        $caskets = Casket::where('id', $includeCasketId)->orWhereHas('deposits', function($query) {
          $query->whereNotNull('end_date');
        })->orDoesntHave('deposits')->with('people')->get();
      } else {
        $caskets = Casket::whereHas('deposits', function($query) {
          $query->whereNotNull('end_date');
        })->orDoesntHave('deposits')->with('people')->get();
      }
      return new CasketCollection($caskets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCasketRequest $request)
    {
      return new CasketResource(Casket::create($request->all()));
    }

    public function createCasketWithPeople(Request $request)
    {
      $casket = $request->casket;
      $people = $request->people;

      $casketCreated = Casket::create(['description' => $casket['description']]);

      foreach ($people as $person) {
        if ($person['tabSelected'] == 'select') {
          Person::where('id', $person['personSelected']['id'])->update([
            'deathdate' => $person['personSelected']['deathdate'],
            'casket_id' => $casketCreated->id
          ]);
        }
        else if ($person['tabSelected'] == 'add') {
          Person::create([
            'dni' => $person['newPersonData']['dni'],
            'first_name' => $person['newPersonData']['firstName'],
            'last_name_1' => $person['newPersonData']['lastName1'],
            'last_name_2' => $person['newPersonData']['lastName2'],
            'address' => $person['newPersonData']['address'],
            'city' => $person['newPersonData']['city'],
            'state' => $person['newPersonData']['state'],
            'postal_code' => $person['newPersonData']['postalCode'],
            'phone' => $person['newPersonData']['phone'],
            'email' => $person['newPersonData']['email'],
            'marital_status' => $person['newPersonData']['maritalStatus'],
            'birthdate' => $person['newPersonData']['birthdate'],
            'deathdate' => $person['newPersonData']['deathdate'],
            'casket_id' => $casketCreated->id
          ]);
        }
      }
      return $casketCreated;
    }

    public function updateCasketWithPeople(Request $request)
    {
      $casket = $request->casket;
      $people = $request->people;

      Casket::where('id', $casket['id'])->update(['description' => $casket['description']]);

      $peopleInCasketSentsIds = [];
      foreach ($people as $person) {
        if ($person['tabSelected'] == 'select') {
          Person::where('id', $person['personSelected']['id'])->update([
            'deathdate' =>  $person['personSelected']['deathdate'],
            'casket_id' => $casket['id']
          ]);
          $peopleInCasketSentsIds[] = $person['personSelected']['id'];
        }
        else if ($person['tabSelected'] == 'add') {
          Person::create([
            'dni' => $person['newPersonData']['dni'],
            'first_name' => $person['newPersonData']['firstName'],
            'last_name_1' => $person['newPersonData']['lastName1'],
            'last_name_2' => $person['newPersonData']['lastName2'],
            'address' => $person['newPersonData']['address'],
            'city' => $person['newPersonData']['city'],
            'state' => $person['newPersonData']['state'],
            'postal_code' => $person['newPersonData']['postalCode'],
            'phone' => $person['newPersonData']['phone'],
            'email' => $person['newPersonData']['email'],
            'marital_status' => $person['newPersonData']['maritalStatus'],
            'birthdate' => $person['newPersonData']['birthdate'],
            'deathdate' => $person['newPersonData']['deathdate'],
            'casket_id' => $casket['id']
          ]);
        }
      }

      $peopleInCasket = Person::where('casket_id', '=', $casket['id'])->get();
      $peopleInCasketIds = [];
      foreach ($peopleInCasket as $person) {
        $peopleInCasketIds[] = $person->id;
      }

      foreach ($peopleInCasketIds as $personId) {
        if (!in_array($personId, $peopleInCasketSentsIds)) {
          Person::where('id', $personId)->update(['casket_id' => null]);
        }
      }

      return $casket['id'];
    }

    public function getCasketById($casketId) {
      $casket = Casket::where('id', $casketId);
      $includePeople = request()->query('includePeople');
      if($includePeople) {
        $casket = $casket->with('people');
      }
      return new CasketCollection($casket->get());
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

      $includeDeposits = request()->query('includeDeposits');
      if($includeDeposits) {
        $casket = $casket->loadMissing('deposits');
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
