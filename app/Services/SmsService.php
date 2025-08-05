<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * @param string $phone
     * @return JsonResponse
     */
    public function sendVerificationCode(string $phone): JsonResponse
    {
        $code = rand(10000, 99999);

        // Store the code in cache for verification
        Cache::put('sms_verification_'.$phone, $code, now()->addMinutes(15));

        /* TODO use any host like Twilio, vonage etc. */
        Log::info("Verification code for phone {$phone}: {$code}");

        return response()->json([
            'success' => true,
            'message' => __('Verification code sent'),
            'code' => $code // Only for development, remove in production
        ]);
    }

    /**
     * @param string $phone
     * @param string $code
     * @return bool
     */
    public function verifyCode(string $phone, string $code): bool
    {
        $storedCode = Cache::get('sms_verification_'.$phone);
        return $storedCode && $storedCode == $code;
    }
}
