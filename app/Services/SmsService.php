<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Get the system SMS balance.
     */
    public function getSystemBalance(): float
    {
        return (float) (Setting::where('key', 'sms_system_balance')->value('value') ?? 0);
    }

    /**
     * Set the system SMS balance.
     */
    public function setSystemBalance(float $balance): void
    {
        Setting::updateOrCreate(
            ['key' => 'sms_system_balance'],
            ['value' => $balance, 'date' => now()]
        );
    }

    /**
     * Topup the system SMS balance.
     */
    public function topUpSystem(float $amount): float
    {
        $current = $this->getSystemBalance();
        $new     = $current + $amount;
        $this->setSystemBalance($new);

        Log::info("SMS system topup: +{$amount}. New balance: {$new}");
        return $new;
    }

    /**
     * Send an SMS using the SMSLENZ API.
     * Deducts from system balance. Double-charge when balance <= 0.
     */
    public function sendSms(string $to, string $message, string $type = 'custom'): array
    {
        $ratePerSms      = (float) (Setting::where('key', 'sms_rate_per_message')->value('value') ?? 2.00);
        $lowBalanceLimit = (float) (Setting::where('key', 'sms_low_balance_threshold')->value('value') ?? 50.00);
        $doubleCharge    = (bool)  (Setting::where('key', 'sms_double_charge_enabled')->value('value') ?? true);

        $smsParts      = $this->calculateSmsParts($message);
        $balanceBefore = $this->getSystemBalance();

        $isDoubleCharged = false;
        if ($doubleCharge && $balanceBefore <= 0 && $type !== 'low_balance') {
            $costPerSms      = $ratePerSms * 2;
            $isDoubleCharged = true;
        } else {
            $costPerSms = $ratePerSms;
        }
        $totalCost = $costPerSms * $smsParts;

        $apiResult = $this->callSmsApi($to, $message);
        $status    = $apiResult['success'] ? 'sent' : 'failed';

        $balanceAfter = $balanceBefore;
        if ($status === 'sent' && $type !== 'low_balance') {
            $balanceAfter = max(-9999, $balanceBefore - $totalCost);
            $this->setSystemBalance($balanceAfter);
        }

        SmsLog::create([
            'phone'          => $to,
            'message'        => $message,
            'sms_parts'      => $smsParts,
            'cost_per_sms'   => $costPerSms,
            'total_cost'     => $status === 'sent' ? $totalCost : 0,
            'double_charged' => $isDoubleCharged,
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceAfter,
            'type'           => $type,
            'status'         => $status,
            'api_response'   => json_encode($apiResult['response'] ?? null),
            'sent_at'        => now(),
        ]);

        if ($status === 'sent' && $type !== 'low_balance') {
            $this->maybeSendLowBalanceAlert($balanceAfter, $lowBalanceLimit);
        }

        return $apiResult + [
            'cost'           => $totalCost,
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceAfter,
            'double_charged' => $isDoubleCharged,
        ];
    }

    /**
     * Send low-balance alert to a phone (free, not charged).
     */
    protected function maybeSendLowBalanceAlert(float $balanceAfter, float $threshold): void
    {
        if ($balanceAfter > $threshold) {
            return;
        }

        $alreadyNotified = (bool) Setting::where('key', 'sms_low_balance_alerted')->value('value');
        if ($alreadyNotified) {
            return;
        }

        $balanceFormatted = number_format($balanceAfter, 2);
        $alertMessage     = "GIZMO Alert: Your SMS balance is low (Rs. {$balanceFormatted}). Please top up to continue sending SMS.";

        $this->sendSms('0770000000', $alertMessage, 'low_balance');

        Setting::updateOrCreate(
            ['key' => 'sms_low_balance_alerted'],
            ['value' => '1', 'date' => now()]
        );
    }

    protected function calculateSmsParts(string $message): int
    {
        $len = mb_strlen($message);
        if ($len <= 160) {
            return 1;
        }
        return (int) ceil($len / 153);
    }

    protected function callSmsApi(string $to, string $message): array
    {
        if (!config('services.smslenz.enabled')) {
            Log::info("SMS disabled. Would have sent to {$to}: {$message}");
            return ['success' => true, 'response' => ['simulated' => true]];
        }

        $endpoint = config('services.smslenz.endpoint');
        $userId   = config('services.smslenz.user_id');
        $apiKey   = config('services.smslenz.api_key');
        $senderId = Setting::where('key', 'sms_sender_number')->value('value') ?? config('services.smslenz.sender_id');

        try {
            $response = Http::asForm()->acceptJson()->post($endpoint, [
                'user_id'   => $userId,
                'api_key'   => $apiKey,
                'sender_id' => $senderId,
                'contact'   => $to,
                'message'   => $message,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent to {$to}. Response: " . $response->body());
                return ['success' => true, 'response' => $response->json()];
            }

            Log::error("SMS failed to {$to}. HTTP {$response->status()}: " . $response->body());
            return [
                'success'  => false,
                'error'    => "HTTP {$response->status()}",
                'response' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error("SMS exception to {$to}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
