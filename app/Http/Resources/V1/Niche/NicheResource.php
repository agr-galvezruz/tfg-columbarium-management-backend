<?php

namespace App\Http\Resources\V1\Niche;

use App\Http\Resources\V1\Row\RowResource;
use App\Http\Resources\V1\Urn\UrnResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NicheResource extends JsonResource
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
        'storageQuantity' => $this->storage_quantity,
        'description' => $this->description,
        'rowId' => $this->row_id,
        'row' => new RowResource($this->whenLoaded('row')),
        'urns' => UrnResource::collection($this->whenLoaded('urns'))
      ];
    }
}
