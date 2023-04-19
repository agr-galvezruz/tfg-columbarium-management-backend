<?php

namespace App\Http\Resources\V1\Urn;

use Illuminate\Http\Request;
use App\Http\Resources\V1\Niche\NicheResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\Relocation\RelocationResource;
use App\Http\Resources\V1\Reservation\ReservationResource;

class UrnResource extends JsonResource
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
        'internalCode' => $this->internal_code,
        'status' => $this->status,
        'description' => $this->description,
        'nicheId' => $this->niche_id,
        'niche' => new NicheResource($this->whenLoaded('niche')),
        'relocations' => RelocationResource::collection($this->whenLoaded('relocations')),
        'reservations' => ReservationResource::collection($this->whenLoaded('reservations'))
      ];
    }
}
