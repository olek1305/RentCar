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
        $order->update([
            'email_verification_token' => $token,
            'email_verified_at' => null
        ]);

        $verificationUrl = URL::signedRoute(
            'orders.verify-email',
            ['token' => $token]
        );

        Mail::to($order->email)->send(new OrderVerificationMail($verificationUrl, $order));

        return true;
    }
}
