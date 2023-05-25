<?php

namespace App\Http\Resources\V1\Casket;

use App\Http\Resources\V1\Deposit\DepositResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\Person\PersonResource;
use App\Http\Resources\V1\Relocation\RelocationResource;

class CasketResource extends JsonResource
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
        'description' => $this->description,
        'people' => PersonResource::collection($this->whenLoaded('people')),
        'deposits' => DepositResource::collection($this->whenLoaded('deposits')),
        'relocations' => RelocationResource::collection($this->whenLoaded('relocations')),
      ];
    }
}
