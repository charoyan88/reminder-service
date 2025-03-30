<?php

namespace App\Listeners;

use App\Events\ReminderFailed;
use App\Events\ReminderSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener that logs reminder status changes and could perform additional actions.
 */
class LogReminderStatus implements ShouldQueue
{
    /**
     * Handle the reminder sent event.
     *
     * @param ReminderSent $event
     * @return void
     */
    public function handleReminderSent(ReminderSent $event)
    {
        $reminder = $event->reminder;
        $order = $reminder->order;
        
        Log::info('Reminder successfully sent', [
            'reminder_id' => $reminder->id,
            'order_id' => $order->id,
            'order_type' => $order->orderType->name,
            'business_name' => $order->business->name,
            'user_email' => $order->user->email,
            'scheduled_date' => $reminder->scheduled_date->toIso8601String(),
            'sent_date' => $reminder->sent_date->toIso8601String()
        ]);
        
        // Additional actions could be performed here, such as:
        // - Update analytics for sent reminders
        // - Notify administrators about high-value customer reminders
        // - Update customer engagement metrics
    }
    
    /**
     * Handle the reminder failed event.
     *
     * @param ReminderFailed $event
     * @return void
     */
    public function handleReminderFailed(ReminderFailed $event)
    {
        $reminder = $event->reminder;
        $order = $reminder->order;
        
        Log::warning('Reminder failed to send', [
            'reminder_id' => $reminder->id,
            'order_id' => $order->id,
            'order_type' => $order->orderType->name,
            'business_name' => $order->business->name,
            'user_email' => $order->user->email,
            'scheduled_date' => $reminder->scheduled_date->toIso8601String(),
            'error_message' => $event->errorMessage
        ]);
        
        // Additional actions could be performed here, such as:
        // - Alert administrators about persistent failures
        // - Schedule a retry if appropriate
        // - Fallback to alternative notification method
    }
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     * @return array
     */
    public function subscribe($events)
    {
        return [
            ReminderSent::class => 'handleReminderSent',
            ReminderFailed::class => 'handleReminderFailed',
        ];
    }
} 