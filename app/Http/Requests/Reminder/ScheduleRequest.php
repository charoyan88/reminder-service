<?php

namespace App\Http\Requests\Reminder;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleRequest extends FormRequest
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
                        $fail('Cannot schedule reminders for inactive orders.');
                    }
                    if ($order->isReplaced()) {
                        $fail('Cannot schedule reminders for replaced orders.');
                    }
                }
            ],
            'expiration_date' => [
                'required',
                'date',
                'after:today',
                function ($attribute, $value, $fail) {
                    if (strtotime($value) > strtotime('+1 year')) {
                        $fail('Expiration date cannot be more than 1 year in the future.');
                    }
                }
            ],
            'language' => [
                'sometimes',
                'string',
                'size:2',
                Rule::in(['en', 'es', 'fr', 'de', 'it', 'pt', 'nl', 'pl', 'ru', 'ja', 'ko', 'zh'])
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
            'order_id.required' => 'Please specify which order to schedule reminders for.',
            'order_id.exists' => 'The specified order does not exist.',
            'expiration_date.required' => 'Please specify when the order expires.',
            'expiration_date.date' => 'The expiration date must be a valid date.',
            'expiration_date.after' => 'The expiration date must be in the future.',
            'language.size' => 'The language code must be exactly 2 characters.',
            'language.in' => 'The selected language is not supported.'
        ];
    }
} 