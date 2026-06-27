<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-cart-check text-primary me-2"></i> Customer Order Requests
            </h3>
            <p class="text-muted mb-0">View, manage, and convert customer order requests to sales</p>
        </div>
    </div>

    {{-- Filter/Search --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Search by order number or customer phone..." wire:model.live="search">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Orders List --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">Order No</th>
                        <th>Customer</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Valid Until</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr wire:key="order-{{ $order->id }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">#{{ $order->quotation_number }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $order->customer_name }}</div>
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $order->customer_phone }}</small>
                            </td>
                            <td class="text-center">
                                <span>{{ $order->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="text-center">
                                <span>{{ $order->valid_until->format('d/m/Y') }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-primary">Rs.{{ number_format($order->total_amount, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill small">Sent (Pending)</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-dark px-3 rounded-pill fw-bold me-2" wire:click="viewOrder({{ $order->id }})">
                                    <i class="bi bi-eye me-1"></i> View Details
                                </button>
                                <button class="btn btn-sm btn-primary px-3 rounded-pill fw-bold" wire:click="openCreateSaleModal({{ $order->id }})">
                                    <i class="bi bi-arrow-right-circle me-1"></i> Convert to Sale
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 text-muted"></i>
                                No customer order requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    {{-- View Modal --}}
    @if($showViewModal && $selectedOrder)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-header bg-black text-white p-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2 text-gold"></i>Order Details #{{ $selectedOrder->quotation_number }}</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body p-4 bg-white text-dark">
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <strong class="text-muted d-block mb-1">Customer Info:</strong>
                                <div class="p-2 border rounded bg-light">
                                    <div class="fw-bold">{{ $selectedOrder->customer_name }}</div>
                                    <div>{{ $selectedOrder->customer_phone }}</div>
                                    <div class="small">{{ $selectedOrder->customer_address }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <strong class="text-muted d-block mb-1">Order Summary:</strong>
                                <div class="p-2 border rounded bg-light">
                                    <div><strong>Date:</strong> {{ $selectedOrder->created_at->format('d/m/Y H:i') }}</div>
                                    <div><strong>Valid Limit:</strong> {{ $selectedOrder->valid_until->format('d/m/Y') }}</div>
                                    <div class="text-primary fw-bold"><strong>Total Value:</strong> Rs.{{ number_format($selectedOrder->total_amount, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">Order Items</h6>
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr style="font-size: 0.75rem;">
                                    <th>Item Details</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedOrder->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $item['product_name'] }}</div>
                                            <small class="text-muted">{{ $item['product_code'] }}</small>
                                        </td>
                                        <td class="text-center">{{ $item['quantity'] }}</td>
                                        <td class="text-end">Rs.{{ number_format($item['unit_price'], 2) }}</td>
                                        <td class="text-end fw-bold">Rs.{{ number_format($item['total'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary px-4 rounded-pill" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Convert to Sale Modal --}}
    @if($createSaleModal && $selectedOrder)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-header bg-black text-white p-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-cart-plus me-2 text-gold"></i>Convert Customer Order #{{ $selectedOrder->quotation_number }} to Sale</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeCreateSaleModal"></button>
                    </div>
                    <div class="modal-body p-4 bg-white text-dark">
                        <table class="table align-middle">
                            <thead class="table-light text-uppercase small">
                                <tr>
                                    <th>Product Details</th>
                                    <th class="text-center" width="100">Stock</th>
                                    <th class="text-center" width="100">Ordered Qty</th>
                                    <th class="text-center" width="120">Selling Qty</th>
                                    <th class="text-end" width="140">Unit Price</th>
                                    <th class="text-end" width="120">Discount (Rs)</th>
                                    <th class="text-end pe-3" width="140">Subtotal</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($editableItems as $index => $item)
                                    <tr wire:key="edit-{{ $index }}">
                                        <td>
                                            <div class="fw-bold">{{ $item['product_name'] }}</div>
                                            <small class="text-muted">{{ $item['product_code'] }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $item['current_stock'] }}</span>
                                        </td>
                                        <td class="text-center text-muted">
                                            {{ $item['original_quantity'] }}
                                        </td>
                                        <td class="text-center">
                                            <input type="number" class="form-control form-control-sm text-center fw-bold"
                                                style="width: 80px; margin: 0 auto;"
                                                value="{{ $item['quantity'] }}" min="1" max="{{ $item['current_stock'] }}"
                                                wire:change="updateItemQuantity({{ $index }}, $event.target.value)">
                                        </td>
                                        <td class="text-end">
                                            Rs.{{ number_format($item['unit_price'], 2) }}
                                        </td>
                                        <td class="text-center">
                                            <input type="number" class="form-control form-control-sm text-end"
                                                style="width: 100px; margin: 0 auto;"
                                                value="{{ $item['discount_per_unit'] }}" min="0" step="0.01"
                                                wire:change="updateItemDiscount({{ $index }}, $event.target.value)">
                                        </td>
                                        <td class="text-end pe-3 fw-bold">
                                            Rs.{{ number_format($item['total'], 2) }}
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm text-danger border-0 p-1" wire:click="removeItem({{ $index }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="row mt-4">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-muted">Additional notes / Remarks</label>
                                <textarea class="form-control" wire:model="saleData.notes" rows="2"></textarea>
                            </div>
                            <div class="col-md-4 bg-light p-3 rounded border">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span class="fw-bold">Rs.{{ number_format($subtotal, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-danger">
                                    <span>Discount:</span>
                                    <span class="fw-bold">- Rs.{{ number_format($totalDiscount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2 fw-black text-primary fs-5">
                                    <span>Grand Total:</span>
                                    <span>Rs.{{ number_format($grandTotal, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary px-4 rounded-pill" wire:click="closeCreateSaleModal">Cancel</button>
                        <button type="button" class="btn btn-primary px-4 rounded-pill fw-bold" wire:click="createSale">Confirm & Create Sale</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
