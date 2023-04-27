<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Urn;
use App\Models\Person;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Filters\V1\ReservationFilter;
use App\Http\Resources\V1\Reservation\ReservationCollection;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new ReservationFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $reservations = Reservation::where($AndFilterItems)->where($OrFilterItems);

      $includePerson = $request->query('includePerson');
      $personFilter = $request->query('person');
      $anyFilter = $request->query('any');

      // Person filter in any or selected
      if ($includePerson) {
        if ($personFilter || $anyFilter) {
          $filterByPersonString = $personFilter['like'] ?? $anyFilter;

          $reservations = $reservations->whereHas('person', function($query) use ($filterByPersonString) {
            $query->where(function($query) use ($filterByPersonString) {
              $name_wildcard = str_replace(' ', '%', $filterByPersonString);
              $name_wildcard = '%' . $name_wildcard . '%';
              $query->whereRaw('CONCAT(first_name," ",last_name_1," ",last_name_2) LIKE ?', [$name_wildcard]);
            });
          })->with('person');

        } else {
          $reservations = $reservations->with('person');
        }
      }

      // Urn filter in any or selected
      $urnFilter = $request->query('urn');
      if ($urnFilter || $anyFilter) {
        $filterByUrnString = $urnFilter['like'] ?? $anyFilter;
        $reservations = $reservations->orWhereHas('urn', function($query) use ($filterByUrnString) {
          $query->where('internal_code', 'like', '%'.$filterByUrnString.'%');
        })->with('urn');

      } else {
        $reservations = $reservations->with('urn');
      }

      if ($anyFilter) {
        $reservations = $reservations->orWhere('start_date', 'like', '%'.$anyFilter.'%')
                                    ->orWhere('end_date', 'like', '%'.$anyFilter.'%')
                                    ->orWhere('description', 'like', '%'.$anyFilter.'%');
      }

      return new ReservationCollection($reservations->orderBy('start_date', 'DESC')->orderBy('end_date', 'DESC')->paginate(25)->appends($request->query()));
    }

    public function getAllReservationsFromUrn(Request $request)
    {
      $urnId = $request->query('urnId');
      if (!$urnId) {
        return [];
      }

      $filter = new ReservationFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $reservations = Reservation::where('urn_id', '=', $urnId)->where($AndFilterItems)->where($OrFilterItems);

      $includePerson = $request->query('includePerson');
      $personFilter = $request->query('person');
      $anyFilter = $request->query('any');

      // Person filter in any or selected
      if ($includePerson) {
        if ($personFilter || $anyFilter) {
          $filterByPersonString = $personFilter['like'] ?? $anyFilter;

          $reservations = $reservations->whereHas('person', function($query) use ($filterByPersonString) {
            $query->where(function($query) use ($filterByPersonString) {
              $name_wildcard = str_replace(' ', '%', $filterByPersonString);
              $name_wildcard = '%' . $name_wildcard . '%';
              $query->whereRaw('CONCAT(first_name," ",last_name_1," ",last_name_2) LIKE ?', [$name_wildcard]);
            });
          })->with('person');

        } else {
          $reservations = $reservations->with('person');
        }
      }

      // Urn filter in any or selected
      $urnFilter = $request->query('urn');
      if ($urnFilter || $anyFilter) {
        $filterByUrnString = $urnFilter['like'] ?? $anyFilter;
        $reservations = $reservations->orWhereHas('urn', function($query) use ($filterByUrnString) {
          $query->where('internal_code', 'like', '%'.$filterByUrnString.'%');
        })->with('urn');

      } else {
        $reservations = $reservations->with('urn');
      }

      if ($anyFilter) {
        $reservations = $reservations->orWhere('start_date', 'like', '%'.$anyFilter.'%')
                                    ->orWhere('end_date', 'like', '%'.$anyFilter.'%')
                                    ->orWhere('description', 'like', '%'.$anyFilter.'%');
      }

      return new ReservationCollection($reservations->orderBy('start_date', 'DESC')->orderBy('end_date', 'DESC')->paginate(25)->appends($request->query()));
    }

    public function getAllReservationsFromPerson(Request $request)
    {
      $personId = $request->query('personId');
      if (!$personId) {
        return [];
      }

      $filter = new ReservationFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $reservations = Reservation::where('person_id', '=', $personId)->where($AndFilterItems)->where($OrFilterItems);
      $reservations = $reservations->with('urn');
      $reservations = $reservations->with('person');

      return new ReservationCollection($reservations->orderBy('start_date', 'DESC')->orderBy('end_date', 'DESC')->paginate(25)->appends($request->query()));
    }

    public function getAllAvailableResources() {
      $urns = Urn::where('status', '=', 'AVAILABLE')->with('niche.row.room')->orderBy('internal_code')->get();
      $arrayIds = [
        'buildingIds' => [],
        'roomIds' => [],
        'rowIds' => [],
        'nicheIds' => [],
        'urnIds' => []
      ];

      foreach ($urns as $urn) {
        $arrayIds['urnIds'][] = $urn->id;

        if (!in_array($urn->niche->id, $arrayIds['nicheIds'])) {
          $arrayIds['nicheIds'][] = $urn->niche->id;
        }
        if (!in_array($urn->niche->row->id, $arrayIds['rowIds'])) {
          $arrayIds['rowIds'][] = $urn->niche->row->id;
        }
        if (!in_array($urn->niche->row->room->id, $arrayIds['roomIds'])) {
          $arrayIds['roomIds'][] = $urn->niche->row->room->id;
        }
        if (!in_array($urn->niche->row->room->building_id, $arrayIds['buildingIds'])) {
          $arrayIds['buildingIds'][] = $urn->niche->row->room->building_id;
        }
      }

      return $arrayIds;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function createReservation(Request $request)
    {
      $reservation = $request->reservationData;
      $person = $request->personForm;

      $personCreatedId = null;
      if ($person['tabSelected'] == 'add') {
        $personCreated = Person::create([
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
          'casket_id' => null
        ]);
        $personCreatedId = $personCreated->id;
      }
      else if ($person['tabSelected'] == 'select') {
        $personCreated = $person['personSelected'];
        $personCreatedId = $personCreated['id'];
      }

      $reservationCreated = Reservation::create([
        'start_date' => $reservation['startDate'],
        'end_date' => $reservation['endDate'],
        'description' => $reservation['description'],
        'urn_id' => $reservation['urnId'],
        'person_id' => $personCreatedId
      ]);

      if ($reservationCreated) {
        Urn::where('id', $reservation['urnId'])->update(['status' => 'RESERVED']);
      } else {
        return response()->json([
          'message' => 'Urn not available.'
        ], 402);
      }

      return $reservationCreated;
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateReservation(Request $request)
    {
      $reservation = $request->reservationData;
      $cancelReservation = $request->cancelReservation;

      if ($cancelReservation) {
        $reservation['endDate'] = date("Y-m-d");
      }

      Reservation::where('id', $reservation['id'])->update([
        'start_date' => $reservation['startDate'],
        'end_date' => $reservation['endDate'],
        'description' => $reservation['description'],
        'urn_id' => $reservation['urnId'],
        'person_id' => $reservation['personId']
      ]);

      if ($cancelReservation) {
        Urn::where('id', $reservation['urnId'])->update(['status' => 'AVAILABLE']);
      }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
      $reservedUrn = $reservation->urn_id;

      $reservation->delete();
      Urn::where('id', $reservedUrn)->update(['status' => 'AVAILABLE']);
    }
}
