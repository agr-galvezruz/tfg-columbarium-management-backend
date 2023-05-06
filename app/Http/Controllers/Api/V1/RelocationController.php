<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\V1\RelocationFilter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Relocation\RelocationCollection;
use App\Http\Resources\V1\Relocation\RelocationResource;
use App\Models\Casket;
use App\Models\Deposit;
use App\Models\Relocation;
use App\Models\Reservation;
use App\Models\Urn;

class RelocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new RelocationFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $relocations = Relocation::where($AndFilterItems)->where($OrFilterItems);

      $anyFilter = $request->query('any');
      // Casket filter in any or selected
      $casketFilter = $request->query('casket');
      if ($casketFilter || $anyFilter) {
        $filterByCasketString = $casketFilter['like'] ?? $anyFilter;

        $relocations = $relocations->orWhereHas('casket.people', function($query) use ($filterByCasketString) {
          $query->where(function($query) use ($filterByCasketString) {
            $name_wildcard = str_replace(' ', '%', $filterByCasketString);
            $name_wildcard = '%' . $name_wildcard . '%';
            $query->whereRaw('CONCAT(first_name," ",last_name_1," ",last_name_2) LIKE ?', [$name_wildcard]);
          });

        })->with('casket.people');

      } else {
        $relocations = $relocations->with('casket.people');
      }

      // Urn filter in any or selected
      $urnFilter = $request->query('urn');
      if ($urnFilter || $anyFilter) {
        $filterByUrnString = $urnFilter['like'] ?? $anyFilter;

        $relocations = $relocations->orWhereHas('urn', function($query) use ($filterByUrnString) {
          $query->where('internal_code', 'like', '%'.$filterByUrnString.'%');

        })->with('urn.niche.row.room.building');

      } else {
        $relocations = $relocations->with('urn.niche.row.room.building');
      }

      if ($anyFilter) {
        $relocations = $relocations->orWhere('start_date', 'like', '%'.$anyFilter.'%')
                                    ->orWhere('end_date', 'like', '%'.$anyFilter.'%')
                                    ->orWhere('description', 'like', '%'.$anyFilter.'%');
      }

      return new RelocationCollection($relocations->orderBy('start_date', 'DESC')->paginate(25)->appends($request->query()));
    }

    public function getAllRelocationsByCasket($casketId) {
      $relocation = Relocation::where('casket_id', $casketId)->with('casket.people')->with('urn.niche.row.room.building')->orderBy('start_date', 'DESC')->get();
      return new RelocationCollection($relocation);
    }

    public function getAllRelocationsByUrn($urnId) {
      $relocation = Relocation::where('urn_id', $urnId)->with('casket.people')->with('urn.niche.row.room.building')->orderBy('start_date', 'DESC')->get();
      return new RelocationCollection($relocation);
    }

    /**
     * Display the specified resource.
     */
    public function show(Relocation $relocation)
    {
      $relocation = $relocation->loadMissing('urn.niche.row.room.building');
      $relocation = $relocation->loadMissing('casket.people');

      return new RelocationResource($relocation);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function createRelocation(Request $request)
    {
      $newUrn = Urn::where('id', $request->urnId)->first();

      if ($request->type == 'active-reservation') {
        $currentReservation = Reservation::where('id', $request->reservationId)->with('urn')->with('deposit.casket.people')->first();

        $casketPeopleNames = '';
        foreach ($currentReservation->deposit->casket->people as $key => $person) {
          $casketPeopleNames .= $person->first_name.' '.$person->last_name_1.' '.$person->last_name_2;
          if (count($currentReservation->deposit->casket->people) < ($key + 1)) {
            $casketPeopleNames .= ' y ';
          }
        }

        Reservation::where('id', $request->reservationId)->update([
          'end_date' => $request->date,
          'description' => $currentReservation->description . '<div>Los R.C. de <b>'.$casketPeopleNames.'</b> reubicados el <b>'.$request->dateSp.'</b> al <b>'.$newUrn->internal_code.'</b></div>'
        ]);

        $reservationCreated = Reservation::create([
          'start_date' => $request->date,
          'end_date' => $currentReservation->end_date,
          'description' => '<div>Los R.C. proceden del <b>'.$currentReservation->urn->internal_code.'</b></div>',
          'urn_id' => $request->urnId,
          'person_id' => $currentReservation->person_id
        ]);

        if ($reservationCreated) {
          Urn::where('id', $currentReservation->urn_id)->update(['status' => 'AVAILABLE']);
          Deposit::where('reservation_id', $request->reservationId)->update(['reservation_id' => $reservationCreated->id]);
          Urn::where('id', $request->urnId)->update(['status' => 'OCCUPIED']);
        }

        return $reservationCreated;
      }
      if ($request->type == 'expired-reservation') {
        $currentCasket = Casket::where('id', $request->casketId)->with('deposits', function($query){
          $query->whereNull('end_date')->orderBy('start_date', 'DESC')->with('reservation.urn');
        })->first();

        if (count($currentCasket->deposits) > 0) {
          $currentUrnId = $currentCasket->deposits[0]->reservation->urn->id;

          Relocation::create([
            'casket_id' => $request->casketId,
            'urn_id' => $request->urnId,
            'start_date' => $request->date,
            'description' => '<div>Los R.C. proceden del <b>'.$currentCasket->deposits[0]->reservation->urn->internal_code.'</b></div>'
          ]);

          Deposit::where('id', $currentCasket->deposits[0]->id)->update([
            'end_date' => $request->date,
            'description' => $currentCasket->deposits[0]->description .'<div>R.C.reubicados el <b>'.$request->dateSp.'</b> al <b>'.$newUrn->internal_code.'</b></div>'
          ]);
          Urn::where('id', $currentUrnId)->update(['status' => 'AVAILABLE']);
          Urn::where('id', $request->urnId)->update(['status' => 'OCCUPIED']);
        }

        return $currentCasket;
      }
    }

    public function updateRelocation(Request $request) {
      $currentRelocation = Relocation::where('id', $request->id)->with('urn')->first();

      if ($request->type == 'expired-reservation') {
        if ($currentRelocation->urn_id == $request->urnId) {
          Relocation::where('id', $request->id)->update([
            'start_date' => $request->date,
            'end_date' => $request->endDate,
            'casket_id' => $request->casketId,
            'description' => $request->description
          ]);
        } else {
          Relocation::where('id', $request->id)->update([
            'urn_id' => $request->urnId,
            'start_date' => $request->date,
            'end_date' => $request->endDate,
            'casket_id' => $request->casketId,
            'description' => $request->description . '<div>Los R.C. proceden del <b>'.$currentRelocation->urn->internal_code.'</b></div>'
          ]);
          Urn::where('id', $currentRelocation->urn_id)->update(['status' => 'AVAILABLE']);
          Urn::where('id', $request->urnId)->update(['status' => 'OCCUPIED']);
        }
        if ($request->endDate) {
          Relocation::where('id', $request->id)->update([
            'description' => $request->description . '<div><b>Traslado externo</b></div>'
          ]);
          Urn::where('id', $request->urnId)->update(['status' => 'AVAILABLE']);
        }
      }
      return $request;
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Relocation $relocation)
    {
      $relocation->delete();
      return Urn::where('id', $relocation->urn_id)->update(['status' => 'AVAILABLE']);
    }
}
