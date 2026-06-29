<div>
    @push('styles')
        <style>
            /* Refined Dashboard Styles - Matching Admin Design */
            .btn-jg-blue {
                background-color: var(--primary);
                color: white;
                border: none;
                transition: all 0.3s;
            }

            .btn-jg-blue:hover {
                background-color: var(--primary-600);
                color: white;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(22, 27, 151, 0.2);
            }

            .stat-card {
                position: relative;
                overflow: hidden;
                background-color: #ffffff !important;
                border-radius: var(--radius-lg);
                border: 1px solid var(--border);
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
                padding: 1.5rem;
                height: 100%;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex;
                flex-direction: column;
            }

            .stat-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
                border-color: var(--primary-100);
            }

            .stat-card .icon-bg {
                position: absolute;
                right: -15px;
                bottom: -15px;
                font-size: 5rem;
                opacity: 0.04;
                transform: rotate(-15deg);
                pointer-events: none;
                color: var(--primary);
            }

            .stat-value {
                font-size: 1.85rem;
                font-weight: 700;
                color: var(--text-main);
                margin-bottom: 0.5rem;
                letter-spacing: -0.01em;
            }

            .stat-label {
                color: var(--text-muted);
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.7rem;
                letter-spacing: 0.08em;
                margin-bottom: 0.75rem;
            }

            .chart-card,
            .widget-container {
                background-color: #ffffff !important;
                border-radius: var(--radius-lg);
                border: 1px solid var(--border);
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
                padding: 1.5rem;
                height: 100%;
            }

            .chart-header {
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid var(--border-light);
            }

            .inventory-item {
                padding: 14px;
                border-radius: 12px;
                transition: all 0.2s;
                border: 1px solid var(--border-light);
                background-color: #fcfcfc;
            }

            .inventory-item:hover {
                background-color: #ffffff;
                border-color: var(--primary-100);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            }

            .status-badge {
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 0.7rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .in-stock {
                background: #e0f2fe;
                color: #0369a1;
            }

            .low-stock {
                background: #fff7ed;
                color: #9a3412;
            }

            .out-of-stock {
                background: #fee2e2;
                color: #991b1b;
            }

            .progress {
                height: 8px;
                border-radius: 4px;
                background: #f1f5f9;
            }

            .fw-600 {
                font-weight: 600;
            }

            .fw-700 {
                font-weight: 700;
            }

            .chart-container {
                position: relative;
                height: 350px;
                padding: 1rem;
            }
        </style>
    @endpush

    <!-- Overview Content -->
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="row align-items-center mb-4">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">
                    <i class="bi bi-speedometer2 text-jg-blue me-2"></i> Shop Staff Dashboard
                </h3>
                <p class="text-muted mb-0">Overview of your sales performance and current inventory.</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('shop-staff.store-billing') }}"
                    class="btn btn-jg-blue px-4 py-2 rounded-3 d-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-cart-plus fs-5"></i>
                    <span class="fw-600">New Billing</span>
                </a>
            </div>
        </div>

        <!-- Stats Cards Row -->
        <div class="row mb-4">
            <!-- Card 1: Today's Sale -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-graph-up-arrow icon-bg"></i>
                    <div class="stat-label mb-2">Today's Sale</div>
                    <div class="stat-value mb-3">Rs.{{ number_format($todaySales, 0) }}</div>

                    <div class="progress mb-2">
                        <div class="progress-bar bg-jg-blue" style="width: {{ min($salePercentage, 100) }}%;"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Total Sales</small>
                        <span class="badge bg-white text-jg-blue border border-jg-blue border-opacity-25 px-2 py-1">
                            Rs.{{ number_format($totalStaffSales, 0) }}
                        </span>
                    </div>
                    <button wire:click="openTodaySummary" class="btn btn-sm btn-outline-jg-blue w-100 rounded-2 mt-auto">
                        <i class="bi bi-eye me-1"></i> View Today Summary
                    </button>
                </div>
            </div>

            <!-- Card 2: Today's Cash -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-currency-dollar icon-bg"></i>
                    <div class="stat-label mb-2">Cash Amount (Today)</div>
                    <div class="stat-value mb-3">Rs.{{ number_format($todayCashAmount, 0) }}</div>

                    @php
                        $cashPercentage = $cashAmount > 0 ? round(($todayCashAmount / $cashAmount) * 100, 1) : 0;
                    @endphp

                    <div class="progress mb-2">
                        <div class="progress-bar bg-success" style="width: {{ min($cashPercentage, 100) }}%;"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Total Cash Collected</small>
                        <span
                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                            Rs.{{ number_format($cashAmount, 0) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Total Stock -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-boxes icon-bg"></i>
                    <div class="stat-label mb-2">System Stock</div>
                    <div class="stat-value mb-3">{{ number_format($availableStock) }} <span
                            class="fs-6 text-muted fw-normal">units</span></div>

                    <div class="progress mb-2">
                        <div class="progress-bar bg-info" style="width: {{ $availablePercentage }}%;"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Total Capacity</small>
                        <span
                            class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                            {{ number_format($totalStock) }} units
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Recent Activity (Invoices) -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-receipt icon-bg"></i>
                    <div class="stat-label mb-2">Recent Invoices</div>
                    <div class="stat-value mb-3">{{ count($recentSales) }}</div>

                    <div class="text-muted small mt-auto">
                        <i class="bi bi-clock-history me-1"></i> Showing last 5 sales
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('shop-staff.products') }}"
                            class="btn btn-sm btn-outline-jg-blue w-100 rounded-2">
                            View All Products
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Section: Chart and Low Stock -->
        <div class="row">
            <!-- Sales Trend Chart -->
            <div class="col-lg-7 mb-4">
                <div class="chart-card">
                    <div class="chart-header">
                        <h6 class="fw-bold text-dark mb-1">Monthly Sales Trend</h6>
                        <p class="text-muted small mb-0">Sales performance over the last 30 days</p>
                    </div>
                    <div class="chart-container" 
                         wire:ignore 
                         x-data="{ 
                            chartData: {{ json_encode($dailySales) }},
                            init() {
                                this.$nextTick(() => {
                                    this.renderChart();
                                });
                            },
                            renderChart() {
                                const chartElement = document.getElementById('staffSalesChart');
                                if (!chartElement || typeof Chart !== 'function') return;
                                
                                const labels = this.chartData.map(item => item.date);
                                const totals = this.chartData.map(item => item.total_sales);
                                
                                const ctx = chartElement.getContext('2d');
                                
                                if (window.staffSalesChart) {
                                    window.staffSalesChart.destroy();
                                }
                                
                                window.staffSalesChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                            label: 'Sales (Rs.)',
                                            data: totals,
                                            backgroundColor: 'rgba(22, 27, 151, 0.7)',
                                            borderColor: '#161b97',
                                            borderWidth: 1,
                                            borderRadius: 4,
                                            barPercentage: 0.7,
                                            categoryPercentage: 0.8
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: { display: false }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                grid: { color: '#f3f4f6' },
                                                ticks: {
                                                    callback: function (value) {
                                                        return 'Rs.' + (value >= 1000 ? (value / 1000) + 'k' : value);
                                                    }
                                                }
                                            },
                                            x: {
                                                grid: { display: false },
                                                ticks: {
                                                    maxRotation: 45,
                                                    minRotation: 45,
                                                    font: { size: 9 }
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                         }">
                        <canvas id="staffSalesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="col-lg-5 mb-4">
                <div class="widget-container h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Low Stock Alerts</h6>
                            <p class="text-muted small mb-0">Items needing attention</p>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-2 rounded-circle">
                            <i class="bi bi-exclamation-triangle text-warning fs-5"></i>
                        </div>
                    </div>

                    <div class="inventory-container custom-scrollbar">
                        @forelse($productInventory as $product)
                            @php
                                $stockPercentage = $product->total_stock > 0 ? round(($product->available_stock / $product->total_stock) * 100, 2) : 0;
                                $statusClass = $product->available_stock == 0 ? 'out-of-stock' : 'low-stock';
                                $statusText = $product->available_stock == 0 ? 'Out of Stock' : 'Low Stock';
                                $progressClass = $product->available_stock == 0 ? 'bg-danger' : 'bg-warning';
                            @endphp
                            <div class="inventory-item mb-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                                        <div class="text-muted small">Code: {{ $product->code }} | Brand:
                                            {{ $product->brand ?? 'N/A' }}</div>
                                    </div>
                                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar {{ $progressClass }}"
                                            style="width: {{ max($stockPercentage, 5) }}%;"></div>
                                    </div>
                                    <small class="text-muted fw-500"
                                        style="min-width: 45px;">{{ $product->available_stock }}/{{ $product->total_stock }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="bi bi-check2-circle text-success fs-1 mb-3 d-block"></i>
                                <p class="text-muted">All stock levels are healthy.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales Table -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="widget-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-dark mb-0">Your Recent Sales</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-3 py-3">Invoice</th>
                                    <th class="border-0 py-3">Customer</th>
                                    <th class="border-0 py-3">Date</th>
                                    <th class="border-0 py-3">Amount</th>
                                    <th class="border-0 py-3">Due</th>
                                    <th class="border-0 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                    <tr>
                                        <td class="px-3">
                                            <span class="fw-bold">#{{ $sale->invoice_number }}</span>
                                        </td>
                                        <td>{{ $sale->customer_name }}</td>
                                        <td>{{ Carbon\Carbon::parse($sale->created_at)->format('M d, Y h:i A') }}</td>
                                        <td class="fw-bold">Rs.{{ number_format($sale->total_amount, 2) }}</td>
                                        <td class="text-danger">Rs.{{ number_format($sale->due_amount, 2) }}</td>
                                        <td>
                                            <span
                                                class="badge rounded-pill {{ $sale->payment_status == 'paid' ? 'bg-success' : ($sale->payment_status == 'partial' ? 'bg-warning' : 'bg-danger') }} bg-opacity-10 {{ $sale->payment_status == 'paid' ? 'text-success' : ($sale->payment_status == 'partial' ? 'text-warning' : 'text-danger') }} border {{ $sale->payment_status == 'paid' ? 'border-success' : ($sale->payment_status == 'partial' ? 'border-warning' : 'border-danger') }} border-opacity-25 px-3">
                                                {{ ucfirst($sale->payment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No sales found for today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today Summary Modal --}}
    @if($showTodaySummary && $summaryData)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6); z-index: 1050;">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #161b97 0%, #12167d 100%);">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-clipboard-data me-2"></i> Today's Summary
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeTodaySummary"></button>
                </div>
                <div class="modal-body p-4">

                    {{-- Opening Balance --}}
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-safe text-info me-2"></i>
                                <span class="fw-bold text-dark">Opening Balance</span>
                            </div>
                            <span class="fs-5 fw-bold text-info">Rs. {{ number_format($summaryData['openingBalance'], 2) }}</span>
                        </div>
                        <hr class="my-2 opacity-25">
                        <div class="d-flex justify-content-between align-items-center text-muted small">
                            <span>Opening + Sales Cash + Due Cash</span>
                            <span class="fw-bold text-info">Rs. {{ number_format($summaryData['openingWithReceipts'], 2) }}</span>
                        </div>
                    </div>

                    {{-- Total Sale --}}
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <i class="bi bi-cart-check text-success me-2"></i>
                                <span class="fw-bold text-dark">Total Sale</span>
                                <span class="badge bg-success bg-opacity-10 text-success ms-2">{{ $summaryData['totalSaleCount'] }} orders</span>
                            </div>
                            <span class="fs-5 fw-bold text-success">Rs. {{ number_format($summaryData['totalSale'], 2) }}</span>
                        </div>
                        <hr class="my-2 opacity-25">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="text-center p-2 rounded-2" style="background: rgba(255,255,255,0.7);">
                                    <i class="bi bi-cash-coin text-success d-block mb-1"></i>
                                    <small class="text-muted d-block">Cash (After Due)</small>
                                    <span class="fw-bold text-dark">Rs. {{ number_format($summaryData['cashSale'], 2) }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 rounded-2" style="background: rgba(255,255,255,0.7);">
                                    <i class="bi bi-phone text-primary d-block mb-1"></i>
                                    <small class="text-muted d-block">Online</small>
                                    <span class="fw-bold text-dark">Rs. {{ number_format($summaryData['onlineSale'], 2) }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 rounded-2" style="background: rgba(255,255,255,0.7);">
                                    <i class="bi bi-bank text-info d-block mb-1"></i>
                                    <small class="text-muted d-block">Bank</small>
                                    <span class="fw-bold text-dark">Rs. {{ number_format($summaryData['bankSale'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Expenses --}}
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 1px solid #fecaca;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-wallet2 text-danger me-2"></i>
                                <span class="fw-bold text-dark">Expenses</span>
                            </div>
                            <span class="fs-5 fw-bold text-danger">- Rs. {{ number_format($summaryData['todayExpenses'], 2) }}</span>
                        </div>
                    </div>

                    {{-- Due Amount --}}
                    @if($summaryData['todayDue'] > 0)
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                <span class="fw-bold text-dark">Due Amount</span>
                            </div>
                            <span class="fs-5 fw-bold text-warning">Rs. {{ number_format($summaryData['todayDue'], 2) }}</span>
                        </div>
                    </div>
                    @endif

                    {{-- Received Due Amount (Today) --}}
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%); border: 1px solid #a5f3fc;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <i class="bi bi-wallet2 text-primary me-2"></i>
                                <span class="fw-bold text-dark">Received Due Amount (Today)</span>
                            </div>
                            <span class="fs-5 fw-bold text-primary">Rs. {{ number_format($summaryData['receivedDueTotal'], 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>Cash: Rs. {{ number_format($summaryData['receivedDueCash'], 2) }}</span>
                            <span>Online: Rs. {{ number_format($summaryData['receivedDueOnline'], 2) }}</span>
                            <span>Bank: Rs. {{ number_format($summaryData['receivedDueBank'], 2) }}</span>
                        </div>

                        <div class="progress" style="height: 10px; border-radius: 999px; background: rgba(255,255,255,0.85);">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $summaryData['receivedDueCashPercent'] }}%" aria-valuenow="{{ $summaryData['receivedDueCashPercent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $summaryData['receivedDueOnlinePercent'] }}%" aria-valuenow="{{ $summaryData['receivedDueOnlinePercent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $summaryData['receivedDueBankPercent'] }}%" aria-valuenow="{{ $summaryData['receivedDueBankPercent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    {{-- Net Cash Before Revenue --}}
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <i class="bi bi-calculator text-primary me-2"></i>
                                <span class="fw-bold text-dark">Net Cash (After Expenses)</span>
                            </div>
                            <span class="fs-5 fw-bold text-primary">Rs. {{ number_format($summaryData['netCashAfterExpenses'], 2) }}</span>
                        </div>
                        <small class="text-muted d-block">
                            Cash (After Due) + Received Due Amount - Expenses
                        </small>
                    </div>

                    <hr class="my-3">

                    {{-- Revenue --}}
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border: 1px solid #ddd6fe;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-cash-stack me-2" style="color: #7c3aed;"></i>
                                <span class="fw-bold text-dark">Today Revenue</span>
                                <small class="text-muted ms-1">(Sale - Expenses)</small>
                            </div>
                            <span class="fs-5 fw-bold" style="color: #7c3aed;">Rs. {{ number_format($summaryData['todayRevenue'], 2) }}</span>
                        </div>
                    </div>

                    {{-- Opening + Sales Cash + Due Cash (before profit section) --}}
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1px solid #86efac;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-wallet-fill text-success me-2"></i>
                                <span class="fw-bold text-dark">Opening + Sales Cash + Due Cash</span>
                            </div>
                            <span class="fw-bold text-success" style="font-size: 2rem; line-height: 1;">Rs. {{ number_format($summaryData['openingWithReceipts'], 2) }}</span>
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-calculator me-2"></i>Profit Calculation</h6>

                    {{-- Product Cost --}}
                    <div class="d-flex justify-content-between align-items-center py-2 px-3 rounded-2 mb-2" style="background: #f8fafc;">
                        <span class="text-muted"><i class="bi bi-box-seam me-2"></i>Product Cost (Supplier Price)</span>
                        <span class="fw-bold text-dark">Rs. {{ number_format($summaryData['totalCost'], 2) }}</span>
                    </div>

                    {{-- Gross Profit --}}
                    <div class="d-flex justify-content-between align-items-center py-2 px-3 rounded-2 mb-2" style="background: #f0fdf4;">
                        <span class="text-muted"><i class="bi bi-graph-up me-2"></i>Gross Profit (Sale - Cost)</span>
                        <span class="fw-bold {{ $summaryData['grossProfit'] >= 0 ? 'text-success' : 'text-danger' }}">Rs. {{ number_format($summaryData['grossProfit'], 2) }}</span>
                    </div>

                    {{-- Net Profit --}}
                    <div class="p-3 rounded-3 mt-3" style="background: linear-gradient(135deg, {{ $summaryData['netProfit'] >= 0 ? '#059669' : '#dc2626' }} 0%, {{ $summaryData['netProfit'] >= 0 ? '#047857' : '#b91c1c' }} 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-trophy text-white me-2"></i>
                                <span class="fw-bold text-white">Net Profit</span>
                                <small class="text-white-50 ms-1">(Gross Profit - Expenses)</small>
                            </div>
                            <span class="fs-4 fw-bold text-white">Rs. {{ number_format($summaryData['netProfit'], 2) }}</span>
                        </div>
                    </div>

                </div>
                
                @php
                    $waText = "📊 *Today's Summary (" . date('Y-m-d') . ")*\n\n" .
                              "💰 *Opening Balance:* Rs. " . number_format($summaryData['openingBalance'], 2) . "\n" .
                              "💰 *Opening + Sales Cash + Due Cash:* Rs. " . number_format($summaryData['openingWithReceipts'], 2) . "\n" .
                              "🛒 *Total Sale (" . $summaryData['totalSaleCount'] . " orders):* Rs. " . number_format($summaryData['totalSale'], 2) . "\n" .
                              "   💵 Cash (After Due): Rs. " . number_format($summaryData['cashSale'], 2) . "\n" .
                              "   📱 Online: Rs. " . number_format($summaryData['onlineSale'], 2) . "\n" .
                              "   🏦 Bank: Rs. " . number_format($summaryData['bankSale'], 2) . "\n\n" .
                              "💸 *Expenses:* Rs. " . number_format($summaryData['todayExpenses'], 2) . "\n" .
                              "⚠️ *Due Amount:* Rs. " . number_format($summaryData['todayDue'], 2) . "\n\n" .
                              "💳 *Received Due Amount:* Rs. " . number_format($summaryData['receivedDueTotal'], 2) . "\n" .
                              "   💵 Cash: Rs. " . number_format($summaryData['receivedDueCash'], 2) . "\n" .
                              "   📱 Online: Rs. " . number_format($summaryData['receivedDueOnline'], 2) . "\n" .
                              "   🏦 Bank: Rs. " . number_format($summaryData['receivedDueBank'], 2) . "\n\n" .
                              "🧮 *Net Cash (After Expenses):* Rs. " . number_format($summaryData['netCashAfterExpenses'], 2) . "\n" .
                              "📈 *Today Revenue:* Rs. " . number_format($summaryData['todayRevenue'], 2) . "\n" .
                              "🛍️ *Product Cost:* Rs. " . number_format($summaryData['totalCost'], 2) . "\n" .
                              "💹 *Gross Profit:* Rs. " . number_format($summaryData['grossProfit'], 2) . "\n" .
                              "🏆 *Net Profit:* Rs. " . number_format($summaryData['netProfit'], 2);
                    $waUrl = "https://wa.me/94777005897?text=" . urlencode($waText);
                @endphp

                <div class="modal-footer border-top-0 pt-0 pb-3 px-4 d-flex justify-content-between">
                    <a href="{{ $waUrl }}" target="_blank" class="btn btn-success fw-bold rounded-pill px-4 py-2 me-auto" style="background-color: #25D366; border-color: #25D366; box-shadow: 0 4px 6px rgba(37, 211, 102, 0.2);">
                        <i class="bi bi-whatsapp me-2"></i> Send to WhatsApp
                    </a>
                    <button type="button" class="btn btn-light border-2 rounded-pill px-4 fw-bold" wire:click="closeTodaySummary">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif


</div>