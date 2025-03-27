<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'business_id',
        'order_type_id',
        'expiration_date',
        'is_active',
        'replaced_by_order_id'
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the business that owns the order.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the order type of the order.
     */
    public function orderType(): BelongsTo
    {
        return $this->belongsTo(OrderType::class);
    }

    /**
     * Get the order that replaced this order.
     */
    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'replaced_by_order_id');
    }

    /**
     * Get the orders that this order replaced.
     */
    public function replacedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'replaced_by_order_id');
    }

    /**
     * Get the reminders for the order.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }
} 