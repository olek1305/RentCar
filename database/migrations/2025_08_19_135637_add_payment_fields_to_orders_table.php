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
            $table->string('payment_session_id')->nullable()->after('status');
            $table->timestamp('payment_link_sent_at')->nullable()->after('payment_session_id');
            $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_link_sent_at');
            $table->string('payment_currency', 3)->nullable()->after('payment_amount');
            $table->timestamp('paid_at')->nullable()->after('payment_currency');
            $table->timestamp('returned_at')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_session_id',
                'payment_link_sent_at',
                'payment_amount',
                'payment_currency',
                'paid_at',
                'returned_at',
            ]);
        });
    }
};
