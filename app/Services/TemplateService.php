<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling email template operations
 */
class TemplateService
{
    /**
     * Parse the template with the given data
     * 
     * @param EmailTemplate $template The email template to parse
     * @param array $data The data to replace in the template
     * @return array The parsed subject and body
     */
    public function parseTemplate(EmailTemplate $template, array $data): array
    {
        try {
            $subject = $template->subject;
            $body = $template->body;
            
            foreach ($data as $key => $value) {
                $placeholder = '{{' . $key . '}}';
                $subject = str_replace($placeholder, $value, $subject);
                $body = str_replace($placeholder, $value, $body);
            }
            
            return [
                'subject' => $subject,
                'body' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('Error parsing email template: ' . $e->getMessage(), [
                'template_id' => $template->id,
                'data' => $data,
                'exception' => $e
            ]);
            
            return [
                'subject' => $template->subject,
                'body' => $template->body
            ];
        }
    }
    
    /**
     * Get a human-readable description for a reminder configuration
     * 
     * @param string $reminderType The type of reminder
     * @param int $intervalValue The value of the interval
     * @param string $intervalUnit The unit of the interval
     * @return string A human-readable interval description
     */
    public function getHumanReadableInterval(string $reminderType, int $intervalValue, string $intervalUnit): string
    {
        $plural = $intervalValue > 1 ? 's' : '';
        
        if ($reminderType === 'pre_expiration') {
            return "{$intervalValue} {$intervalUnit}{$plural} before expiration";
        }
        
        return "{$intervalValue} {$intervalUnit}{$plural} after expiration";
    }
} 