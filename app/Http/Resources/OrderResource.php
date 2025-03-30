<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'business' => $this->whenLoaded('business'),
            'order_type_id' => $this->order_type_id,
            'order_type' => $this->whenLoaded('orderType'),
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user'),
            'external_order_id' => $this->external_order_id,
            'application_date' => $this->application_date?->format('Y-m-d'),
            'expiration_date' => $this->expiration_date?->format('Y-m-d'),
            'is_active' => (bool) $this->is_active,
            'reminders' => $this->whenLoaded('reminders'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
} 