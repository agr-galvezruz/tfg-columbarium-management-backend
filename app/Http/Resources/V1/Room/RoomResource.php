<?php

namespace App\Http\Resources\V1\Room;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\Building\BuildingResource;

class RoomResource extends JsonResource
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
        'location' => $this->location,
        'description' => $this->description,
        'buildingId' => $this->building_id,
        'building' => new BuildingResource($this->whenLoaded('building')),
        'rows' => BuildingResource::collection($this->whenLoaded('rows'))
      ];
    }
}
