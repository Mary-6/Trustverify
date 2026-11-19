<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index(Request $request)
    {
        return view('customer.api.index', [
            'keys' => $request->user()->apiKeys()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $plain = 'tvsk_'.Str::random(48);
        $key = $request->user()->apiKeys()->create([
            'name' => $data['name'],
            'token_hash' => hash('sha256', $plain),
            'prefix' => substr($plain, 0, 12),
            'abilities' => ['products:read', 'orders:read', 'wallet:read'],
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return back()->with('api_token', $plain)->with('status', 'API key created. Copy it now; it will not be shown again.');
    }

    public function destroy(Request $request, ApiKey $apiKey)
    {
        abort_unless($apiKey->user_id === $request->user()->id, 403);
        $apiKey->forceFill(['revoked_at' => now()])->save();

        return back()->with('status', 'API key revoked.');
    }
}
