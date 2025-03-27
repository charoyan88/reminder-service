<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserPreference\UpdateLanguageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    /**
     * Get the current user's preferences
     */
    public function getPreferences(): JsonResponse
    {
        $user = Auth::user();
        
        return response()->json([
            'language' => $user->getPreferredLanguage(),
            'available_languages' => config('app.available_locales', ['en']),
        ]);
    }
    
    /**
     * Update the user's language preference
     */
    public function updateLanguage(UpdateLanguageRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        $user->language = $validatedData['language'];
        $user->save();
        
        return response()->json([
            'message' => 'Language preference updated successfully',
            'language' => $user->language,
        ]);
    }
    
    /**
     * Get a list of available languages
     */
    public function getAvailableLanguages(): JsonResponse
    {
        return response()->json([
            'available_languages' => config('app.available_locales', ['en']),
        ]);
    }
} 