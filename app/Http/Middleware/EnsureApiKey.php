<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();
        if (! $plain || ! str_starts_with($plain, 'tvsk_')) {
            return response()->json(['message' => 'Missing or invalid API token.'], 401);
        }

        $key = \App\Models\ApiKey::where('token_hash', hash('sha256', $plain))->first();
        if (! $key || ! $key->isUsable() || $key->user->status !== 'active') {
            return response()->json(['message' => 'API token is invalid, expired or revoked.'], 401);
        }

        $key->forceFill(['last_used_at' => now()])->save();
        $request->setUserResolver(fn () => $key->user);
        $request->attributes->set('api_key', $key);

        return $next($request);
    }
}
