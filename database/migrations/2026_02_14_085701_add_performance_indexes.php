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
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index('event');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index('suspended_at');
            $table->index('created_at');
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->index('read_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex(['event']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['suspended_at']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropIndex(['read_at']);
            $table->dropIndex(['created_at']);
        });
    }
};
