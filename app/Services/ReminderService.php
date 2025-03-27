<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Reminder;
use App\Models\ReminderConfiguration;
use App\Repositories\EmailTemplateRepository;
use App\Repositories\ReminderRepository;
use App\Services\Interfaces\ReminderServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * This service handles everything related to sending reminders to customers.
 * 
 * Think of it as your friendly postal service that makes sure notifications
 * are sent at just the right time - not too early, not too late!
 */
class ReminderService implements ReminderServiceInterface
{
    /**
     * @var ReminderRepository
     */
    private ReminderRepository $reminderRepository;

    /**
     * @var EmailTemplateRepository
     */
    private EmailTemplateRepository $emailTemplateRepository;

    /**
     * Constructor with dependency injection
     */
    public function __construct(
        ReminderRepository $reminderRepository,
        EmailTemplateRepository $emailTemplateRepository
    ) {
        $this->reminderRepository = $reminderRepository;
        $this->emailTemplateRepository = $emailTemplateRepository;
    }

    /**
     * Check if an order has been replaced by another order
     * 
     * @param Order $order The order to check
     * @return bool Whether the order has been replaced
     */
    public function isOrderReplaced(Order $order): bool
    {
        return $order->replaced_by_order_id !== null;
    }

    /**
     * Calculate the expiration date based on the order type and application date
     * 
     * @param Order $order The order to calculate expiration date for
     * @return Carbon The calculated expiration date
     */
    public function calculateExpirationDate(Order $order): Carbon
    {
        if ($order->orderType->expiration_type === OrderType::EXPIRATION_TYPE_YEARLY) {
            // Expires 1 year after application date
            return $order->application_date->copy()->addYear();
        } elseif ($order->orderType->expiration_type === OrderType::EXPIRATION_TYPE_CALENDAR_YEAR) {
            // Expires on December 31 of the current year
            return Carbon::create(
                $order->application_date->year,
                12,
                31,
                23,
                59,
                59
            );
        }

        // Default to 1 year if expiration type is not recognized
        return $order->application_date->copy()->addYear();
    }

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
     * 
     * @param Order $order The order to schedule reminders for
     * @return array Array of created Reminder instances
     */
    public function scheduleRemindersForOrder(Order $order): array
    {
        try {
            // Validate order status
            if (!$order->is_active) {
                throw new \InvalidArgumentException('Cannot schedule reminders for inactive orders.');
            }

            if ($this->isOrderReplaced($order)) {
                throw new \InvalidArgumentException('Cannot schedule reminders for replaced orders.');
            }

            // Find all the active reminder settings that apply to this type of order
            $configurations = ReminderConfiguration::where('order_type_id', $order->order_type_id)
                ->where('is_active', true)
                ->get();
                
            $createdReminders = [];
            
            // Check what language the customer prefers
            $user = $order->user;
            $language = $user->getPreferredLanguage();
            
            // Set the app to use the customer's language 
            App::setLocale($language);
            
            foreach ($configurations as $config) {
                // Figure out when this specific reminder should be sent
                $scheduledDate = $this->calculateReminderDate($order->expiration_date, $config);
                
                // No point sending reminders for dates that have already passed
                if ($scheduledDate->isPast()) {
                    continue;
                }
                
                // Find the right email template in the customer's language
                $template = $this->emailTemplateRepository->findActiveByTypeAndLanguage($config->reminder_type, $language);
                
                if (!$template) {
                    // If we can't find a template in their language, try English as a fallback
                    $template = $this->emailTemplateRepository->findActiveByTypeAndLanguage($config->reminder_type, 'en');
                    
                    if (!$template) {
                        Log::warning('No email template found for reminder', [
                            'reminder_type' => $config->reminder_type,
                            'language' => $language,
                            'order_id' => $order->id
                        ]);
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
                try {
                    $reminder = $this->reminderRepository->create([
                        'order_id' => $order->id,
                        'reminder_configuration_id' => $config->id,
                        'scheduled_date' => $scheduledDate,
                        'status' => Reminder::STATUS_PENDING,
                        'email_to' => $order->user->email,
                        'email_subject' => $parsedTemplate['subject'],
                        'email_content' => $parsedTemplate['body'],
                    ]);
                    
                    $createdReminders[] = $reminder;
                } catch (Throwable $e) {
                    Log::error('Failed to create reminder: ' . $e->getMessage(), [
                        'order_id' => $order->id,
                        'config_id' => $config->id,
                        'exception' => $e
                    ]);
                    // Continue with other reminders even if one fails
                }
            }
            
            return $createdReminders;
        } catch (Throwable $e) {
            Log::error('Error scheduling reminders for order: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e
            ]);
            return [];
        }
    }
    
    /**
     * Stops any unsent reminders when they're no longer needed.
     * 
     * For example, if a customer renews early, we don't want to keep
     * sending them reminders about their old order expiring.
     * 
     * @param Order $order The order whose reminders should be cancelled
     * @param string $reason The reason for cancellation
     * @return int Number of reminders cancelled
     */
    public function cancelRemindersForOrder(Order $order, string $reason = 'Order replaced'): int
    {
        try {
            // Validate order status
            if (!$order->is_active) {
                throw new \InvalidArgumentException('Cannot cancel reminders for inactive orders.');
            }

            $pendingReminders = $this->reminderRepository->findPendingByOrder($order);
                
            $cancelCount = 0;
            
            foreach ($pendingReminders as $reminder) {
                if ($this->reminderRepository->markAsCancelled($reminder, $reason)) {
                    $cancelCount++;
                }
            }
            
            Log::info("Cancelled {$cancelCount} reminders for order {$order->id}", [
                'order_id' => $order->id,
                'reason' => $reason
            ]);
            
            return $cancelCount;
        } catch (Throwable $e) {
            Log::error('Error cancelling reminders for order: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'reason' => $reason,
                'exception' => $e
            ]);
            return 0;
        }
    }
    
