# SMS Admin Panel — Setup Guide

> Secret SMS Master Control panel for managing SMS settings, balance, and logs.
> URL: `/sms-admin?token=YOUR_TOKEN`

---

## File Summary

| # | File Path | Type |
|---|-----------|------|
| 1 | `app/Http/Middleware/SmsTokenMiddleware.php` | New |
| 2 | `app/Livewire/Admin/SmsAdminPanel.php` | New |
| 3 | `resources/views/livewire/admin/sms-admin-panel.blade.php` | New |
| 4 | `app/Services/SmsService.php` | New |
| 5 | `app/Models/SmsLog.php` | New |
| 6 | `database/migrations/2026_06_30_090000_create_sms_logs_table.php` | New |
| 7 | `routes/web.php` | Modify |
| 8 | `app/Http/Kernel.php` | Modify |
| 9 | `config/services.php` | Modify |
| 10 | `.env` | Modify |

---

## Files to Create

### 1. `app/Http/Middleware/SmsTokenMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SmsTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $secretToken = config('services.sms_master_token');
        $token       = $request->query('token');

        if (!$token || !hash_equals($secretToken, $token)) {
            abort(404);
        }

        return $next($request);
    }
}
```

---

### 2. `app/Livewire/Admin/SmsAdminPanel.php`

```php
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

    public float  $smsRate              = 0.70;
    public float  $lowBalanceThreshold  = 50.00;
    public bool   $doubleChargeEnabled  = true;
    public string $senderNumber         = '';
    public string $lowBalanceAlertPhone = '';

    public string $filterMonth          = '';
    public string $filterYear           = '';
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
        $this->filterYear     = now()->format('Y');
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
        $this->lowBalanceAlertPhone = (string) (Setting::where('key', 'sms_low_balance_alert_phone')->value('value') ?? '');
    }

    public function saveSettings(): void
    {
        $this->validate([
            'smsRate'             => 'required|numeric|min:0',
            'lowBalanceThreshold' => 'required|numeric|min:0',
            'senderNumber'        => 'nullable|string|max:20',
            'lowBalanceAlertPhone'=> 'nullable|string|max:20',
        ]);

        Setting::updateOrCreate(['key' => 'sms_rate_per_message'],          ['value' => $this->smsRate,              'date' => now()]);
        Setting::updateOrCreate(['key' => 'sms_low_balance_threshold'],      ['value' => $this->lowBalanceThreshold,  'date' => now()]);
        Setting::updateOrCreate(['key' => 'sms_double_charge_enabled'],      ['value' => $this->doubleChargeEnabled ? '1' : '0', 'date' => now()]);
        Setting::updateOrCreate(['key' => 'sms_sender_number'],             ['value' => $this->senderNumber,         'date' => now()]);
        Setting::updateOrCreate(['key' => 'sms_low_balance_alert_phone'],   ['value' => $this->lowBalanceAlertPhone, 'date' => now()]);

        $this->loadSettings();

        $this->js("Swal.fire('Saved!', 'SMS settings updated. Rate: Rs. " . number_format($this->smsRate, 2) . "', 'success')");
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

    public function updatedFilterYear(): void
    {
        if ($this->filterYear === now()->format('Y')) {
            $this->filterMonth = now()->format('Y-m');
        } else {
            $this->filterMonth = $this->filterYear . '-01';
        }
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
        $year = $this->filterYear ?: now()->format('Y');
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = $year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
        }
        return $months;
    }

    public function getAvailableYearsProperty(): array
    {
        $currentYear = (int) now()->format('Y');
        $years = [];
        for ($y = $currentYear; $y >= $currentYear - 3; $y--) {
            $years[] = (string) $y;
        }
        return $years;
    }

    public function render()
    {
        return view('livewire.admin.sms-admin-panel', [
            'smsLogs' => $this->smsLogs,
        ])->layout('layouts.app');
    }
}
```

---

### 3. `resources/views/livewire/admin/sms-admin-panel.blade.php`

Full Blade view with dark theme UI. See actual file at `resources/views/livewire/admin/sms-admin-panel.blade.php` (329 lines).

**Key sections:**
- Header with logo + "Test SMS" button
- Year + Month filter chips
- System Balance gradient card with "Topup GIZMO" button
- 5-column stats grid (Total SMS All Time, SMS This Month, Revenue This Month, Total Revenue, Double-Charged SMS)
- Two-column layout: Left = SMS Rate Settings panel, Right = SMS Activity Log table
- Topup modal with quick amount buttons (100, 250, 500, 1000)
- Test SMS modal (phone + message)

---

### 4. `app/Services/SmsService.php`

```php
<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function getSystemBalance(): float
    {
        return (float) (Setting::where('key', 'sms_system_balance')->value('value') ?? 0);
    }

    public function setSystemBalance(float $balance): void
    {
        Setting::updateOrCreate(
            ['key' => 'sms_system_balance'],
            ['value' => $balance, 'date' => now()]
        );
    }

    public function topUpSystem(float $amount): float
    {
        $current = $this->getSystemBalance();
        $new     = $current + $amount;
        $this->setSystemBalance($new);

        Log::info("SMS system topup: +{$amount}. New balance: {$new}");
        return $new;
    }

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

    protected function maybeSendLowBalanceAlert(float $balanceAfter, float $threshold): void
    {
        if ($balanceAfter > $threshold) {
            return;
        }

        $alreadyNotified = (bool) Setting::where('key', 'sms_low_balance_alerted')->value('value');
        if ($alreadyNotified) {
            return;
        }

        $alertPhone = (string) (Setting::where('key', 'sms_low_balance_alert_phone')->value('value') ?? '');
        if (empty($alertPhone)) {
            return;
        }

        $balanceFormatted = number_format($balanceAfter, 2);
        $alertMessage     = "GIZMO Alert: Your SMS balance is low (Rs. {$balanceFormatted}). Please top up to continue sending SMS.";

        $this->sendSms($alertPhone, $alertMessage, 'low_balance');

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
        $senderId = Setting::where('key', 'sms_sender_number')->value('value');
        if (empty(trim($senderId ?? ''))) {
            $senderId = config('services.smslenz.sender_id');
        }
        $senderId = trim($senderId);

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
```

---

### 5. `app/Models/SmsLog.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsLog extends Model
{
    use HasFactory;

    protected $table = 'sms_logs';

    protected $fillable = [
        'customer_id',
        'user_id',
        'phone',
        'message',
        'sms_parts',
        'cost_per_sms',
        'total_cost',
        'double_charged',
        'balance_before',
        'balance_after',
        'type',
        'status',
        'api_response',
        'sent_at',
    ];

    protected $casts = [
        'cost_per_sms'    => 'decimal:4',
        'total_cost'      => 'decimal:4',
        'balance_before'  => 'decimal:4',
        'balance_after'   => 'decimal:4',
        'double_charged'  => 'boolean',
        'sent_at'         => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForMonth($query, string $month)
    {
        return $query->where('created_at', 'like', $month . '%');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
```

---

### 6. `database/migrations/2026_06_30_090000_create_sms_logs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('phone', 20);
            $table->text('message');
            $table->integer('sms_parts')->default(1);
            $table->decimal('cost_per_sms', 10, 4)->default(0);
            $table->decimal('total_cost', 10, 4)->default(0);
            $table->boolean('double_charged')->default(false);
            $table->decimal('balance_before', 10, 4)->default(0);
            $table->decimal('balance_after', 10, 4)->default(0);
            $table->enum('type', ['invoice', 'alert', 'custom', 'low_balance'])->default('custom');
            $table->enum('status', ['sent', 'failed', 'skipped'])->default('sent');
            $table->string('api_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
```

---

## Files to Modify

### 7. `routes/web.php` — Add import and route

```php
// In imports section at top:
use App\Livewire\Admin\SmsAdminPanel;

// In route group (after other routes):
Route::middleware('sms_token')->group(function () {
    Route::get('/sms-admin', SmsAdminPanel::class)
        ->name('sms.master.panel');
});
```

---

### 8. `app/Http/Kernel.php` — Register middleware

```php
// In $middlewareAliases array:
'sms_token' => \App\Http\Middleware\SmsTokenMiddleware::class,
```

---

### 9. `config/services.php` — Add config values

```php
// At end of return array:
'smslenz' => [
    'enabled'   => env('SMSLENZ_ENABLED', false),
    'endpoint'  => env('SMSLENZ_ENDPOINT'),
    'user_id'   => env('SMSLENZ_USER_ID'),
    'api_key'   => env('SMSLENZ_API_KEY'),
    'sender_id' => env('SMSLENZ_SENDER_ID'),
],

'sms_master_token' => env('SMS_MASTER_TOKEN', 'changeme-secret-token-2026'),
```

---

### 10. `.env` — Add environment variables

```env
# SMS SETUP
SMSLENZ_ENABLED=true
SMSLENZ_ENDPOINT=https://www.smslenz.lk/api/send-sms
SMSLENZ_USER_ID=your_user_id
SMSLENZ_API_KEY=your_api_key
SMSLENZ_SENDER_ID="GIZMO ELEC"

# SECRET SMS MASTER TOKEN — only YOU know this URL
SMS_MASTER_TOKEN=gizmo-sms-2026-secret-panel
```

---

## UI Pages & Input Fields

### System Balance Card (Top)
- Displays current balance: `Rs. XXX.XX`
- Status badge: Active / Low Balance / No Balance
- Button: **+ Topup GIZMO**

### Stats Grid (5 cards, equal width)
| Card | Value | Icon |
|------|-------|------|
| Total SMS All Time | Count | 📨 |
| SMS This Month | Count | 📅 |
| Revenue This Month | Rs. X.XX | 💰 |
| Total Revenue | Rs. X.XX | 💵 |
| Double-Charged SMS | Count | ⚡ |

### SMS Rate Settings Panel (Left column)
| Input | Type | Description |
|-------|------|-------------|
| Rate Per SMS Message (Rs.) | `number` step=0.01 | Cost per single SMS (160 chars) |
| Low Balance Alert Threshold (Rs.) | `number` step=1 | Alert SMS sent below this |
| Low Balance Alert Phone Number | `text` | Phone to receive alert SMS |
| Double Charge When Balance = 0 | `toggle` | Toggle on/off |
| **Save Settings** | button | Saves all settings |

### SMS Activity Log Table (Right column)
| Column | Description |
|--------|-------------|
| # | Log ID |
| Phone | Recipient phone |
| Type | invoice / alert / custom / low_balance |
| Parts | SMS parts count |
| Rate | Cost per SMS |
| Cost | Total cost |
| Bal Before → After | Balance change |
| Status | Sent / Failed |
| Sent At | Timestamp |

**Filters:** Month picker + Phone search input

### Topup Modal
| Input | Type |
|-------|------|
| Amount (Rs.) | `number` step=1 |
| Quick buttons | 100, 250, 500, 1000 |
| **Confirm Topup** | button |

### Test SMS Modal
| Input | Type |
|-------|------|
| Phone Number | `text` (min 9 chars) |
| Message | `textarea` (min 3 chars) |
| **Send Test** | button |

---

## Database Tables Used

### `settings` (existing)
Stores SMS config as key-value pairs:
| Key | Value |
|-----|-------|
| `sms_system_balance` | Current balance (float) |
| `sms_rate_per_message` | Cost per SMS (float, default 0.70) |
| `sms_low_balance_threshold` | Alert threshold (float, default 50.00) |
| `sms_double_charge_enabled` | `1` or `0` |
| `sms_sender_number` | Sender mask string (nullable, falls back to .env) |
| `sms_low_balance_alert_phone` | Phone for low balance alerts |
| `sms_low_balance_alerted` | `1` if already alerted (prevents duplicates) |

### `sms_logs` (new)
Created by migration #6.

---

## Access URL

```
https://your-domain.com/sms-admin?token=YOUR_SECRET_TOKEN
```

---

## Post-Install Commands

```bash
php artisan migrate
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
```
