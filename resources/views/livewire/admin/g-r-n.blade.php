<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-clipboard-check-fill text-success me-2"></i> Goods Received Notes
            </h3>
            <p class="text-muted mb-0">Process and track incoming orders from suppliers</p>
        </div>
    </div>

    <div class="container-fluid p-4">
        <!-- Summary Cards -->
        <div class="row mb-2">
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card summary-card awaiting h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-container bg-warning bg-opacity-10 me-3">
                                <i class="bi bi-box-seam text-warning fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Awaiting Receipt</p>
                                <h4 class="fw-bold mb-0">{{ $this->getOrderCounts()['received'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card summary-card received h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-container bg-success bg-opacity-10 me-3">
                                <i class="bi bi-archive-fill text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Fully Received</p>
                                <h4 class="fw-bold mb-0">{{ $this->getOrderCounts()['complete'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        
        </div>

        <!-- Purchase Orders Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-list-task text-primary me-2"></i> Purchase Orders
                    </h5>
                    <p class="text-muted small mb-0">Process incoming orders from suppliers</p>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="width: 60%; margin: auto">
                <!-- 🔍 Search Bar -->
                    <div class="search-bar flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" wire:model.live="search"
                                placeholder="Search by order code or supplier name...">
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-sm text-muted fw-medium">Show</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="500">500</option>
                    </select>
                    <span class="text-sm text-muted">entries</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table" style="color: black !important;">
                            <tr>
                                <th class="ps-4">Received Date</th>
                                <th>Supplier</th>
                                <th class="text-center">Combined Orders</th>
                                <th class="text-end pe-4">Total Amount</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                                
                              
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrders as $po)
                            <tr class="cursor-pointer" wire:click="viewGRN({{ $po->supplier_id }}, '{{ $po->received_date }}')">
                                <td class="ps-4">
                                    <span class="fw-medium text-dark">{{ \Carbon\Carbon::parse($po->received_date)->format('M d, Y') }}</span>
                                </td>
                                <td>{{ $po->supplier->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $po->po_count }} Orders</span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="fw-bold">Rs. {{ number_format($po->total_sum, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">Received</span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" wire:click.stop="viewGRN({{ $po->supplier_id }}, '{{ $po->received_date }}')">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
               <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $purchaseOrders->links('livewire.custom-pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- View GRN Details Modal -->
    <div wire:ignore.self class="modal fade" id="viewGrnModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-eye text-white me-2"></i> GRN Details - {{ $selectedSupplier?->name }} ({{ \Carbon\Carbon::parse($selectedReceivedDate)->format('M d, Y') }})
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedSupplier)
                    <div class="row mb-3 bg-light p-3 rounded">
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small uppercase fw-bold">Supplier</p>
                            <p class="mb-0 fw-bold">{{ $selectedSupplier->name }}</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <p class="mb-1 text-muted small uppercase fw-bold">Received Date</p>
                            <p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($selectedReceivedDate)->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <p class="mb-1 text-muted small uppercase fw-bold">Combined Orders</p>
                            <p class="mb-0">
                                @foreach($groupedPOs as $po)
                                    <span class="badge bg-primary">{{ $po->order_code }}</span>
                                @endforeach
                            </p>
                        </div>
                    </div>

                    <h5>Received Items</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>PO #</th>
                                    <th>Product</th>
                                    <th>Ordered Qty</th>
                                    <th>Received Qty</th>
                                    <th>Supplier Price</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grnItems as $item)
                                <tr class="
                                    @if(strtolower($item['status'] ?? '') === 'received') table-success
                                    @elseif(strtolower($item['status'] ?? '') === 'notreceived') table-danger
                                    @endif
                                ">
                                    <td><span class="badge bg-light text-dark fw-bold">{{ $item['order_code'] }}</span></td>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['ordered_qty'] ?? 0 }}</td>
                                    <td>{{ $item['received_qty'] ?? 0 }}</td>
                                    <td>Rs. {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                                    <td>
                                        @php
                                            $discountType = $item['discount_type'] ?? 'rs';
                                            $discount = floatval($item['discount'] ?? 0);
                                            $unitPrice = floatval($item['unit_price'] ?? 0);
                                            $receivedQty = floatval($item['received_qty'] ?? 0);
                                            
                                            if ($discountType === 'percent') {
                                                // Total discount amount
                                                $totalDiscountAmount = (($receivedQty * $unitPrice) * $discount) / 100;
                                            } else {
                                                $totalDiscountAmount = $discount;
                                            }
                                        @endphp
                                        Rs. {{ number_format($totalDiscountAmount, 2) }}
                                        @if($discountType === 'percent')
                                        <br><small class="text-muted">({{ number_format($discount, 2) }}%)</small>
                                        @endif
                                    </td>
                                    <td>Rs. {{ number_format($this->calculateItemTotal($item), 2) }}</td>
                                    <td>
                                        <span class="badge 
                                            @if(strtolower($item['status'] ?? '') === 'received') bg-success
                                            @elseif(strtolower($item['status'] ?? '') === 'notreceived') bg-danger
                                            @else bg-warning
                                            @endif
                                        ">
                                            {{ ucfirst($item['status'] ?? 'pending') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="6" class="text-end">Grand Total:</td>
                                    <td>
                                        @php
                                            $grandTotal = 0;
                                            foreach($grnItems as $item) {
                                                $grandTotal += $this->calculateItemTotal($item);
                                            }
                                        @endphp
                                        Rs. {{ number_format($grandTotal, 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        
    .container-fluid,
    .card,
    .modal-content {
        font-size: 13px !important;
    }

    .table th,
    .table td {
        font-size: 12px !important;
        padding: 0.35rem 0.5rem !important;
    }
     .modal-header{
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        margin-bottom: 0.25rem !important;
     }
   
    .modal-footer,
    .card-header,
    .card-body,
    .row,
    .col-md-6,
    .col-md-4,
    .col-md-2,
    .col-md-12 {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        margin-top: 0.25rem !important;
        margin-bottom: 0.25rem !important;
    }

    .form-control,
    .form-select {
        font-size: 12px !important;
        padding: 0.35rem 0.5rem !important;
    }

    .btn,
    .btn-sm,
    .btn-primary,
    .btn-secondary,
    .btn-outline-danger,
    .btn-outline-secondary {
        font-size: 12px !important;
        padding: 0.25rem 0.5rem !important;
    }

    .badge {
        font-size: 11px !important;
        padding: 0.25em 0.5em !important;
    }

    .list-group-item,
    .dropdown-item {
        font-size: 12px !important;
        padding: 0.35rem 0.5rem !important;
    }

    .summary-card,
    .card {
        border-radius: 8px !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
    }

    .icon-container {
        width: 36px !important;
        height: 36px !important;
        font-size: 1.1rem !important;
    }
        .summary-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        }

        .summary-card.awaiting {
            border-left-color: #ffc107;
        }

        .summary-card.received {
            border-left-color: #28a745;
        }

        .icon-container {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px 12px 0 0 !important;
            padding: 1.25rem 1.5rem;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            
            
            
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }

        .summary-card.total {
            border-left-color: #2a83df;
        }

        .btn-link {
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-link:hover {
            transform: scale(1.1);
        }

        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            border-color: #4361ee;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #4361ee;
            border-color: #4361ee;
        }

        .btn-primary:hover {
            background-color: #3f37c9;
            border-color: #3f37c9;
            transform: translateY(-2px);
        }

        /* Product Search Dropdown Styling */
        .product-search {
            position: relative;
        }

        .list-group-item-action:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .list-group {
            border-radius: 8px;
            overflow: hidden;
        }

        .list-group-item {
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .list-group-item:hover {
            background-color: #e3f2fd;
            transform: translateX(5px);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Handle success alerts
        window.addEventListener('alert', event => {
            Swal.fire('Success', event.detail.message, 'success');
            var modalEl = document.getElementById('grnModal');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
        });

        // Open GRN modal after data is loaded
        Livewire.on('open-grn-modal', () => {
            var modalEl = document.getElementById('grnModal');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        });

        // Open View GRN modal after data is loaded
        Livewire.on('open-view-grn-modal', () => {
            var modalEl = document.getElementById('viewGrnModal');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    </script>
    @endpush
</div>