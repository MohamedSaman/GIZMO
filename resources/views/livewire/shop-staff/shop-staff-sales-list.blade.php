<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-receipt text-primary me-2"></i> Sale List
            </h3>
            <p class="text-muted mb-0">View, print and manage your store sales</p>
        </div>
        <a href="{{ route('shop-staff.store-billing') }}" class="btn btn-primary">
            <i class="bi bi-cart-plus me-2"></i> New Sale
        </a>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by invoice or customer...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Payment Status</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" wire:model.live="dateFilter" class="form-control" placeholder="Filter by date">
                </div>
                <div class="col-md-1">
                    @if($search || $statusFilter || $dateFilter)
                    <button wire:click="$set('search', ''); $set('statusFilter', ''); $set('dateFilter', '')" class="btn btn-outline-secondary w-100" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #161b97 0%, #12167d 100%);">
                <div class="card-body text-center py-3 text-white">
                    <h4 class="fw-bold mb-0">Rs. {{ number_format($summarySales, 2) }}</h4>
                    <small class="opacity-75">
                        @if($dateFilter)
                            Sales Total for {{ \Carbon\Carbon::parse($dateFilter)->format('d M, Y') }}
                        @else
                            Today's Sales Total
                        @endif
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="fw-bold text-success mb-0">{{ $summaryCount }}</h4>
                    <small class="text-muted">
                        @if($dateFilter)
                            Transactions on {{ \Carbon\Carbon::parse($dateFilter)->format('d M, Y') }}
                        @else
                            Today's Transactions
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Invoice #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $sale->invoice_number }}</span>
                            </td>
                            <td>
                                @if($sale->walking_customer_name)
                                    {{ $sale->walking_customer_name }}
                                    @if($sale->walking_customer_phone)<br><small class="text-muted">{{ $sale->walking_customer_phone }}</small>@endif
                                @elseif($sale->customer && $sale->customer->name !== 'Walking Customer')
                                    {{ $sale->customer->name }}
                                    @if($sale->customer->phone)<br><small class="text-muted">{{ $sale->customer->phone }}</small>@endif
                                @else
                                    Walking Customer
                                @endif
                            </td>
                            <td class="fw-semibold">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                            <td class="text-success fw-semibold">Rs. {{ number_format($sale->payments->sum('amount') ?? 0, 2) }}</td>
                            <td>
                                @if(($sale->due_amount ?? 0) > 0)
                                    <span class="text-danger fw-semibold">Rs. {{ number_format($sale->due_amount, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($sale->payment_status === 'paid')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Paid</span>
                                @elseif($sale->payment_status === 'partial')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Partial</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i>Pending</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $sale->created_at->format('M d, Y') }}<br><small>{{ $sale->created_at->format('h:i A') }}</small></td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <button wire:click="printInvoice({{ $sale->id }})" class="btn btn-sm btn-outline-secondary" title="Print Invoice">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                    <button wire:click="viewDetails({{ $sale->id }})" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button wire:click="editSale({{ $sale->id }})" class="btn btn-sm btn-outline-warning" title="Edit Sale">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No sales found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    {{-- View Details Modal --}}
    @if($showDetailsModal && $selectedSale)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #161b97 0%, #12167d 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-receipt me-2"></i>Sale Details - {{ $selectedSale->invoice_number }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDetailsModal"></button>
                </div>
                <div class="modal-body">
                    {{-- Sale Info --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Customer:</strong> 
                                @if($selectedSale->walking_customer_name)
                                    {{ $selectedSale->walking_customer_name }}
                                    @if($selectedSale->walking_customer_phone) ({{ $selectedSale->walking_customer_phone }}) @endif
                                @elseif($selectedSale->customer && $selectedSale->customer->name !== 'Walking Customer')
                                    {{ $selectedSale->customer->name }}
                                    @if($selectedSale->customer->phone) ({{ $selectedSale->customer->phone }}) @endif
                                @else
                                    Walking Customer
                                @endif
                            </p>
                            <p class="mb-1"><strong>Date:</strong> {{ $selectedSale->created_at->format('M d, Y h:i A') }}</p>
                            <p class="mb-0"><strong>Payment Status:</strong>
                                @if($selectedSale->payment_status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($selectedSale->payment_status === 'partial')
                                    <span class="badge bg-warning text-dark">Partial</span>
                                @else
                                    <span class="badge bg-danger">Pending</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Invoice:</strong> {{ $selectedSale->invoice_number }}</p>
                            <p class="mb-1"><strong>Payment Type:</strong> {{ ucfirst(str_replace('_', ' ', $selectedSale->payment_type ?? 'N/A')) }}</p>
                            @if(($selectedSale->due_amount ?? 0) > 0)
                            <p class="mb-0 text-danger"><strong>Due Amount:</strong> Rs. {{ number_format($selectedSale->due_amount, 2) }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Items --}}
                    <h6 class="fw-bold mb-2">Sale Items</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedSale->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end text-danger">
                                        @if(($item->discount_per_unit ?? 0) > 0)
                                            Rs. {{ number_format($item->discount_per_unit, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">Rs. {{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                    <td class="text-end">Rs. {{ number_format($selectedSale->subtotal, 2) }}</td>
                                </tr>
                                @if(($selectedSale->discount_amount ?? 0) > 0)
                                <tr>
                                    <td colspan="4" class="text-end text-danger">Discount:</td>
                                    <td class="text-end text-danger">- Rs. {{ number_format($selectedSale->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                    <td class="text-end fw-bold">Rs. {{ number_format($selectedSale->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Returns Section --}}
                    @if($saleReturns && count($saleReturns) > 0)
                    <h6 class="fw-bold mb-2 text-danger"><i class="bi bi-arrow-return-left me-2"></i>Returns</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered border-danger">
                            <thead class="table-danger">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Returned Qty</th>
                                    <th class="text-end">Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($saleReturns as $return)
                                <tr>
                                    <td>{{ $return->product->name ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $return->return_quantity }}</td>
                                    <td class="text-end">Rs. {{ number_format($return->total_amount, 2) }}</td>
                                    <td class="text-muted">{{ $return->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    {{-- Payments --}}
                    @if($selectedSale->payments && count($selectedSale->payments) > 0)
                    <h6 class="fw-bold mb-2 text-success"><i class="bi bi-cash-coin me-2"></i>Payment Records</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-success">
                                <tr>
                                    <th>Method</th>
                                    <th class="text-end">Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedSale->payments as $payment)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                    <td class="text-end">Rs. {{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-muted">{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button wire:click="printInvoice({{ $selectedSale->id }})" class="btn btn-outline-secondary">
                        <i class="bi bi-printer me-2"></i>Print Invoice
                    </button>
                    <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
