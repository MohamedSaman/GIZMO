<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-wallet2 text-primary me-2"></i> Daily Expenses
            </h3>
            <p class="text-muted mb-0">Add and track your daily shop expenses</p>
        </div>
        <div class="d-flex align-items-center">
            <div class="me-2">
                <input type="date" class="form-control form-control-sm" wire:model.live="filter_date" aria-label="Filter by date">
            </div>
            <button class="btn btn-primary" wire:click="openAddModal">
                <i class="bi bi-plus-lg me-1"></i> Add Expense
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <div class="card-body text-center py-3 text-white">
                    <h4 class="fw-bold mb-0">Rs. {{ number_format($todayExpenses, 2) }}</h4>
                    <small class="opacity-75">Today's Expenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #161b97 0%, #12167d 100%);">
                <div class="card-body text-center py-3 text-white">
                    <h4 class="fw-bold mb-0">Rs. {{ number_format($monthExpenses, 2) }}</h4>
                    <small class="opacity-75">This Month Total</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Expenses List Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-journal-text text-primary me-2"></i> Expense History
                    </h5>
                    <p class="text-muted small mb-0">All expenses you have recorded</p>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- Search --}}
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search expenses..." wire:model.live="search">
                    </div>
                </div>
            </div>

            {{-- Expenses Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('d M, Y') }}</td>
                                <td>
                                    <span class="fw-medium">{{ $expense->expense_type }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ Str::limit($expense->description, 50) ?: '-' }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger">Rs. {{ number_format($expense->amount, 2) }}</span>
                                </td>
                                <td>
                                    @if($expense->status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock me-1"></i> Pending
                                        </span>
                                    @elseif($expense->status === 'approved')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i> Approved
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i> Rejected
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $expense->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No expenses found. Click "Add Expense" to get started.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- Add Expense Modal --}}
    @if($showAddModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <!-- Header: Dark Navy -->
                <div class="modal-header border-0 py-3" style="background: linear-gradient(135deg, #161b97 0%, #12167d 100%); color: white;">
                    <h5 class="modal-title fw-bold d-flex align-items-center">
                        <i class="bi bi-plus-circle-fill me-2 fs-5"></i>
                        Add Daily Expense
                    </h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" wire:click="closeAddModal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <form wire:submit.prevent="addExpense">

                        {{-- Salary Deduction Section (MOVED TO TOP) --}}
                        <div class="salary-deduction-section rounded-3 p-3 mb-4" style="background-color: #f8fafc; border: 1px solid #f1f5f9;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Salary Deduction</h6>
                                    <p class="text-muted small mb-0">Deduct this amount from salary</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="salaryDeductionToggle"
                                        wire:model.live="is_salary_deduction" style="width: 3em; height: 1.5em; cursor: pointer;">
                                </div>
                            </div>

                            @if($is_salary_deduction)
                            <div class="staff-selection-area mt-2"
                                x-data="{}"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0">

                                <label class="form-label fw-bold text-muted small text-uppercase tracking-wider mb-2">Reduce from which employee?</label>
                                <select class="form-select" wire:model="deduction_staff_id" style="border-radius: 10px; border: 1px solid #e2e8f0;">
                                    <option value="">-- Select Employee --</option>
                                    @foreach($allStaff as $staff)
                                        <option value="{{ $staff['id'] }}">{{ $staff['name'] }}</option>
                                    @endforeach
                                </select>

                                @if($deduction_staff_id && $amount > 0)
                                    <div class="confirmation-info p-2 rounded-2 mt-2" style="background-color: #ecfdf5; border-left: 4px solid #10b981;">
                                        <p class="mb-0 text-success small fw-medium">
                                            <i class="bi bi-info-circle-fill me-1"></i>
                                            Rs. {{ number_format($amount, 2) }} will be deducted from
                                            <span class="fw-bold">{{ collect($allStaff)->firstWhere('id', $deduction_staff_id)['name'] ?? 'this staff' }}</span>'s
                                            salary this month.
                                        </p>
                                    </div>
                                @elseif($is_salary_deduction && !$deduction_staff_id)
                                    <p class="text-danger small mb-0 mt-1">Please select an employee</p>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Category/Type (Dropdown) - Hidden when Salary Deduction is ON -->
                        @if(!$is_salary_deduction)
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small text-uppercase tracking-wider">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('expense_type') is-invalid @enderror" style="border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px;" wire:model.live="expense_type" required>
                                <option value="">Select Category</option>
                                @foreach($expenseTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                                <option value="Other">Other</option>
                            </select>
                            @error('expense_type') <span class="text-danger small">{{ $message }}</span> @enderror

                            {{-- Show custom input when 'Other' selected --}}
                            @if($expense_type === 'Other')
                                <div class="mt-2">
                                    <input type="text" class="form-control @error('customExpenseType') is-invalid @enderror"
                                        style="border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px;"
                                        wire:model="customExpenseType" placeholder="Enter expense type...">
                                    @error('customExpenseType')
                                        <div class="invalid-feedback text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>
                        @endif

                        <!-- Amount (with Rs. prefix) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small text-uppercase tracking-wider">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light text-muted" style="border-radius: 10px 0 0 10px; border-color: #e2e8f0; font-weight: 600;">Rs.</span>
                                <input type="number" step="0.01" class="form-control border-start-0 @error('amount') is-invalid @enderror"
                                    style="border-radius: 0 10px 10px 0; border-color: #e2e8f0; padding: 10px 15px;"
                                    wire:model.live="amount" placeholder="0.00" required>
                            </div>
                            @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Date -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small text-uppercase tracking-wider">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('expense_date') is-invalid @enderror"
                                style="border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px;"
                                wire:model="expense_date" required>
                            @error('expense_date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase tracking-wider">Description</label>
                            <textarea class="form-control" style="border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px;"
                                wire:model="description" rows="2" placeholder="Add optional details..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3 fw-bold shadow-sm d-flex justify-content-center align-items-center"
                                style="border-radius: 12px; background: linear-gradient(135deg, #161b97 0%, #12167d 100%); border: none;"
                                wire:loading.attr="disabled">
                                <span>Save Daily Expense</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this expense? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteExpense">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
