<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderType extends Model
{
    use HasFactory;

    // Expiration types
    public const EXPIRATION_TYPE_YEARLY = 'yearly'; // Expires 1 year after application date
    public const EXPIRATION_TYPE_CALENDAR_YEAR = 'calendar_year'; // Expires on Dec 31 of current year

    protected $fillable = [
        'business_id',
        'name',
        'code',
        'description',
        'expiration_type',
        'active',
        'requires_renewal',
        'allows_early_renewal',
        'is_active',
        'expiration_period_months',
    ];

    protected $casts = [
        'active' => 'boolean',
        'requires_renewal' => 'boolean',
        'allows_early_renewal' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the business that owns the order type.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the orders for the order type.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the reminder configurations for the order type.
     */
    public function reminderConfigurations(): HasMany
    {
        return $this->hasMany(ReminderConfiguration::class);
    }
} 