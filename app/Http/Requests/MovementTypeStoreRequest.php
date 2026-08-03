<?php

namespace App\Http\Requests;

use App\Constants\StatusCodeConstants;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MovementTypeStoreRequest extends FormRequest
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
            'name' => ['required', 'string', Rule::unique('movement_types', 'name')->where(fn ($query) => $query->where('is_active', StatusCodeConstants::ACTIVE))],
            'description' => ['nullable', 'string'],
        ];
    }
}
