<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
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
      $method = $this->method();
      if ($method == 'PUT') {
        return [
          'internalCode' => ['required', 'unique:rooms,internal_code,'. $this->id .',id,building_id,'. $this->building_id],
          'location' => ['required'],
          'buildingId' => ['required']
        ];
      }
    }

    protected function prepareForValidation() {
      $this->merge([
        'internal_code' => $this->internalCode,
        'building_id' => $this->buildingId
      ]);
    }
}
