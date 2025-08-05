<?php

namespace App\Services;

use App\Mail\OrderVerificationMail;
use App\Models\Order;
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
        $hashedToken = hash('sha256', $token);

        $order->update([
            'email_verification_token' => $hashedToken,
            'email_verification_sent_at' => now(),
            'email_verified_at' => null
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'orders.verify-email',
            now()->addHours(24),
            ['orderId' => $order->id, 'token' => $token]
        );

        Mail::to($order->email)->send(new OrderVerificationMail($verificationUrl, $order));
        return true;
    }
}
