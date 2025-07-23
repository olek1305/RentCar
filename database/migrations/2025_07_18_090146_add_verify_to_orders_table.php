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
            $table->string('email_verification_token')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('sms_verification_code')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'email_verification_token',
                'email_verified_at',
                'sms_verification_code',
                'phone_verified_at',
            ]);
        });
    }
};
