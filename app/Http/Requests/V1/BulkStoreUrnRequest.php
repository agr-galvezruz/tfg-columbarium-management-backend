<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreUrnRequest extends FormRequest
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
            '*.internalCode' => ['required', 'unique:urns,internal_code,NULL,id,niche_id,'. $this->niche_id],
            '*.status' => ['required', Rule::in(['OCCUPIED', 'RESERVED', 'AVAILABLE', 'DISABLED'])],
            '*.nicheId' => ['required'],
            '*.description' => ['nullable']
        ];
    }

    protected function prepareForValidation() {
      $data = [];

      foreach ($this->toArray() as $obj) {
        $obj['internal_code'] = $obj['internalCode'] ?? null;
        $obj['niche_id'] = $obj['nicheId'] ?? null;

        $data[] = $obj;
      }

      $this->merge($data);
    }
}
