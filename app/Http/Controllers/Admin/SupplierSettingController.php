<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SupplierSettingController extends Controller
{
    public function edit(string $provider, SettingsService $settings)
    {
        abort_unless($provider === 'herosms', 404);

        return view('admin.suppliers.settings', [
            'provider' => $provider,
            'apiKeyConfigured' => $settings->get('supplier.herosms.api_key') !== null || ! empty(config('herosms.api_key')),
            'values' => [
                'enabled' => (bool) $settings->get('supplier.herosms_enabled', true),
                'base_url' => (string) $settings->get('supplier.herosms.base_url', config('herosms.base_url')),
                'timeout' => (int) $settings->get('supplier.herosms.timeout', config('herosms.timeout', 30)),
            ],
        ]);
    }

    public function update(Request $request, string $provider, SettingsService $settings)
    {
        abort_unless($provider === 'herosms', 404);

        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'base_url' => ['required', 'url', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'clear_api_key' => ['nullable', 'boolean'],
            'timeout' => ['required', 'integer', 'min:5', 'max:120'],
        ]);

        $settings->set('supplier.herosms_enabled', $request->boolean('enabled'), 'boolean', 'supplier');
        $settings->set('supplier.herosms.base_url', $data['base_url'], 'string', 'supplier');
        $settings->set('supplier.herosms.timeout', (int) $data['timeout'], 'integer', 'supplier');

        if ($request->boolean('clear_api_key')) {
            Setting::where('key', 'supplier.herosms.api_key')->delete();
        } elseif (! empty($data['api_key'])) {
            $settings->set('supplier.herosms.api_key', $data['api_key'], 'string', 'supplier', true);
        }

        return back()->with('status', 'HeroSMS settings saved.');
    }
}
