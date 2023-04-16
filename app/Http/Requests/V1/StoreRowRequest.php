<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRowRequest extends FormRequest
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
        'internalCode' => ['required', 'unique:rows,internal_code,NULL,id,room_id,'. $this->room_id],
        'room_id' => ['required']
      ];
    }

    protected function prepareForValidation() {
      $this->merge([
        'internal_code' => $this->internalCode,
        'room_id' => $this->roomId
      ]);
    }
}
