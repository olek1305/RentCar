<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->index('type');
            $table->index('fuel_type');
            $table->index('transmission');
            $table->index('hidden');
        });

        Schema::table('currency_settings', function (Blueprint $table) {
            $table->unique('currency_code');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['fuel_type']);
            $table->dropIndex(['transmission']);
            $table->dropIndex(['hidden']);
        });

        Schema::table('currency_settings', function (Blueprint $table) {
            $table->dropUnique(['currency_code']);
            $table->dropIndex(['is_default']);
        });
    }
};
