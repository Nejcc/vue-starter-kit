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
        Schema::table('users', function (Blueprint $table): void {
            $table->json('cookie_consent_preferences')->nullable();
            $table->timestamp('cookie_consent_given_at')->nullable();
            $table->boolean('data_processing_consent')->default(false);
            $table->timestamp('data_processing_consent_given_at')->nullable();
            $table->string('gdpr_ip_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'cookie_consent_preferences',
                'cookie_consent_given_at',
                'data_processing_consent',
                'data_processing_consent_given_at',
                'gdpr_ip_address',
            ]);
        });
    }
};
