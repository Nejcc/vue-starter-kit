<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_taggables', function (Blueprint $table): void {
            $table->foreignId('tag_id')->constrained('ecommerce_tags')->cascadeOnDelete();
            $table->morphs('taggable');

            $table->unique(['tag_id', 'taggable_type', 'taggable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_taggables');
    }
};
