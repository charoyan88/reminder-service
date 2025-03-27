<?php

use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ReminderConfigurationController;
use App\Http\Controllers\API\ReminderController;
use App\Http\Controllers\API\EmailTemplateController;
use App\Http\Controllers\API\UserPreferenceController;
use App\Http\Controllers\API\ReminderIntervalConfigController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Simple status endpoint to check if the API is running
Route::get('/status', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'reminder-service',
        'timestamp' => now()->toIso8601String()
    ]);
});

// User authentication
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Order management routes
Route::post('/orders', [OrderController::class, 'store']);
Route::put('/orders/{id}', [OrderController::class, 'update']);
Route::get('/orders/expiring', [OrderController::class, 'getExpiringOrders']);
Route::get('/orders/expired', [OrderController::class, 'getExpiredOrders']);

// Reminder configuration routes
Route::get('/reminder-configurations', [ReminderConfigurationController::class, 'index']);
Route::post('/reminder-configurations', [ReminderConfigurationController::class, 'store']);
Route::put('/reminder-configurations/{id}', [ReminderConfigurationController::class, 'update']);
Route::delete('/reminder-configurations/{id}', [ReminderConfigurationController::class, 'destroy']);
Route::post('/reminder-configurations/{id}/status', [ReminderConfigurationController::class, 'setStatus']);

// Reminder routes
Route::get('/orders/{orderId}/reminders', [ReminderController::class, 'getOrderReminders']);
Route::get('/reminders/{id}', [ReminderController::class, 'show']);
Route::post('/reminders/{id}/send', [ReminderController::class, 'send']);
Route::post('/reminders/process', [ReminderController::class, 'processPendingReminders']);
Route::post('/reminders/{id}/cancel', [ReminderController::class, 'cancel']);
Route::post('/reminders/{id}/reschedule', [ReminderController::class, 'reschedule']);

// Email template routes
Route::get('/email-templates', [EmailTemplateController::class, 'index']);
Route::post('/email-templates', [EmailTemplateController::class, 'store']);
Route::put('/email-templates/{id}', [EmailTemplateController::class, 'update']);
Route::delete('/email-templates/{id}', [EmailTemplateController::class, 'destroy']);
Route::get('/email-templates/{id}/preview', [EmailTemplateController::class, 'preview']);

// User preference routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/preferences', [UserPreferenceController::class, 'getPreferences']);
    Route::put('/user/preferences/language', [UserPreferenceController::class, 'updateLanguage']);
});
Route::get('/languages', [UserPreferenceController::class, 'getAvailableLanguages']);

// Reminder interval configuration routes
Route::get('/reminder-intervals', [ReminderIntervalConfigController::class, 'index']);
Route::post('/reminder-intervals', [ReminderIntervalConfigController::class, 'store']);
Route::put('/reminder-intervals/{id}', [ReminderIntervalConfigController::class, 'update']);
Route::delete('/reminder-intervals/{id}', [ReminderIntervalConfigController::class, 'destroy']);
Route::post('/reminder-intervals/{id}/toggle-status', [ReminderIntervalConfigController::class, 'toggleStatus']); 