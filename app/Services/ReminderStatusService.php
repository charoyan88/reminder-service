<?php

namespace App\Services;

use App\Models\Reminder;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling reminder status operations
 */
class ReminderStatusService
{
    /**
     * Check if a reminder can be sent (is pending and scheduled for now or earlier)
     * 
     * @param Reminder $reminder The reminder to check
     * @return bool Whether the reminder can be sent
     */
    public function canReminderBeSent(Reminder $reminder): bool
    {
        return $reminder->status === Reminder::STATUS_PENDING && $reminder->scheduled_date->isPast();
    }
    
    /**
     * Cancel a reminder (e.g., when an order is replaced)
     * 
     * @param Reminder $reminder The reminder to cancel
     * @param string $reason The reason for cancellation
     * @return bool Whether the operation was successful
     */
    public function cancelReminder(Reminder $reminder, string $reason = 'Order replaced'): bool
    {
        try {
            if ($reminder->status === Reminder::STATUS_PENDING) {
                $reminder->status = Reminder::STATUS_CANCELLED;
                $reminder->error_message = $reason;
                $result = $reminder->save();
                
                if ($result) {
                    Log::info('Reminder cancelled', [
                        'reminder_id' => $reminder->id,
                        'reason' => $reason
                    ]);
                }
                
                return $result;
            }
            
            return false;
        } catch (\Throwable $e) {
            Log::error('Error cancelling reminder: ' . $e->getMessage(), [
                'reminder_id' => $reminder->id,
                'reason' => $reason,
                'exception' => $e
            ]);
            return false;
        }
    }
} 