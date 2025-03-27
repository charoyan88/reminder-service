<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'order_type_id',
        'user_id',
        'external_order_id',
        'application_date',
        'expiration_date',
        'is_active',
        'replaced_by_order_id',
    ];

    protected $casts = [
        'application_date' => 'date',
        'expiration_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the business that owns the order.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the order type that the order belongs to.
     */
    public function orderType(): BelongsTo
    {
        return $this->belongsTo(OrderType::class);
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the replacement order.
     */
    public function replacedByOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'replaced_by_order_id');
    }

    /**
     * Get the reminders for the order.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    /**
     * Check if this order has been replaced
     */
    public function isReplaced(): bool
    {
        return $this->replaced_by_order_id !== null;
    }

    /**
     * Calculate the expiration date based on the order type and application date
     */
    public function calculateExpirationDate(): Carbon
    {
        if ($this->orderType->expiration_type === OrderType::EXPIRATION_TYPE_YEARLY) {
            // Expires 1 year after application date
            return $this->application_date->copy()->addYear();
        } elseif ($this->orderType->expiration_type === OrderType::EXPIRATION_TYPE_CALENDAR_YEAR) {
            // Expires on December 31 of the current year
            return Carbon::create(
                $this->application_date->year,
                12,
                31,
                23,
                59,
                59
            );
        }

        // Default to 1 year if expiration type is not recognized
        return $this->application_date->copy()->addYear();
    }
} 