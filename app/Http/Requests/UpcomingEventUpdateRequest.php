<?php

namespace App\Http\Requests;

use App\Constants\ConfigurationCodeConstants;
use App\Constants\StatusCodeConstants;
use App\Models\Configuration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpcomingEventUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', Rule::unique('upcoming_events', 'name')->where(fn ($query) => $query->where('is_active', StatusCodeConstants::ACTIVE))->ignore($this->route('uuid'), 'uuid')],
            'description' => ['required', 'string'],
            'location' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'is_published' => ['required', 'boolean'],
            'department_uuid' => ['nullable', 'array'],
            'department_uuid.*' => ['nullable', 'string', 'uuid'],
            'office_uuid' => ['nullable', 'array'],
            'office_uuid.*' => ['nullable', 'string', 'uuid'],
            'images' => ['nullable', 'array'],
            'images.*.uuid' => ['nullable', 'string', 'uuid'],
            'images.*.image' => [
                'nullable',
                'image',
                'mimes:' . Configuration::findByKey(ConfigurationCodeConstants::IMAGE_ALLOWED_TYPES)->value,
                'max:' . Configuration::findByKey(ConfigurationCodeConstants::IMAGE_MAX_SIZE_MB)->value * 1024, // size in MB
            ],
        ];
    }
}
