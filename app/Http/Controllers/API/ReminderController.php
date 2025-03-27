<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Reminder;
use App\Services\EmailService;
use App\Services\ReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    protected ReminderService $reminderService;
    protected EmailService $emailService;
    
    public function __construct(ReminderService $reminderService, EmailService $emailService)
    {
        $this->reminderService = $reminderService;
        $this->emailService = $emailService;
    }
    
    /**
     * Get all reminders for an order
     */
    public function getOrderReminders($orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $reminders = $order->reminders()->orderBy('scheduled_date')->get();
        
        return response()->json(['reminders' => $reminders]);
    }
    
    /**
     * Get details of a specific reminder
     */
    public function show($id): JsonResponse
    {
        $reminder = Reminder::with(['order', 'reminderConfiguration'])->findOrFail($id);
        
        return response()->json(['reminder' => $reminder]);
    }
    
    /**
     * Manually send a specific reminder
     */
    public function send($id): JsonResponse
    {
        $reminder = Reminder::findOrFail($id);
        
        if ($reminder->status !== Reminder::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'This reminder is not in pending status and cannot be sent.',
            ], 400);
        }
        
        $success = $this->emailService->sendReminder($reminder);
        
        return response()->json([
            'success' => $success,
            'reminder' => $reminder->fresh(),
        ]);
    }
    
    /**
     * Process all pending reminders that are due to be sent
     */
    public function processPendingReminders(): JsonResponse
    {
        $results = $this->emailService->sendPendingReminders();
        
        return response()->json(['results' => $results]);
    }
    
    /**
     * Cancel a pending reminder
     */
    public function cancel($id, Request $request): JsonResponse
    {
        $reminder = Reminder::findOrFail($id);
        
        if ($reminder->status !== Reminder::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending reminders can be cancelled.',
            ], 400);
        }
        
        $reason = $request->input('reason', 'Manually cancelled');
        $success = $reminder->cancel($reason);
        
        return response()->json([
            'success' => $success,
            'reminder' => $reminder->fresh(),
        ]);
    }
    
    /**
     * Reschedule a pending reminder
     */
    public function reschedule($id, Request $request): JsonResponse
    {
        $reminder = Reminder::findOrFail($id);
        
        if ($reminder->status !== Reminder::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending reminders can be rescheduled.',
            ], 400);
        }
        
        $request->validate([
            'scheduled_date' => 'required|date|after:now',
        ]);
        
        $reminder->scheduled_date = $request->scheduled_date;
        $success = $reminder->save();
        
        return response()->json([
            'success' => $success,
            'reminder' => $reminder,
        ]);
    }
} 