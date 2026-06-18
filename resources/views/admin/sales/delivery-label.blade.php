<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Label - {{ $sale->deliverySale->delivery_barcode ?? 'N/A' }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bree+Serif&family=Fjalla+One&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 6in 4in;
            margin: 1px;
        }

        body {
            width: 6in;
            height: 4in;
            font-family: "Bree Serif", serif;
            color: #000;
            overflow: hidden;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .label-wrapper {
            width: 6in;
            height: 4in;
        }

        .label-border {
            border: 2px solid #000;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 10px;
            border-bottom: 2px solid #000;
        }

        .company-name {
            flex: 1;
            font-size: 30px;
            font-weight: 900;
            line-height: 1.1;
            text-align: center;
        }

        .company-name span {
            font-size: 16px;
            font-weight: 600;
            display: block;
            text-align: center;
            letter-spacing: 4px;
        }

        .meta {
            text-align: right;
            font-weight: 800;
            position: absolute;
            right: 10px;
        }

        .meta .method {
            font-size: 18px;
        }

        .meta .date {
            font-size: 14px;
            margin-top: 2px;
        }

        /* CUSTOMER + BARCODE ROW */
        .row {
            display: flex;
            width: 100%;
        }

        .row-grow {
            flex: 1;
        }

        .customer-section {
            flex: 2;
            padding: 8px 10px;
            border-right: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        .to-label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 0px;
        }

        .customer-line {
            font-size: 17px;
            font-weight: 500;
            line-height: 1.6;
            display: block;
            margin-left: 28px;
        }

        .barcode-section {
            flex: 1;
            padding: 8px;
            text-align: center;
            border-bottom: 2px solid #000;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .barcode-section svg {
            width: 100%;
            height: 70px;
        }

        .barcode-text {
            font-size: 12px;
            margin-top: 3px;
        }

        /* PRICE + ICONS ROW */
        .price-row,
        .icons-row {
            flex: 1;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            padding: 6px;
            border-bottom: 2px solid #000;
        }

        .price-row {
            background: #d1d1d1;
            font-size: 42px;
            font-weight: 900;
        }

        .icons-row {
            justify-content: space-evenly;
            padding: 6px;
        }

        .icon-item {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .icon-box {
            width: 40px;
            height: 40px;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* COD black label style */
        .cod-box {
            width: 70px;
            height: 48px;
            background: #000;
            color: #fff;
            font-weight: 900;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* FOOTER */
        .footer {
            padding: 4px 10px;
            font-size: 16px;
            font-weight: 700;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <div class="label-wrapper">
        <div class="label-border">

            <!-- HEADER -->
            <div class="header">
                <div class="company-name">
                    JAFFNA GOLD COVERING <br><span>World Wide Delivery</span>
                </div>
                <div class="meta">
                    <div class="method">
                        {{ strtoupper($sale->deliverySale->delivery_method ?? 'POST') }}
                    </div>
                    <div class="date">
                        {{ $sale->created_at->format('d.m.Y') }}
                    </div>
                </div>
            </div>

            <!-- CUSTOMER + BARCODE -->
            <div class="row row-grow">
                <div class="customer-section">
                    <div class="to-label">TO :</div>
                    @php
                    $details = $sale->deliverySale->customer_details ?? $sale->customer->address ?? '';
                    // Try to split by common delimiters: pipe, comma, period+space, or newlines
                    $parts = preg_split('/\s*[\|]\s*|\n|\r\n/', $details);
                    // If only one part, try splitting by period followed by space and digits (phone after name)
                    if (count($parts) <= 1) {
                        $parts=preg_split('/(?<=\D)[\.\,]\s*(?=\d)/', $details);
                        }
                        @endphp
                        @foreach($parts as $part)
                        @if(trim($part))
                        <span class="customer-line">{{ trim($part) }}</span>
                        @endif
                        @endforeach
                </div>

                <div class="barcode-section">
                    <svg id="barcode"></svg>
                    <div class="barcode-text">
                        {{ $sale->deliverySale->delivery_barcode }}
                    </div>
                </div>
            </div>

            <!-- PRICE + ICONS -->
            <div class="row">
                <div class="price-row">
                    Rs.{{ number_format($sale->total_amount, 2) }}
                </div>

                <div class="icons-row">

                    <!-- Fragile -->
                    <div class="icon-item">
                        <div class="icon-box">
                            <svg viewBox="0 0 100 100" width="40" height="40">
                                <rect x="5" y="5" width="90" height="90"
                                    fill="none" stroke="black" stroke-width="6" />

                                <!-- Glass -->
                                <path d="M30 25 H70 C70 50 55 60 50 60 
             C45 60 30 50 30 25 Z" fill="black" />

                                <line x1="50" y1="60" x2="50" y2="80"
                                    stroke="black" stroke-width="6" />
                                <line x1="40" y1="80" x2="60" y2="80"
                                    stroke="black" stroke-width="6" />
                            </svg>
                        </div>
                    </div>

                    <!-- Recycle -->
                    <div class="icon-item">
                        <div class="icon-box">
                            <svg viewBox="0 0 100 100" width="40" height="40">
                                <rect x="5" y="5" width="90" height="90"
                                    fill="none" stroke="black" stroke-width="6" />

                                <polygon points="50,20 60,40 40,40" fill="black" />
                                <polygon points="20,60 40,55 35,75" fill="black" />
                                <polygon points="80,60 65,75 60,55" fill="black" />
                            </svg>
                        </div>
                    </div>

                    <!-- This Side Up -->
                    <div class="icon-item">
                        <div class="icon-box">
                            <svg viewBox="0 0 100 100" width="40" height="40">
                                <!-- Outer Border -->
                                <rect x="5" y="5" width="90" height="90"
                                    fill="none" stroke="black" stroke-width="6" />

                                <!-- Left Arrow -->
                                <line x1="35" y1="75" x2="35" y2="35"
                                    stroke="black" stroke-width="8" />
                                <polygon points="35,20 20,40 50,40" fill="black" />

                                <!-- Right Arrow -->
                                <line x1="65" y1="75" x2="65" y2="35"
                                    stroke="black" stroke-width="8" />
                                <polygon points="65,20 50,40 80,40" fill="black" />
                            </svg>
                        </div>
                    </div>

                    <!-- COD -->
                    @if($sale->deliverySale && $sale->deliverySale->payment_method === 'Cash on Delivery')
                    <div class="icon-item">
                        <div class="cod-box">
                            COD
                        </div>
                    </div>
                    @else
                    <div class="icon-item">
                        <div class="cod-box">
                            PAID
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <!-- FOOTER -->
            <div class="footer">
                <div>NO: 237 KKS ROAD, JAFFNA | NO: 37 NEW MARKET JAFFNA</div>
                <div>076 1919 650 | 077 5287 556</div>
                <div>Return will be accepted within 3 days</div>
                <div>Thank you for your purchase!</div>
            </div>

        </div>
    </div>

    <script>
        JsBarcode("#barcode", "{{ $sale->deliverySale->delivery_barcode ?? 'N/A' }}", {
            format: "CODE128",
            width: 2,
            height: 70,
            displayValue: false,
            margin: 0
        });

        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>