<?php

namespace App\Http\Resources\V1\Casket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\Person\PersonResource;

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
        'people' => PersonResource::collection($this->whenLoaded('people'))
      ];
    }
}
