<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reminder\ScheduleRequest;
use App\Http\Requests\Reminder\CancelRequest;
use App\Http\Requests\Reminder\StatusRequest;
use App\Models\Order;
use App\Models\Reminder;
use App\Services\Interfaces\ReminderServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ReminderController extends Controller
{
    /**
     * @var ReminderServiceInterface
     */
    private ReminderServiceInterface $reminderService;

    /**
     * Constructor with dependency injection
     *
     * @param ReminderServiceInterface $reminderService
     */
    public function __construct(ReminderServiceInterface $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * Schedule reminders for an order.
     *
     * @param ScheduleRequest $request
     * @return JsonResponse
     */
    public function schedule(ScheduleRequest $request): JsonResponse
    {
        try {
            $order = Order::findOrFail($request->order_id);
            
            // Calculate expiration date if not provided
            if (!$request->has('expiration_date')) {
                $order->expiration_date = $this->reminderService->calculateExpirationDate($order);
            } else {
                $order->expiration_date = $request->expiration_date;
            }
            
            $order->save();

            $reminders = $this->reminderService->scheduleRemindersForOrder($order);

            return response()->json([
                'message' => 'Successfully scheduled reminders for the order',
                'data' => [
                    'order_id' => $order->id,
                    'reminders_scheduled' => count($reminders)
                ]
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } catch (\Throwable $e) {
            Log::error('Failed to schedule reminders: ' . $e->getMessage(), [
                'order_id' => $request->order_id,
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Failed to schedule reminders',
                'errors' => ['general' => ['Please try again later.']]
            ], 500);
        }
    }

    /**
     * Cancel reminders for an order.
     *
     * @param CancelRequest $request
     * @return JsonResponse
     */
    public function cancel(CancelRequest $request): JsonResponse
    {
        try {
            $order = Order::findOrFail($request->order_id);
            $cancelledCount = $this->reminderService->cancelRemindersForOrder($order, $request->reason);

            return response()->json([
                'message' => "Successfully cancelled {$cancelledCount} reminders",
                'data' => [
                    'order_id' => $order->id,
                    'cancelled_count' => $cancelledCount
                ]
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } catch (\Throwable $e) {
            Log::error('Failed to cancel reminders: ' . $e->getMessage(), [
                'order_id' => $request->order_id,
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Failed to cancel reminders',
                'errors' => ['general' => ['Please try again later.']]
            ], 500);
        }
    }

    /**
     * Update the status of a reminder.
     *
     * @param StatusRequest $request
     * @return JsonResponse
     */
    public function updateStatus(StatusRequest $request): JsonResponse
    {
        try {
            $reminder = Reminder::findOrFail($request->reminder_id);
            $success = false;

            if ($request->status === 'sent') {
                $success = $this->reminderService->markReminderAsSent($reminder);
            } elseif ($request->status === 'failed') {
                $success = $this->reminderService->markReminderAsFailed($reminder, $request->error_message);
            }

            if (!$success) {
                return response()->json([
                    'message' => 'Failed to update reminder status',
                    'errors' => ['general' => ['Please try again later.']]
                ], 500);
            }

            return response()->json([
                'message' => "Successfully marked reminder as {$request->status}",
                'data' => [
                    'reminder_id' => $reminder->id,
                    'status' => $request->status
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to update reminder status: ' . $e->getMessage(), [
                'reminder_id' => $request->reminder_id,
                'status' => $request->status,
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Failed to update reminder status',
                'errors' => ['general' => ['Please try again later.']]
            ], 500);
        }
    }
} 