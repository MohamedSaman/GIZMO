# SMS Admin Panel — Setup Guide

> Secret SMS Master Control panel for managing SMS settings, balance, and logs.
> URL: `/sms-admin?token=YOUR_TOKEN`

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
    public string $testMessage          = 'Test SMS from system.';

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
        $this->testMessage   = 'Test SMS from system.';
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
```

---

### 3. `resources/views/livewire/admin/sms-admin-panel.blade.php`

```blade
<div class="container-fluid py-4" style="background: #0f172a; min-height: 100vh;">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-white mb-1">
                <i class="bi bi-chat-dots text-warning me-2"></i> SMS Master Control
            </h3>
            <p class="text-secondary mb-0">
                <span class="badge bg-warning text-dark me-2">SECRET PANEL</span>
                Manage SMS settings, balance & logs
            </p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="openTestSmsModal" class="btn btn-outline-info btn-sm">
                <i class="bi bi-send me-1"></i> Test SMS
            </button>
            <button wire:click="openTopupModal" class="btn btn-warning btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Top Up
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75" style="font-size:11px;">SYSTEM BALANCE</p>
                            <h3 class="fw-bold mb-0">Rs. {{ number_format($stats['system_balance'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75" style="font-size:11px;">ALL TIME SMS</p>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_sms_all_time'] ?? 0) }}</h3>
                        </div>
                        <i class="bi bi-envelope-check fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75" style="font-size:11px;">THIS MONTH SMS</p>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_sms_this_month'] ?? 0) }}</h3>
                        </div>
                        <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75" style="font-size:11px;">MONTH COST</p>
                            <h3 class="fw-bold mb-0">Rs. {{ number_format($stats['total_cost_this_month'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="bi bi-currency-exchange fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Settings Card --}}
    <div class="card border-0 shadow-sm mb-4" style="background: #1e293b;">
        <div class="card-header bg-transparent border-bottom border-secondary">
            <h6 class="text-white mb-0"><i class="bi bi-gear me-2"></i>SMS Settings</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-secondary" style="font-size:11px;">Rate per SMS (Rs.)</label>
                    <input type="number" wire:model="smsRate" class="form-control bg-dark text-white border-secondary" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-secondary" style="font-size:11px;">Low Balance Alert (Rs.)</label>
                    <input type="number" wire:model="lowBalanceThreshold" class="form-control bg-dark text-white border-secondary" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-secondary" style="font-size:11px;">Sender Number</label>
                    <input type="text" wire:model="senderNumber" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 94771234567">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-secondary" style="font-size:11px;">Double Charge</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" wire:model="doubleChargeEnabled" id="doubleCharge">
                        <label class="form-check-label text-white" for="doubleCharge" style="font-size:11px;">Enabled</label>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button wire:click="saveSettings" class="btn btn-warning w-100">
                        <i class="bi bi-check-lg"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SMS Logs --}}
    <div class="card border-0 shadow-sm" style="background: #1e293b;">
        <div class="card-header bg-transparent border-bottom border-secondary d-flex justify-content-between align-items-center">
            <h6 class="text-white mb-0"><i class="bi bi-list-ul me-2"></i>SMS Logs</h6>
            <div class="d-flex gap-2">
                <select wire:model="logFilterMonth" class="form-select form-select-sm bg-dark text-white border-secondary" style="width:140px;">
                    <option value="">All Months</option>
                    @foreach($this->availableMonths as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
                <input type="text" wire:model.live.debounce.300ms="logSearch" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Search phone..." style="width:180px;">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr class="text-secondary" style="font-size:11px;">
                            <th>Date</th>
                            <th>Phone</th>
                            <th>Message</th>
                            <th>SMS Parts</th>
                            <th>Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($smsLogs as $log)
                        <tr>
                            <td style="font-size:11px;">{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td style="font-size:11px;">{{ $log->phone }}</td>
                            <td style="font-size:11px; max-width:300px;" class="text-truncate">{{ $log->message }}</td>
                            <td style="font-size:11px;">{{ $log->sms_parts }}</td>
                            <td style="font-size:11px;">Rs. {{ number_format($log->total_cost, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $log->status === 'sent' ? 'success' : 'danger' }}" style="font-size:10px;">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-secondary">No SMS logs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($smsLogs->hasPages())
            <div class="p-3 border-top border-secondary">
                {{ $smsLogs->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Topup Modal --}}
    @if($showTopupModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.7); z-index:1050;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="background:#1e293b;">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title text-white fw-bold">Top Up Balance</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeTopupModal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label text-secondary">Amount (Rs.)</label>
                    <input type="number" wire:model="topupAmount" class="form-control bg-dark text-white border-secondary" step="0.01" placeholder="Enter amount">
                    @error('topupAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="modal-footer border-top border-secondary">
                    <button class="btn btn-secondary" wire:click="closeTopupModal">Cancel</button>
                    <button class="btn btn-warning" wire:click="doTopup">Top Up</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Test SMS Modal --}}
    @if($showTestModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.7); z-index:1050;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="background:#1e293b;">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title text-white fw-bold">Send Test SMS</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeTestSmsModal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Phone Number</label>
                        <input type="text" wire:model="testPhone" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 0771234567">
                        @error('testPhone') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Message</label>
                        <textarea wire:model="testMessage" class="form-control bg-dark text-white border-secondary" rows="3"></textarea>
                        @error('testMessage') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary">
                    <button class="btn btn-secondary" wire:click="closeTestSmsModal">Cancel</button>
                    <button class="btn btn-info" wire:click="sendTestSms">Send Test</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
```

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
    private float $smsRate;
    private float $doubleChargeRate;

    public function __construct()
    {
        $this->smsRate          = (float) (Setting::where('key', 'sms_rate_per_message')->value('value') ?? 2.00);
        $this->doubleChargeRate = $this->smsRate * 2;
    }

    public function getSystemBalance(): float
    {
        return (float) (Setting::where('key', 'sms_system_balance')->value('value') ?? 0);
    }

    public function setSystemBalance(float $balance): void
    {
        Setting::updateOrCreate(['key' => 'sms_system_balance'], ['value' => $balance, 'date' => now()]);
    }

    public function topUpSystem(float $amount): float
    {
        $current    = $this->getSystemBalance();
        $newBalance = $current + $amount;
        $this->setSystemBalance($newBalance);
        return $newBalance;
    }

    public function sendSms(string $phone, string $message, string $type = 'custom'): array
    {
        $balance      = $this->getSystemBalance();
        $smsParts     = $this->calculateSmsParts($message);
        $doubleCharge = (bool) (Setting::where('key', 'sms_double_charge_enabled')->value('value') ?? true);
        $cost         = $doubleCharge ? ($smsParts * $this->doubleChargeRate) : ($smsParts * $this->smsRate);

        if ($balance < $cost) {
            return ['success' => false, 'error' => 'Insufficient SMS balance. Required: Rs. ' . number_format($cost, 2)];
        }

        try {
            $result = $this->callSmsApi($phone, $message);

            $balanceBefore = $balance;
            $newBalance    = $balance - $cost;
            $this->setSystemBalance($newBalance);

            SmsLog::create([
                'phone'          => $phone,
                'message'        => $message,
                'sms_parts'      => $smsParts,
                'cost_per_sms'   => $doubleCharge ? $this->doubleChargeRate : $this->smsRate,
                'total_cost'     => $cost,
                'double_charged' => $doubleCharge,
                'balance_before' => $balanceBefore,
                'balance_after'  => $newBalance,
                'type'           => $type,
                'status'         => $result['success'] ? 'sent' : 'failed',
                'api_response'   => $result['response'] ?? null,
                'sent_at'        => now(),
                'user_id'        => auth()->id(),
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('SMS send failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function calculateSmsParts(string $message): int
    {
        $length = mb_strlen($message);
        if ($length <= 160) return 1;
        if ($length <= 306) return 2;
        return (int) ceil($length / 153);
    }

    private function callSmsApi(string $phone, string $message): array
    {
        $config = config('services.smslenz');

        if (!$config['enabled']) {
            return ['success' => true, 'response' => 'SMS disabled in config', 'debug' => true];
        }

        $response = Http::timeout(15)
            ->post($config['endpoint'], [
                'user_id'   => $config['user_id'],
                'api_key'   => $config['api_key'],
                'sender_id' => $config['sender_id'],
                'mobile'    => $phone,
                'message'   => $message,
            ]);

        if ($response->successful()) {
            return ['success' => true, 'response' => $response->body()];
        }

        return ['success' => false, 'error' => 'API error: ' . $response->body(), 'response' => $response->body()];
    }
}
```

---

### 5. `app/Models/SmsLog.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $table = 'sms_logs';

    protected $fillable = [
        'customer_id', 'phone', 'message', 'sms_parts',
        'cost_per_sms', 'total_cost', 'double_charged',
        'balance_before', 'balance_after', 'type', 'status',
        'api_response', 'sent_at', 'user_id',
    ];

    protected $casts = [
        'double_charged' => 'boolean',
        'sms_parts'      => 'integer',
        'sent_at'        => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForMonth($query, $month)
    {
        return $query->where('created_at', 'like', $month . '%');
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
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone');
            $table->text('message');
            $table->integer('sms_parts')->default(1);
            $table->decimal('cost_per_sms', 8, 2)->default(0);
            $table->decimal('total_cost', 8, 2)->default(0);
            $table->boolean('double_charged')->default(false);
            $table->decimal('balance_before', 12, 2)->default(0);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->enum('type', ['custom', 'notification', 'marketing', 'otp', 'alert'])->default('custom');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('api_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
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
SMS_MASTER_TOKEN=your-secret-token-here

SMSLENZ_ENABLED=true
SMSLENZ_ENDPOINT=https://api.smslenz.lk/send
SMSLENZ_USER_ID=your_user_id
SMSLENZ_API_KEY=your_api_key
SMSLENZ_SENDER_ID=your_sender_id
```

---

## Database Tables Used

### `settings` (existing)
Stores SMS config as key-value pairs:
| Key | Value |
|-----|-------|
| `sms_system_balance` | Current balance (float) |
| `sms_rate_per_message` | Cost per SMS (float) |
| `sms_low_balance_threshold` | Alert threshold (float) |
| `sms_double_charge_enabled` | `1` or `0` |
| `sms_sender_number` | Sender ID string |

### `sms_logs` (new)
Created by migration #6 above.

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
