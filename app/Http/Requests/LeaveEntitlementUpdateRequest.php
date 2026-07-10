<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LeaveEntitlementUpdateRequest extends FormRequest
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
            'entitled_days' => ['required', 'numeric', 'min:0'],
            'carried_forward_days' => ['required', 'numeric', 'min:0'],
            'used_days' => ['required', 'numeric', 'min:0'],
            'balance_days' => ['required', 'numeric', 'min:0'],
            'carry_forward_expiry_date' => ['nullable', 'date'],
        ];
    }
}
