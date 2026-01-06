<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->string('field_type')->default('input')->after('value')->comment('input, checkbox, or multioptions (comma-separated)');
            $table->text('options')->nullable()->after('field_type')->comment('For multioptions: comma-separated list of options');
            $table->string('label')->nullable()->after('options');
            $table->text('description')->nullable()->after('label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn(['field_type', 'options', 'label', 'description']);
        });
    }
};
