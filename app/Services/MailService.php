<?php

namespace App\Services;

use App\Mail\OrderVerificationMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class MailService
{
    /**
     * @param Order $order
     * @return true
     */
    public function sendVerificationEmail(Order $order): bool
    {
        $token = Str::random(32);
        $order->update([
            'email_verification_token' => $token,
            'email_verified_at' => null
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'orders.verify-email',
            now()->addHours(24),
            ['token' => $token]
        );

        Log::debug('Verification URL generated', [
            'url' => $verificationUrl,
            'order_id' => $order->id,
            'valid' => URL::hasValidSignature(Request::create($verificationUrl))
        ]);

        Mail::to($order->email)->send(new OrderVerificationMail($verificationUrl, $order));
        return true;
    }
}
