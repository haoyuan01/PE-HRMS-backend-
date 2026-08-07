<?php

namespace App\Http\Requests;

use App\Constants\RegexConstants;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserIndexRequest extends FormRequest
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
            'user_uuid' => ['nullable', 'string', 'uuid'],
            'name' => ['nullable', 'string'],
            'email' => ['nullable', 'string', 'email'],
            'company_email' => ['nullable', 'string', 'email'],
            'phone_number' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
            'postcode' => ['nullable', 'string'],
            'blood_type' => ['nullable', 'string'],
            'identity_number' => ['nullable', 'string'],
            'passport_number' => ['nullable', 'string'],
            'gender' => ['nullable', 'integer', 'between:0,1'],
            'is_married' => ['nullable', 'integer', 'between:0,1'],
            'is_active' => ['nullable', 'integer', 'between:0,1'],
            'department' => ['nullable', 'string'],
            'position' => ['nullable', 'string'],
            'office' => ['nullable', 'string'],
            'joined_date_from' => ['nullable', 'date'],
            'joined_date_to' => ['nullable', 'date'],
            'payroll_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'payroll_year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'search_words' => ['nullable', 'array'],
            'search_words.*' => ['nullable', 'string', 'regex:' . RegexConstants::DYNAMIC_SEARCH_INJECTION_PROTECTED],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1'],
            'sortBy' => ['nullable', 'string', 'alpha_dash'],
            'orderBy' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
