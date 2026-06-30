<div>
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --sms-bg: #0a0e1a; --sms-card: #111827; --sms-border: #1f2937;
        --sms-accent: #6366f1; --sms-green: #10b981; --sms-red: #ef4444;
        --sms-yellow: #f59e0b; --sms-blue: #3b82f6; --sms-text: #e5e7eb; --sms-muted: #6b7280;
    }
    .sms-panel * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
    .sms-panel { background: var(--sms-bg); min-height: 100vh; padding: 2rem; color: var(--sms-text); }
    .sms-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--sms-border); }
    .sms-header-title { display: flex; align-items: center; gap: 1rem; }
    .sms-logo { width: 48px; height: 48px; background: linear-gradient(135deg, var(--sms-accent), #8b5cf6); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
    .sms-title { font-size: 1.5rem; font-weight: 800; color: #fff; margin: 0; }
    .sms-subtitle { font-size: 0.75rem; color: var(--sms-muted); margin: 0; }
    .secret-badge { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: var(--sms-red); padding: 4px 12px; border-radius: 100px; font-size: 0.7rem; font-weight: 600; letter-spacing: 1px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .stat-card { background: var(--sms-card); border: 1px solid var(--sms-border); border-radius: 16px; padding: 1.25rem; position: relative; overflow: hidden; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-2px); border-color: var(--sms-accent); }
    .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 3px; border-radius: 16px 16px 0 0; }
    .stat-card.accent::before { background: linear-gradient(90deg, var(--sms-accent), #8b5cf6); }
    .stat-card.green::before { background: linear-gradient(90deg, var(--sms-green), #34d399); }
    .stat-card.red::before { background: linear-gradient(90deg, var(--sms-red), #f87171); }
    .stat-card.blue::before { background: linear-gradient(90deg, var(--sms-blue), #60a5fa); }
    .stat-card.purple::before { background: linear-gradient(90deg, #a855f7, #c084fc); }
    .stat-icon { font-size: 1.4rem; margin-bottom: 0.5rem; }
    .stat-value { font-size: 1.75rem; font-weight: 800; color: #fff; line-height: 1; }
    .stat-label { font-size: 0.7rem; color: var(--sms-muted); margin-top: 0.4rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .panel { background: var(--sms-card); border: 1px solid var(--sms-border); border-radius: 16px; overflow: hidden; margin-bottom: 1.5rem; }
    .panel-header { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.5rem; border-bottom: 1px solid var(--sms-border); background: rgba(255,255,255,0.02); }
    .panel-header h5 { font-size: 0.9rem; font-weight: 700; color: #fff; margin: 0; }
    .panel-body { padding: 1.5rem; }
    .sms-table { width: 100%; border-collapse: collapse; }
    .sms-table th { padding: 0.65rem 0.85rem; font-size: 0.7rem; font-weight: 600; color: var(--sms-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--sms-border); text-align: left; }
    .sms-table td { padding: 0.75rem 0.85rem; font-size: 0.82rem; border-bottom: 1px solid rgba(31,41,55,0.5); vertical-align: middle; }
    .sms-table tr:last-child td { border-bottom: none; }
    .sms-table tr:hover td { background: rgba(99,102,241,0.04); }
    .badge-sms { display: inline-block; padding: 3px 9px; border-radius: 100px; font-size: 0.7rem; font-weight: 600; }
    .badge-good { background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); }
    .badge-low { background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); }
    .badge-zero { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
    .badge-sent { background: rgba(16,185,129,0.15); color: #10b981; }
    .badge-failed { background: rgba(239,68,68,0.15); color: #ef4444; }
    .badge-double { background: rgba(239,68,68,0.2); color: #f87171; border: 1px solid rgba(239,68,68,0.4); }
    .sms-input { background: var(--sms-bg); border: 1px solid var(--sms-border); color: var(--sms-text); border-radius: 10px; padding: 0.55rem 0.85rem; font-size: 0.85rem; width: 100%; outline: none; transition: border-color 0.2s; }
    .sms-input:focus { border-color: var(--sms-accent); }
    .sms-input::placeholder { color: var(--sms-muted); }
    .sms-label { display: block; font-size: 0.72rem; font-weight: 600; color: var(--sms-muted); margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.4px; }
    .btn-sms { padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.8rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; }
    .btn-sms-primary { background: var(--sms-accent); color: #fff; }
    .btn-sms-primary:hover { background: #4f46e5; }
    .btn-sms-green { background: var(--sms-green); color: #fff; }
    .btn-sms-green:hover { background: #059669; }
    .btn-sms-ghost { background: rgba(255,255,255,0.05); color: var(--sms-text); border: 1px solid var(--sms-border); }
    .btn-sms-ghost:hover { background: rgba(255,255,255,0.1); }
    .toggle-wrap { display: flex; align-items: center; gap: 0.75rem; }
    .sms-toggle { position: relative; width: 44px; height: 24px; cursor: pointer; }
    .sms-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
    .sms-slider { position: absolute; inset: 0; background: var(--sms-border); border-radius: 100px; transition: 0.2s; }
    .sms-slider::before { content: ''; position: absolute; left: 3px; top: 3px; width: 18px; height: 18px; background: #fff; border-radius: 50%; transition: 0.2s; }
    .sms-toggle input:checked + .sms-slider { background: var(--sms-green); }
    .sms-toggle input:checked + .sms-slider::before { transform: translateX(20px); }
    .sms-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; }
    .sms-modal { background: var(--sms-card); border: 1px solid var(--sms-border); border-radius: 20px; padding: 2rem; width: 100%; max-width: 440px; box-shadow: 0 25px 60px rgba(0,0,0,0.5); animation: smsSlideIn 0.2s ease; }
    @keyframes smsSlideIn { from { opacity:0; transform: scale(0.95) translateY(-10px); } to { opacity:1; transform: scale(1) translateY(0); } }
    .sms-modal-title { font-size: 1.1rem; font-weight: 700; color: #fff; margin: 0 0 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
    .month-selector { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .month-chip { padding: 4px 12px; border-radius: 100px; font-size: 0.72rem; font-weight: 600; cursor: pointer; border: 1px solid var(--sms-border); color: var(--sms-muted); background: transparent; transition: all 0.15s; }
    .month-chip.active, .month-chip:hover { background: var(--sms-accent); color: #fff; border-color: var(--sms-accent); }
</style>
@endpush

<div class="sms-panel">

    {{-- Header --}}
    <div class="sms-header">
        <div class="sms-header-title">
            <div class="sms-logo">📱</div>
            <div>
                <p class="sms-title">SMS Master Control</p>
                <p class="sms-subtitle">Billing • Balance • Logs • Configuration</p>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <span class="secret-badge">🔒 SECRET PANEL</span>
            <button class="btn-sms btn-sms-ghost" wire:click="openTestSmsModal">
                <i class="bi bi-send me-1"></i> Test SMS
            </button>
        </div>
    </div>

    {{-- Month Filter --}}
    <div class="month-selector">
        @foreach($this->availableMonths as $m)
            <button wire:click="$set('filterMonth', '{{ $m }}')"
                class="month-chip {{ $filterMonth === $m ? 'active' : '' }}">
                {{ \Carbon\Carbon::parse($m . '-01')->format('M Y') }}
            </button>
        @endforeach
    </div>

    {{-- System Balance Card --}}
    @php
        $sysBal = $stats['system_balance'] ?? 0;
    @endphp
    <div style="background:linear-gradient(135deg,var(--sms-accent),#8b5cf6);border-radius:20px;padding:2rem;margin-bottom:2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
            <div style="font-size:0.8rem;opacity:0.8;text-transform:uppercase;letter-spacing:1px;color:#fff;">GIZMO — System SMS Balance</div>
            <div style="font-size:2.5rem;font-weight:800;color:#fff;margin-top:4px;">Rs. {{ number_format($sysBal, 2) }}</div>
            <div style="font-size:0.75rem;opacity:0.7;margin-top:4px;">
                @if($sysBal <= 0)
                    <span class="badge-sms badge-zero">No Balance</span>
                @elseif($sysBal < $lowBalanceThreshold)
                    <span class="badge-sms badge-low">Low Balance</span>
                @else
                    <span class="badge-sms badge-good">Active</span>
                @endif
            </div>
        </div>
        <button wire:click="openTopupModal" class="btn-sms" style="background:#fff;color:#6366f1;font-size:1rem;padding:0.75rem 1.5rem;font-weight:700;">
            + Topup GIZMO
        </button>
    </div>

    {{-- Stats Grid --}}
    <div class="stats-grid">
        <div class="stat-card accent">
            <div class="stat-icon">📨</div>
            <div class="stat-value">{{ number_format($stats['total_sms_all_time']) }}</div>
            <div class="stat-label">Total SMS All Time</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon">📅</div>
            <div class="stat-value">{{ number_format($stats['total_sms_this_month']) }}</div>
            <div class="stat-label">SMS This Month</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon">💰</div>
            <div class="stat-value">Rs.{{ number_format($stats['total_cost_this_month'], 2) }}</div>
            <div class="stat-label">Revenue This Month</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon">💵</div>
            <div class="stat-value">Rs.{{ number_format($stats['total_revenue'], 2) }}</div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon">⚡</div>
            <div class="stat-value">{{ number_format($stats['double_charge_sms']) }}</div>
            <div class="stat-label">Double-Charged SMS</div>
        </div>
    </div>

    {{-- Two-column layout --}}
    <div style="display:grid;grid-template-columns:340px 1fr;gap:1.5rem;align-items:start;">

        {{-- LEFT: Rate Settings --}}
        <div>
            <div class="panel">
                <div class="panel-header">
                    <span style="color:var(--sms-accent);font-size:1.1rem;">⚙️</span>
                    <h5>SMS Rate Settings</h5>
                </div>
                <div class="panel-body" style="display:flex;flex-direction:column;gap:1.25rem;">
                    <div>
                        <label class="sms-label">Rate Per SMS Message (Rs.)</label>
                        <input type="number" step="0.01" min="0" wire:model="smsRate" class="sms-input" placeholder="0.70">
                        <p style="font-size:0.68rem;color:var(--sms-muted);margin-top:4px;">Cost per single SMS (160 chars). Multi-part = parts × rate.</p>
                    </div>
                    <div>
                        <label class="sms-label">Low Balance Alert Threshold (Rs.)</label>
                        <input type="number" step="1" min="0" wire:model="lowBalanceThreshold" class="sms-input" placeholder="50">
                        <p style="font-size:0.68rem;color:var(--sms-muted);margin-top:4px;">Alert SMS sent when balance drops below this.</p>
                    </div>
                    <div>
                        <label class="sms-label">Low Balance Alert Phone Number</label>
                        <input type="text" wire:model="lowBalanceAlertPhone" class="sms-input" placeholder="e.g. 0759037101">
                        <p style="font-size:0.68rem;color:var(--sms-muted);margin-top:4px;">Phone number to receive low balance alert SMS.</p>
                    </div>
                    <div>
                        <label class="sms-label">Double Charge When Balance = 0</label>
                        <div class="toggle-wrap" style="margin-top:6px;">
                            <label class="sms-toggle">
                                <input type="checkbox" wire:model="doubleChargeEnabled">
                                <span class="sms-slider"></span>
                            </label>
                            <span style="font-size:0.8rem;color:{{ $doubleChargeEnabled ? '#10b981' : '#6b7280' }};">
                                {{ $doubleChargeEnabled ? '⚡ ENABLED — cost doubles at zero balance' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                    <button wire:click="saveSettings" class="btn-sms btn-sms-primary" style="width:100%;">
                        <span wire:loading.remove wire:target="saveSettings">💾 Save Settings</span>
                        <span wire:loading wire:target="saveSettings">Saving...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- RIGHT: SMS Logs --}}
        <div class="panel">
            <div class="panel-header" style="justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <span style="color:var(--sms-blue);font-size:1.1rem;">📋</span>
                    <h5>SMS Activity Log</h5>
                </div>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <input type="month" wire:model.live="logFilterMonth" class="sms-input" style="width:160px;padding:0.4rem 0.75rem;">
                    <input type="text" wire:model.live.debounce.300ms="logSearch" class="sms-input" style="width:200px;" placeholder="Search phone...">
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="sms-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Phone</th><th>Type</th><th>Parts</th>
                            <th>Rate</th><th>Cost</th><th>Bal Before → After</th>
                            <th>Status</th><th>Sent At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($smsLogs as $log)
                        <tr>
                            <td style="color:var(--sms-muted);font-size:0.72rem;">{{ $log->id }}</td>
                            <td style="color:var(--sms-muted);">{{ $log->phone }}</td>
                            <td>
                                @php
                                    $tc = ['invoice'=>'rgba(99,102,241,0.15)|#818cf8','alert'=>'rgba(245,158,11,0.15)|#fcd34d','custom'=>'rgba(107,114,128,0.15)|#9ca3af','low_balance'=>'rgba(239,68,68,0.15)|#f87171'];
                                    $c = explode('|', $tc[$log->type] ?? $tc['custom']);
                                @endphp
                                <span class="badge-sms" style="background:{{ $c[0] }};color:{{ $c[1] }};">{{ ucfirst(str_replace('_',' ',$log->type)) }}</span>
                                @if($log->double_charged)<span class="badge-sms badge-double" style="font-size:0.6rem;margin-left:2px;">2×</span>@endif
                            </td>
                            <td style="text-align:center;">{{ $log->sms_parts }}</td>
                            <td>Rs. {{ number_format($log->cost_per_sms, 2) }}</td>
                            <td style="font-weight:600;color:#fcd34d;">Rs. {{ number_format($log->total_cost, 2) }}</td>
                            <td style="font-size:0.75rem;">
                                <span style="color:var(--sms-muted);">{{ number_format($log->balance_before, 2) }}</span>
                                <span style="color:var(--sms-muted);margin:0 4px;">→</span>
                                <span style="color:{{ $log->balance_after < 0 ? '#ef4444' : ($log->balance_after < $lowBalanceThreshold ? '#f59e0b' : '#10b981') }};">{{ number_format($log->balance_after, 2) }}</span>
                            </td>
                            <td><span class="badge-sms {{ $log->status==='sent' ? 'badge-sent' : 'badge-failed' }}">{{ ucfirst($log->status) }}</span></td>
                            <td style="color:var(--sms-muted);font-size:0.72rem;">{{ $log->sent_at?->format('d M Y H:i') ?? $log->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" style="text-align:center;color:var(--sms-muted);padding:2rem;">No SMS logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($smsLogs->hasPages())
            <div style="padding:1rem 1.5rem;border-top:1px solid var(--sms-border);">{{ $smsLogs->links() }}</div>
            @endif
        </div>
    </div>

    {{-- TOPUP MODAL --}}
    @if($showTopupModal)
    <div class="sms-overlay" wire:click.self="closeTopupModal">
        <div class="sms-modal">
            <p class="sms-modal-title">💳 Top Up GIZMO SMS Balance</p>
            <div style="margin-bottom:1.25rem;">
                <label class="sms-label">Amount to Add (Rs.)</label>
                <input type="number" step="1" min="1" wire:model="topupAmount" class="sms-input" placeholder="e.g. 500" autofocus>
                @error('topupAmount')<p style="color:#ef4444;font-size:0.72rem;margin-top:4px;">{{ $message }}</p>@enderror
                <div style="display:flex;gap:0.5rem;margin-top:0.75rem;">
                    @foreach([100, 250, 500, 1000] as $q)
                    <button wire:click="$set('topupAmount', {{ $q }})" class="btn-sms btn-sms-ghost btn-sms-sm" style="flex:1;">Rs. {{ $q }}</button>
                    @endforeach
                </div>
            </div>
            <div style="display:flex;gap:0.75rem;">
                <button wire:click="closeTopupModal" class="btn-sms btn-sms-ghost" style="flex:1;">Cancel</button>
                <button wire:click="doTopup" class="btn-sms btn-sms-green" style="flex:1;">
                    <span wire:loading.remove wire:target="doTopup">✅ Confirm Topup</span>
                    <span wire:loading wire:target="doTopup">Processing...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- TEST SMS MODAL --}}
    @if($showTestModal)
    <div class="sms-overlay" wire:click.self="closeTestSmsModal">
        <div class="sms-modal">
            <p class="sms-modal-title">📱 Send Test SMS</p>
            <div style="margin-bottom:1rem;">
                <label class="sms-label">Phone Number</label>
                <input type="text" wire:model="testPhone" class="sms-input" placeholder="07XXXXXXXX">
                @error('testPhone')<p style="color:#ef4444;font-size:0.72rem;margin-top:4px;">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:1.25rem;">
                <label class="sms-label">Message</label>
                <textarea wire:model="testMessage" class="sms-input" rows="3" style="resize:vertical;" placeholder="Your test message..."></textarea>
                @error('testMessage')<p style="color:#ef4444;font-size:0.72rem;margin-top:4px;">{{ $message }}</p>@enderror
            </div>
            <div style="display:flex;gap:0.75rem;">
                <button wire:click="closeTestSmsModal" class="btn-sms btn-sms-ghost" style="flex:1;">Cancel</button>
                <button wire:click="sendTestSms" class="btn-sms btn-sms-primary" style="flex:1;">
                    <span wire:loading.remove wire:target="sendTestSms">🚀 Send Test</span>
                    <span wire:loading wire:target="sendTestSms">Sending...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
