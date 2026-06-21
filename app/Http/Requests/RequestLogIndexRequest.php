<?php

namespace App\Http\Requests;

use App\Constants\RegexConstants;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RequestLogIndexRequest extends FormRequest
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
            'method' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'path' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'ip' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'user_agent' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'status_code' => ['nullable', 'integer', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'success' => ['nullable', 'integer', 'between:-1,1'],
            'created_at_start' => ['nullable', 'date_format:Y-m-d'],
            'created_at_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:created_at_start'],
            'search_words' => ['nullable', 'array'],
            'search_words.*' => ['nullable', 'string', 'regex:' . RegexConstants::DYNAMIC_SEARCH_INJECTION_PROTECTED],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1'],
            'sortBy' => ['nullable', 'string', 'alpha_dash'],
            'orderBy' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
