<?php

namespace App\Http\Requests\V1;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StorePersonRequest extends FormRequest
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
          'dni' => ['nullable', 'unique:people,dni'],
          'firstName' => ['required'],
          'lastName1' => ['required'],
          'lastName2' => ['required'],
          'address' => ['required'],
          'city' => ['required'],
          'state' => ['required'],
          'postalCode' => ['required'],
          'maritalStatus' => [Rule::in(['SINGLE','MARRIED','UNION','SEPARATE','DIVORCED','WIDOWER']), 'nullable']
        ];
    }

    protected function prepareForValidation() {
      $this->merge([
        'first_name' => $this->firstName,
        'last_name_1' => $this->lastName1,
        'last_name_2' => $this->lastName2,
        'postal_code' => $this->postalCode,
        'marital_status' => $this->maritalStatus
      ]);
    }
}