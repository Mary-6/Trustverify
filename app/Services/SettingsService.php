<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        $value = $setting->value;
        if ($setting->is_encrypted && $value !== null) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Throwable) {
                return $default;
            }
        }

        return match ($setting->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode((string) $value, true),
            default => $value,
        };
    }

    public function set(string $key, mixed $value, string $type = 'string', ?string $group = null, bool $encrypted = false): Setting
    {
        $stored = $value;
        if ($encrypted && $value !== null) {
            $stored = Crypt::encryptString((string) $value);
        } elseif (is_array($value) || is_object($value)) {
            $stored = json_encode($value);
            $type = 'json';
        } elseif (is_bool($value)) {
            $stored = $value ? '1' : '0';
            $type = 'boolean';
        }

        return Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'type' => $type, 'group' => $group, 'is_encrypted' => $encrypted]
        );
    }
}
