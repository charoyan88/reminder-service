<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'business_id' => 'required|exists:businesses,id',
            'order_type_id' => 'required|exists:order_types,id',
            'user_id' => 'required|exists:users,id',
            'external_order_id' => 'nullable|string',
            'application_date' => 'required|date',
        ];
    }
} 