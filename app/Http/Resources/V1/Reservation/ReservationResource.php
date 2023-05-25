<?php

namespace App\Http\Resources\V1\Reservation;

use App\Http\Resources\V1\Deposit\DepositResource;
use App\Http\Resources\V1\Person\PersonResource;
use App\Http\Resources\V1\Urn\UrnResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
        'urnId' => $this->urn_id,
        'personId' => $this->person_id,
        'person' => new PersonResource($this->whenLoaded('person')),
        'urn' => new UrnResource($this->whenLoaded('urn')),
        'deposit' => new DepositResource($this->whenLoaded('deposit'))
      ];
    }
}
