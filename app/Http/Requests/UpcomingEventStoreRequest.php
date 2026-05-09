<?php

namespace App\Http\Requests;

use App\Constants\ConfigurationCodeConstants;
use App\Constants\StatusCodeConstants;
use App\Models\Configuration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpcomingEventStoreRequest extends FormRequest
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
            'name' => ['required', 'string', Rule::unique('announcements', 'name')->where(fn ($query) => $query->where('is_active', StatusCodeConstants::ACTIVE))],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_published' => ['required', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => [
                'required',
                'image',
                'mimes:' . Configuration::findByKey(ConfigurationCodeConstants::IMAGE_ALLOWED_TYPES)->value,
                'max:' . Configuration::findByKey(ConfigurationCodeConstants::IMAGE_MAX_SIZE_MB)->value * 1024, // size in MB
            ],
        ];
    }
}