    /**
     * Finds all reminders that are ready to be sent right now.
     * 
     * This is like checking your calendar each morning to see what 
     * appointments you have today.
     * 
     * @return Collection Collection of Reminder models
     */
    public function getPendingRemindersToSend(): Collection
    {
        try {
            return $this->reminderRepository->findPendingToSend();
        } catch (Throwable $e) {
            Log::error('Error getting pending reminders to send: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return new Collection();
        }
    }
    
    /**
     * Records that a reminder email was successfully sent.
     * 
     * This is like putting a checkmark on your to-do list.
     * 
     * @param Reminder $reminder The reminder to mark as sent
     * @return bool Whether the operation was successful
     */
    public function markReminderAsSent(Reminder $reminder): bool
    {
        try {
            $result = $this->reminderRepository->markAsSent($reminder);
            
            if ($result) {
                Log::info('Reminder marked as sent', ['reminder_id' => $reminder->id]);
                
                // Dispatch an event when a reminder is successfully sent
                event(new \App\Events\ReminderSent($reminder));
            }
            
            return $result;
        } catch (Throwable $e) {
            Log::error('Error marking reminder as sent: ' . $e->getMessage(), [
                'reminder_id' => $reminder->id,
                'exception' => $e
            ]);
            return false;
        }
    }
    
    /**
     * Records that we had a problem sending a reminder.
     * 
     * This helps us track issues so we can fix them and possibly
     * try sending the reminder again later.
     * 
     * @param Reminder $reminder The reminder to mark as failed
     * @param string $errorMessage The error message explaining the failure
     * @return bool Whether the operation was successful
     */
    public function markReminderAsFailed(Reminder $reminder, string $errorMessage): bool
    {
        try {
            $result = $this->reminderRepository->markAsFailed($reminder, $errorMessage);
            
            if ($result) {
                Log::warning('Reminder marked as failed', [
                    'reminder_id' => $reminder->id,
                    'error_message' => $errorMessage
                ]);
                
                // Dispatch an event when a reminder fails to send
                event(new \App\Events\ReminderFailed($reminder, $errorMessage));
            }
            
            return $result;
        } catch (Throwable $e) {
            Log::error('Error marking reminder as failed: ' . $e->getMessage(), [
                'reminder_id' => $reminder->id,
                'error_message' => $errorMessage,
                'exception' => $e
            ]);
            return false;
        }
    }
} 