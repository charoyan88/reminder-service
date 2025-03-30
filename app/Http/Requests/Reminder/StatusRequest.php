<?php

namespace App\Http\Requests\Reminder;

use App\Models\Reminder;
use Illuminate\Foundation\Http\FormRequest;

class StatusRequest extends FormRequest
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
            'reminder_id' => [
                'required',
                'exists:reminders,id',
                function ($attribute, $value, $fail) {
                    $reminder = Reminder::find($value);
                    if ($reminder->status !== Reminder::STATUS_PENDING) {
                        $fail('Only pending reminders can be marked as sent or failed.');
                    }
                }
            ],
            'status' => [
                'required',
                'in:sent,failed'
            ],
            'error_message' => [
                'required_if:status,failed',
                'string',
                'max:1000'
            ]
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
            'reminder_id.required' => 'Please specify which reminder to update.',
            'reminder_id.exists' => 'The specified reminder does not exist.',
            'status.required' => 'Please specify the new status for the reminder.',
            'status.in' => 'The status must be either "sent" or "failed".',
            'error_message.required_if' => 'Please provide an error message when marking a reminder as failed.',
            'error_message.max' => 'The error message cannot exceed 1000 characters.'
        ];
    }
} 