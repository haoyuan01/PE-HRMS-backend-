<?php

namespace App\Http\Requests;

use App\Constants\ConfigurationCodeConstants;
use App\Constants\StatusCodeConstants;
use App\Models\Configuration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', Rule::unique('users', 'email')->where(fn ($query) => $query->where('is_active', StatusCodeConstants::ACTIVE))],
            'password' => ['required', 'string', 'min:6'],
            'role_uuid' => ['required', 'string', 'uuid'],

            // personal
            'personal.full_name' => ['nullable', 'string'],
            'personal.first_name' => ['nullable', 'string'],
            'personal.last_name' => ['nullable', 'string'],
            'personal.identity_number' => ['nullable', 'string'],
            'personal.passport_number' => ['nullable', 'string'],
            'personal.passport_expiry_date' => ['nullable', 'date'],
            'personal.blood_type' => ['nullable', 'string'],
            'personal.image' => [
                'nullable',
                'image',
                'mimes:' . Configuration::findByKey(ConfigurationCodeConstants::IMAGE_ALLOWED_TYPES)->value,
                'max:' . Configuration::findByKey(ConfigurationCodeConstants::IMAGE_MAX_SIZE_MB)->value * 1024, // size in MB
            ],
            'personal.gender' => ['nullable', 'string'],
            'personal.is_married' => ['nullable', 'boolean'],

            // contact
            'contact.company_email' => ['nullable', 'email'],
            'contact.phone_number' => ['nullable', 'string'],
            'contact.address_1' => ['nullable', 'string'],
            'contact.address_2' => ['nullable', 'string'],
            'contact.address_3' => ['nullable', 'string'],
            'contact.city' => ['nullable', 'string'],
            'contact.state' => ['nullable', 'string'],
            'contact.postcode' => ['nullable', 'string'],
            'contact.country' => ['nullable', 'string'],

            // employment
            'employment.position_uuid' => ['nullable', 'string', 'uuid'],
            'employment.department_uuid' => ['nullable', 'string', 'uuid'],
            'employment.office_uuid' => ['nullable', 'string', 'uuid'],
            'employment.joined_date' => ['nullable', 'date'],

            // emergency
            'emergency.name' => ['nullable', 'string'],
            'emergency.phone_number' => ['nullable', 'string'],
            'emergency.relationship' => ['nullable', 'string'],
        ];
    }
}
