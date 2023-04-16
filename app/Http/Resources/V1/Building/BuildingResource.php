<?php

namespace App\Http\Resources\V1\Building;

use Illuminate\Http\Request;
use App\Http\Resources\V1\Room\RoomResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
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
        'name' => $this->name,
        'address' => $this->address,
        'description' => $this->description,
        'rooms' => RoomResource::collection($this->whenLoaded('rooms'))
      ];
    }
}
