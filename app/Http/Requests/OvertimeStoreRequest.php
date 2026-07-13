<?php

namespace App\Http\Requests;

use App\Constants\ConfigurationCodeConstants;
use App\Models\Configuration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OvertimeStoreRequest extends FormRequest
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
            'manager_approver_uuid' => ['required', 'string', 'uuid'],
            'description' => ['nullable', 'string'],
            'start_datetime' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_datetime' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:start_datetime'],
            'total_days' => ['nullable', 'numeric', 'min:0'],
            'attachment' => [
                'nullable',
                'mimes:' . Configuration::findByKey(ConfigurationCodeConstants::FILE_ALLOWED_TYPES)->value,
                'max:' . Configuration::findByKey(ConfigurationCodeConstants::FILE_MAX_SIZE_MB)->value * 1024, // size in MB
            ],
        ];
    }
}
