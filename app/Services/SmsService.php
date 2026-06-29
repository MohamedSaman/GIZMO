<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS using the SMSLENZ API.
     *
     * @param string $to
     * @param string $message
     * @return array|bool
     */
    public function sendSms($to, $message)
    {
        if (!config('services.smslenz.enabled')) {
            Log::info("SMS disabled. Would have sent to {$to}: {$message}");
            return true; // Simulate success
        }

        $endpoint = config('services.smslenz.endpoint');
        $userId = config('services.smslenz.user_id');
        $apiKey = config('services.smslenz.api_key');
        $senderId = config('services.smslenz.sender_id');

        try {
            $response = Http::asForm()->acceptJson()->post($endpoint, [
                'user_id' => $userId,
                'api_key' => $apiKey,
                'sender_id' => $senderId,
                'contact' => $to,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent successfully to {$to}. Response: " . $response->body());
                return [
                    'success' => true,
                    'response' => $response->json(),
                ];
            } else {
                Log::error("Failed to send SMS to {$to}. HTTP Status: {$response->status()}, Response: " . $response->body());
                return [
                    'success' => false,
                    'error' => "HTTP {$response->status()}",
                    'response' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            Log::error("Exception while sending SMS to {$to}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
