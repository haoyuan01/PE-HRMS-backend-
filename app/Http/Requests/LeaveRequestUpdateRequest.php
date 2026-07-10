<?php

namespace App\Http\Requests;

use App\Constants\ConfigurationCodeConstants;
use App\Models\Configuration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestUpdateRequest extends FormRequest
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
            'manager_approver_uuid' => ['required', 'string', 'uuid'],
            'leave_entitlement_uuid' => ['required', 'string', 'uuid'],
            'handover_by_uuid' => ['nullable', 'string', 'uuid'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'resume_date' => ['required', 'date', 'after_or_equal:end_date'],
            'total_days' => ['required', 'numeric', 'min:0'],
            'is_half_day' => ['nullable', 'boolean'],
            'is_first_half' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string'],
            'attachment' => [
                'nullable',
                'mimes:' . Configuration::findByKey(ConfigurationCodeConstants::FILE_ALLOWED_TYPES)->value,
                'max:' . Configuration::findByKey(ConfigurationCodeConstants::FILE_MAX_SIZE_MB)->value * 1024, // size in MB
            ],
        ];
    }
}
