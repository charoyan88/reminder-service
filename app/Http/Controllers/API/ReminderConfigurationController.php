<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReminderConfiguration\IndexRequest;
use App\Http\Requests\ReminderConfiguration\StoreRequest;
use App\Http\Requests\ReminderConfiguration\UpdateRequest;
use App\Http\Requests\ReminderConfiguration\StatusRequest;
use App\Models\ReminderConfiguration;
use Illuminate\Http\JsonResponse;

class ReminderConfigurationController extends Controller
{
    /**
     * Get all reminder configurations for a specific order type
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        
        $configurations = ReminderConfiguration::where('order_type_id', $validatedData['order_type_id'])
            ->get();

        return response()->json(['configurations' => $configurations]);
    }

    /**
     * Store a new reminder configuration
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $configuration = ReminderConfiguration::create($validatedData);

        return response()->json(['configuration' => $configuration], 201);
    }

    /**
     * Update an existing reminder configuration
     */
    public function update(UpdateRequest $request, $id): JsonResponse
    {
        $configuration = ReminderConfiguration::findOrFail($id);
        $validatedData = $request->validated();
        $configuration->update($validatedData);

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
    public function setStatus(StatusRequest $request, $id): JsonResponse
    {
        $configuration = ReminderConfiguration::findOrFail($id);
        $validatedData = $request->validated();
        
        $configuration->is_active = $validatedData['is_active'];
        $configuration->save();

        return response()->json(['configuration' => $configuration]);
    }
} 