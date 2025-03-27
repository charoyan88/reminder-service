<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from user if authenticated
        if (Auth::check()) {
            $locale = Auth::user()->getPreferredLanguage();
        } 
        // Otherwise get from request or header
        else {
            $locale = $request->query('lang', $request->header('Accept-Language', config('app.locale')));
            
            // If Accept-Language header contains multiple languages, get the first one
            if (str_contains($locale, ',')) {
                $locale = explode(',', $locale)[0];
            }
            
            // If locale includes a region (e.g., en-US), just use the language part
            if (str_contains($locale, '-')) {
                $locale = explode('-', $locale)[0];
            }
        }
        
        // Validate that the locale is available
        $availableLocales = array_keys(config('app.available_locales', ['en' => 'English']));
        if (!in_array($locale, $availableLocales)) {
            $locale = config('app.fallback_locale', 'en');
        }
        
        App::setLocale($locale);
        
        return $next($request);
    }
} 