<?php

namespace App\Http\Requests;

use App\Constants\RegexConstants;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PayrollIndexRequest extends FormRequest
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
            'uuid' => ['nullable', 'string', 'uuid'],
            'user_uuid' => ['nullable', 'string', 'uuid'],
            'is_active' => ['nullable', 'integer', 'between:0,1'],
            'is_published' => ['nullable', 'integer', 'between:0,1'],
            'date' => ['nullable', 'date'],
            'email' => ['nullable', 'string', 'regex:' . RegexConstants::EMAIL],
            'company_email' => ['nullable', 'string', 'regex:' . RegexConstants::EMAIL],
            'department' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'position' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'office' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'joined_date' => ['nullable', 'date'],
            'joined_date_from' => ['nullable', 'date'],
            'joined_date_to' => ['nullable', 'date', 'after_or_equal:joined_date_from'],
            'search_words' => ['nullable', 'array'],
            'search_words.*' => ['nullable', 'string', 'regex:' . RegexConstants::DYNAMIC_SEARCH_INJECTION_PROTECTED],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1'],
            'sortBy' => ['nullable', 'string', 'alpha_dash'],
            'orderBy' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
