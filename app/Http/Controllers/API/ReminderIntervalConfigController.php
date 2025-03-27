<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ReminderIntervalConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReminderIntervalConfigController extends Controller
{
    public function index()
    {
        $intervals = ReminderIntervalConfig::active()
            ->ordered()
            ->get();

        return response()->json([
            'message' => 'Here are all your available reminder intervals',
            'data' => $intervals
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_type' => 'required|in:pre_expiration,post_expiration',
            'days' => 'required|integer|min:1',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'There are some issues with your input',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for duplicate days within the same reminder type
        $exists = ReminderIntervalConfig::where('reminder_type', $request->reminder_type)
            ->where('days', $request->days)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This interval already exists',
                'errors' => ['days' => ['You already have a reminder set for ' . $request->days . ' days ' . ($request->reminder_type == 'pre_expiration' ? 'before' : 'after') . ' expiration.']]
            ], 422);
        }

        $interval = ReminderIntervalConfig::create($request->all());

        return response()->json([
            'message' => 'Successfully created a new reminder interval',
            'data' => $interval
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $interval = ReminderIntervalConfig::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'reminder_type' => 'sometimes|in:pre_expiration,post_expiration',
            'days' => 'sometimes|integer|min:1',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'There are some issues with your update',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for duplicate days within the same reminder type, excluding current record
        if ($request->has('days') || $request->has('reminder_type')) {
            $exists = ReminderIntervalConfig::where('reminder_type', $request->reminder_type ?? $interval->reminder_type)
                ->where('days', $request->days ?? $interval->days)
                ->where('id', '!=', $interval->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'This would create a duplicate interval',
                    'errors' => ['days' => ['You already have a reminder set for ' . ($request->days ?? $interval->days) . ' days ' . (($request->reminder_type ?? $interval->reminder_type) == 'pre_expiration' ? 'before' : 'after') . ' expiration.']]
                ], 422);
            }
        }

        $interval->update($request->all());

        return response()->json([
            'message' => 'Successfully updated the reminder interval',
            'data' => $interval
        ]);
    }

    public function destroy($id)
    {
        $interval = ReminderIntervalConfig::findOrFail($id);
        
        if ($interval->is_default) {
            return response()->json([
                'message' => 'This is a system default interval and cannot be deleted',
                'errors' => ['message' => 'Default intervals are required for the system to work properly. You can disable them instead of deleting them.']
            ], 422);
        }

        $interval->delete();

        return response()->json([
            'message' => 'The reminder interval has been successfully deleted'
        ], 204);
    }

    public function toggleStatus($id)
    {
        $interval = ReminderIntervalConfig::findOrFail($id);
        $newStatus = !$interval->is_active;
        
        $interval->update(['is_active' => $newStatus]);

        return response()->json([
            'message' => $newStatus 
                ? 'The reminder interval has been activated' 
                : 'The reminder interval has been deactivated',
            'data' => $interval
        ]);
    }
} 