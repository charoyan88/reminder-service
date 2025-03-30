<?php

namespace App\Repositories;

use App\Models\Reminder;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Repository for reminder-related database operations.
 * 
 * This class encapsulates all database operations related to reminders,
 * separating data access logic from business logic.
 */
class ReminderRepository
{
    /**
     * Create a new reminder.
     *
     * @param array $data Reminder data
     * @return Reminder The created reminder
     */
    public function create(array $data): Reminder
    {
        try {
            return Reminder::create($data);
        } catch (\Exception $e) {
            Log::error('Failed to create reminder: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e
            ]);
            throw $e;
        }
    }
    
    /**
     * Find all pending reminders for a specific order.
     *
     * @param Order $order The order to find reminders for
     * @return Collection Collection of pending reminders
     */
    public function findPendingByOrder(Order $order): Collection
    {
        return Reminder::where('order_id', $order->id)
            ->where('status', Reminder::STATUS_PENDING)
            ->get();
    }
    
    /**
     * Find all pending reminders that are scheduled for now or earlier.
     *
     * @return Collection Collection of pending reminders to send
     */
    public function findPendingToSend(): Collection
    {
        return Reminder::where('status', Reminder::STATUS_PENDING)
            ->where('scheduled_date', '<=', now())
            ->get();
    }
    
    /**
     * Mark a reminder as sent.
     *
     * @param Reminder $reminder The reminder to mark as sent
     * @return bool Whether the update was successful
     */
    public function markAsSent(Reminder $reminder): bool
    {
        try {
            $reminder->status = Reminder::STATUS_SENT;
            $reminder->sent_date = now();
            return $reminder->save();
        } catch (\Exception $e) {
            Log::error('Failed to mark reminder as sent: ' . $e->getMessage(), [
                'reminder_id' => $reminder->id,
                'exception' => $e
            ]);
            return false;
        }
    }
    
    /**
     * Mark a reminder as failed.
     *
     * @param Reminder $reminder The reminder to mark as failed
     * @param string $errorMessage The error message
     * @return bool Whether the update was successful
     */
    public function markAsFailed(Reminder $reminder, string $errorMessage): bool
    {
        try {
            $reminder->status = Reminder::STATUS_FAILED;
            $reminder->error_message = $errorMessage;
            return $reminder->save();
        } catch (\Exception $e) {
            Log::error('Failed to mark reminder as failed: ' . $e->getMessage(), [
                'reminder_id' => $reminder->id,
                'error_message' => $errorMessage,
                'exception' => $e
            ]);
            return false;
        }
    }
    
    /**
     * Mark a reminder as cancelled.
     *
     * @param Reminder $reminder The reminder to cancel
     * @param string $reason The reason for cancellation
     * @return bool Whether the update was successful
     */
    public function markAsCancelled(Reminder $reminder, string $reason): bool
    {
        try {
            if ($reminder->status !== Reminder::STATUS_PENDING) {
                return false;
            }
            
            $reminder->status = Reminder::STATUS_CANCELLED;
            $reminder->error_message = $reason;
            return $reminder->save();
        } catch (\Exception $e) {
            Log::error('Failed to cancel reminder: ' . $e->getMessage(), [
                'reminder_id' => $reminder->id,
                'reason' => $reason,
                'exception' => $e
            ]);
            return false;
        }
    }
} 