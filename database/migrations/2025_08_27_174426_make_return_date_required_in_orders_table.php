<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('orders')->whereNull('return_date')->update([
            'return_date' => now()->format('Y-m-d')
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->date('return_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->date('return_date')->nullable()->change();
        });
    }
};
