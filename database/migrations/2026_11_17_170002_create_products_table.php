<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->index();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->longText('terms')->nullable();
            $table->decimal('price', 14, 2);
            $table->char('currency', 3)->default('XAF');
            $table->decimal('supplier_cost', 14, 2)->nullable();
            $table->string('supplier', 50)->nullable();
            $table->string('supplier_product_id', 191)->nullable();
            $table->string('supplier_adapter', 100)->nullable();
            $table->json('meta')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->unsignedInteger('stock_quantity')->nullable();
            $table->unsignedInteger('min_order')->default(1);
            $table->unsignedInteger('max_order')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active', 'status']);
            $table->index(['supplier', 'supplier_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
