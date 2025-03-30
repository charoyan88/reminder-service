<?php

namespace App\Http\Requests\Reminder;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class CancelRequest extends FormRequest
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
            'order_id' => [
                'required',
                'exists:orders,id',
                function ($attribute, $value, $fail) {
                    $order = Order::find($value);
                    if (!$order->is_active) {
                        $fail('Cannot cancel reminders for inactive orders.');
                    }
                }
            ],
            'reason' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (strlen($value) < 10) {
                        $fail('Please provide a more detailed reason for cancellation.');
                    }
                }
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
            'order_id.required' => 'Please specify which order\'s reminders to cancel.',
            'order_id.exists' => 'The specified order does not exist.',
            'reason.required' => 'Please provide a reason for cancelling the reminders.',
            'reason.max' => 'The reason cannot exceed 255 characters.',
            'reason.string' => 'The reason must be a text string.'
        ];
    }
} 