<div class="container-fluid py-4 min-vh-100 bg-light-soft">
    {{-- Header Section --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-receipt-cutoff text-success me-2"></i> Supplier Receipt Hub
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Supplier Receipts</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex flex-wrap gap-2 bg-white p-2 rounded-3 shadow-sm border">
                <div class="pe-3 border-end text-start">
                    <small class="text-muted d-block lh-1 mb-1">Total Paid</small>
                    <span class="fw-bold text-dark">LKR {{ number_format($totalPaid, 2) }}</span>
                </div>
                <div class="pe-3 border-end text-start ps-2">
                    <small class="text-muted d-block lh-1 mb-1">Cash</small>
                    <span class="fw-bold text-success">LKR {{ number_format($cashPaid, 2) }}</span>
                </div>
                <div class="pe-3 border-end text-start ps-2">
                    <small class="text-muted d-block lh-1 mb-1">Cheque</small>
                    <span class="fw-bold text-warning">LKR {{ number_format($chequePaid, 2) }}</span>
                </div>
                <div class="text-start ps-2">
                    <small class="text-muted d-block lh-1 mb-1">Transfers</small>
                    <span class="fw-bold text-primary">LKR {{ number_format($transferPaid, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-bold text-dark-50 small mb-2 text-uppercase">
                        <i class="bi bi-search me-1"></i> Supplier Lookup
                    </label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-building text-muted"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control border-start-0 ps-0" 
                            placeholder="Enter supplier name..." 
                            wire:model.live.debounce.300ms="filterSupplier"
                        >
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-bold text-dark-50 small mb-2 text-uppercase">
                        <i class="bi bi-wallet2 me-1"></i> Payment Method
                    </label>
                    <select class="form-select form-control-modern" wire:model.live="filterPaymentMethod">
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="overpayment_credit">Overpayment Credit</option>
                        <option value="others">Others</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-bold text-dark-50 small mb-2 text-uppercase">
                        <i class="bi bi-calendar-event me-1"></i> Start Date
                    </label>
                    <input 
                        type="date" 
                        class="form-control form-control-modern" 
                        wire:model.live="filterDateFrom"
                    >
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-bold text-dark-50 small mb-2 text-uppercase">
                        <i class="bi bi-calendar-check me-1"></i> End Date
                    </label>
                    <input 
                        type="date" 
                        class="form-control form-control-modern" 
                        wire:model.live="filterDateTo"
                    >
                </div>
                <div class="col-lg-3 col-md-6 text-end">
                    @if($filterSupplier || $filterDateFrom || $filterDateTo || $filterPaymentMethod)
                    <button class="btn btn-soft-danger w-100 fw-bold py-2" wire:click="clearFilters">
                        <i class="bi bi-x-circle me-1"></i> Reset Filters
                    </button>
                    @else
                    <div class="btn btn-soft-primary w-100 disabled border-0 py-2">
                        <i class="bi bi-funnel me-1"></i> Filter Active
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table Section --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-list-ul text-primary me-2"></i> Supplier Payment Receipts
            </h5>
            <div class="d-flex align-items-center">
                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill">
                    {{ $payments->total() }} Receipts
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-soft text-dark-50 small text-uppercase fw-bold letter-spacing-1">
                        <tr>
                            <th class="ps-4 py-3" style="width: 100px;">Receipt ID</th>
                            <th>Supplier Details</th>
                            <th class="text-center">Payment Date</th>
                            <th class="text-center">Method</th>
                            <th>Reference & Notes</th>
                            <th class="text-end">Paid Amount</th>
                            <th class="text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($payments as $payment)
                        <tr>
                            <td class="ps-4 text-dark fw-bold">#PAY-{{ $payment->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-soft-primary text-primary rounded-circle me-3 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-building fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $payment->supplier->name ?? 'Unknown Supplier' }}</div>
                                        <div class="text-muted small">
                                            @if($payment->supplier?->mobile)
                                                <i class="bi bi-telephone me-1"></i>{{ $payment->supplier->mobile }}
                                            @else
                                                <span class="text-muted">No mobile</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="fw-semibold text-dark">{{ date('d M Y', strtotime($payment->payment_date)) }}</div>
                                <div class="text-muted small">{{ date('l', strtotime($payment->payment_date)) }}</div>
                            </td>
                            <td class="text-center">
                                @if($payment->payment_method === 'cash')
                                    <span class="badge bg-soft-success text-success px-3 py-2 rounded-pill">
                                        <i class="bi bi-cash me-1"></i> Cash
                                    </span>
                                @elseif($payment->payment_method === 'cheque')
                                    <span class="badge bg-soft-warning text-warning px-3 py-2 rounded-pill">
                                        <i class="bi bi-receipt me-1"></i> Cheque
                                    </span>
                                @elseif($payment->payment_method === 'bank_transfer')
                                    <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill">
                                        <i class="bi bi-bank me-1"></i> Bank Transfer
                                    </span>
                                @elseif($payment->payment_method === 'overpayment_credit')
                                    <span class="badge bg-soft-info text-info px-3 py-2 rounded-pill">
                                        <i class="bi bi-wallet2 me-1"></i> Credit Credit
                                    </span>
                                @else
                                    <span class="badge bg-soft-secondary text-secondary px-3 py-2 rounded-pill">
                                        {{ ucfirst($payment->payment_method) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($payment->payment_method === 'cheque')
                                    <div class="small fw-semibold text-dark">Cheque #: {{ $payment->cheque_number }}</div>
                                    <div class="small text-muted">Bank: {{ $payment->bank_name }} | Due: {{ $payment->cheque_date }}</div>
                                @elseif($payment->payment_method === 'bank_transfer')
                                    <div class="small fw-semibold text-dark">Txn: {{ $payment->bank_transaction }}</div>
                                    <div class="small text-muted">Bank: {{ $payment->bank_name }}</div>
                                @else
                                    <div class="small fw-semibold text-dark">{{ $payment->payment_reference ?? 'N/A' }}</div>
                                @endif
                                @if($payment->notes)
                                    <div class="text-muted small text-truncate" style="max-width: 250px;" title="{{ $payment->notes }}">
                                        <i class="bi bi-chat-left-text me-1"></i>{{ $payment->notes }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end">
                                @php
                                    $totalReceiptAmt = $payment->amount + $payment->overpayment_used;
                                @endphp
                                <div class="fw-bold text-success">Rs. {{ number_format($totalReceiptAmt, 2) }}</div>
                                @if($payment->overpayment_used > 0)
                                <div class="small text-info" style="font-size: 0.75rem;">
                                    (Credit: Rs. {{ number_format($payment->overpayment_used, 2) }})
                                </div>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-link link-dark text-decoration-none p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical fs-5"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-2">
                                        <li>
                                            <a class="dropdown-item py-2 px-3 d-flex align-items-center rounded-2" href="javascript:void(0)" wire:click="viewReceipt({{ $payment->id }})">
                                                <i class="bi bi-file-earmark-text me-2 text-primary"></i> <span>View Receipt</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2 px-3 d-flex align-items-center rounded-2" href="javascript:void(0)" wire:click="downloadReceipt({{ $payment->id }})">
                                                <i class="bi bi-download me-2 text-success"></i> <span>Download PDF</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-receipt text-muted display-1 opacity-25"></i>
                                    <h5 class="mt-3 text-muted fw-normal">No supplier receipts found.</h5>
                                    @if($filterSupplier || $filterDateFrom || $filterDateTo || $filterPaymentMethod)
                                    <button class="btn btn-soft-primary mt-3 btn-sm" wire:click="clearFilters">Clear all filters</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Optimized Pagination --}}
            @if($payments->hasPages())
            <div class="px-4 py-3 bg-light-soft border-top d-flex justify-content-center">
                {{ $payments->links('livewire.custom-pagination') }}
            </div>
            @endif
        </div>
    </div>

    {{-- Receipt Detail Modal --}}
    @if($showReceiptModal && $selectedReceipt)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.6); z-index: 1050;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-primary text-white p-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm">
                            <i class="bi bi-receipt fs-2"></i>
                        </div>
                        <div>
                            <h4 class="modal-title fw-bold mb-0">Supplier Receipt Statement</h4>
                            <p class="mb-0 opacity-75 small">Receipt ID: #PAY-{{ $selectedReceipt->id }} • {{ date('M d, Y', strtotime($selectedReceipt->payment_date)) }}</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeReceiptModal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Receipt Content --}}
                    <div class="p-4" id="receipt-content">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold">Payment Receipt</h4>
                        </div>

                        <div class="row mb-4 text-start">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Receipt ID:</strong> #PAY-{{ $selectedReceipt->id }}</p>
                                <p class="mb-1"><strong>Payment Date:</strong> {{ date('M d, Y', strtotime($selectedReceipt->payment_date)) }}</p>
                                <p class="mb-1"><strong>Supplier:</strong> {{ $selectedReceipt->supplier->name ?? 'Unknown' }}</p>
                                <p class="mb-0"><strong>Phone:</strong> {{ $selectedReceipt->supplier->mobile ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $selectedReceipt->payment_method)) }}</p>
                                @if($selectedReceipt->overpayment_used > 0)
                                <p class="mb-1"><strong>Overpayment Credit Used:</strong> <span class="text-info">Rs.{{ number_format($selectedReceipt->overpayment_used, 2) }}</span></p>
                                @endif
                                <p class="mb-1"><strong>Cash Paid:</strong> Rs.{{ number_format($selectedReceipt->amount, 2) }}</p>
                                <p class="mb-1"><strong>Total Payment:</strong> <span class="text-success fw-bold">Rs.{{ number_format($selectedReceipt->amount + $selectedReceipt->overpayment_used, 2) }}</span></p>
                                <p class="mb-1"><strong>Reference No:</strong> {{ $selectedReceipt->payment_reference ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Received By:</strong> Admin</p>
                            </div>
                        </div>

                        {{-- Allocated Orders --}}
                        <h6 class="fw-bold text-muted mb-3 text-start">PAYMENT ALLOCATION</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order Code</th>
                                        <th class="text-end">Allocated Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalPaymentForReceipt = $selectedReceipt->amount + $selectedReceipt->overpayment_used;
                                        $allocatedToOrders = $selectedReceipt->allocations->sum('allocated_amount');
                                        $openingBalancePaid = $totalPaymentForReceipt - $allocatedToOrders;
                                    @endphp
                                    @if(round($openingBalancePaid, 2) > 0)
                                    <tr>
                                        <td class="fw-bold">Opening Balance</td>
                                        <td class="text-end text-success">Rs.{{ number_format($openingBalancePaid, 2) }}</td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                    </tr>
                                    @endif
                                    @foreach($selectedReceipt->allocations as $allocation)
                                    <tr>
                                        <td class="fw-bold">{{ $allocation->order->order_code }}</td>
                                        <td class="text-end text-success">Rs.{{ number_format($allocation->allocated_amount, 2) }}</td>
                                        <td>
                                            @if($allocation->order->due_amount == 0)
                                                <span class="badge bg-success">Fully Paid</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Partial Paid</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    @if($selectedReceipt->overpayment_used > 0)
                                    <tr>
                                        <td class="fw-bold">Overpayment Credit</td>
                                        <td class="text-end fw-bold text-info">Rs.{{ number_format($selectedReceipt->overpayment_used, 2) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Cash Payment</td>
                                        <td class="text-end fw-bold">Rs.{{ number_format($selectedReceipt->amount, 2) }}</td>
                                        <td></td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="fw-bold">TOTAL</td>
                                        <td class="text-end fw-bold text-success">Rs.{{ number_format($selectedReceipt->amount + $selectedReceipt->overpayment_used, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($selectedReceipt->notes)
                        <div class="mt-3 text-start">
                            <h6 class="fw-bold text-muted">NOTES</h6>
                            <p class="mb-0">{{ $selectedReceipt->notes }}</p>
                        </div>
                        @endif

                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted mb-0">Thank you for your payment!</p>
                            <small class="text-muted">Generated on {{ now()->format('M d, Y h:i A') }}</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4">
                    <button type="button" class="btn btn-soft-secondary px-4 fw-bold" wire:click="closeReceiptModal">
                        <i class="bi bi-x me-1"></i> Close View
                    </button>
                    <button type="button" class="btn btn-success px-4 fw-bold" onclick="printReceiptContentJS()">
                        <i class="bi bi-printer me-1"></i> Print Receipt
                    </button>
                    <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" wire:click="downloadReceipt({{ $selectedReceipt->id }})">
                        <i class="bi bi-download me-1"></i> Download Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .letter-spacing-1 { letter-spacing: 0.05rem; }
        .bg-light-soft { background-color: #f8f9fc; }
        .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
        .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
        .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
        .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
        .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
        .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); }
        .btn-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border: none; }
        .btn-soft-primary:hover { background-color: #0d6efd; color: #fff; }
        .btn-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; border: none; }
        .btn-soft-danger:hover { background-color: #dc3545; color: #fff; }
        .btn-soft-secondary { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; border: none; }
        .btn-soft-secondary:hover { background-color: #6c757d; color: #fff; }
        
        .form-control-modern { border: 1px solid #e1e9f1; padding: 0.75rem 1rem; border-radius: 0.75rem; background-color: #fff; transition: all 0.2s; }
        .form-control-modern:focus { background-color: #fff; border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.05); }
        .input-group-modern .form-control { border-radius: 0 0.75rem 0.75rem 0; padding: 0.75rem 1rem 0.75rem 0; border: 1px solid #e1e9f1; border-left: none; }
        .input-group-modern .input-group-text { border-radius: 0.75rem 0 0 0.75rem; border: 1px solid #e1e9f1; border-right: none; }
        
        .avatar-sm { width: 40px; height: 40px; }
        .avatar-md { width: 56px; height: 56px; }
        
        .text-dark-50 { color: #6c7a91; }
        .rounded-4 { border-radius: 1.2rem !important; }
        
        .dropdown-item:hover { background-color: #f8f9fa; }
        
        .table > :not(caption) > * > * { padding: 1.25rem 0.75rem; }
        .breadcrumb-item + .breadcrumb-item::before { content: "›"; color: #adb5bd; font-size: 1.2rem; line-height: 1; }
        .text-start { text-align: left !important; }
        .text-end { text-align: right !important; }
    </style>
</div>

@push('scripts')
<script>
    function printReceiptContentJS() {
        const receiptElement = document.getElementById('receipt-content');
        if (!receiptElement) return;
        
        const receiptContent = receiptElement.innerHTML;
        
        // Create a print-friendly version
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Supplier Payment Receipt</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; background: #ffffff; color: #000000; }
                    .receipt-header { border-bottom: 2px solid #000000; padding-bottom: 15px; margin-bottom: 20px; }
                    .text-success { color: #198754 !important; }
                    .table th { background-color: #f8f9fa !important; }
                    @media print { 
                        .no-print { display: none !important; }
                        body { margin: 0; padding: 15px; }
                    }
                </style>
            </head>
            <body>
                ${receiptContent}
                <div class="text-center mt-4 no-print">
                    <button class="btn btn-secondary" onclick="window.close()">Close Window</button>
                </div>
                <script>
                    window.onload = function() {
                        window.print();
                    }
                <\/script>
            </body>
            </html>
        `;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.open();
        printWindow.document.write(printContent);
        printWindow.document.close();
    }
</script>
@endpush