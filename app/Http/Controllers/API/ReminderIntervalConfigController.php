<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReminderInterval\StoreRequest;
use App\Http\Requests\ReminderInterval\UpdateRequest;
use App\Http\Resources\ReminderIntervalResource;
use App\Models\ReminderIntervalConfig;
use App\Repositories\ReminderIntervalRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Controller for managing reminder interval configurations.
 */
class ReminderIntervalConfigController extends Controller
{
    /**
     * @var ReminderIntervalRepository
     */
    private ReminderIntervalRepository $repository;

    /**
     * Constructor with dependency injection
     *
     * @param ReminderIntervalRepository $repository
     */
    public function __construct(ReminderIntervalRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List all active reminder intervals.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $intervals = $this->repository->findAllActive();
            
            return response()->json([
                'message' => 'Here are all your available reminder intervals',
                'data' => ReminderIntervalResource::collection($intervals)
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve reminder intervals: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'An error occurred while retrieving reminder intervals',
                'errors' => ['general' => ['Please try again later.']]
            ], 500);
        }
    }

    /**
     * Create a new reminder interval.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $interval = $this->repository->create($validatedData);
            
            return response()->json([
                'message' => 'Successfully created a new reminder interval',
                'data' => new ReminderIntervalResource($interval)
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create reminder interval: ' . $e->getMessage(), [
                'data' => $request->all(),
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'An error occurred while creating the reminder interval',
                'errors' => ['general' => ['Please try again later.']]
            ], 500);
        }
    }

    /**
     * Update an existing reminder interval.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        try {
            $interval = $this->repository->findById($id);
            
            if (!$interval) {
                return response()->json([
                    'message' => 'Reminder interval not found',
                    'errors' => ['id' => ['The specified reminder interval does not exist.']]
                ], 404);
            }
            
            $validatedData = $request->validated();
            $success = $this->repository->update($interval, $validatedData);
            
            if (!$success) {
                return response()->json([
                    'message' => 'Failed to update the reminder interval',
                    'errors' => ['general' => ['Please try again later.']]
                ], 500);
            }
            
            // Reload the model to get the updated data
            $interval = $this->repository->findById($id);
            
            return response()->json([
                'message' => 'Successfully updated the reminder interval',
                'data' => new ReminderIntervalResource($interval)
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to update reminder interval: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $request->all(),
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'An error occurred while updating the reminder interval',
                'errors' => ['general' => ['Please try again later.']]
            ], 500);
        }
    }

    /**
     * Delete a reminder interval.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $interval = $this->repository->findById($id);
            
            if (!$interval) {
                return response()->json([
                    'message' => 'Reminder interval not found',
                    'errors' => ['id' => ['The specified reminder interval does not exist.']]
                ], 404);
            }
            
            if ($interval->is_default) {
                return response()->json([
                    'message' => 'This is a system default interval and cannot be deleted',
                    'errors' => ['message' => ['Default intervals are required for the system to work properly. You can disable them instead of deleting them.']]
                ], 422);
            }
            
            $success = $this->repository->delete($interval);
            
            if (!$success) {
                return response()->json([
                    'message' => 'Failed to delete the reminder interval',
                    'errors' => ['general' => ['Please try again later.']]
                ], 500);
            }
            
            return response()->json([
                'message' => 'The reminder interval has been successfully deleted'
            ], 204);
        } catch (\Throwable $e) {
            Log::error('Failed to delete reminder interval: ' . $e->getMessage(), [
                'id' => $id,
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'An error occurred while deleting the reminder interval',
                'errors' => ['general' => ['Please try again later.']]
            ], 500);
        }
    }

    /**
     * Toggle the active status of a reminder interval.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $interval = $this->repository->findById($id);
            
            if (!$interval) {
                return response()->json([
                    'message' => 'Reminder interval not found',
                    'errors' => ['id' => ['The specified reminder interval does not exist.']]
                ], 404);
            }
            
            $success = $this->repository->toggleStatus($interval);
            
            if (!$success) {
                return response()->json([
                    'message' => 'Failed to toggle the reminder interval status',
                    'errors' => ['general' => ['Please try again later.']]
                ], 500);
            }
            
            // Reload the model to get the updated status
            $interval = $this->repository->findById($id);
            $newStatus = $interval->is_active;
            
            return response()->json([
                'message' => $newStatus 
                    ? 'The reminder interval has been activated' 
                    : 'The reminder interval has been deactivated',
                'data' => new ReminderIntervalResource($interval)
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to toggle reminder interval status: ' . $e->getMessage(), [
                'id' => $id,
                'exception' => $e
            ]);
            
            return response()->json([
                'message' => 'An error occurred while toggling the reminder interval status',
                'errors' => ['general' => ['Please try again later.']]
            ], 500);
        }
    }
} 