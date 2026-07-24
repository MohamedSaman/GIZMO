<div class="container-fluid py-4" style="background:#f1f5f9; min-height:100vh;">

    {{-- ══════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════ --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1e293b;">
                <i class="bi bi-cash-coin text-primary me-2"></i>Staff Salary
            </h4>
            <small class="text-muted">Manage monthly salary payments for all staff</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="text-muted small fw-semibold me-1 mb-0">Month:</label>
            <input type="month" wire:model.live="selectedMonth"
                   class="form-control form-control-sm"
                   style="width:160px; border-radius:8px;">
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         MAIN TABLE CARD
    ══════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead style="background:#1e293b; color:white;">
                    <tr>
                        <th class="px-4 py-3 fw-semibold" style="font-size:.82rem; letter-spacing:.5px;">#</th>
                        <th class="px-3 py-3 fw-semibold" style="font-size:.82rem; letter-spacing:.5px;">STAFF</th>
                        <th class="px-3 py-3 fw-semibold text-end" style="font-size:.82rem; letter-spacing:.5px;">BASIC SALARY</th>
                        <th class="px-3 py-3 fw-semibold text-end" style="font-size:.82rem; letter-spacing:.5px;">PAID</th>
                        <th class="px-3 py-3 fw-semibold text-end" style="font-size:.82rem; letter-spacing:.5px;">BALANCE</th>
                        <th class="px-3 py-3 fw-semibold text-center" style="font-size:.82rem; letter-spacing:.5px;">STATUS</th>
                        <th class="px-4 py-3 fw-semibold text-center" style="font-size:.82rem; letter-spacing:.5px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffSalaryList as $i => $row)
                    <tr wire:key="staff-{{ $row['user_id'] }}"
                        style="border-bottom:1px solid #f1f5f9; transition:background .15s;">
                        <td class="px-4 py-3 text-muted small">{{ $i + 1 }}</td>

                        <td class="px-3 py-3">
                            <div class="fw-bold text-dark" style="font-size:.95rem;">{{ $row['name'] }}</div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $row['staff_type'] }}</div>
                        </td>

                        <td class="px-3 py-3 text-end fw-semibold text-dark">
                            Rs. {{ number_format($row['basic_salary'], 2) }}
                        </td>

                        <td class="px-3 py-3 text-end fw-bold" style="color:#16a34a;">
                            @if($row['paid_amount'] > 0)
                                Rs. {{ number_format($row['paid_amount'], 2) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-end fw-bold"
                            style="color:{{ $row['balance'] <= 0 ? '#16a34a' : '#dc2626' }};">
                            Rs. {{ number_format(max($row['balance'], 0), 2) }}
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($row['status'] === 'paid')
                                <span class="badge rounded-pill px-3 py-2"
                                      style="background:#dcfce7; color:#16a34a; font-size:.75rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Paid
                                </span>
                            @elseif($row['status'] === 'partial')
                                <span class="badge rounded-pill px-3 py-2"
                                      style="background:#fef3c7; color:#d97706; font-size:.75rem;">
                                    <i class="bi bi-hourglass-split me-1"></i>Partial
                                </span>
                            @elseif($row['status'] === 'pending')
                                <span class="badge rounded-pill px-3 py-2"
                                      style="background:#fee2e2; color:#dc2626; font-size:.75rem;">
                                    <i class="bi bi-clock me-1"></i>Pending
                                </span>
                            @else
                                <span class="badge rounded-pill px-3 py-2"
                                      style="background:#f1f5f9; color:#64748b; font-size:.75rem;">
                                    <i class="bi bi-dash-circle me-1"></i>Not Set
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            <div class="d-flex justify-content-center gap-2">
                                @if($row['status'] !== 'paid')
                                <button class="btn btn-sm fw-semibold px-3"
                                        wire:click="openPayModal({{ $row['user_id'] }})"
                                        wire:loading.attr="disabled"
                                        style="background:#2563eb; color:white; border-radius:8px; font-size:.8rem;">
                                    <i class="bi bi-cash me-1"></i>Pay
                                </button>
                                @else
                                <button class="btn btn-sm fw-semibold px-3 disabled"
                                        style="background:#dcfce7; color:#16a34a; border-radius:8px; font-size:.8rem; cursor:default;">
                                    <i class="bi bi-check-lg me-1"></i>Paid
                                </button>
                                @endif

                                @if($row['has_salary'])
                                <button class="btn btn-sm fw-semibold px-3"
                                        wire:click="openViewModal({{ $row['user_id'] }})"
                                        style="background:#f8fafc; color:#475569; border:1px solid #e2e8f0; border-radius:8px; font-size:.8rem;">
                                    <i class="bi bi-eye me-1"></i>View
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-2 d-block mb-2 opacity-30"></i>
                            No staff members found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         PAY MODAL
    ══════════════════════════════════════════ --}}
    @if($showPayModal)
    <div class="modal fade show d-block" tabindex="-1"
         style="background:rgba(0,0,0,.45);">
        <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">

                {{-- Header --}}
                <div class="px-4 pt-4 pb-3 d-flex justify-content-between align-items-start"
                     style="background:#1e293b;">
                    <div>
                        <h5 class="fw-bold text-white mb-1">
                            <i class="bi bi-cash-coin me-2"></i>Pay Salary
                        </h5>
                        <div class="text-white opacity-75 small">
                            {{ $payingSalaryInfo['name'] ?? '' }} &bull;
                            {{ $payingSalaryInfo['month'] ?? '' }}
                        </div>
                    </div>
                    <button class="btn-close btn-close-white mt-1"
                            wire:click="closePayModal"></button>
                </div>

                {{-- Summary Strip --}}
                <div class="px-4 py-3 d-flex gap-3"
                     style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <div class="flex-fill text-center">
                        <div class="text-muted" style="font-size:.73rem; text-transform:uppercase; letter-spacing:.5px;">Basic Salary</div>
                        <div class="fw-bold text-dark" style="font-size:1rem;">
                            Rs. {{ number_format($payingSalaryInfo['net_salary'] ?? 0, 2) }}
                        </div>
                    </div>
                    <div class="vr opacity-25"></div>
                    <div class="flex-fill text-center">
                        <div class="text-muted" style="font-size:.73rem; text-transform:uppercase; letter-spacing:.5px;">Already Paid</div>
                        <div class="fw-bold" style="color:#16a34a; font-size:1rem;">
                            Rs. {{ number_format($payingSalaryInfo['paid_amount'] ?? 0, 2) }}
                        </div>
                    </div>
                    <div class="vr opacity-25"></div>
                    <div class="flex-fill text-center">
                        <div class="text-muted" style="font-size:.73rem; text-transform:uppercase; letter-spacing:.5px;">Balance</div>
                        <div class="fw-bold" style="color:#dc2626; font-size:1rem;">
                            Rs. {{ number_format($payingSalaryInfo['balance'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <div class="modal-body px-4 py-4">
                    <form wire:submit.prevent="savePayment">

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">
                                Amount to Pay <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold"
                                      style="background:#eff6ff; border-color:#bfdbfe; color:#1d4ed8;">Rs.</span>
                                <input type="number" step="0.01" min="0.01"
                                       wire:model="payAmount"
                                       class="form-control form-control-lg fw-bold"
                                       style="border-color:#bfdbfe; font-size:1.15rem;"
                                       placeholder="0.00"
                                       autofocus>
                            </div>
                            @error('payAmount')
                                <div class="text-danger mt-1 small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">
                                Payment Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" wire:model="payDate"
                                   class="form-control"
                                   style="border-radius:10px;">
                            @error('payDate')
                                <div class="text-danger mt-1 small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark small">
                                Note <span class="text-muted">(optional)</span>
                            </label>
                            <input type="text" wire:model="payNote"
                                   class="form-control"
                                   style="border-radius:10px;"
                                   placeholder="e.g. Advance, Bonus...">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light flex-fill"
                                    wire:click="closePayModal"
                                    style="border-radius:10px;">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="btn flex-fill fw-bold"
                                    style="background:#2563eb; color:white; border-radius:10px;"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="savePayment">
                                    <i class="bi bi-check-lg me-1"></i>Save Payment
                                </span>
                                <span wire:loading wire:target="savePayment">
                                    <span class="spinner-border spinner-border-sm me-1"></span>Saving...
                                </span>
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════
         VIEW HISTORY MODAL
    ══════════════════════════════════════════ --}}
    @if($showViewModal)
    <div class="modal fade show d-block" tabindex="-1"
         style="background:rgba(0,0,0,.45);">
        <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">

                {{-- Header --}}
                <div class="px-4 pt-4 pb-3 d-flex justify-content-between align-items-start"
                     style="background:#1e293b;">
                    <div>
                        <h5 class="fw-bold text-white mb-1">
                            <i class="bi bi-clock-history me-2"></i>Payment History
                        </h5>
                        <div class="text-white opacity-75 small">
                            {{ $viewingSalaryInfo['name'] ?? '' }} &bull;
                            {{ $viewingSalaryInfo['month'] ?? '' }}
                        </div>
                    </div>
                    <button class="btn-close btn-close-white mt-1"
                            wire:click="closeViewModal"></button>
                </div>

                {{-- Summary Strip --}}
                <div class="px-4 py-3 d-flex gap-3"
                     style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <div class="flex-fill text-center">
                        <div class="text-muted" style="font-size:.73rem; text-transform:uppercase; letter-spacing:.5px;">Basic Salary</div>
                        <div class="fw-bold text-dark">
                            Rs. {{ number_format($viewingSalaryInfo['net_salary'] ?? 0, 2) }}
                        </div>
                    </div>
                    <div class="vr opacity-25"></div>
                    <div class="flex-fill text-center">
                        <div class="text-muted" style="font-size:.73rem; text-transform:uppercase; letter-spacing:.5px;">Total Paid</div>
                        <div class="fw-bold" style="color:#16a34a;">
                            Rs. {{ number_format($viewingSalaryInfo['paid_amount'] ?? 0, 2) }}
                        </div>
                    </div>
                    <div class="vr opacity-25"></div>
                    <div class="flex-fill text-center">
                        <div class="text-muted" style="font-size:.73rem; text-transform:uppercase; letter-spacing:.5px;">Balance</div>
                        <div class="fw-bold" style="color:{{ ($viewingSalaryInfo['balance'] ?? 0) <= 0 ? '#16a34a' : '#dc2626' }};">
                            Rs. {{ number_format(max($viewingSalaryInfo['balance'] ?? 0, 0), 2) }}
                        </div>
                    </div>
                </div>

                {{-- Payment Records --}}
                <div class="modal-body px-4 py-3">
                    @if(count($viewingPayments) > 0)
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th class="py-2 text-muted small fw-semibold"
                                    style="font-size:.75rem; text-transform:uppercase; letter-spacing:.4px;">Date</th>
                                <th class="py-2 text-muted small fw-semibold"
                                    style="font-size:.75rem; text-transform:uppercase; letter-spacing:.4px;">Note</th>
                                <th class="py-2 text-muted small fw-semibold text-end"
                                    style="font-size:.75rem; text-transform:uppercase; letter-spacing:.4px;">Amount</th>
                                <th class="py-2 text-muted small fw-semibold text-center"
                                    style="font-size:.75rem; text-transform:uppercase; letter-spacing:.4px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($viewingPayments as $pay)
                            <tr wire:key="pay-{{ $pay['id'] }}" style="border-bottom:1px solid #f1f5f9;">
                                <td class="py-2 text-muted small">{{ $pay['date'] }}</td>
                                <td class="py-2 small text-dark">{{ $pay['note'] }}</td>
                                <td class="py-2 text-end fw-bold" style="color:#2563eb;">
                                    Rs. {{ number_format($pay['amount'], 2) }}
                                </td>
                                <td class="py-2 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm py-0 px-2"
                                                wire:click="openEditPayModal({{ $pay['id'] }})"
                                                title="Edit"
                                                style="background:#eff6ff; color:#2563eb; border-radius:6px; font-size:.78rem;">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm py-0 px-2"
                                                wire:click="confirmDeletePayment({{ $pay['id'] }})"
                                                title="Delete"
                                                style="background:#fee2e2; color:#dc2626; border-radius:6px; font-size:.78rem;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#eff6ff; border-top:2px solid #bfdbfe;">
                                <td colspan="3" class="py-2 fw-bold text-dark small">Total Paid</td>
                                <td class="py-2 text-end fw-bold" style="color:#16a34a;">
                                    Rs. {{ number_format($viewingSalaryInfo['paid_amount'] ?? 0, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2 opacity-30"></i>
                        No payments recorded yet.
                    </div>
                    @endif
                </div>

                <div class="px-4 pb-4">
                    <button class="btn btn-light w-100 fw-semibold"
                            wire:click="closeViewModal"
                            style="border-radius:10px;">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════
         EDIT PAYMENT MODAL
    ══════════════════════════════════════════ --}}
    @if($showEditPayModal)
    <div class="modal fade show d-block" tabindex="-1"
         style="background:rgba(0,0,0,.55); z-index:1060;">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">

                <div class="px-4 pt-4 pb-3 d-flex justify-content-between align-items-center"
                     style="background:#1e293b;">
                    <h5 class="fw-bold text-white mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Edit Payment
                    </h5>
                    <button class="btn-close btn-close-white" wire:click="closeEditPayModal"></button>
                </div>

                <div class="modal-body px-4 py-4">
                    <form wire:submit.prevent="updatePayment">

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold"
                                      style="background:#eff6ff; border-color:#bfdbfe; color:#1d4ed8;">Rs.</span>
                                <input type="number" step="0.01" min="0.01"
                                       wire:model="editPayAmount"
                                       class="form-control form-control-lg fw-bold"
                                       style="border-color:#bfdbfe;"
                                       placeholder="0.00">
                            </div>
                            @error('editPayAmount')
                                <div class="text-danger mt-1 small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">
                                Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" wire:model="editPayDate"
                                   class="form-control" style="border-radius:10px;">
                            @error('editPayDate')
                                <div class="text-danger mt-1 small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark small">
                                Note <span class="text-muted">(optional)</span>
                            </label>
                            <input type="text" wire:model="editPayNote"
                                   class="form-control" style="border-radius:10px;"
                                   placeholder="e.g. Advance, Bonus...">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light flex-fill"
                                    wire:click="closeEditPayModal"
                                    style="border-radius:10px;">Cancel</button>
                            <button type="submit"
                                    class="btn flex-fill fw-bold"
                                    style="background:#2563eb; color:white; border-radius:10px;"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updatePayment">
                                    <i class="bi bi-check-lg me-1"></i>Update
                                </span>
                                <span wire:loading wire:target="updatePayment">
                                    <span class="spinner-border spinner-border-sm me-1"></span>Saving...
                                </span>
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════
         DELETE CONFIRM MODAL
    ══════════════════════════════════════════ --}}
    @if($showDeleteConfirm)
    <div class="modal fade show d-block" tabindex="-1"
         style="background:rgba(0,0,0,.55); z-index:1060;">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">

                <div class="modal-body p-4 text-center">
                    <div class="mb-3" style="font-size:2.5rem; color:#dc2626;">&#9888;</div>
                    <h5 class="fw-bold text-dark mb-2">Delete Payment?</h5>
                    <p class="text-muted small mb-4">
                        This payment record will be permanently deleted.<br>This action cannot be undone.
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button class="btn btn-light px-4 fw-semibold"
                                wire:click="cancelDeletePayment"
                                style="border-radius:10px;">Cancel</button>
                        <button class="btn px-4 fw-bold"
                                wire:click="deletePayment"
                                style="background:#dc2626; color:white; border-radius:10px;"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="deletePayment">
                                <i class="bi bi-trash me-1"></i>Yes, Delete
                            </span>
                            <span wire:loading wire:target="deletePayment">
                                <span class="spinner-border spinner-border-sm"></span>
                            </span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif

</div>
