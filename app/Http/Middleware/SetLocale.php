<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = ['en', 'fr'];
        $requested = $request->query('lang');

        if (is_string($requested) && in_array($requested, $available, true)) {
            $request->session()->put('locale', $requested);
        }

        $locale = $request->session()->get('locale', config('app.locale', 'en'));
        app()->setLocale(in_array($locale, $available, true) ? $locale : 'en');

        return $next($request);
    }
}
