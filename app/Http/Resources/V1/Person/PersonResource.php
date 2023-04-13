<?php

namespace App\Http\Resources\V1\Person;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\Casket\CasketResource;
use App\Http\Resources\V1\User\UserResource;

class PersonResource extends JsonResource
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
        'dni' => $this->dni,
        'firstName' => $this->first_name,
        'lastName1' => $this->last_name_1,
        'lastName2' => $this->last_name_2,
        'address' => $this->address,
        'city' => $this->city,
        'state' => $this->state,
        'postalCode' => $this->postal_code,
        'phone' => $this->phone,
        'casketId' => $this->casket_id,
        'casket' => new CasketResource($this->whenLoaded('casket')),
        'user' => new UserResource($this->whenLoaded('user'))
      ];
    }
}
