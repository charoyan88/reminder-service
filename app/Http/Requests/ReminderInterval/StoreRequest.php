<?php

namespace App\Http\Requests\ReminderInterval;

use App\Models\ReminderIntervalConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_type' => ['required', Rule::in(['pre_expiration', 'post_expiration'])],
            'days' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    // Check for duplicate days within the same reminder type
                    $exists = ReminderIntervalConfig::where('reminder_type', $this->reminder_type)
                        ->where('days', $value)
                        ->exists();

                    if ($exists) {
                        $intervalType = $this->reminder_type === 'pre_expiration' ? 'before' : 'after';
                        $fail("You already have a reminder set for {$value} days {$intervalType} expiration.");
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
            'reminder_type.required' => 'Please specify whether this is a pre-expiration or post-expiration reminder.',
            'reminder_type.in' => 'The reminder type must be either pre-expiration or post-expiration.',
            'days.required' => 'Please specify how many days before/after expiration this reminder should be sent.',
            'days.min' => 'The number of days must be at least 1.',
        ];
    }
} 