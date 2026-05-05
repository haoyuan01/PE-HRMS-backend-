<?php

namespace App\Http\Requests;

use App\Constants\ConfigurationCodeConstants;
use App\Models\Configuration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfigurationUpdateRequesst extends FormRequest
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
        $configuration = Configuration::findByUuid($this->route('uuid'));

        $value_rules = match ($configuration?->value_type) {
            ConfigurationCodeConstants::VALUE_TYPE_INTEGER => ['required', 'integer'],
            ConfigurationCodeConstants::VALUE_TYPE_FLOAT => ['required', 'numeric'],
            ConfigurationCodeConstants::VALUE_TYPE_BOOLEAN => ['required', 'boolean'],
            ConfigurationCodeConstants::VALUE_TYPE_JSON => ['required', 'json'],
            ConfigurationCodeConstants::VALUE_TYPE_STRING => ['required', 'string'],
            default   => ['required'],
        };

        return [
            'uuid' => ['required', 'string', 'uuid'],
            'value' => $value_rules,
        ];
    }
}
