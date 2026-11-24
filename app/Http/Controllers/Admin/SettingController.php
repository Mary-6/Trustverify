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
                ],
            ],
        ]);
    }

    public function update(Request $request, SettingsService $settings)
    {
        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:120'],
            'support_email' => ['nullable', 'email', 'max:120'],
            'primary_currency' => ['nullable', 'string', 'size:3'],
            'global_markup_percent' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'global_markup_fixed' => ['nullable', 'numeric', 'min:0'],
            'payment_methods' => ['nullable', 'string', 'max:500'],
            'referral_reward_amount' => ['nullable', 'numeric', 'min:0'],
            'manual_fulfillment_enabled' => ['nullable', 'boolean'],
            'faussms_enabled' => ['nullable', 'boolean'],
        ]);

        $settings->set('general.site_name', $data['site_name'] ?? config('app.name'), 'string', 'general');
        $settings->set('general.support_email', $data['support_email'] ?? null, 'string', 'general');
        $settings->set('general.primary_currency', strtoupper($data['primary_currency'] ?? 'XAF'), 'string', 'general');
        $settings->set('pricing.global_markup_percent', $data['global_markup_percent'] ?? 30, 'integer', 'pricing');
        $settings->set('pricing.global_markup_fixed', $data['global_markup_fixed'] ?? 0, 'integer', 'pricing');

        $methods = array_values(array_filter(array_map('trim', explode(',', (string) ($data['payment_methods'] ?? '')))));
        $settings->set('payments.methods', $methods, 'json', 'payments');
        $settings->set('referrals.reward_amount', $data['referral_reward_amount'] ?? 0, 'integer', 'referrals');
        $settings->set('supplier.manual_fulfillment_enabled', $request->boolean('manual_fulfillment_enabled'), 'boolean', 'supplier');
        $settings->set('supplier.faussms_enabled', $request->boolean('faussms_enabled'), 'boolean', 'supplier');

        return back()->with('status', 'Settings saved.');
    }
}
