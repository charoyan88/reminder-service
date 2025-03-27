<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Simple status endpoint to check if the API is running
Route::get('/api/status', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'reminder-service',
        'timestamp' => now()->toIso8601String()
    ]);
});

// Route to display all registered routes
Route::get('/routes', function() {
    $routes = collect(Route::getRoutes())->map(function ($route) {
        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
        ];
    });
    
    return response()->json($routes);
});
