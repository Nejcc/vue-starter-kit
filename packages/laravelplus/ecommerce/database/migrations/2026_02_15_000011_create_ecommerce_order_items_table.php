<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('ecommerce_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('ecommerce_products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('ecommerce_product_variants')->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('total');
            $table->json('options')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_order_items');
    }
};
