<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReminderIntervalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'reminder_type' => $this->reminder_type,
            'days' => $this->days,
            'is_default' => (bool) $this->is_default,
            'is_active' => (bool) $this->is_active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
            
            // Add additional derived fields for frontend convenience
            'interval_type' => $this->reminder_type === 'pre_expiration' ? 'before' : 'after',
            'formatted_interval' => $this->days . ' days ' . 
                ($this->reminder_type === 'pre_expiration' ? 'before' : 'after') . ' expiration',
        ];
    }
} 