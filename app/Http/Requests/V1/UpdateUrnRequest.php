<?php

namespace App\Http\Requests\V1;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUrnRequest extends FormRequest
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
          'internalCode' => ['required', 'unique:urns,internal_code,'. $this->id .',id,niche_id,'. $this->niche_id],
          'status' => ['required', Rule::in(['OCCUPIED', 'RESERVED', 'EXPIRED', 'AVAILABLE', 'DISABLED'])],
          'nicheId' => ['required']
        ];
      }
    }

    protected function prepareForValidation() {
      $this->merge([
        'internal_code' => $this->internalCode,
        'niche_id' => $this->nicheId
      ]);
    }
}
