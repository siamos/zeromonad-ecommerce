<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocaleFromSession
{
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = $request->session()->get('locale', 'en');
        app()->setLocale(in_array($locale, ['en', 'el']) ? $locale : 'en');

        return $next($request);
    }
}
