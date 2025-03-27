<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    use HasFactory;
    
    // Reminder status
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'reminder_configuration_id',
        'scheduled_date',
        'sent_date',
        'status',
        'email_to',
        'email_subject',
        'email_content',
        'error_message',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'sent_date' => 'datetime',
    ];

    /**
     * Get the order that the reminder belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the reminder configuration that the reminder belongs to.
     */
    public function reminderConfiguration(): BelongsTo
    {
        return $this->belongsTo(ReminderConfiguration::class);
    }

    /**
     * Check if this reminder can be sent (is pending and scheduled for now or earlier)
     */
    public function canBeSent(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->scheduled_date->isPast();
    }

    /**
     * Cancel this reminder (e.g., when an order is replaced)
     */
    public function cancel(string $reason = 'Order replaced'): bool
    {
        if ($this->status === self::STATUS_PENDING) {
            $this->status = self::STATUS_CANCELLED;
            $this->error_message = $reason;
            return $this->save();
        }
        
        return false;
    }
} 