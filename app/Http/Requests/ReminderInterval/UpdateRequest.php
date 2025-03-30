<?php

namespace App\Http\Requests\ReminderInterval;

use App\Models\ReminderIntervalConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $intervalId = $this->route('id');
        $interval = ReminderIntervalConfig::findOrFail($intervalId);

        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'reminder_type' => ['sometimes', Rule::in(['pre_expiration', 'post_expiration'])],
            'days' => [
                'sometimes',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($intervalId, $interval) {
                    // Only validate if days or reminder_type is changing
                    if ($this->has('days') || $this->has('reminder_type')) {
                        $reminderType = $this->reminder_type ?? $interval->reminder_type;
                        
                        // Check for duplicate days within the same reminder type, excluding current record
                        $exists = ReminderIntervalConfig::where('reminder_type', $reminderType)
                            ->where('days', $value)
                            ->where('id', '!=', $intervalId)
                            ->exists();

                        if ($exists) {
                            $intervalType = $reminderType === 'pre_expiration' ? 'before' : 'after';
                            $fail("You already have a reminder set for {$value} days {$intervalType} expiration.");
                        }
                    }
                },
            ],
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'The interval name is required.',
            'reminder_type.in' => 'The reminder type must be either pre-expiration or post-expiration.',
            'days.min' => 'The number of days must be at least 1.',
        ];
    }
} 