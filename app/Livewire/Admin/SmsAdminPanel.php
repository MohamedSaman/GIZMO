<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\Models\SmsLog;
use App\Services\SmsService;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('SMS Master Control')]
class SmsAdminPanel extends Component
{
    use WithPagination;

    public float  $smsRate              = 2.00;
    public float  $lowBalanceThreshold  = 50.00;
    public bool   $doubleChargeEnabled  = true;
    public string $senderNumber         = '';

    public string $filterMonth          = '';
    public string $logFilterMonth       = '';
    public string $logSearch            = '';

    public bool   $showTopupModal       = false;
    public float  $topupAmount          = 0;

    public bool   $showTestModal        = false;
    public string $testPhone            = '';
    public string $testMessage          = 'Test SMS from GIZMO system.';

    public array  $stats                = [];

    public function mount(): void
    {
        $this->filterMonth    = now()->format('Y-m');
        $this->logFilterMonth = now()->format('Y-m');
        $this->loadSettings();
        $this->loadStats();
    }

    public function loadSettings(): void
    {
        $this->smsRate             = (float) (Setting::where('key', 'sms_rate_per_message')->value('value') ?? 2.00);
        $this->lowBalanceThreshold = (float) (Setting::where('key', 'sms_low_balance_threshold')->value('value') ?? 50.00);
        $this->doubleChargeEnabled = (bool)  (Setting::where('key', 'sms_double_charge_enabled')->value('value') ?? true);
        $this->senderNumber        = (string) (Setting::where('key', 'sms_sender_number')->value('value') ?? '');
    }

    public function saveSettings(): void
    {
        $this->validate([
            'smsRate'             => 'required|numeric|min:0',
            'lowBalanceThreshold' => 'required|numeric|min:0',
            'senderNumber'        => 'required|string|max:20',
        ]);

        Setting::updateOrCreate(['key' => 'sms_rate_per_message'],      ['value' => $this->smsRate,             'date' => now()]);
        Setting::updateOrCreate(['key' => 'sms_low_balance_threshold'],  ['value' => $this->lowBalanceThreshold, 'date' => now()]);
        Setting::updateOrCreate(['key' => 'sms_double_charge_enabled'],  ['value' => $this->doubleChargeEnabled ? '1' : '0', 'date' => now()]);
        Setting::updateOrCreate(['key' => 'sms_sender_number'],          ['value' => $this->senderNumber,        'date' => now()]);

        $this->js("Swal.fire('Saved!', 'SMS settings updated successfully.', 'success')");
    }

    public function loadStats(): void
    {
        $smsService = app(SmsService::class);
        $monthQuery = SmsLog::where('created_at', 'like', $this->filterMonth . '%');

        $this->stats = [
            'system_balance'       => $smsService->getSystemBalance(),
            'total_sms_all_time'   => SmsLog::where('status', 'sent')->sum('sms_parts'),
            'total_sms_this_month' => (clone $monthQuery)->where('status', 'sent')->sum('sms_parts'),
            'total_cost_this_month'=> (clone $monthQuery)->where('status', 'sent')->sum('total_cost'),
            'total_revenue'        => SmsLog::where('status', 'sent')->sum('total_cost'),
            'double_charge_sms'    => (clone $monthQuery)->where('double_charged', true)->count(),
        ];
    }

    public function updatedFilterMonth(): void
    {
        $this->loadStats();
    }

    public function openTopupModal(): void
    {
        $this->topupAmount    = 0;
        $this->showTopupModal = true;
    }

    public function closeTopupModal(): void
    {
        $this->showTopupModal = false;
        $this->topupAmount    = 0;
        $this->resetErrorBag();
    }

    public function doTopup(): void
    {
        $this->validate(['topupAmount' => 'required|numeric|min:0.01']);

        try {
            $smsService = app(SmsService::class);
            $newBalance = $smsService->topUpSystem($this->topupAmount);

            $this->closeTopupModal();
            $this->loadStats();
            $this->js("Swal.fire('Topped Up!', 'Rs. " . number_format($this->topupAmount, 2) . " added. New balance: Rs. " . number_format($newBalance, 2) . "', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Topup failed: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    public function deductBalance(float $amount): void
    {
        try {
            $smsService = app(SmsService::class);
            $current    = $smsService->getSystemBalance();
            $newBalance = max(-9999, $current - $amount);
            $smsService->setSystemBalance($newBalance);
            $this->loadStats();
            $this->js("Swal.fire('Done!', 'Balance deducted.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Failed to deduct balance.', 'error')");
        }
    }

    public function openTestSmsModal(): void
    {
        $this->showTestModal = true;
    }

    public function closeTestSmsModal(): void
    {
        $this->showTestModal = false;
        $this->testPhone     = '';
        $this->testMessage   = 'Test SMS from GIZMO system.';
        $this->resetErrorBag();
    }

    public function sendTestSms(): void
    {
        $this->validate([
            'testPhone'   => 'required|string|min:9',
            'testMessage' => 'required|string|min:3',
        ]);

        try {
            $smsService = app(SmsService::class);
            $result     = $smsService->sendSms($this->testPhone, $this->testMessage, 'custom');

            if ($result['success']) {
                $this->closeTestSmsModal();
                $this->js("Swal.fire('Sent!', 'Test SMS sent successfully.', 'success')");
            } else {
                $this->js("Swal.fire('Failed!', 'SMS send failed: " . addslashes($result['error'] ?? 'Unknown') . "', 'error')");
            }
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', '" . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    public function getSmsLogsProperty()
    {
        $q = SmsLog::query()
            ->when($this->logFilterMonth, fn($q) => $q->where('created_at', 'like', $this->logFilterMonth . '%'))
            ->when($this->logSearch, fn($q) => $q->where('phone', 'like', '%' . $this->logSearch . '%'))
            ->orderByDesc('created_at');

        return $q->paginate(20);
    }

    public function getAvailableMonthsProperty(): array
    {
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }
        return $months;
    }

    public function render()
    {
        return view('livewire.admin.sms-admin-panel', [
            'smsLogs' => $this->smsLogs,
        ])->layout('layouts.app');
    }
}
