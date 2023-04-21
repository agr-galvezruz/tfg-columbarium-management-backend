<?php

namespace App\Http\Resources\V1\Province;

use Illuminate\Http\Request;
use App\Http\Resources\V1\Room\RoomResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvinceResource extends JsonResource
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
        'name' => $this->name
      ];
    }
}
