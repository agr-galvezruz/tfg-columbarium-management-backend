<?php

namespace App\Http\Resources\V1\Deposit;

use Illuminate\Http\Request;
use App\Http\Resources\V1\Casket\CasketResource;
use App\Http\Resources\V1\Person\PersonResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\Reservation\ReservationResource;

class DepositResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
      return [
        'id' => $this->id,
        'startDate' => $this->start_date,
        'endDate' => $this->end_date,
        'description' => $this->description,
        'deceasedRelationship' => $this->deceased_relationship,
        'reservationId' => $this->reservation_id,
        'personId' => $this->person_id,
        'casketId' => $this->casket_id,
        'person' => new PersonResource($this->whenLoaded('person')),
        'casket' => new CasketResource($this->whenLoaded('casket')),
        'reservation' => new ReservationResource($this->whenLoaded('reservation')),
      ];
    }
}
