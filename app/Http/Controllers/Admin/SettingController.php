<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(SettingsService $settings)
    {
        return view('admin.settings.index', [
            'settings' => [
                'general' => [
                    'site_name' => $settings->get('general.site_name', config('app.name')),
                    'support_email' => $settings->get('general.support_email'),
                    'contact_phone' => $settings->get('general.contact_phone'),
                    'contact_address' => $settings->get('general.contact_address'),
                    'primary_currency' => $settings->get('general.primary_currency', 'XAF'),
                ],
                'pricing' => [
                    'global_markup_percent' => $settings->get('pricing.global_markup_percent', 30),
                    'global_markup_fixed' => $settings->get('pricing.global_markup_fixed', 0),
                ],
                'payments' => [
                    'methods' => implode(', ', (array) $settings->get('payments.methods', ['mtn_momo'])),
                ],
                'referrals' => [
                    'reward_amount' => $settings->get('referrals.reward_amount', 0),
                ],
                'supplier' => [
                    'manual_fulfillment_enabled' => $settings->get('supplier.manual_fulfillment_enabled', true),
                    'faussms_enabled' => $settings->get('supplier.faussms_enabled', false),
                    'textverified_enabled' => $settings->get('supplier.textverified_enabled', true),
                    'herosms_enabled' => $settings->get('supplier.herosms_enabled', true),
                    'justanotherpanel_enabled' => $settings->get('supplier.justanotherpanel_enabled', true),
                ],
            ],
        ]);
    }

    public function update(Request $request, SettingsService $settings)
    {
        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:120'],
            'support_email' => ['nullable', 'email', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:80'],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'primary_currency' => ['nullable', 'string', 'size:3'],
            'global_markup_percent' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'global_markup_fixed' => ['nullable', 'numeric', 'min:0'],
            'payment_methods' => ['nullable', 'string', 'max:500'],
            'referral_reward_amount' => ['nullable', 'numeric', 'min:0'],
            'manual_fulfillment_enabled' => ['nullable', 'boolean'],
            'faussms_enabled' => ['nullable', 'boolean'],
            'textverified_enabled' => ['nullable', 'boolean'],
            'herosms_enabled' => ['nullable', 'boolean'],
            'justanotherpanel_enabled' => ['nullable', 'boolean'],
        ]);

        $settings->set('general.site_name', $data['site_name'] ?? config('app.name'), 'string', 'general');
        $settings->set('general.support_email', $data['support_email'] ?? null, 'string', 'general');
        $settings->set('general.contact_phone', $data['contact_phone'] ?? null, 'string', 'general');
        $settings->set('general.contact_address', $data['contact_address'] ?? null, 'string', 'general');
        $settings->set('general.primary_currency', strtoupper($data['primary_currency'] ?? 'XAF'), 'string', 'general');
        $settings->set('pricing.global_markup_percent', $data['global_markup_percent'] ?? 30, 'integer', 'pricing');
        $settings->set('pricing.global_markup_fixed', $data['global_markup_fixed'] ?? 0, 'integer', 'pricing');

        $methods = array_values(array_filter(array_map('trim', explode(',', (string) ($data['payment_methods'] ?? '')))));
        $settings->set('payments.methods', $methods, 'json', 'payments');
        $settings->set('referrals.reward_amount', $data['referral_reward_amount'] ?? 0, 'integer', 'referrals');
        $settings->set('supplier.manual_fulfillment_enabled', $request->boolean('manual_fulfillment_enabled'), 'boolean', 'supplier');
        $settings->set('supplier.faussms_enabled', $request->boolean('faussms_enabled'), 'boolean', 'supplier');
        $settings->set('supplier.textverified_enabled', $request->boolean('textverified_enabled'), 'boolean', 'supplier');
        $settings->set('supplier.herosms_enabled', $request->boolean('herosms_enabled'), 'boolean', 'supplier');
        $settings->set('supplier.justanotherpanel_enabled', $request->boolean('justanotherpanel_enabled'), 'boolean', 'supplier');

        return back()->with('status', 'Settings saved.');
    }
}
