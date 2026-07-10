<?php

namespace App\Http\Requests;

use App\Constants\StatusCodeConstants;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeavePolicyStoreRequest extends FormRequest
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
            'name' => ['required', 'string', Rule::unique('leave_policies', 'name')->where(fn ($query) => $query->where('is_active', StatusCodeConstants::ACTIVE))],
            'code' => ['required', 'string', Rule::unique('leave_policies', 'code')->where(fn ($query) => $query->where('is_active', StatusCodeConstants::ACTIVE))],
            'description' => ['nullable', 'string'],
            'allow_half_day' => ['required', 'boolean'],
            'carry_forward_days' => ['nullable', 'numeric', 'min:0'],
            'carry_forward_expiry_month' => ['nullable', 'integer', 'between:1,12'],
            'carry_forward_expiry_date' => ['nullable', 'integer', 'between:1,31'],
            'is_handover_required' => ['required', 'boolean'],
            'handover_min_days' => ['nullable', 'numeric', 'min:0'],
            'min_notice_days' => ['nullable', 'integer', 'min:0'],
            'requires_attachment' => ['required', 'boolean'],
            'is_paid' => ['required', 'boolean'],
            'leave_policy_tiers' => ['required', 'array', 'min:1'],
            'leave_policy_tiers.*.service_year_from' => ['required', 'integer', 'min:0'],
            'leave_policy_tiers.*.service_year_to' => ['nullable', 'integer', 'min:0'],
            'leave_policy_tiers.*.entitlement_days' => ['required', 'numeric', 'min:0'],
        ];
    }
}
