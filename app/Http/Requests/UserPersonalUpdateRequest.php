<?php

namespace App\Http\Requests;

use App\Constants\ConfigurationCodeConstants;
use App\Constants\StatusCodeConstants;
use App\Models\Configuration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserPersonalUpdateRequest extends FormRequest
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
            'user_uuid' => ['required', 'string', 'uuid'],
            'full_name' => ['nullable', 'string'],
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'identity_number' => ['nullable', 'string'],
            'passport_number' => ['nullable', 'string'],
            'passport_expiry_date' => ['nullable', 'date'],
            'blood_type' => ['nullable', 'string'],
            'image' => [
                'nullable',
                'image',
                'mimes:' . Configuration::findByKey(ConfigurationCodeConstants::IMAGE_ALLOWED_TYPES)->value,
                'max:' . Configuration::findByKey(ConfigurationCodeConstants::IMAGE_MAX_SIZE_MB)->value * 1024, // size in MB
            ],
            'gender' => ['nullable', 'string'],
            'is_married' => ['nullable', 'boolean'],
        ];
    }
}
