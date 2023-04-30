<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Urn;
use App\Models\Person;
use App\Models\Deposit;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Filters\V1\DepositFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Deposit\DepositResource;
use App\Http\Resources\V1\Deposit\DepositCollection;

class DepositController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new DepositFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $deposits = Deposit::where($AndFilterItems)->where($OrFilterItems);

      $includePerson = $request->query('includePerson');
      $personFilter = $request->query('person');
      $anyFilter = $request->query('any');

      // Person filter in any or selected
      if ($includePerson) {
        if ($personFilter || $anyFilter) {
          $filterByPersonString = $personFilter['like'] ?? $anyFilter;

          $deposits = $deposits->whereHas('person', function($query) use ($filterByPersonString) {
            $query->where(function($query) use ($filterByPersonString) {
              $name_wildcard = str_replace(' ', '%', $filterByPersonString);
              $name_wildcard = '%' . $name_wildcard . '%';
              $query->whereRaw('CONCAT(first_name," ",last_name_1," ",last_name_2) LIKE ?', [$name_wildcard]);
            });
          })->with('person');

        } else {
          $deposits = $deposits->with('person');
        }
      }


      // Casket filter in any or selected
      $casketFilter = $request->query('casket');
      if ($casketFilter || $anyFilter) {
        $filterByCasketString = $casketFilter['like'] ?? $anyFilter;

        $deposits = $deposits->orWhereHas('casket.people', function($query) use ($filterByCasketString) {
          $query->where(function($query) use ($filterByCasketString) {
            $name_wildcard = str_replace(' ', '%', $filterByCasketString);
            $name_wildcard = '%' . $name_wildcard . '%';
            $query->whereRaw('CONCAT(first_name," ",last_name_1," ",last_name_2) LIKE ?', [$name_wildcard]);
          });

        })->with('casket.people');

      } else {
        $deposits = $deposits->with('casket.people');
      }

      // Urn filter in any or selected
      $urnFilter = $request->query('urn');
      if ($urnFilter || $anyFilter) {
        $filterByUrnString = $urnFilter['like'] ?? $anyFilter;

        $deposits = $deposits->orWhereHas('reservation.urn', function($query) use ($filterByUrnString) {
          $query->where('internal_code', 'like', '%'.$filterByUrnString.'%');

        })->with('reservation.urn');

      } else {
        $deposits = $deposits->with('reservation.urn');
      }

      $deposits = $deposits->with('reservation.person');

      if ($anyFilter) {
        $deposits = $deposits->orWhere('start_date', 'like', '%'.$anyFilter.'%')
                                    ->orWhere('end_date', 'like', '%'.$anyFilter.'%')
                                    ->orWhere('deceased_relationship', 'like', '%'.$anyFilter.'%')
                                    ->orWhere('description', 'like', '%'.$anyFilter.'%');
      }

      return new DepositCollection($deposits->orderBy('start_date', 'DESC')->orderBy('end_date', 'DESC')->paginate(25)->appends($request->query()));
    }

    public function getDepositByReservationId($reservationId) {
      $deposit = Deposit::where('reservation_id', $reservationId);

      $includePerson = request()->query('includePerson');
      if ($includePerson) {
        $deposit = $deposit->with('person');
      }

      $includeCasket = request()->query('includeCasket');
      if ($includeCasket) {
        $deposit = $deposit->with('casket.people');
      }

      $includeReservation = request()->query('includeReservation');
      if ($includeReservation) {
        $deposit = $deposit->with('reservation.urn');
        $deposit = $deposit->with('reservation.person');
      }

      return new DepositCollection($deposit->get());
    }

    public function getDepositByPersonId($personId) {
      $deposits = Deposit::where('person_id', $personId);

      $includePerson = request()->query('includePerson');
      if ($includePerson) {
        $deposits = $deposits->with('person');
      }

      $includeCasket = request()->query('includeCasket');
      if ($includeCasket) {
        $deposits = $deposits->with('casket.people');
      }

      $includeReservation = request()->query('includeReservation');
      if ($includeReservation) {
        $deposits = $deposits->with('reservation.urn');
        $deposits = $deposits->with('reservation.person');
      }

      return new DepositCollection($deposits->get());
    }

    public function getDepositByCasketId($casketId) {
      $deposits = Deposit::where('casket_id', $casketId);

      $includePerson = request()->query('includePerson');
      if ($includePerson) {
        $deposits = $deposits->with('person');
      }

      $includeCasket = request()->query('includeCasket');
      if ($includeCasket) {
        $deposits = $deposits->with('casket.people');
      }

      $includeReservation = request()->query('includeReservation');
      if ($includeReservation) {
        $deposits = $deposits->with('reservation.urn');
        $deposits = $deposits->with('reservation.person');
      }

      return new DepositCollection($deposits->orderBy('id', 'DESC')->get());
    }

    /**
     * Display the specified resource.
     */
    public function show(Deposit $deposit)
    {
      $includeReservation = request()->query('includeReservation');
      if ($includeReservation) {
        $deposit = $deposit->loadMissing('reservation.person');
        $deposit = $deposit->loadMissing('reservation.urn');
      }

      $includePerson = request()->query('includePerson');
      if ($includePerson) {
        $deposit = $deposit->loadMissing('person');
      }

      $includeCasket = request()->query('includeCasket');
      if ($includeCasket) {
        $deposit = $deposit->loadMissing('casket.people');
      }

      return new DepositResource($deposit);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createDeposit(Request $request)
    {
      $deposit = $request->depositData;
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

      $depositCreated = Deposit::create([
        'start_date' => $deposit['startDate'],
        'end_date' => null,
        'description' => $deposit['description'],
        'deceased_relationship' => $deposit['deceasedRelationship'],
        'casket_id' => $deposit['casketId'],
        'reservation_id' => $deposit['reservationId'],
        'person_id' => $personCreatedId
      ]);

      if ($depositCreated) {
        $reservation = Reservation::where('id', $deposit['reservationId'])->first();

        Urn::where('id', $reservation->urn_id)->update(['status' => 'OCCUPIED']);
      } else {
        return response()->json([
          'message' => 'Urn not available.'
        ], 402);
      }

      return $depositCreated;
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateDeposit(Request $request)
    {
      $deposit = $request->depositData;

      Deposit::where('id', $deposit['id'])->update([
        'start_date' => $deposit['startDate'],
        'end_date' => $deposit['endDate'],
        'description' => $deposit['description'],
        'deceased_relationship' => $deposit['deceasedRelationship'],
        'casket_id' => $deposit['casketId'],
        'reservation_id' => $deposit['reservationId'],
        'person_id' => $deposit['personId']
      ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deposit $deposit)
    {
      $reservation = Reservation::where('id', $deposit->reservation_id)->first();
      $deposit->delete();
      Urn::where('id', $reservation->urn_id)->whereIn('status', ['OCCUPIED'])->update(['status' => 'RESERVED']);
    }
}
