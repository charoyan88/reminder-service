<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Reminder;
use App\Models\ReminderConfiguration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * This service handles everything related to sending reminders to customers.
 * 
 * Think of it as your friendly postal service that makes sure notifications
 * are sent at just the right time - not too early, not too late!
 */
class ReminderService
{
    /**
     * Figures out when a reminder should be sent based on when an order expires.
     * 
     * For example:
     * - If an order expires on March 15th
     * - And we want to send a reminder 7 days before
     * - This will calculate March 8th as the reminder date
     */
    private function calculateReminderDate(Carbon $expirationDate, ReminderConfiguration $config): Carbon
    {
        $date = clone $expirationDate;
        
        if ($config->reminder_type === ReminderConfiguration::TYPE_PRE_EXPIRATION) {
            // For "before expiration" reminders, we count backward from the expiration date
            if ($config->interval_unit === ReminderConfiguration::UNIT_DAY) {
                return $date->subDays($config->interval_value);
            } elseif ($config->interval_unit === ReminderConfiguration::UNIT_WEEK) {
                return $date->subWeeks($config->interval_value);
            } elseif ($config->interval_unit === ReminderConfiguration::UNIT_MONTH) {
                return $date->subMonths($config->interval_value);
            }
        } elseif ($config->reminder_type === ReminderConfiguration::TYPE_POST_EXPIRATION) {
            // For "after expiration" reminders, we count forward from the expiration date
            if ($config->interval_unit === ReminderConfiguration::UNIT_DAY) {
                return $date->addDays($config->interval_value);
            } elseif ($config->interval_unit === ReminderConfiguration::UNIT_WEEK) {
                return $date->addWeeks($config->interval_value);
            } elseif ($config->interval_unit === ReminderConfiguration::UNIT_MONTH) {
                return $date->addMonths($config->interval_value);
            }
        }
        
        return $date;
    }
    
    /**
     * Sets up all the reminders a customer should receive for their order.
     * 
     * When a new order is created, this looks at all possible reminder types
     * (like 1 week before, 3 days before, etc.) and schedules them all at once.
     * It's like setting up a bunch of future alarm clocks!
     */
    public function scheduleRemindersForOrder(Order $order): array
    {
        // Find all the active reminder settings that apply to this type of order
        $configurations = ReminderConfiguration::where('order_type_id', $order->order_type_id)
            ->where('is_active', true)
            ->get();
            
        $createdReminders = [];
        
        // Check what language the customer prefers
        $user = $order->user;
        $language = $user->getPreferredLanguage();
        
        // Set the app to use the customer's language 
        \App::setLocale($language);
        
        foreach ($configurations as $config) {
            // Figure out when this specific reminder should be sent
            $scheduledDate = $this->calculateReminderDate($order->expiration_date, $config);
            
            // No point sending reminders for dates that have already passed
            if ($scheduledDate->isPast()) {
                continue;
            }
            
            // Find the right email template in the customer's language
            $template = $this->getEmailTemplate($config->reminder_type, $language);
            
            if (!$template) {
                // If we can't find a template in their language, try English as a fallback
                $template = $this->getEmailTemplate($config->reminder_type);
                
                if (!$template) {
                    continue; // If we still can't find a template, skip this reminder
                }
            }
            
            // Create a friendly message about the time interval (e.g., "7 days before")
            $intervalType = $config->reminder_type === ReminderConfiguration::TYPE_PRE_EXPIRATION ? 'before' : 'after';
            $intervalKey = "{$config->interval_unit}s_{$intervalType}";
            $interval = trans_choice($intervalKey, $config->interval_value, ['count' => $config->interval_value]);
            
            // Prepare all the information to personalize the email
            $templateData = [
                'business_name' => $order->business->name,
                'order_type' => $order->orderType->name,
                'expiration_date' => $order->expiration_date->translatedFormat('j F Y'),
                'interval' => $interval,
            ];
            
            // Fill in the email template with the customer's actual information
            $parsedTemplate = $template->parse($templateData);
            
            // Save the reminder in our system so we know to send it when the time comes
            $reminder = Reminder::create([
                'order_id' => $order->id,
                'reminder_configuration_id' => $config->id,
                'scheduled_date' => $scheduledDate,
                'status' => Reminder::STATUS_PENDING,
                'email_to' => $order->user->email,
                'email_subject' => $parsedTemplate['subject'],
                'email_content' => $parsedTemplate['body'],
            ]);
            
            $createdReminders[] = $reminder;
        }
        
        return $createdReminders;
    }
    
    /**
     * Stops any unsent reminders when they're no longer needed.
     * 
     * For example, if a customer renews early, we don't want to keep
     * sending them reminders about their old order expiring.
     */
    public function cancelRemindersForOrder(Order $order, string $reason = 'Order replaced'): int
    {
        $pendingReminders = Reminder::where('order_id', $order->id)
            ->where('status', Reminder::STATUS_PENDING)
            ->get();
            
        $cancelCount = 0;
        
        foreach ($pendingReminders as $reminder) {
            if ($reminder->cancel($reason)) {
                $cancelCount++;
            }
        }
        
        return $cancelCount;
    }
    
    /**
     * Finds all reminders that are ready to be sent right now.
     * 
     * This is like checking your calendar each morning to see what 
     * appointments you have today.
     */
    public function getPendingRemindersToSend(): Collection
    {
        return Reminder::where('status', Reminder::STATUS_PENDING)
            ->where('scheduled_date', '<=', now())
            ->get();
    }
    
    /**
     * Finds the right email template to use for a specific reminder.
     * 
     * We try to match both the type of reminder (before/after expiration)
     * and the customer's preferred language.
     */
    private function getEmailTemplate(string $reminderType, string $languageCode = 'en'): ?EmailTemplate
    {
        return EmailTemplate::where('type', $reminderType)
            ->where('language_code', $languageCode)
            ->where('is_active', true)
            ->first();
    }
    
    /**
     * Records that a reminder email was successfully sent.
     * 
     * This is like putting a checkmark on your to-do list.
     */
    public function markReminderAsSent(Reminder $reminder): bool
    {
        $reminder->status = Reminder::STATUS_SENT;
        $reminder->sent_date = now();
        return $reminder->save();
    }
    
    /**
     * Records that we had a problem sending a reminder.
     * 
     * This helps us track issues so we can fix them and possibly
     * try sending the reminder again later.
     */
    public function markReminderAsFailed(Reminder $reminder, string $errorMessage): bool
    {
        $reminder->status = Reminder::STATUS_FAILED;
        $reminder->error_message = $errorMessage;
        return $reminder->save();
    }
} 