<?php

namespace App\Http\Requests;

use App\Constants\RegexConstants;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ErrorLogIndexRequest extends FormRequest
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
            'level' => ['nullable', 'string', 'regex:' . RegexConstants::INJECTION_PROTECTED],
            'search_words' => ['nullable', 'array'],
            'search_words.*' => ['nullable', 'string', 'regex:' . RegexConstants::DYNAMIC_SEARCH_INJECTION_PROTECTED],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1'],
            'sortBy' => ['nullable', 'string', 'alpha_dash'],
            'orderBy' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
