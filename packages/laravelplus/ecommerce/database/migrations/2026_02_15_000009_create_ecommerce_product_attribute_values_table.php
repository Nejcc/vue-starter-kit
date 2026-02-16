<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_product_attribute_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('ecommerce_products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('ecommerce_attributes')->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_product_attribute_values');
    }
};
