<?php

namespace App\Services\Mail;

use App\Models\Reminder;
use App\Mail\ReminderNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Service for sending reminder emails.
 */
class ReminderMailService
{
    /**
     * Send a reminder email.
     *
     * @param Reminder $reminder The reminder to send
     * @return bool Whether the email was sent successfully
     */
    public function sendReminderEmail(Reminder $reminder): bool
    {
        try {
            if (!$this->validateReminderData($reminder)) {
                Log::warning('Invalid reminder data for sending email', ['reminder_id' => $reminder->id]);
                return false;
            }
            
            $mail = new ReminderNotification($reminder);
            
            Mail::to($reminder->email_to)
                ->send($mail);
                
            Log::info('Reminder email sent successfully', [
                'reminder_id' => $reminder->id,
                'order_id' => $reminder->order_id,
                'email_to' => $reminder->email_to
            ]);
            
            // In Laravel, the Mail facade doesn't throw exceptions on failure by default,
            // so we need to check for failures explicitly
            if (count(Mail::failures()) > 0) {
                Log::warning('Failed to send reminder email', [
                    'reminder_id' => $reminder->id,
                    'failures' => Mail::failures()
                ]);
                return false;
            }
            
            return true;
        } catch (Throwable $e) {
            Log::error('Exception while sending reminder email: ' . $e->getMessage(), [
                'reminder_id' => $reminder->id,
                'email_to' => $reminder->email_to,
                'exception' => $e
            ]);
            return false;
        }
    }
    
    /**
     * Validate that a reminder has all the data needed to send an email.
     *
     * @param Reminder $reminder The reminder to validate
     * @return bool Whether the reminder data is valid
     */
    private function validateReminderData(Reminder $reminder): bool
    {
        // Check that we have an email address to send to
        if (empty($reminder->email_to)) {
            return false;
        }
        
        // Check that we have email content
        if (empty($reminder->email_subject) || empty($reminder->email_content)) {
            return false;
        }
        
        // Make sure the order still exists and is accessible
        if (!$reminder->order) {
            return false;
        }
        
        return true;
    }
} 