<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->seedCategories();
        $this->seedCountries();
        $this->seedAdmin();
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'SMM Services', 'type' => 'smm_service', 'sort_order' => 1],
            ['name' => 'Virtual Numbers & SMS', 'type' => 'virtual_number', 'sort_order' => 3],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'type' => $category['type'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedCountries(): void
    {
        $countries = [
            ['name' => 'Cameroon', 'code' => 'CM', 'dial_code' => '+237', 'currency' => 'XAF', 'sort_order' => 1],
            ['name' => 'Nigeria', 'code' => 'NG', 'dial_code' => '+234', 'currency' => 'NGN', 'sort_order' => 2],
            ['name' => 'Ghana', 'code' => 'GH', 'dial_code' => '+233', 'currency' => 'GHS', 'sort_order' => 3],
            ['name' => 'Kenya', 'code' => 'KE', 'dial_code' => '+254', 'currency' => 'KES', 'sort_order' => 4],
            ['name' => 'South Africa', 'code' => 'ZA', 'dial_code' => '+27', 'currency' => 'ZAR', 'sort_order' => 5],
            ['name' => 'United States', 'code' => 'US', 'dial_code' => '+1', 'currency' => 'USD', 'sort_order' => 6],
            ['name' => 'United Kingdom', 'code' => 'GB', 'dial_code' => '+44', 'currency' => 'GBP', 'sort_order' => 7],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                $country + ['is_active' => true]
            );
        }
    }

    private function seedAdmin(): void
    {
        $email = env('SEED_ADMIN_EMAIL');
        $password = env('SEED_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('SEED_ADMIN_NAME', 'TrustVerifySMS Admin'),
                'password' => $password,
                'role' => User::ROLE_SUPER_ADMIN,
                'status' => 'active',
            ]
        );
    }
}
