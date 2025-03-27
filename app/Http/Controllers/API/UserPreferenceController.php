<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
    public function updateLanguage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required|string|size:2',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user = Auth::user();
        $user->language = $request->language;
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