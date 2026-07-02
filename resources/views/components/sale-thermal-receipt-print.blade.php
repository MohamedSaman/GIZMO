<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt - {{ $sale->invoice_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 80mm;
            background: #fff;
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            font-weight: bold;
        }
        .receipt-container {
            width: 80mm;
            margin: 0;
            padding: 3mm;
            box-sizing: border-box;
        }
        .receipt-table {
            border: 1px solid #000;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .receipt-table th,
        .receipt-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: top;
        }
        @media print {
            html, body { margin: 0; padding: 0; width: 80mm; }
        }
        @media screen {
            body { background: #f0f0f0; display: flex; justify-content: center; padding: 20px 0; }
            .receipt-container { background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.15); }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
</head>
<body>
    <div class="receipt-container">
        <!-- Thermal Receipt Header -->
        <div style="text-align:center; font-weight:bold; padding-top: 3mm;">
            <div style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 5px 0; margin-bottom: 5px; font-size: 16px; text-transform: uppercase;">
                GIZMO ELECTRONICS
            </div>
            <div style="font-size: 11px;">No-10 Keyzer Street, Colombo 11</div>
            <div style="font-size: 11px;">Tel: 0777005897 / 0112337242</div>
            <div style="font-size: 11px;">Hotline: 0112337242</div>
        </div>

        <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

        <!-- Receipt Meta -->
        <div style="font-size: 11px; font-weight: bold;">
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Receipt No</span>
                <span>: {{ $sale->invoice_number }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Date</span>
                <span>: {{ $sale->created_at->format('d-m-Y') }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Time</span>
                <span>: {{ $sale->created_at->format('h:i A') }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Cashier</span>
                <span>: {{ $sale->user->name ?? 'Admin' }}</span>
            </div>
            @if($sale->customer && $sale->customer->name !== 'Walking Customer')
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Customer</span>
                <span>: {{ $sale->customer->name }}</span>
            </div>
            @elseif($sale->walking_customer_name)
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Customer</span>
                <span>: {{ $sale->walking_customer_name }}</span>
            </div>
            @endif
        </div>

        <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

        <!-- Table Header & Items -->
        <table class="receipt-table" style="width: 100%; margin-top: 5px; margin-bottom: 5px;">
            <thead>
                <tr>
                    <th style="text-align: left; font-size: 11px; font-weight: bold; width: 56%;">Item</th>
                    <th style="text-align: center; font-size: 11px; font-weight: bold; width: 14%;">Qty</th>
                    <th style="text-align: right; font-size: 11px; font-weight: bold; width: 30%;">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr>
                    <td style="font-size: 11px; font-weight: bold; text-align: left; word-break: break-word;">
                        {{ $index + 1 }}. {{ $item->product_name }}
                        @if($item->has_warranty)
                        <div style="font-size: 9px; font-weight: bold; font-style: italic; color: #000; margin-top: 1px;">({{ $item->warranty_duration }} Warranty)</div>
                        @endif
                        @if($item->discount_per_unit > 0)
                        <div style="font-size: 9px; font-weight: bold; color: #000; margin-top: 2px;">
                            Disc: {{ number_format($item->discount_per_unit, 0) }}
                            @if($item->discount_percentage > 0) ({{ number_format($item->discount_percentage, 0) }}%) @endif
                        </div>
                        @endif
                    </td>
                    <td style="border: 1px solid #000; padding: 4px 5px; font-size: 11px; font-weight: bold; text-align: center; vertical-align: top;">
                        {{ $item->quantity }}
                    </td>
                    <td style="border: 1px solid #000; padding: 4px 5px; font-size: 11px; font-weight: bold; text-align: right; vertical-align: top;">
                        {{ number_format($item->unit_price, 0) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

        <!-- Totals -->
        <div style="font-size: 11px;">
            @php
            $originalSubtotal = $sale->items->sum(fn($i) => $i->unit_price * $i->quantity);
            $totalDiscountRs = $sale->discount_amount;
            $totalDiscPercentage = $originalSubtotal > 0 ? ($totalDiscountRs / $originalSubtotal * 100) : 0;
            @endphp
            <div style="display: flex; justify-content: space-between; margin-bottom: 2px; font-weight: bold;">
                <span>Total Items / Qty</span>
                <span>{{ $sale->items->count() }} / {{ $sale->items->sum('quantity') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 2px; font-weight: bold;">
                <span>Sub Total</span>
                <span>{{ number_format($originalSubtotal, 0) }}</span>
            </div>
            @if($totalDiscountRs > 0)
            <div style="display: flex; justify-content: space-between; margin-bottom: 2px; font-weight: bold;">
                <span>Discount @if($totalDiscPercentage > 0)({{ number_format($totalDiscPercentage, 1) }}%)@endif</span>
                <span>-{{ number_format($totalDiscountRs, 0) }}</span>
            </div>
            @endif

            <div style="border-bottom: 1px dashed #000; margin: 5px 0;"></div>

            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px;">
                <span>TOTAL</span>
                <span>{{ number_format($sale->total_amount, 0) }}</span>
            </div>

            <div style="border-bottom: 1px dashed #000; margin: 5px 0;"></div>

            @php
            $totalReceived = $sale->payments->sum(function($p) {
                return $p->amount_tendered ?? $p->amount;
            });
            $balanceAmount = max(0, $totalReceived - $sale->total_amount);
            @endphp
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                <span>RECEIVED</span>
                <span>{{ number_format($totalReceived, 0) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                <span>BALANCE</span>
                <span>{{ number_format($balanceAmount, 0) }}</span>
            </div>
            @if($sale->due_amount > 0)
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                <span>DUE AMOUNT</span>
                <span>{{ number_format($sale->due_amount, 0) }}</span>
            </div>
            @endif

            <div style="border-bottom: 1px dashed #000; margin: 5px 0;"></div>
        </div>

        <!-- Payment Method -->
        <div style="font-size: 11px; margin-bottom: 5px;">
            @if($sale->payments && $sale->payments->count() > 0)
            @foreach($sale->payments as $payment)
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 110px; font-weight: bold;">Payment Method</span>
                <span>: {{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}</span>
            </div>
            @endforeach
            @else
            @if($sale->payment_type == 'full')
            <div style="display: flex;">
                    <td style="font-size: 11px; font-weight: bold; text-align: center;">
                <span>: CASH</span>
            </div>
                    <td style="font-size: 11px; font-weight: bold; text-align: right;">
            <div style="display: flex;">
                <span style="width: 110px; font-weight: bold;">Payment Method</span>
                <span>: DUE</span>
            </div>
            @endif
            @endif
            <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
        </div>

        <!-- Barcode -->
        <div style="display: flex; justify-content: center; margin: 8px 0;">
            <svg id="barcode"></svg>
        </div>

        <!-- Footer -->
        <div style="text-align: center; font-size: 11px; margin-top: 10px;">
            <div style="font-weight: bold;">Thank You for Your Visit!</div>
            <div style="font-weight: bold;">Return will be accepted within 3 days</div>
            <div style="font-weight: bold;">Visit Us Again</div>
            <div style="border-top: 1px solid #000; margin-top: 8px; padding-top: 2px;"></div>
            <div style="border-top: 1px solid #000; margin-top: 2px;"></div>
        </div>
    </div>

    <script>
        window.onload = function() {
            // Render barcode
            try {
                JsBarcode("#barcode", "{{ $sale->invoice_number }}", {
                    format: 'CODE128',
                    width: 1.5,
                    height: 60,
                    displayValue: true,
                    fontSize: 14,
                    background: '#ffffff',
                    lineColor: '#000000',
                    margin: 10
                });
            } catch(e) {}

            // Auto-print after short delay
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
