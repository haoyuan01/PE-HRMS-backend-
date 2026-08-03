<?php

namespace App\Http\Requests;

use App\Constants\ConfigurationCodeConstants;
use App\Models\Configuration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PayrollStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_uuid' => ['required', 'string', 'uuid'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
            'remark' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'attachment' => [
                'required',
                'mimes:' . Configuration::findByKey(ConfigurationCodeConstants::FILE_ALLOWED_TYPES)->value,
                'max:' . Configuration::findByKey(ConfigurationCodeConstants::FILE_MAX_SIZE_MB)->value * 1024,
            ],
        ];
    }
}
