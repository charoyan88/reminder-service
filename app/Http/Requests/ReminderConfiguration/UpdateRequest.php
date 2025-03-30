<?php

namespace App\Http\Requests\ReminderConfiguration;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reminder_type' => 'in:pre_expiration,post_expiration',
            'interval_value' => 'integer|min:1',
            'interval_unit' => 'in:day,week,month',
            'is_active' => 'boolean',
            'email_template' => 'nullable|string',
            'email_subject' => 'nullable|string',
        ];
    }
} 