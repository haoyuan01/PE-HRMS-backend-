<?php

namespace App\Http\Requests;

use App\Constants\StatusCodeConstants;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', Rule::unique('departments', 'name')->where(fn ($query) => $query->where('is_active', StatusCodeConstants::ACTIVE))->ignore($this->route('uuid'), 'uuid')],
            'description' => ['nullable', 'string'],
        ];
    }
}
