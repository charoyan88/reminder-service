<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Reminder;
use App\Models\ReminderConfiguration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class LocalizedMailService
{
    /**
     * Send a localized expiration reminder email
     *
     * @param Reminder $reminder The reminder record
     * @param string|null $language The language code to use (if null, use user's preference)
     * @return bool Whether the email was sent successfully
     */
    public function sendExpirationReminder(Reminder $reminder, ?string $language = null): bool
    {
        $order = $reminder->order;
        $user = $order->user;
        $language = $language ?? $user->getPreferredLanguage();
        
        // Set the application locale to the user's preferred language
        App::setLocale($language);
        
        // Build the interval text using the appropriate language file
        $config = $reminder->reminderConfiguration;
        $intervalKey = $this->getIntervalTranslationKey($config);
        
        $data = [
            'isPreExpiration' => $config->reminder_type === ReminderConfiguration::TYPE_PRE_EXPIRATION,
            'orderType' => $order->orderType->name,
            'businessName' => $order->business->name,
            'expirationDate' => $order->expiration_date->translatedFormat('j F Y'),
            'interval' => trans_choice($intervalKey, $config->interval_value, ['count' => $config->interval_value]),
            'renewUrl' => route('orders.renew', ['id' => $order->id]),
        ];
        
        try {
            // Send the email using the localized template
            Mail::send(['html' => 'emails.reminders.expiration-notification', 'text' => 'emails.reminders.expiration-notification-text'], 
                $data, 
                function ($message) use ($reminder, $user, $data) {
                    $subject = $data['isPreExpiration'] 
                        ? __('reminders.pre_expiration_subject', ['order_type' => $data['orderType'], 'expiration_date' => $data['expirationDate']])
                        : __('reminders.post_expiration_subject', ['order_type' => $data['orderType']]);
                    
                    $message->to($user->email, $user->name)
                            ->subject($subject);
                }
            );
            
            return true;
        } catch (\Exception $e) {
            // Log the error
            \Log::error("Failed to send localized email: " . $e->getMessage(), [
                'reminder_id' => $reminder->id,
                'language' => $language,
            ]);
            
            return false;
        }
    }
    
    /**
     * Get the translation key for the interval
     *
     * @param ReminderConfiguration $config
     * @return string
     */
    private function getIntervalTranslationKey(ReminderConfiguration $config): string
    {
        $type = $config->reminder_type === ReminderConfiguration::TYPE_PRE_EXPIRATION ? 'before' : 'after';
        $unit = $config->interval_unit;
        
        return "{$unit}s_{$type}";
    }
} 