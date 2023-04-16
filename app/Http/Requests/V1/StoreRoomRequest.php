<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
      return [
        'internalCode' => ['required', 'unique:rooms,internal_code,NULL,id,building_id,'. $this->building_id],
        'location' => ['required'],
        'building_id' => ['required']
      ];
    }

    protected function prepareForValidation() {
      $this->merge([
        'internal_code' => $this->internalCode,
        'building_id' => $this->buildingId
      ]);
    }
}
