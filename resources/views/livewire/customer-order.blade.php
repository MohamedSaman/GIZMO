<div class="container py-4">
    {{-- Header Banner --}}
    <div class="bg-black text-white p-4 rounded-4 mb-4 border-gold shadow-lg d-flex flex-column align-items-center justify-content-center text-center">
        <div class="header-icon mb-2">
            <i class="bi bi-bag-check-fill fs-2"></i>
        </div>
        <h2 class="fw-bold text-uppercase tracking-wider" style="letter-spacing: 2px; color: white;">Order Placement</h2>
        @if($selectedCustomer)
            <p class="mb-0 text-gold fw-bold">Customer Portal: Welcome, {{ $selectedCustomer->name }}!</p>
            <p class="mb-0 opacity-70">Target Delivery Date: {{ \Carbon\Carbon::parse($validUntil)->format('d/m/Y') }}</p>
        @else
            <p class="mb-0 opacity-70 text-danger fw-bold"><i class="bi bi-exclamation-triangle me-1"></i>Warning: No valid customer selected. Please use your custom ordering link.</p>
        @endif
    </div>

    @if($orderSubmitted)
        {{-- Success Screen --}}
        <div class="card premium-card text-center border-0 shadow-lg py-5 px-4 mb-5">
            <div class="card-body">
                <div class="success-icon mb-4">
                    <i class="bi bi-check2-circle display-1 text-success animate-bounce"></i>
                </div>
                <h3 class="fw-black text-dark mb-2">Order Request Placed Successfully!</h3>
                <p class="text-muted mb-4 fs-6">Thank you for submitting your request. Your order code is <strong class="text-primary">#{{ $createdOrderNo }}</strong>. We have logged it on our system and will review it shortly.</p>
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-gold-premium btn-lg px-5 py-3 rounded-pill text-white" wire:click="resetOrder">
                        <i class="bi bi-cart-plus me-2"></i>View Catalog Again
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="row g-4">
            {{-- Catalog Section (Left) --}}
            <div class="col-lg-7">
                {{-- Search & Filters --}}
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-light mb-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm border-gold rounded-pill ps-3"
                                wire:model.live="search" placeholder="Search catalog...">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm border-gold rounded-pill" wire:model.live="selectedCategoryId">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm border-gold rounded-pill" wire:model.live="selectedTypeId">
                                <option value="">All Product Types</option>
                                @foreach($productTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Products Grid --}}
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-grid-fill me-2 text-primary"></i>Available Products</h5>
                    <div class="row g-3">
                        @forelse($products as $product)
                            <div class="col-sm-6">
                                <div class="card h-100 border rounded-3 product-card bg-white shadow-xs overflow-hidden transition-all">
                                    <div class="position-relative bg-light text-center" style="height: 120px; overflow: hidden;">
                                        <img src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}" class="img-fluid h-100 object-fit-cover">
                                        <span class="position-absolute bottom-0 end-0 bg-black text-white px-2 py-1 small rounded-start fw-bold" style="font-size: 0.75rem;">
                                            Rs.{{ number_format($product['price'], 2) }}
                                        </span>
                                    </div>
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">{{ $product['name'] }}</h6>
                                        <div class="d-flex gap-1 mb-2">
                                            <span class="badge bg-secondary-soft text-secondary rounded px-2" style="font-size: 0.6rem;">{{ $product['category_name'] }}</span>
                                            <span class="badge bg-primary-soft text-primary rounded px-2" style="font-size: 0.6rem;">{{ $product['type'] }}</span>
                                        </div>
                                        <div class="text-muted small mb-3" style="font-size: 0.7rem;">
                                            <div>Code: {{ $product['code'] }}</div>
                                            <div>Model: {{ $product['model'] }}</div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-bold"
                                            wire:click="addToCart({{ json_encode($product) }})">
                                            <i class="bi bi-cart-plus me-1"></i> Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                No products found. Choose other filters or search query.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Cart & Checkout Form (Right) --}}
            <div class="col-lg-5">
                {{-- Cart Overview --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-black text-white py-3 px-4 rounded-top-4 d-flex justify-content-between align-items-center border-bottom border-gold border-opacity-25">
                        <h5 class="mb-0 fw-bold" style="color: white;"><i class="bi bi-cart3 me-2"></i>Your Order Cart</h5>
                        <span class="badge bg-gold text-white rounded-pill px-3">{{ count($cart) }} Items</span>
                    </div>
                    <div class="card-body p-0">
                        @if(empty($cart))
                            <div class="text-center py-5 text-muted bg-white rounded-bottom-4">
                                <i class="bi bi-cart-x display-6 d-block mb-2"></i>
                                Cart is empty
                            </div>
                        @else
                            <div class="table-responsive bg-white" style="max-height: 250px; overflow-y: auto;">
                                <table class="table mb-0 text-dark" style="font-size: 0.85rem;">
                                    <tbody>
                                        @foreach($cart as $index => $item)
                                            <tr>
                                                <td class="ps-3 py-3">
                                                    <div class="fw-bold">{{ $item['name'] }}</div>
                                                    <div class="text-muted small">Rs.{{ number_format($item['price'], 2) }} each</div>
                                                </td>
                                                <td class="py-3 text-center" style="width: 100px;">
                                                    <input type="number" class="form-control form-control-sm text-center border"
                                                        value="{{ $item['quantity'] }}" min="1"
                                                        wire:change="updateQuantity({{ $index }}, $event.target.value)">
                                                </td>
                                                <td class="py-3 text-end fw-bold pe-3" style="width: 120px;">
                                                    Rs.{{ number_format($item['total'], 2) }}
                                                </td>
                                                <td class="pe-3 py-3 text-center" style="width: 40px;">
                                                    <button class="btn btn-link text-danger p-0" wire:click="removeFromCart({{ $index }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center rounded-bottom-4">
                                <span class="fw-bold text-dark fs-6">Subtotal:</span>
                                <span class="fw-black text-primary fs-5">Rs.{{ number_format($this->subtotal, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Action Panel --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-black text-white py-3 px-4 rounded-top-4 border-bottom border-gold border-opacity-25">
                        <h5 class="mb-0 fw-bold" style="color: white;"><i class="bi bi-arrow-right-circle me-2"></i>Place Order Request</h5>
                    </div>
                    <div class="card-body p-4 bg-white text-dark rounded-bottom-4">
                        @if($selectedCustomer)
                            <div class="mb-4 text-center p-3 border rounded bg-light">
                                <h6 class="fw-bold text-dark mb-1">Confirming order for:</h6>
                                <div class="text-primary fw-bold fs-5 mb-1">{{ $selectedCustomer->name }}</div>
                                <div class="text-muted small">{{ $selectedCustomer->phone }}</div>
                                <div class="text-muted small mt-1" style="font-size: 0.75rem;">Address: {{ $selectedCustomer->address }}</div>
                            </div>
                            
                            <button type="button" class="btn btn-gold-premium w-100 py-3 rounded-pill text-white fw-bold d-flex align-items-center justify-content-center gap-2"
                                wire:click="submitOrder" {{ empty($cart) ? 'disabled' : '' }}>
                                <i class="bi bi-file-earmark-arrow-up"></i>
                                <span>SUBMIT ORDER TO GIZMO</span>
                            </button>
                        @else
                            <div class="alert alert-danger mb-0 text-center text-dark">
                                You must use a valid order link generated by the shop administrator.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .border-gold {
            border-color: #c5a02c !important;
        }
        .header-icon {
            width: 50px;
            height: 50px;
            background: #c5a02c;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(197, 160, 44, 0.4);
        }
        .btn-gold-premium {
            background: linear-gradient(135deg, #161b97 0%, #0d0f5e 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(22, 27, 151, 0.3);
            transition: all 0.3s;
        }
        .btn-gold-premium:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(22, 27, 151, 0.5);
        }
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
            border-color: #161b97 !important;
        }
        .bg-secondary-soft {
            background-color: #f1f3f5;
        }
        .bg-primary-soft {
            background-color: #e8f0fe;
        }
        .success-icon i {
            text-shadow: 0 4px 15px rgba(25, 135, 84, 0.2);
        }
    </style>
</div>
