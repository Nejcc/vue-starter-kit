<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_product_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('ecommerce_products')->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->unsignedBigInteger('price')->nullable();
            $table->unsignedBigInteger('compare_at_price')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->json('options');
            $table->decimal('weight', 10, 3)->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_product_variants');
    }
};
