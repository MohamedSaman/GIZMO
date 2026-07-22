<div class="container-fluid py-4" style="background: #f8fafc; min-height: 100vh;">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1" style="color: #1e293b; letter-spacing: -0.5px;">
                <i class="bi bi-wallet2 text-primary me-2"></i>Staff Salary
            </h3>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">Calculate and manage staff salaries with precision</p>
        </div>
    </div>

    {{-- Search & Month Selection Card --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: white;">
        <div class="card-body p-4">
            <div class="row g-4 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Search Staff</label>
                    <div class="position-relative">
                        <input type="text" class="form-control form-control-lg bg-light border-0" 
                               wire:model.live.debounce.300ms="search" 
                               placeholder="Type staff name, email or phone..."
                               style="padding-left: 2.8rem; border-radius: 12px; transition: all 0.2s;"
                               @if($showSearchResults && count($staffResults) > 0) autocomplete="off" @endif>
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-primary"></i>

                        {{-- Search Results Dropdown --}}
                        @if($showSearchResults && count($staffResults) > 0)
                        <div class="list-group position-absolute w-100 mt-2 shadow" style="max-height: 350px; overflow-y: auto; z-index: 1050; border-radius: 12px; border: 1px solid #e2e8f0;">
                            @foreach($staffResults as $staff)
                            <button type="button" class="list-group-item list-group-item-action border-0 py-3" 
                                    wire:click="selectStaff({{ $staff['id'] }})">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <h6 class="mb-1 fw-bold text-dark">{{ $staff['name'] }}</h6>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ ucfirst(str_replace('_', ' ', $staff['staff_type'] ?? 'Staff')) }}</span>
                                </div>
                                <p class="mb-0 text-muted small d-flex align-items-center">
                                    <i class="bi bi-envelope me-2"></i> {{ $staff['email'] }}
                                    <span class="mx-2 text-light">|</span>
                                    <i class="bi bi-telephone me-2"></i> {{ $staff['contact'] }}
                                </p>
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Target Month</label>
                    <div class="position-relative">
                        <input type="month" class="form-control form-control-lg bg-light border-0" 
                               wire:model.live="salary_month"
                               style="border-radius: 12px; padding-left: 1rem;">
                    </div>
                </div>

                <div class="col-md-2">
                    @if($selectedStaff)
                    <button wire:click="clearSelection" class="btn btn-light w-100 btn-lg text-danger" style="border-radius: 12px;">
                        <i class="bi bi-x-lg me-2"></i> Clear
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Selected Staff Details (Only shows if a staff is selected) --}}
    @if($selectedStaff)
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                <div class="bg-primary-subtle p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-person-fill text-primary fs-5"></i>
                </div>
                {{ $selectedStaff->name }}
            </h5>
            <span class="badge bg-dark rounded-pill px-3 py-2 fw-medium">{{ ucfirst(str_replace('_', ' ', $selectedStaff->staff_type)) }}</span>
        </div>
        
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-4 h-100">
                        <p class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing: 0.5px;">Contact Info</p>
                        <p class="fw-medium text-dark mb-1"><i class="bi bi-envelope text-primary me-2"></i> {{ $selectedStaff->email }}</p>
                        <p class="fw-medium text-dark mb-0"><i class="bi bi-telephone text-primary me-2"></i> {{ $selectedStaff->contact }}</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="p-3 rounded-4 h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-success-emphasis small fw-bold text-uppercase mb-0" style="letter-spacing: 0.5px;">Financial Overview ({{ \Carbon\Carbon::parse($salary_month . '-01')->format('F Y') }})</p>
                        </div>
                        <div class="row g-2">
                            <div class="col-4 border-end border-success-subtle">
                                <p class="text-success-emphasis small mb-0 opacity-75">Basic Salary</p>
                                <h5 class="fw-bold text-success mb-0">Rs. {{ number_format($basic_salary, 2) }}</h5>
                            </div>
                            <div class="col-4 border-end border-success-subtle ps-3">
                                <p class="text-success-emphasis small mb-0 opacity-75">Approved Expenses</p>
                                <h5 class="fw-bold text-success mb-0">Rs. {{ number_format($approved_expenses, 2) }}</h5>
                            </div>
                            <div class="col-4 ps-3">
                                <p class="text-danger-emphasis small mb-0 opacity-75">Advances Taken</p>
                                <div class="d-flex justify-content-between align-items-center mb-0 mt-1">
                                    <h5 class="fw-bold text-danger mb-0">Rs. {{ number_format($advance_salary, 2) }}</h5>
                                    <div class="d-flex gap-1">
                                        @if($advance_salary > 0)
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" wire:click="openAdvancesListModal" style="font-size: 0.7rem; border-radius: 6px;">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @endif
                                        @if(!$this->hasExistingSalary())
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" wire:click="openAdvanceModal" style="font-size: 0.7rem; border-radius: 6px;">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 d-flex align-items-stretch">
                    <button class="btn btn-primary w-100 h-100 rounded-4 shadow-sm fw-bold d-flex flex-column align-items-center justify-content-center hover-lift" 
                            wire:click="openAddSalaryModal" 
                            style="transition: all 0.3s; gap: 8px;">
                        <i class="bi bi-plus-circle-fill fs-3"></i>
                        <span>Process Salary for {{ \Carbon\Carbon::parse($salary_month . '-01')->format('M Y') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Salary History --}}
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-header bg-white border-bottom-0 p-4">
            <h5 class="fw-bold text-dark mb-0">Salary History</h5>
        </div>
        <div class="card-body p-0">
            @if($salaries->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.95rem;">
                    <thead class="bg-light text-secondary" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4 py-3 border-0 rounded-start">Month</th>
                            <th class="py-3 border-0">Type</th>
                            <th class="py-3 border-0">Basic</th>
                            <th class="py-3 border-0">Additions</th>
                            <th class="py-3 border-0">Deductions</th>
                            <th class="py-3 border-0">Net Salary</th>
                            <th class="py-3 border-0">Status</th>
                            <th class="text-end pe-4 py-3 border-0 rounded-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach($salaries as $salary)
                        @php
                            $additions = $salary->allowance + $salary->bonus + $salary->overtime;
                            $deductions = $salary->deductions + ($salary->additional_salary ?? 0);
                        @endphp
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="fw-bold text-dark">{{ $salary->salary_month->format('M Y') }}</span>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light text-secondary border px-2 py-1">{{ ucfirst($salary->salary_type) }}</span>
                            </td>
                            <td class="py-3 text-muted">Rs. {{ number_format($salary->basic_salary, 2) }}</td>
                            <td class="py-3 text-success fw-medium">+Rs. {{ number_format($additions, 2) }}</td>
                            <td class="py-3 text-danger fw-medium">-Rs. {{ number_format($deductions, 2) }}</td>
                            <td class="py-3">
                                <span class="fw-bold text-dark fs-6">Rs. {{ number_format($salary->net_salary, 2) }}</span>
                            </td>
                            <td class="py-3">
                                @if($salary->payment_status === 'pending')
                                    <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">Pending</span>
                                @else
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Paid</span>
                                @endif
                            </td>
                            <td class="text-end pe-4 py-3">
                                <div class="btn-group shadow-sm rounded-pill">
                                    <button class="btn btn-sm btn-light border-0" wire:click="viewSalary({{ $salary->salary_id }})" title="View">
                                        <i class="bi bi-eye text-primary"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light border-0" wire:click="editSalary({{ $salary->salary_id }})" title="Edit">
                                        <i class="bi bi-pencil text-warning"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light border-0" wire:click="deleteConfirm({{ $salary->salary_id }})" title="Delete">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-light rounded-bottom" style="border-radius: 0 0 16px 16px;">
                {{ $salaries->links('livewire.custom-pagination') }}
            </div>
            @else
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-folder2-open fs-1 text-muted"></i>
                </div>
                <h5 class="fw-bold text-dark">No Salary Records</h5>
                <p class="text-muted">There are no salary records for this staff member yet.</p>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-header bg-white border-bottom-0 p-4">
            <h5 class="fw-bold text-dark mb-0">
                All Staff Salaries for {{ \Carbon\Carbon::parse($salary_month . '-01')->format('F Y') }}
            </h5>
        </div>
        <div class="card-body p-0">
            @if($allMonthSalaries && $allMonthSalaries->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.95rem;">
                    <thead class="bg-light text-secondary" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4 py-3 border-0 rounded-start">Staff</th>
                            <th class="py-3 border-0">Type</th>
                            <th class="py-3 border-0">Basic</th>
                            <th class="py-3 border-0">Additions</th>
                            <th class="py-3 border-0">Deductions</th>
                            <th class="py-3 border-0">Net Salary</th>
                            <th class="py-3 border-0">Status</th>
                            <th class="text-end pe-4 py-3 border-0 rounded-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach($allMonthSalaries as $salary)
                        @php
                            $additions = $salary->allowance + $salary->bonus + $salary->overtime;
                            $deductions = $salary->deductions + ($salary->additional_salary ?? 0);
                        @endphp
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="fw-bold text-dark">{{ $salary->user->name ?? 'Unknown' }}</span>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light text-secondary border px-2 py-1">{{ ucfirst($salary->salary_type) }}</span>
                            </td>
                            <td class="py-3 text-muted">Rs. {{ number_format($salary->basic_salary, 2) }}</td>
                            <td class="py-3 text-success fw-medium">+Rs. {{ number_format($additions, 2) }}</td>
                            <td class="py-3 text-danger fw-medium">-Rs. {{ number_format($deductions, 2) }}</td>
                            <td class="py-3">
                                <span class="fw-bold text-dark fs-6">Rs. {{ number_format($salary->net_salary, 2) }}</span>
                            </td>
                            <td class="py-3">
                                @if($salary->payment_status === 'pending')
                                    <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">Pending</span>
                                @else
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Paid</span>
                                @endif
                            </td>
                            <td class="text-end pe-4 py-3">
                                <div class="btn-group shadow-sm rounded-pill">
                                    <button class="btn btn-sm btn-light border-0" wire:click="viewSalary({{ $salary->salary_id }})" title="View">
                                        <i class="bi bi-eye text-primary"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-light rounded-bottom" style="border-radius: 0 0 16px 16px;">
                {{ $allMonthSalaries->links('livewire.custom-pagination') }}
            </div>
            @else
            <div class="text-center py-5">
                <img src="https://illustrations.popsy.co/amber/freelancer.svg" alt="Select Staff" style="width: 250px; opacity: 0.8;" class="mb-4">
                <h4 class="fw-bold text-dark">Ready to calculate salaries</h4>
                <p class="text-muted max-w-md mx-auto">Use the search bar above to find a staff member and manage their salary calculations.</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Salary Modal --}}
    @if($showSalaryModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                
                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4 position-relative">
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, {{ $isEditMode ? '#fef3c7, #fde68a' : '#e0e7ff, #c7d2fe' }}); opacity: 0.5; z-index: 0;"></div>
                    <div class="position-relative z-1 w-100 d-flex justify-content-between align-items-center pb-3">
                        <div>
                            <h4 class="modal-title fw-bold text-dark mb-1">
                                @if($isEditMode)
                                    <i class="bi bi-pencil-square text-warning me-2"></i>Edit Salary
                                @else
                                    <i class="bi bi-calculator text-primary me-2"></i>Process Salary
                                @endif
                            </h4>
                            <p class="text-muted mb-0 small">For {{ $selectedStaff->name }} • {{ \Carbon\Carbon::parse($salary_month . '-01')->format('F Y') }}</p>
                        </div>
                        <button type="button" class="btn-close bg-white rounded-circle p-2 shadow-sm" wire:click="closeSalaryModal" style="opacity: 1;"></button>
                    </div>
                </div>

                <div class="modal-body p-4 bg-light">
                    <form wire:submit.prevent="saveSalary">
                        <div class="row g-4">
                            
                            {{-- Earnings Column --}}
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                                    <div class="card-header bg-white border-bottom pb-2 pt-4 px-4">
                                        <h6 class="fw-bold text-success mb-0 d-flex align-items-center">
                                            <i class="bi bi-arrow-up-circle-fill me-2"></i> Earnings & Additions
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <label class="form-label text-muted small fw-semibold text-uppercase">Basic Salary</label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light border-0 text-muted">Rs.</span>
                                                <input type="number" class="form-control bg-light border-0 fw-bold fs-5 text-dark" wire:model="basic_salary" readonly>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label text-muted small fw-semibold text-uppercase d-flex justify-content-between">
                                                <span>Approved Expenses</span>
                                                @if(count($monthlyExpenses) > 0)
                                                    <span class="badge bg-success-subtle text-success rounded-pill">{{ count($monthlyExpenses) }} Items</span>
                                                @endif
                                            </label>
                                            <div class="input-group input-group-lg mb-2">
                                                <span class="input-group-text bg-success-subtle border-0 text-success">Rs.</span>
                                                <input type="number" class="form-control bg-success-subtle border-0 fw-bold fs-5 text-success" wire:model="approved_expenses" readonly>
                                            </div>
                                            
                                            @if(count($monthlyExpenses) > 0)
                                            <div class="bg-white border rounded-3 p-2" style="max-height: 120px; overflow-y: auto;">
                                                <table class="table table-sm table-borderless mb-0 small">
                                                    <tbody>
                                                        @foreach($monthlyExpenses as $expense)
                                                        <tr>
                                                            <td class="text-muted">{{ \Carbon\Carbon::parse($expense['expense_date'])->format('d M') }}</td>
                                                            <td>{{ ucfirst(str_replace('_', ' ', $expense['expense_type'])) }}</td>
                                                            <td class="text-end fw-medium text-success">+{{ number_format($expense['amount'], 2) }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @endif
                                        </div>

                                        <div class="row g-3 mb-4">
                                            <div class="col-6">
                                                <label class="form-label text-muted small fw-semibold">Allowances</label>
                                                <input type="number" step="0.01" class="form-control custom-input" wire:model.live="allowance" placeholder="0.00">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label text-muted small fw-semibold">Bonus</label>
                                                <input type="number" step="0.01" class="form-control custom-input" wire:model.live="bonus" placeholder="0.00">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label text-muted small fw-semibold">Overtime Pay</label>
                                                <input type="number" step="0.01" class="form-control custom-input" wire:model.live="overtime" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Deductions Column --}}
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                                    <div class="card-header bg-white border-bottom pb-2 pt-4 px-4">
                                        <h6 class="fw-bold text-danger mb-0 d-flex align-items-center">
                                            <i class="bi bi-arrow-down-circle-fill me-2"></i> Deductions & Advances
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <label class="form-label text-muted small fw-semibold text-uppercase d-flex justify-content-between">
                                                <span>Advances Taken ({{ \Carbon\Carbon::parse($salary_month . '-01')->format('M Y') }})</span>
                                                <span class="badge bg-danger-subtle text-danger rounded-pill">Auto-fetched</span>
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-danger-subtle border-0 text-danger">Rs.</span>
                                                <input type="number" class="form-control bg-danger-subtle border-0 fw-bold fs-5 text-danger" wire:model.live="advance_salary">
                                            </div>
                                            <small class="text-muted mt-1 d-block">Advances pulled from staff records. Can be overridden if needed.</small>
                                        </div>

                                        @if($previous_advance_balance > 0)
                                        <div class="mb-4">
                                            <label class="form-label text-muted small fw-semibold text-uppercase d-flex justify-content-between">
                                                <span>Previous Month Balance</span>
                                                <span class="badge bg-warning-subtle text-warning rounded-pill">Carried Forward</span>
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-warning-subtle border-0 text-warning">Rs.</span>
                                                <input type="number" class="form-control bg-warning-subtle border-0 fw-bold fs-5 text-warning" wire:model.live="previous_advance_balance">
                                            </div>
                                            <small class="text-muted mt-1 d-block">Unsettled negative balance from the previous month.</small>
                                        </div>
                                        @endif

                                        <div class="mb-4">
                                            <label class="form-label text-muted small fw-semibold">Other Deductions</label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-white border custom-border text-muted">Rs.</span>
                                                <input type="number" step="0.01" class="form-control custom-input border-start-0" wire:model.live="deductions" placeholder="0.00">
                                            </div>
                                        </div>

                                        <hr class="my-4 border-light">

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label text-muted small fw-semibold">Salary Type</label>
                                                <select class="form-select custom-input" wire:model="salary_type">
                                                    <option value="monthly">Monthly</option>
                                                    <option value="daily">Daily</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label text-muted small fw-semibold">Payment Status</label>
                                                <select class="form-select custom-input" wire:model="payment_status">
                                                    <option value="pending">Pending</option>
                                                    <option value="paid">Paid</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Final Calculation Bar --}}
                        <div class="card mt-4 border-0 shadow" style="border-radius: 16px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <p class="text-slate-400 small fw-semibold text-uppercase mb-1" style="color: #94a3b8; letter-spacing: 1px;">Final Net Salary</p>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <span class="text-white opacity-75 fs-4">Rs.</span>
                                            <h1 class="text-white fw-bold mb-0" style="letter-spacing: -1px;">{{ number_format($net_salary, 2) }}</h1>
                                        </div>
                                    </div>
                                    <div class="col-md-5 d-flex gap-3 justify-content-end mt-3 mt-md-0">
                                        <button type="button" class="btn btn-light px-4 py-3 fw-bold rounded-3" wire:click="closeSalaryModal">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary px-5 py-3 fw-bold rounded-3 shadow" wire:loading.attr="disabled">
                                            <span wire:loading.remove>Save Salary Record</span>
                                            <span wire:loading><span class="spinner-border spinner-border-sm me-2"></span>Saving...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- View Modal & Delete Modals remain functionally similar but updated visually if needed. For brevity, keeping them functional and clean. --}}
    @if($showViewModal && $viewingSalary)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark d-flex align-items-center">
                        <div class="bg-primary-subtle p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-file-text text-primary"></i>
                        </div>
                        Salary Payslip
                    </h5>
                    <button type="button" class="btn-close bg-light rounded-circle p-2" wire:click="closeViewModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h6 class="text-muted mb-1">{{ $viewingSalary->user->name }}</h6>
                        <h4 class="fw-bold">{{ $viewingSalary->salary_month->format('F Y') }}</h4>
                        <span class="badge {{ $viewingSalary->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }} rounded-pill px-3 py-2 mt-2">
                            {{ strtoupper($viewingSalary->payment_status) }}
                        </span>
                    </div>

                    <div class="bg-light rounded-4 p-3 mb-4">
                        {{-- Top Summary Cards --}}
                        @php
                            $totalEarnings = $viewingSalary->basic_salary + $viewingSalary->allowance + $viewingSalary->bonus + $viewingSalary->overtime;
                            $totalAdvances = $viewingSalary->additional_salary + $viewingSalary->previous_advance_balance;
                        @endphp
                        <div class="row g-2 mb-4">
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-3 text-center border shadow-sm">
                                    <span class="d-block text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Earnings</span>
                                    <span class="fw-bold text-success fs-6">Rs. {{ number_format($totalEarnings, 2) }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-3 text-center border shadow-sm">
                                    <span class="d-block text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Advances</span>
                                    <span class="fw-bold text-danger fs-6">Rs. {{ number_format($totalAdvances, 2) }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 text-center border shadow-sm" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
                                    <span class="d-block text-success-emphasis small fw-semibold text-uppercase" style="font-size: 0.7rem;">Net Balance</span>
                                    <span class="fw-bold text-success fs-6">Rs. {{ number_format($viewingSalary->net_salary, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Ledger Table --}}
                        <div class="table-responsive bg-white rounded-3 border">
                            <table class="table table-borderless table-sm mb-0 align-middle">
                                <thead class="border-bottom bg-light opacity-75">
                                    <tr>
                                        <th class="small fw-semibold text-muted py-2 ps-3">Description</th>
                                        <th class="small fw-semibold text-muted py-2">Date</th>
                                        <th class="small fw-semibold text-muted py-2 text-end">Amount</th>
                                        <th class="small fw-semibold text-muted py-2 text-end pe-3">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $runningBalance = $totalEarnings; @endphp
                                    
                                    {{-- Starting Balance / Earnings --}}
                                    <tr>
                                        <td class="ps-3 py-2 fw-medium text-dark" style="font-size: 0.85rem;">Total Earnings</td>
                                        <td class="py-2 text-muted small">-</td>
                                        <td class="text-end py-2 text-success fw-medium small">+Rs. {{ number_format($totalEarnings, 2) }}</td>
                                        <td class="text-end pe-3 py-2 fw-bold text-dark small">Rs. {{ number_format($runningBalance, 2) }}</td>
                                    </tr>

                                    {{-- Other Deductions --}}
                                    @if($viewingSalary->deductions > 0)
                                    @php $runningBalance -= $viewingSalary->deductions; @endphp
                                    <tr>
                                        <td class="ps-3 py-2 text-muted" style="font-size: 0.85rem;">Other Deductions</td>
                                        <td class="py-2 text-muted small">-</td>
                                        <td class="text-end py-2 text-danger small">-Rs. {{ number_format($viewingSalary->deductions, 2) }}</td>
                                        <td class="text-end pe-3 py-2 fw-medium text-dark small">Rs. {{ number_format($runningBalance, 2) }}</td>
                                    </tr>
                                    @endif

                                    {{-- Previous Month Balance --}}
                                    @if($viewingSalary->previous_advance_balance > 0)
                                    @php $runningBalance -= $viewingSalary->previous_advance_balance; @endphp
                                    <tr>
                                        <td class="ps-3 py-2 text-warning-emphasis" style="font-size: 0.85rem;"><i class="bi bi-arrow-90deg-up me-1"></i>Prev. Month Balance</td>
                                        <td class="py-2 text-muted small">-</td>
                                        <td class="text-end py-2 text-warning small">-Rs. {{ number_format($viewingSalary->previous_advance_balance, 2) }}</td>
                                        <td class="text-end pe-3 py-2 fw-medium text-dark small">Rs. {{ number_format($runningBalance, 2) }}</td>
                                    </tr>
                                    @endif

                                    {{-- Advances Loop --}}
                                    @if(count($viewingAdvances) > 0)
                                        @foreach($viewingAdvances as $adv)
                                        @php $runningBalance -= $adv['amount']; @endphp
                                        <tr>
                                            <td class="ps-3 py-2" style="font-size: 0.85rem;">
                                                Advance
                                                @if($adv['note'])
                                                    <span class="text-muted small fst-italic d-block" style="font-size: 0.75rem;">{{ $adv['note'] }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2 text-muted" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($adv['advance_date'])->format('d M') }}</td>
                                            <td class="text-end py-2 text-danger small">-Rs. {{ number_format($adv['amount'], 2) }}</td>
                                            <td class="text-end pe-3 py-2 fw-medium text-dark small">Rs. {{ number_format($runningBalance, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    @elseif($viewingSalary->additional_salary > 0)
                                        @php $runningBalance -= $viewingSalary->additional_salary; @endphp
                                        <tr>
                                            <td class="ps-3 py-2 text-muted" style="font-size: 0.85rem;">Advances Taken</td>
                                            <td class="py-2 text-muted small">-</td>
                                            <td class="text-end py-2 text-danger small">-Rs. {{ number_format($viewingSalary->additional_salary, 2) }}</td>
                                            <td class="text-end pe-3 py-2 fw-medium text-dark small">Rs. {{ number_format($runningBalance, 2) }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @if($viewingSalary->payment_status === 'pending')
                    <button class="btn btn-success w-100 py-3 rounded-3 fw-bold" wire:click="markAsPaid">
                        <i class="bi bi-check2-circle me-2"></i> Mark as Paid
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirmModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-4" style="border-radius: 20px;">
                <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle rounded-circle mb-3 mx-auto" style="width: 70px; height: 70px;">
                    <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                </div>
                <h4 class="fw-bold">Delete Record?</h4>
                <p class="text-muted mb-4">Are you sure you want to delete the salary record for <strong>{{ $deleteConfirmName }}</strong>?</p>
                <div class="d-flex gap-2 w-100">
                    <button type="button" class="btn btn-light w-50 rounded-3 py-2 fw-medium" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger w-50 rounded-3 py-2 fw-medium" wire:click="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Add Advance Modal --}}
    @if($showAdvanceModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-danger d-flex align-items-center">
                        <div class="bg-danger-subtle p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-cash-coin text-danger"></i>
                        </div>
                        Add Advance for {{ $selectedStaff->name }}
                    </h5>
                    <button type="button" class="btn-close bg-light rounded-circle p-2" wire:click="closeAdvanceModal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="saveAdvance">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Advance Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">Rs.</span>
                                <input type="number" step="0.01" class="form-control bg-light border-start-0 custom-input" wire:model="new_advance_amount" required placeholder="0.00">
                            </div>
                            @error('new_advance_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control custom-input" wire:model="new_advance_date" required>
                            @error('new_advance_date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-semibold">Note / Reason</label>
                            <textarea class="form-control custom-input" wire:model="new_advance_note" rows="2" placeholder="Optional details..."></textarea>
                            @error('new_advance_note') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="d-flex gap-2 w-100 mt-2">
                            <button type="button" class="btn btn-light w-50 rounded-3 py-2 fw-medium" wire:click="closeAdvanceModal">Cancel</button>
                            <button type="submit" class="btn btn-danger w-50 rounded-3 py-2 fw-bold" wire:loading.attr="disabled">
                                <span wire:loading.remove>Save Advance</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-2"></span>Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- View Advances List Modal --}}
    @if($showAdvancesListModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark d-flex align-items-center">
                        <div class="bg-primary-subtle p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-list-stars text-primary"></i>
                        </div>
                        Advances Taken ({{ \Carbon\Carbon::parse($salary_month . '-01')->format('F Y') }})
                    </h5>
                    <button type="button" class="btn-close bg-light rounded-circle p-2" wire:click="closeAdvancesListModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3 bg-light p-3 rounded-3 border-0">
                        <span class="text-muted small fw-semibold text-uppercase d-block" style="font-size: 0.75rem; letter-spacing: 0.5px;">Staff Member</span>
                        <span class="fw-bold text-dark fs-5">{{ $selectedStaff->name }}</span>
                    </div>

                    @if(count($selectedAdvances) > 0)
                    <div class="table-responsive bg-white rounded-3 border">
                        <table class="table table-borderless table-sm mb-0 align-middle">
                            <thead class="border-bottom bg-light opacity-75">
                                <tr>
                                    <th class="small fw-semibold text-muted py-2 ps-3">Date</th>
                                    <th class="small fw-semibold text-muted py-2">Note / Reason</th>
                                    <th class="small fw-semibold text-muted py-2 text-end pe-3">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedAdvances as $adv)
                                <tr class="border-bottom border-light">
                                    <td class="ps-3 py-2 text-muted small">{{ \Carbon\Carbon::parse($adv['advance_date'])->format('d M, Y') }}</td>
                                    <td class="py-2 text-dark small" style="white-space: pre-line;">{{ $adv['note'] ?: '-' }}</td>
                                    <td class="text-end pe-3 py-2 fw-semibold text-danger">Rs. {{ number_format($adv['amount'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <span class="fw-bold text-dark">Total Advances</span>
                        <span class="fw-bold text-danger fs-5">Rs. {{ number_format(collect($selectedAdvances)->sum('amount'), 2) }}</span>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">No advances recorded for this month.</p>
                    </div>
                    @endif

                    <div class="mt-4">
                        <button type="button" class="btn btn-light w-100 rounded-3 py-3 fw-bold" wire:click="closeAdvancesListModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    body {
        background-color: #f8fafc;
    }
    
    .custom-input {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        transition: all 0.2s;
    }
    .custom-input:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    .custom-border {
        border-color: #e2e8f0 !important;
    }

    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2) !important;
    }

    .list-group-item {
        transition: background-color 0.2s;
    }
    .list-group-item:hover {
        background-color: #f1f5f9 !important;
    }
    
    /* Scrollbar styling for dropdowns */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f5f9; 
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1; 
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8; 
    }
</style>
@endpush

@push('scripts')
<script>
    Livewire.on('showToast', (event) => {
        // Livewire 3 passes array payloads inside an outer array
        const data = Array.isArray(event) ? event[0] : event;
        const type = data?.type || 'info';
        const message = data?.message || '';
        
        const iconMap = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };

        Swal.fire({
            icon: iconMap[type] || 'info',
            title: type.charAt(0).toUpperCase() + type.slice(1),
            text: message,
            toast: true,
            position: 'top-right',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-4 shadow-lg border-0'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    });
</script>
@endpush
