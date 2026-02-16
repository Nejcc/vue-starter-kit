<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('short_description')->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('compare_at_price')->nullable();
            $table->unsignedBigInteger('cost_price')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('draft');
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_digital')->default(false);
            $table->boolean('has_variants')->default(false);
            $table->decimal('weight', 10, 3)->nullable();
            $table->json('dimensions')->nullable();
            $table->json('images')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'status']);
            $table->index('is_featured');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_products');
    }
};
