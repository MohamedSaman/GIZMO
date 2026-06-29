<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-journal-check text-success me-2"></i> Cheque Management
            </h3>
            <p class="text-muted mb-0">View and manage all customer cheques</p>
        </div>
        @if($activeTab === 'historical')
        <div>
            <button wire:click="openHistoricalModal" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Add Historical Cheque
            </button>
        </div>
        @endif
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4 border-bottom-0">
        <li class="nav-item">
            <a class="nav-link fw-bold {{ $activeTab === 'system' ? 'active bg-white border-bottom-0 text-primary' : 'text-muted' }}" 
               href="#" wire:click.prevent="$set('activeTab', 'system')">
                <i class="bi bi-bank me-2"></i> System Cheques
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link fw-bold {{ $activeTab === 'historical' ? 'active bg-white border-bottom-0 text-primary' : 'text-muted' }}" 
               href="#" wire:click.prevent="$set('activeTab', 'historical')">
                <i class="bi bi-archive me-2"></i> Historical Cheques
            </a>
        </li>
    </ul>

    @if($activeTab === 'system')
    {{-- Statistics Cards --}}
    <div class="row mb-5">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-warning border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Cheques</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $pendingCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-clock-history fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Completed Cheques</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $completeCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-check2-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-start border-danger border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Overdue Cheques</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $overdueCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cheque Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i> Cheque List
                </h5>
                <span class="badge bg-primary">{{ $cheques->total() ?? 0 }} records</span>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="width: 60%; margin: auto">
                <div class="search-bar flex-grow-1">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" wire:model.live="search"
                            placeholder="Search by cheque number or customer name...">
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="text-sm text-muted fw-medium">Filter</label>
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width: 130px;">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="complete">Complete</option>
                    <option value="overdue">Overdue</option>
                    <option value="return">Return</option>
                </select>
                <label class="text-sm text-muted fw-medium">Show</label>
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0 overflow-auto">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Cheque No</th>
                            <th>Customer</th>
                            <th class="text-center">Bank</th>
                            <th class="text-center">Amount</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cheques as $cheque)
                        <tr wire:key="cheque-{{ $cheque->id }}">
                            <td class="ps-4">{{ $cheque->cheque_number }}</td>
                            <td>{{ $cheque->customer->name ?? '-' }}</td>
                            <td class="text-center">{{ $cheque->bank_name }}</td>
                            <td class="text-center">Rs.{{ number_format($cheque->cheque_amount, 2) }}</td>
                            <td class="text-center">{{ $cheque->cheque_date ? date('M d, Y', strtotime($cheque->cheque_date)) : '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $cheque->status == 'pending' ? 'warning' : ($cheque->status == 'complete' ? 'success' : ($cheque->status == 'return' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($cheque->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if($cheque->status == 'pending' || $cheque->status == 'overdue')
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-gear-fill"></i> Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button class="dropdown-item" wire:click="confirmComplete({{ $cheque->id }})">
                                                <i class="bi bi-check2-circle text-success me-2"></i> Complete
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" wire:click="confirmReturn({{ $cheque->id }})">
                                                <i class="bi bi-arrow-counterclockwise text-danger me-2"></i> Return
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-x-circle display-4 d-block mb-2"></i>
                                No cheques found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($cheques->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $cheques->links('livewire.custom-pagination') }}
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    @if($activeTab === 'historical')
    {{-- Historical Cheques Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-archive text-primary me-2"></i> Historical Cheques
                </h5>
                <span class="badge bg-primary">{{ $historicalCheques->total() ?? 0 }} records</span>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="width: 50%; margin: auto">
                <div class="search-bar flex-grow-1">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" wire:model.live="search"
                            placeholder="Search party name, cheque no, bank, or notes...">
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width: 130px;">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="complete">Complete</option>
                    <option value="return">Return</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0 overflow-auto">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Type</th>
                            <th>Cheque No</th>
                            <th>Party Name (Cust/Supp)</th>
                            <th class="text-center">Bank</th>
                            <th class="text-center">Amount</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historicalCheques as $cheque)
                        <tr wire:key="h-cheque-{{ $cheque->id }}">
                            <td class="ps-4">
                                @if($cheque->type === 'received')
                                    <span class="badge" style="background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc;"><i class="bi bi-arrow-down-left"></i> Received</span>
                                @else
                                    <span class="badge" style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7;"><i class="bi bi-arrow-up-right"></i> Issued</span>
                                @endif
                            </td>
                            <td class="fw-bold">{{ $cheque->cheque_number }}</td>
                            <td>{{ $cheque->party_name }}</td>
                            <td class="text-center">{{ $cheque->bank_name }}</td>
                            <td class="text-center fw-bold">Rs.{{ number_format($cheque->cheque_amount, 2) }}</td>
                            <td class="text-center">{{ $cheque->cheque_date ? date('M d, Y', strtotime($cheque->cheque_date)) : '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $cheque->status == 'pending' ? 'warning' : ($cheque->status == 'complete' ? 'success' : ($cheque->status == 'return' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($cheque->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-gear-fill"></i> Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button class="dropdown-item" wire:click="editHistoricalCheque({{ $cheque->id }})">
                                                <i class="bi bi-pencil-square text-primary me-2"></i> Edit Details
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        @if($cheque->status !== 'complete')
                                        <li>
                                            <button class="dropdown-item" wire:click="confirmHistoricalComplete({{ $cheque->id }})">
                                                <i class="bi bi-check2-circle text-success me-2"></i> Mark Complete
                                            </button>
                                        </li>
                                        @endif
                                        @if($cheque->status !== 'return')
                                        <li>
                                            <button class="dropdown-item" wire:click="confirmHistoricalReturn({{ $cheque->id }})">
                                                <i class="bi bi-arrow-counterclockwise text-danger me-2"></i> Mark Returned
                                            </button>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-archive display-4 d-block mb-2 opacity-50"></i>
                                No historical cheques found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($historicalCheques->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $historicalCheques->links('livewire.custom-pagination') }}
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Add Historical Cheque Modal --}}
    @if($showHistoricalModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-bottom-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle me-2 text-primary"></i> Add Historical Cheque
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeHistoricalModal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <form wire:submit.prevent="saveHistoricalCheque">
                        
                        <!-- Cheque Type (Received / Issued) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-3">Cheque Direction <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check custom-radio border rounded p-3 bg-white w-50" style="cursor:pointer;" onclick="document.getElementById('typeReceived').click()">
                                    <input class="form-check-input ms-1" type="radio" wire:model="h_type" value="received" id="typeReceived">
                                    <label class="form-check-label fw-bold ms-2 text-success" for="typeReceived">
                                        <i class="bi bi-arrow-down-left-circle me-1"></i> Collect Cheque (Received)
                                    </label>
                                </div>
                                <div class="form-check custom-radio border rounded p-3 bg-white w-50" style="cursor:pointer;" onclick="document.getElementById('typeIssued').click()">
                                    <input class="form-check-input ms-1" type="radio" wire:model="h_type" value="issued" id="typeIssued">
                                    <label class="form-check-label fw-bold ms-2 text-danger" for="typeIssued">
                                        <i class="bi bi-arrow-up-right-circle me-1"></i> We Give Someone (Issued)
                                    </label>
                                </div>
                            </div>
                            @error('h_type') <span class="text-danger small mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="row g-3">
                            <!-- Party Name -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-dark">Customer / Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="h_party_name" list="partySuggestions" placeholder="Enter or select name..." autocomplete="off">
                                <datalist id="partySuggestions">
                                    @foreach($this->partySuggestions as $suggestion)
                                        <option value="{{ $suggestion }}">
                                    @endforeach
                                </datalist>
                                @error('h_party_name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <!-- Cheque Number -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Cheque Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="h_cheque_number" placeholder="Enter cheque no...">
                                @error('h_cheque_number') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <!-- Bank Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="h_bank_name" placeholder="Enter bank name...">
                                @error('h_bank_name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <!-- Date & Amount -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Cheque Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model="h_cheque_date">
                                @error('h_cheque_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Amount (Rs) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" wire:model="h_cheque_amount" placeholder="0.00">
                                @error('h_cheque_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-dark">Status <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="h_status">
                                    <option value="pending">Pending</option>
                                    <option value="complete">Complete</option>
                                    <option value="return">Return</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                @error('h_status') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <!-- Notes -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-dark">Notes (Optional)</label>
                                <textarea class="form-control" wire:model="h_note" placeholder="Type notes here..." rows="3"></textarea>
                                @error('h_note') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-white border-top-0">
                    <button type="button" class="btn btn-light fw-bold" wire:click="closeHistoricalModal">Cancel</button>
                    <button type="button" class="btn btn-primary fw-bold px-4" wire:click="saveHistoricalCheque">
                        <i class="bi bi-save me-2"></i> Save Cheque
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>