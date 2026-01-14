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
            $table->string('final_payment_session_id')->nullable()->after('payment_session_id');
            $table->timestamp('final_payment_link_sent_at')->nullable()->after('final_payment_session_id');
            $table->decimal('final_payment_amount', 10, 2)->nullable()->after('final_payment_link_sent_at');
            $table->timestamp('final_paid_at')->nullable()->after('final_payment_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'final_payment_session_id',
                'final_payment_link_sent_at',
                'final_payment_amount',
                'final_paid_at',
            ]);
        });
    }
};
