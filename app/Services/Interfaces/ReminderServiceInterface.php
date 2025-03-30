<?php

namespace App\Services\Interfaces;

use App\Models\Order;
use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contract for reminder service implementations.
 * 
 * This interface defines the operations that any reminder service must implement,
 * allowing for better dependency injection, testability, and alternate implementations.
 */
interface ReminderServiceInterface
{
    /**
     * Check if an order has been replaced by another order
     * 
     * @param Order $order The order to check
     * @return bool Whether the order has been replaced
     */
    public function isOrderReplaced(Order $order): bool;

    /**
     * Calculate the expiration date based on the order type and application date
     * 
     * @param Order $order The order to calculate expiration date for
     * @return Carbon The calculated expiration date
     */
    public function calculateExpirationDate(Order $order): Carbon;

    /**
     * Schedules all necessary reminders for an order.
     *
     * @param Order $order The order to schedule reminders for
     * @return array Array of created Reminder instances
     */
    public function scheduleRemindersForOrder(Order $order): array;
    
    /**
     * Cancels all pending reminders for an order.
     *
     * @param Order $order The order whose reminders should be cancelled
     * @param string $reason The reason for cancellation
     * @return int Number of reminders cancelled
     */
    public function cancelRemindersForOrder(Order $order, string $reason = 'Order replaced'): int;
    
    /**
     * Retrieves all pending reminders that are due to be sent.
     *
     * @return Collection Collection of Reminder models
     */
    public function getPendingRemindersToSend(): Collection;
    
    /**
     * Marks a reminder as successfully sent.
     *
     * @param Reminder $reminder The reminder to mark as sent
     * @return bool Whether the operation was successful
     */
    public function markReminderAsSent(Reminder $reminder): bool;
    
    /**
     * Marks a reminder as failed to send.
     *
     * @param Reminder $reminder The reminder to mark as failed
     * @param string $errorMessage The error message explaining the failure
     * @return bool Whether the operation was successful
     */
    public function markReminderAsFailed(Reminder $reminder, string $errorMessage): bool;
} 