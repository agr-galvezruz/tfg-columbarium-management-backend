<?php

namespace App\Http\Resources\V1\Relocation;

use App\Http\Resources\V1\Casket\CasketResource;
use App\Http\Resources\V1\Urn\UrnResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelocationResource extends JsonResource
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
        'casketId' => $this->casket_id,
        'urnId' => $this->urn_id,
        'casket' => new CasketResource($this->whenLoaded('casket')),
        'urn' => new UrnResource($this->whenLoaded('urn')),
      ];
    }
}
