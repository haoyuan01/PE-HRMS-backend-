<?php

namespace App\Http\Requests;

use App\Constants\ConfigurationCodeConstants;
use App\Constants\StatusCodeConstants;
use App\Models\Configuration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', Rule::unique('announcements', 'name')->where(fn ($query) => $query->where('is_active', StatusCodeConstants::ACTIVE))->ignore($this->route('uuid'), 'uuid')],
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
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
