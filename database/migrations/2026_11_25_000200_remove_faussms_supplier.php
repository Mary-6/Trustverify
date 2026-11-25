<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('key', 'supplier.faussms_enabled')->delete();

        DB::table('supplier_logs')
            ->where('supplier', 'faussms')
            ->orWhere('adapter', 'faussms')
            ->delete();

        DB::table('products')
            ->where('supplier', 'faussms')
            ->orWhere('supplier_adapter', 'faussms')
            ->update([
                'status' => 'archived',
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // FAUSSMS removal is intentionally not reversible.
    }
};
