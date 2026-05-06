<?php

namespace App\Http\Requests;

use App\Constants\StatusCodeConstants;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfficeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge(['uuid' => $this->route('uuid')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'uuid' => ['required', 'string', 'uuid'],
            'name' => ['required', 'string', Rule::unique('offices', 'name')->where(fn ($query) => $query->where('is_active', StatusCodeConstants::ACTIVE))->ignore($this->route('uuid'), 'uuid')],
            'description' => ['nullable', 'string'],
            'address_1' => ['nullable', 'string'],
            'address_2' => ['nullable', 'string'],
            'address_3' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'postcode' => ['nullable', 'numeric'],
            'country' => ['nullable', 'string'],
            'phone_code' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'numeric'],
            'phone_iso' => ['nullable', 'string'],
            'fax_code' => ['nullable', 'string'],
            'fax_number' => ['nullable', 'numeric'],
            'fax_iso' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
        ];
    }
}
