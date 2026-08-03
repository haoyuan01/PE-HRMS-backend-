<?php

namespace App\Http\Requests;

use App\Constants\ConfigurationCodeConstants;
use App\Models\Configuration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MovementStoreRequest extends FormRequest
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
            'movement_type_uuid' => ['required', 'string', 'uuid'],
            'location' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'attachment' => [
                'nullable',
                'mimes:' . Configuration::findByKey(ConfigurationCodeConstants::FILE_ALLOWED_TYPES)->value,
                'max:' . Configuration::findByKey(ConfigurationCodeConstants::FILE_MAX_SIZE_MB)->value * 1024, // size in MB
            ],
        ];
    }
}
