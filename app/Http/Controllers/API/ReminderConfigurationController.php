<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ReminderConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReminderConfigurationController extends Controller
{
    /**
     * Get all reminder configurations for a specific order type
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_type_id' => 'required|exists:order_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $configurations = ReminderConfiguration::where('order_type_id', $request->order_type_id)
            ->get();

        return response()->json(['configurations' => $configurations]);
    }

    /**
     * Store a new reminder configuration
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_type_id' => 'required|exists:order_types,id',
            'reminder_type' => 'required|in:pre_expiration,post_expiration',
            'interval_value' => 'required|integer|min:1',
            'interval_unit' => 'required|in:day,week,month',
            'is_active' => 'boolean',
            'email_template' => 'nullable|string',
            'email_subject' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $configuration = ReminderConfiguration::create($request->all());

        return response()->json(['configuration' => $configuration], 201);
    }

    /**
     * Update an existing reminder configuration
     */
    public function update(Request $request, $id): JsonResponse
    {
        $configuration = ReminderConfiguration::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reminder_type' => 'in:pre_expiration,post_expiration',
            'interval_value' => 'integer|min:1',
            'interval_unit' => 'in:day,week,month',
            'is_active' => 'boolean',
            'email_template' => 'nullable|string',
            'email_subject' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $configuration->update($request->all());

        return response()->json(['configuration' => $configuration]);
    }

    /**
     * Delete a reminder configuration
     */
    public function destroy($id): JsonResponse
    {
        $configuration = ReminderConfiguration::findOrFail($id);
        $configuration->delete();

        return response()->json(null, 204);
    }

    /**
     * Enable or disable a reminder configuration
     */
    public function setStatus(Request $request, $id): JsonResponse
    {
        $configuration = ReminderConfiguration::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $configuration->is_active = $request->is_active;
        $configuration->save();

        return response()->json(['configuration' => $configuration]);
    }
} 