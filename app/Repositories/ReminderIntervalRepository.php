<?php

namespace App\Repositories;

use App\Models\ReminderIntervalConfig;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Repository for reminder interval configuration-related database operations.
 * 
 * This class encapsulates all database operations related to reminder intervals,
 * with caching for performance optimization.
 */
class ReminderIntervalRepository
{
    /**
     * Cache key for active intervals
     */
    private const CACHE_KEY_ACTIVE = 'reminder_intervals:active';
    
    /**
     * Cache duration in seconds (1 hour)
     */
    private const CACHE_DURATION = 3600;
    
    /**
     * Find all active reminder intervals, ordered by sort_order.
     *
     * @return Collection Collection of active intervals
     */
    public function findAllActive(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE, self::CACHE_DURATION, function () {
            try {
                return ReminderIntervalConfig::active()
                    ->ordered()
                    ->get();
            } catch (\Exception $e) {
                Log::error('Failed to retrieve active reminder intervals: ' . $e->getMessage(), [
                    'exception' => $e
                ]);
                return new Collection();
            }
        });
    }
    
    /**
     * Find an interval by ID.
     *
     * @param int $id The interval ID
     * @return ReminderIntervalConfig|null The interval or null if not found
     */
    public function findById(int $id): ?ReminderIntervalConfig
    {
        try {
            return ReminderIntervalConfig::findOrFail($id);
        } catch (\Exception $e) {
            Log::error('Failed to find reminder interval: ' . $e->getMessage(), [
                'id' => $id,
                'exception' => $e
            ]);
            return null;
        }
    }
    
    /**
     * Create a new reminder interval.
     *
     * @param array $data Interval data
     * @return ReminderIntervalConfig The created interval
     */
    public function create(array $data): ReminderIntervalConfig
    {
        try {
            $interval = ReminderIntervalConfig::create($data);
            $this->clearCache();
            return $interval;
        } catch (\Exception $e) {
            Log::error('Failed to create reminder interval: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e
            ]);
            throw $e;
        }
    }
    
    /**
     * Update a reminder interval.
     *
     * @param ReminderIntervalConfig $interval The interval to update
     * @param array $data The data to update
     * @return bool Whether the update was successful
     */
    public function update(ReminderIntervalConfig $interval, array $data): bool
    {
        try {
            $result = $interval->update($data);
            $this->clearCache();
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to update reminder interval: ' . $e->getMessage(), [
                'interval_id' => $interval->id,
                'data' => $data,
                'exception' => $e
            ]);
            return false;
        }
    }
    
    /**
     * Toggle the active status of a reminder interval.
     *
     * @param ReminderIntervalConfig $interval The interval to toggle
     * @return bool Whether the toggle was successful
     */
    public function toggleStatus(ReminderIntervalConfig $interval): bool
    {
        try {
            $newStatus = !$interval->is_active;
            $result = $interval->update(['is_active' => $newStatus]);
            $this->clearCache();
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to toggle reminder interval status: ' . $e->getMessage(), [
                'interval_id' => $interval->id,
                'exception' => $e
            ]);
            return false;
        }
    }
    
    /**
     * Delete a reminder interval.
     *
     * @param ReminderIntervalConfig $interval The interval to delete
     * @return bool Whether the deletion was successful
     */
    public function delete(ReminderIntervalConfig $interval): bool
    {
        try {
            if ($interval->is_default) {
                return false;
            }
            
            $result = $interval->delete();
            $this->clearCache();
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to delete reminder interval: ' . $e->getMessage(), [
                'interval_id' => $interval->id,
                'exception' => $e
            ]);
            return false;
        }
    }
    
    /**
     * Clear the cache for reminder intervals.
     *
     * @return void
     */
    private function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVE);
    }
} 