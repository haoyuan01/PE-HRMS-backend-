<?php

namespace App\Http\Requests;

use App\Constants\RegexConstants;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MovementIndexRequest extends FormRequest
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
            'uuid' => ['nullable', 'string', 'uuid'],
            'movement_type_uuid' => ['nullable', 'string', 'uuid'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'is_active' => ['nullable', 'integer', 'between:-1,1'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date'],
            'user_uuid' => ['nullable', 'string', 'uuid'],
            'user_name' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'email' => ['nullable', 'string', 'regex:' . RegexConstants::EMAIL],
            'company_email' => ['nullable', 'string', 'regex:' . RegexConstants::EMAIL],
            'phone_number' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'department' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'position' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'office' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'search_words' => ['nullable', 'array'],
            'search_words.*' => ['nullable', 'string', 'regex:' . RegexConstants::DYNAMIC_SEARCH_INJECTION_PROTECTED],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1'],
            'sortBy' => ['nullable', 'string', 'alpha_dash'],
            'orderBy' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
