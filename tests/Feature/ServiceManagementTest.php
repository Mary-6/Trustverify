<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_and_toggle_services_across_suppliers(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $manual = $this->product('Manual Growth Service', 'manual', 'smm_service');
        $jap = $this->product('JustAnotherPanel Instagram Followers', 'justanotherpanel', 'smm_service');
        $textVerified = $this->product('TextVerified WhatsApp US SMS Verification', 'textverified', 'virtual_number');

        $this->actingAs($admin)
            ->get(route('admin.service-management.index', ['supplier' => 'textverified']))
            ->assertOk()
            ->assertSee('TextVerified WhatsApp US SMS Verification')
            ->assertDontSee('JustAnotherPanel Instagram Followers');

        $this->actingAs($admin)->patch(route('admin.service-management.update'), [
            'action' => 'deactivate',
            'selected_ids' => [$manual->id, $jap->id, $textVerified->id],
        ])->assertRedirect();

        foreach ([$manual, $jap, $textVerified] as $product) {
            $product->refresh();
            $this->assertFalse($product->is_active);
            $this->assertSame('draft', $product->status);
            $this->assertTrue((bool) ($product->meta['admin_disabled'] ?? false));
        }

        $this->actingAs($admin)->patch(route('admin.service-management.update'), [
            'action' => 'activate',
            'selected_ids' => [$manual->id, $jap->id, $textVerified->id],
        ])->assertRedirect();

        foreach ([$manual, $jap, $textVerified] as $product) {
            $product->refresh();
            $this->assertTrue($product->is_active);
            $this->assertSame('active', $product->status);
            $this->assertFalse((bool) ($product->meta['admin_disabled'] ?? true));
        }
    }

    private function product(string $name, string $supplier, string $type): Product
    {
        return Product::create([
            'name' => $name,
            'type' => $type,
            'price' => 100,
            'currency' => 'XAF',
            'supplier' => $supplier,
            'supplier_adapter' => $supplier,
            'supplier_product_id' => strtolower(str_replace(' ', '-', $name)),
            'status' => 'active',
            'is_active' => true,
        ]);
    }
}
