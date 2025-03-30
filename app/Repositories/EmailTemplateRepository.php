<?php

namespace App\Repositories;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Repository for email template-related database operations.
 * 
 * This class encapsulates all database operations related to email templates,
 * with caching for performance optimization.
 */
class EmailTemplateRepository
{
    /**
     * Cache key prefix for email templates
     */
    private const CACHE_KEY_PREFIX = 'email_template:';
    
    /**
     * Cache duration in seconds (1 hour)
     */
    private const CACHE_DURATION = 3600;
    
    /**
     * Find an active email template by type and language.
     *
     * @param string $reminderType The type of reminder
     * @param string $languageCode The language code
     * @return EmailTemplate|null The template or null if not found
     */
    public function findActiveByTypeAndLanguage(string $reminderType, string $languageCode = 'en'): ?EmailTemplate
    {
        $cacheKey = self::CACHE_KEY_PREFIX . "{$reminderType}:{$languageCode}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($reminderType, $languageCode) {
            try {
                return EmailTemplate::where('type', $reminderType)
                    ->where('language_code', $languageCode)
                    ->where('is_active', true)
                    ->first();
            } catch (\Exception $e) {
                Log::error('Failed to retrieve email template: ' . $e->getMessage(), [
                    'type' => $reminderType,
                    'language_code' => $languageCode,
                    'exception' => $e
                ]);
                return null;
            }
        });
    }
    
    /**
     * Create a new email template.
     *
     * @param array $data Template data
     * @return EmailTemplate The created template
     */
    public function create(array $data): EmailTemplate
    {
        try {
            $template = EmailTemplate::create($data);
            $this->clearCache($template->type, $template->language_code);
            return $template;
        } catch (\Exception $e) {
            Log::error('Failed to create email template: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e
            ]);
            throw $e;
        }
    }
    
    /**
     * Update an email template.
     *
     * @param EmailTemplate $template The template to update
     * @param array $data The data to update
     * @return bool Whether the update was successful
     */
    public function update(EmailTemplate $template, array $data): bool
    {
        try {
            $result = $template->update($data);
            $this->clearCache($template->type, $template->language_code);
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to update email template: ' . $e->getMessage(), [
                'template_id' => $template->id,
                'data' => $data,
                'exception' => $e
            ]);
            return false;
        }
    }
    
    /**
     * Clear the cache for a specific template.
     *
     * @param string $type The template type
     * @param string $languageCode The language code
     * @return void
     */
    private function clearCache(string $type, string $languageCode): void
    {
        $cacheKey = self::CACHE_KEY_PREFIX . "{$type}:{$languageCode}";
        Cache::forget($cacheKey);
    }
} 