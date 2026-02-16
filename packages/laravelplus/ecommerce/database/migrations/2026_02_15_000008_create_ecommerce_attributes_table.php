<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_attributes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attribute_group_id')->constrained('ecommerce_attribute_groups')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('text');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('values')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_attributes');
    }
};
