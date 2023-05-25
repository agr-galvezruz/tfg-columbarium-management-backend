<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Urn;
use App\Models\Person;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Filters\V1\ReservationFilter;
use App\Http\Resources\V1\Reservation\ReservationCollection;
use App\Http\Resources\V1\Reservation\ReservationResource;
use App\Models\Deposit;

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
        })->with('urn.niche.row.room.building');

      } else {
        $reservations = $reservations->with('urn.niche.row.room.building');
      }

      $reservations = $reservations->with('deposit');

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
        })->with('urn.niche.row.room.building');

      } else {
        $reservations = $reservations->with('urn.niche.row.room.building');
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
      $reservations = $reservations->with('urn.niche.row.room.building');
      $reservations = $reservations->with('person');

      return new ReservationCollection($reservations->orderBy('start_date', 'DESC')->orderBy('end_date', 'DESC')->paginate(25)->appends($request->query()));
    }

    public function getAllReservationsWithNoDeposit(Request $request) {
      date_default_timezone_set('Europe/Madrid');

      $includeReservationId = $request->query('includeReservationId');
      if ($includeReservationId) {
        $reservations = Reservation::where('id', $includeReservationId)->orWhere('end_date', '>=', date("Y-m-d"))->doesntHave('deposit')->whereHas('urn', function($query) {
          $query->whereIn('status', ['RESERVED']);
        })->with('urn')->with('person')->orderBy('start_date', 'DESC')->orderBy('end_date', 'DESC')->get();
      } else {
        $reservations = Reservation::where('end_date', '>=', date("Y-m-d"))->doesntHave('deposit')->whereHas('urn', function($query) {
          $query->whereIn('status', ['RESERVED']);
        })->with('urn')->with('person')->orderBy('start_date', 'DESC')->orderBy('end_date', 'DESC')->get();
      }
      return new ReservationCollection($reservations);
    }

    public function getAllReservationsWithDepositInDate(Request $request) {
      $relocationDate = $request->query('relocationDate');
      // $includeReservationId = $request->query('includeReservationId');
      // if ($includeReservationId) {
      //   $reservations = Reservation::where('id', $includeReservationId)->orWhere('end_date', '>=', date("Y-m-d"))->doesntHave('deposit')->whereHas('urn', function($query) {
      //     $query->whereIn('status', ['RESERVED']);
      //   })->with('urn')->with('person')->orderBy('start_date', 'DESC')->orderBy('end_date', 'DESC')->get();
      // } else {
        $reservations = Reservation::where('start_date', '<=', $relocationDate)->where('end_date', '>=', $relocationDate)->whereHas('deposit', function($query) {
          $query->whereNull('end_date');
        })->whereHas('urn', function($query) {
          $query->whereIn('status', ['OCCUPIED']);
        })->with('urn')->with('person')->orderBy('start_date', 'DESC')->orderBy('end_date', 'DESC')->get();
      // }
      return new ReservationCollection($reservations);
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

    public function getReservationById($reservationId) {
      $reservation = Reservation::where('id', $reservationId);

      $includePerson = request()->query('includePerson');
      if ($includePerson) {
        $reservation = $reservation->with('person');
      }

      $includeUrn = request()->query('includeUrn');
      if ($includeUrn) {
        $reservation = $reservation->with('urn.niche.row.room.building');
      }

      return new ReservationCollection($reservation->get());
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
      $includeUrn = request()->query('includeUrn');
      if ($includeUrn) {
        $reservation = $reservation->loadMissing('urn.niche.row.room.building');
      }

      $includePerson = request()->query('includePerson');
      if ($includePerson) {
        $reservation = $reservation->loadMissing('person');
      }

      $includeDeposit = request()->query('includeDeposit');
      if ($includeDeposit) {
        $reservation = $reservation->loadMissing('deposit');
      }

      return new ReservationResource($reservation);
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
      $person = $request->personForm;
      $cancelReservation = $request->cancelReservation;

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

      $currentReservation = Reservation::where('id', $reservation['id'])->with('urn')->with('deposit')->with('person')->first();

      if (!$currentReservation->deposit) {
        // If Urn has been changed
        if ($currentReservation->urn_id != $reservation['urnId']) {
          Urn::where('id', $currentReservation->urn_id)->update(['status' => 'AVAILABLE']); // Current urn
          Urn::where('id', $reservation['urnId'])->update(['status' => 'RESERVED']); // New urn
          $newUrn = Urn::where('id', $reservation['urnId'])->first(); // Get new Urn data
          $reservation['description'] .= '<div>Reserva de urna cambiada: de <b>'.$currentReservation->urn->internal_code.'</b> a <b>'.$newUrn->internal_code.'</b></div>';
        }

        // If Reservaton has been cancelled
        if ($cancelReservation) {
          date_default_timezone_set('Europe/Madrid');
          $reservation['endDate'] = date("Y-m-d");

          Deposit::where('reservation_id', $reservation['id'])->update(['end_date' => $reservation['endDate']]);
          $reservation['description'] .= '<div>Reserva cancelada</div>';
        }
      }

      if ($currentReservation->deposit) {
        // If Person has been changed
        if ($currentReservation->person_id != $personCreatedId) {
          $newPerson = Person::where('id', $personCreatedId)->first();
          $reservation['description'] .= '<div>El titular de la reserva ha cambiado de <b>'.$currentReservation->person->first_name.' '.$currentReservation->person->last_name_1.' '.$currentReservation->person->last_name_2.'</b> a <b>'.$newPerson->first_name.' '.$newPerson->last_name_1.' '.$newPerson->last_name_2.'</b>.</div>';
        }
      }

      Reservation::where('id', $reservation['id'])->update([
        'start_date' => $reservation['startDate'],
        'end_date' => $reservation['endDate'],
        'description' => $reservation['description'],
        'urn_id' => $reservation['urnId'],
        'person_id' => $personCreatedId
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
