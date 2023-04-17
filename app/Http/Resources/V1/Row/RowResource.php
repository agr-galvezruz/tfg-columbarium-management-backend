<?php

namespace App\Http\Resources\V1\Row;

use App\Http\Resources\V1\Room\RoomResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RowResource extends JsonResource
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
        'description' => $this->description,
        'roomId' => $this->room_id,
        'room' => new RoomResource($this->whenLoaded('room')),
        'niches' => RoomResource::collection($this->whenLoaded('niches'))
      ];
    }
}
