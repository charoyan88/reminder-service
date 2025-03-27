<?php

namespace App\Services;

use App\Models\Reminder;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    protected ReminderService $reminderService;
    protected LocalizedMailService $localizedMailService;

    public function __construct(ReminderService $reminderService, LocalizedMailService $localizedMailService)
    {
        $this->reminderService = $reminderService;
        $this->localizedMailService = $localizedMailService;
    }

    /**
     * Send an email reminder
     */
    public function sendReminder(Reminder $reminder): bool
    {
        try {
            // If the order is replaced, don't send the reminder
            if ($reminder->order->isReplaced()) {
                $this->reminderService->cancelRemindersForOrder($reminder->order, 'Order has been replaced');
                return false;
            }
            
            // Send the localized email
            $success = $this->localizedMailService->sendExpirationReminder($reminder);
            
            if ($success) {
                // Mark the reminder as sent
                $this->reminderService->markReminderAsSent($reminder);
            } else {
                // Mark the reminder as failed
                $this->reminderService->markReminderAsFailed($reminder, 'Failed to send email');
            }
            
            return $success;
        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to send reminder email', [
                'reminder_id' => $reminder->id,
                'error' => $e->getMessage(),
            ]);
            
            // Mark the reminder as failed
            $this->reminderService->markReminderAsFailed($reminder, $e->getMessage());
            
            return false;
        }
    }
    
    /**
     * Send all pending reminders that are ready to be sent
     */
    public function sendPendingReminders(): array
    {
        $pendingReminders = $this->reminderService->getPendingRemindersToSend();
        
        $results = [
            'total' => count($pendingReminders),
            'sent' => 0,
            'failed' => 0,
            'cancelled' => 0,
        ];
        
        foreach ($pendingReminders as $reminder) {
            if ($reminder->order->isReplaced()) {
                $this->reminderService->cancelRemindersForOrder($reminder->order, 'Order has been replaced');
                $results['cancelled']++;
                continue;
            }
            
            $success = $this->sendReminder($reminder);
            
            if ($success) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }
        
        return $results;
    }
} 