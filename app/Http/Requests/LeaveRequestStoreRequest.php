<?php

namespace App\Http\Requests;

use App\Constants\ConfigurationCodeConstants;
use App\Models\Configuration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestStoreRequest extends FormRequest
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
            'manager_approver_uuid' => ['required', 'string', 'uuid'],
            'leave_entitlement_uuid' => ['required', 'string', 'uuid'],
            'handover_by_uuid' => ['nullable', 'string', 'uuid'],
            'resume_date' => ['required', 'date'],
            'total_days' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string'],
            'attachment' => [
                'nullable',
                'mimes:' . Configuration::findByKey(ConfigurationCodeConstants::FILE_ALLOWED_TYPES)->value,
                'max:' . Configuration::findByKey(ConfigurationCodeConstants::FILE_MAX_SIZE_MB)->value * 1024, // size in MB
            ],
            'request_dates' => ['required', 'array', 'min:1'],
            'request_dates.*.date' => ['required', 'date', 'distinct', 'before:resume_date'],
            'request_dates.*.is_half_day' => ['nullable', 'boolean'],
            'request_dates.*.is_first_half' => ['nullable', 'boolean'],
        ];
    }
}
