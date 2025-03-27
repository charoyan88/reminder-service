<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderConfiguration extends Model
{
    use HasFactory;

    // Reminder types
    public const TYPE_PRE_EXPIRATION = 'pre_expiration';
    public const TYPE_POST_EXPIRATION = 'post_expiration';

    // Units for interval
    public const UNIT_DAY = 'day';
    public const UNIT_WEEK = 'week';
    public const UNIT_MONTH = 'month';

    protected $fillable = [
        'order_type_id',
        'reminder_type',
        'interval_value',
        'interval_unit',
        'is_active',
        'email_template',
        'email_subject',
    ];

    protected $casts = [
        'interval_value' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the order type that the reminder configuration belongs to.
     */
    public function orderType(): BelongsTo
    {
        return $this->belongsTo(OrderType::class);
    }

    /**
     * Get a human-readable description of this reminder timing
     */
    public function getHumanReadableInterval(): string
    {
        $plural = $this->interval_value > 1 ? 's' : '';
        
        if ($this->reminder_type === self::TYPE_PRE_EXPIRATION) {
            return "{$this->interval_value} {$this->interval_unit}{$plural} before expiration";
        }
        
        return "{$this->interval_value} {$this->interval_unit}{$plural} after expiration";
    }
} 