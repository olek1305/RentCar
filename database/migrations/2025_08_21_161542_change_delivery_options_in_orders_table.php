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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_option')->default('pickup')->after('return_time');

            // Usuń stare kolumny boolean
            $table->dropColumn(['extra_delivery_fee', 'airport_delivery']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('extra_delivery_fee')->default(false);
            $table->boolean('airport_delivery')->default(false);

            $table->dropColumn('delivery_option');
        });
    }
};
