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
        'message' => 'The Reminder Service is up and running!'
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
Route::prefix('reminders')->group(function () {
    Route::post('/schedule', [ReminderController::class, 'schedule']);
    Route::post('/cancel', [ReminderController::class, 'cancel']);
    Route::post('/status', [ReminderController::class, 'updateStatus']);
});

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
Route::prefix('reminder-intervals')->group(function () {
    Route::get('/', [ReminderIntervalConfigController::class, 'index']);
    Route::post('/', [ReminderIntervalConfigController::class, 'store']);
    Route::put('/{id}', [ReminderIntervalConfigController::class, 'update']);
    Route::delete('/{id}', [ReminderIntervalConfigController::class, 'destroy']);
    Route::post('/{id}/toggle-status', [ReminderIntervalConfigController::class, 'toggleStatus']);
}); 