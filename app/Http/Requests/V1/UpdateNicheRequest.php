<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNicheRequest extends FormRequest
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
          'internalCode' => ['required', 'unique:niches,internal_code,'. $this->id .',id,row_id,'. $this->row_id],
          'storageQuantity' => ['required'],
          'storageRows' => ['required'],
          'row_id' => ['required']
        ];
      }
    }

    protected function prepareForValidation() {
      $this->merge([
        'internal_code' => $this->internalCode,
        'storage_quantity' => $this->storageQuantity,
        'storage_rows' => $this->storageRows,
        'row_id' => $this->rowId
      ]);
    }
}
