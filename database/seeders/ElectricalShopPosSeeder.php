<?php

namespace Database\Seeders;

use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductSupplier;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ElectricalShopPosSeeder extends Seeder
{
    /**
     * Seed realistic POS demo data for an electrical shop.
     */
    public function run(): void
    {
        $faker = fake();

        // 1. Categories
        $categories = [
            'Lighting',
            'Wiring & Cables',
            'Switches & Sockets',
            'Circuit Protection',
            'Electrical Accessories'
        ];

        $categoryMap = [];
        foreach ($categories as $categoryName) {
            $category = CategoryList::firstOrCreate(['category_name' => $categoryName]);
            $categoryMap[$categoryName] = $category;
        }

        // 2. Brands
        $brands = [
            'Philips',
            'Schneider Electric',
            'Legrand',
            'Panasonic',
            'Havells',
            'Polycab'
        ];

        $brandIds = [];
        foreach ($brands as $brandName) {
            $brandIds[] = BrandList::firstOrCreate(['brand_name' => $brandName])->id;
        }

        // 3. Suppliers
        $suppliers = collect();
        $supplierData = [
            ['name' => 'Philips Lighting Supply', 'company' => 'Philips Distribution'],
            ['name' => 'Schneider Industrial Dist', 'company' => 'Schneider Solutions'],
            ['name' => 'Legrand Core Wholesalers', 'company' => 'Legrand Lanka'],
            ['name' => 'Polycab Cables & Wires', 'company' => 'Polycab Lanka'],
            ['name' => 'Havells Lanka Distributors', 'company' => 'Havells Pvt Ltd'],
        ];

        foreach ($supplierData as $s) {
            $suppliers->push(ProductSupplier::create([
                'name' => $s['name'],
                'businessname' => $s['company'],
                'contact' => $faker->phoneNumber(),
                'address' => $faker->address(),
                'email' => strtolower(str_replace(' ', '', $s['name'])) . '@example.com',
                'phone' => $faker->phoneNumber(),
                'status' => 'active',
                'notes' => 'Supplies authentic electrical goods and equipment.',
                'overpayment' => 0,
            ]));
        }

        // 4. Customers
        $customers = collect();
        for ($i = 1; $i <= 15; $i++) {
            $customerType = $faker->randomElement(['retail', 'wholesale', 'distributor']);

            $customers->push(Customer::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->phoneNumber(),
                'address' => $faker->address(),
                'type' => $customerType,
                'notes' => $faker->sentence(),
                'business_name' => $customerType === 'retail' ? null : $faker->company() . ' Electricals',
                'opening_balance' => 0,
                'due_amount' => 0,
                'total_due' => 0,
                'overpaid_amount' => 0,
            ]));
        }

        // 5. Electrical Products List
        $electricalTemplates = [
            [
                'category' => 'Lighting',
                'brand' => 'Philips',
                'name' => '9W LED Bulb Warm White',
                'type' => 'LED Bulb',
                'specs' => [
                    'voltage' => '220V-240V',
                    'power' => '9W',
                    'warranty' => '2 Years',
                    'material' => 'Plastic / Aluminum',
                    'color' => 'Warm White (3000K)'
                ],
                'price' => 450,
                'stock' => 100
            ],
            [
                'category' => 'Lighting',
                'brand' => 'Philips',
                'name' => '12W LED Bulb Cool Daylight',
                'type' => 'LED Bulb',
                'specs' => [
                    'voltage' => '220V-240V',
                    'power' => '12W',
                    'warranty' => '2 Years',
                    'material' => 'Plastic / Aluminum',
                    'color' => 'Cool Daylight (6500K)'
                ],
                'price' => 600,
                'stock' => 120
            ],
            [
                'category' => 'Wiring & Cables',
                'brand' => 'Polycab',
                'name' => '1.5 Sq mm Single Core Cable (Red)',
                'type' => 'Electrical Cable',
                'specs' => [
                    'voltage' => '1100V',
                    'power' => 'N/A',
                    'warranty' => '15 Years',
                    'material' => 'Copper / PVC',
                    'color' => 'Red'
                ],
                'price' => 4500,
                'stock' => 30
            ],
            [
                'category' => 'Wiring & Cables',
                'brand' => 'Polycab',
                'name' => '2.5 Sq mm Single Core Cable (Black)',
                'type' => 'Electrical Cable',
                'specs' => [
                    'voltage' => '1100V',
                    'power' => 'N/A',
                    'warranty' => '15 Years',
                    'material' => 'Copper / PVC',
                    'color' => 'Black'
                ],
                'price' => 7200,
                'stock' => 25
            ],
            [
                'category' => 'Switches & Sockets',
                'brand' => 'Schneider Electric',
                'name' => '13A 3-Pin Socket Outlet with Switch',
                'type' => 'Socket Outlet',
                'specs' => [
                    'voltage' => '250V',
                    'power' => '13A',
                    'warranty' => '5 Years',
                    'material' => 'Polycarbonate',
                    'color' => 'White'
                ],
                'price' => 750,
                'stock' => 80
            ],
            [
                'category' => 'Switches & Sockets',
                'brand' => 'Legrand',
                'name' => '1 Gang 2 Way Light Switch',
                'type' => 'Wall Switch',
                'specs' => [
                    'voltage' => '250V',
                    'power' => '10AX',
                    'warranty' => '5 Years',
                    'material' => 'Polycarbonate',
                    'color' => 'Matt Black'
                ],
                'price' => 620,
                'stock' => 95
            ],
            [
                'category' => 'Circuit Protection',
                'brand' => 'Schneider Electric',
                'name' => '16A Single Pole MCB C-Curve',
                'type' => 'Miniature Circuit Breaker',
                'specs' => [
                    'voltage' => '230V-400V',
                    'power' => '16A',
                    'warranty' => '3 Years',
                    'material' => 'Nylon Glass Fiber',
                    'color' => 'Gray'
                ],
                'price' => 1250,
                'stock' => 45
            ],
            [
                'category' => 'Circuit Protection',
                'brand' => 'Legrand',
                'name' => '32A Double Pole MCB C-Curve',
                'type' => 'Miniature Circuit Breaker',
                'specs' => [
                    'voltage' => '400V',
                    'power' => '32A',
                    'warranty' => '3 Years',
                    'material' => 'Nylon Glass Fiber',
                    'color' => 'Gray / Black'
                ],
                'price' => 2400,
                'stock' => 35
            ],
            [
                'category' => 'Electrical Accessories',
                'brand' => 'Panasonic',
                'name' => 'Heavy Duty PVC Electrical Insulation Tape (Black)',
                'type' => 'Insulation Tape',
                'specs' => [
                    'voltage' => 'Up to 600V',
                    'power' => 'N/A',
                    'warranty' => 'N/A',
                    'material' => 'PVC / Rubber Adhesive',
                    'color' => 'Black'
                ],
                'price' => 120,
                'stock' => 200
            ],
            [
                'category' => 'Electrical Accessories',
                'brand' => 'Schneider Electric',
                'name' => 'Universal Extension Cord 4-Way 3M',
                'type' => 'Extension Cord',
                'specs' => [
                    'voltage' => '250V',
                    'power' => '13A max / 3250W',
                    'warranty' => '1 Year',
                    'material' => 'Flame Retardant Plastic',
                    'color' => 'White / Orange'
                ],
                'price' => 1950,
                'stock' => 60
            ],
        ];

        $products = collect();

        foreach ($electricalTemplates as $index => $t) {
            $category = $categoryMap[$t['category']];
            $sellingPrice = (float) $t['price'];
            $supplierPrice = round($sellingPrice * 0.75, 2);
            $availableStock = $t['stock'];
            $damageStock = $faker->numberBetween(0, 3);

            $supplier = $suppliers->random();
            $productCode = sprintf('EL-%s-%03d', Carbon::now()->format('ymd'), $index + 1);

            // Fetch brand list object matching the template's brand
            $brandObj = BrandList::where('brand_name', $t['brand'])->first();
            $brandId = $brandObj ? $brandObj->id : $brandIds[0];

            $product = ProductDetail::create([
                'code' => $productCode,
                'name' => $t['name'],
                'model' => 'Model ' . $faker->bothify('??-###'),
                'brand' => $t['brand'],
                'type' => $t['type'],
                'specifications' => $t['specs'],
                'description' => sprintf('%s by %s. Category: %s.', $t['name'], $t['brand'], $t['category']),
                'barcode' => $faker->unique()->ean13(),
                'status' => 'active',
                'sale_bonus' => round($sellingPrice * 0.02, 2),
                'brand_id' => $brandId,
                'category_id' => $category->id,
                'supplier_id' => $supplier->id,
                'variant_id' => null,
            ]);

            ProductPrice::create([
                'product_id' => $product->id,
                'variant_id' => null,
                'variant_value' => null,
                'pricing_mode' => 'single',
                'supplier_price' => $supplierPrice,
                'selling_price' => $sellingPrice,
                'retail_price' => $sellingPrice,
                'wholesale_price' => round($sellingPrice * 0.94, 2),
                'distributor_price' => round($sellingPrice * 0.90, 2),
                'discount_price' => $faker->boolean(25) ? round($sellingPrice * 0.95, 2) : null,
            ]);

            ProductStock::create([
                'product_id' => $product->id,
                'variant_id' => null,
                'variant_value' => null,
                'available_stock' => $availableStock,
                'damage_stock' => $damageStock,
                'total_stock' => $availableStock + $damageStock,
                'sold_count' => 0,
                'restocked_quantity' => $availableStock,
            ]);

            $products->push($product->load(['price', 'stock']));
        }

        // 6. Sales History (Seeding POS transactions)
        $saleUserId = User::query()->value('id') ?? User::factory()->create()->id;

        DB::transaction(function () use ($faker, $customers, $products, $saleUserId): void {
            for ($i = 1; $i <= 30; $i++) {
                $customer = $customers->random();
                $saleDate = Carbon::now()->subDays($faker->numberBetween(0, 90));
                $itemCount = $faker->numberBetween(1, 3);

                $selectedProducts = $products->shuffle()->take($itemCount);

                $subtotal = 0;
                $preparedItems = [];

                foreach ($selectedProducts as $product) {
                    $unitPrice = (float) ($product->price->discount_price ?? $product->price->selling_price);
                    $maxQty = max(1, min(10, (int) $product->stock->available_stock));
                    $quantity = $faker->numberBetween(1, $maxQty);
                    $lineTotal = round($unitPrice * $quantity, 2);

                    $preparedItems[] = [
                        'product_id' => $product->id,
                        'product_code' => $product->code,
                        'product_name' => $product->name,
                        'product_model' => $product->model,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_per_unit' => 0,
                        'total_discount' => 0,
                        'discount_type' => 'fixed',
                        'discount_percentage' => 0,
                        'total' => $lineTotal,
                        'variant_id' => null,
                        'variant_value' => null,
                        'has_warranty' => true,
                        'warranty_duration' => $product->specifications['warranty'] ?? '1 Year',
                    ];

                    $subtotal += $lineTotal;
                }

                $discountAmount = $faker->boolean(20) ? round($subtotal * 0.05, 2) : 0;
                $totalAmount = max(0, round($subtotal - $discountAmount, 2));

                $isPartial = $faker->boolean(15);
                $dueAmount = $isPartial ? round($totalAmount * 0.20, 2) : 0;

                $sale = Sale::create([
                    'sale_id' => Sale::generateSaleId(),
                    'invoice_number' => Sale::generateInvoiceNumber(),
                    'sale_type' => 'pos',
                    'customer_id' => $customer->id,
                    'customer_type' => $customer->type,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'discount_type' => 'fixed',
                    'additional_discount_type' => 'fixed',
                    'additional_discount_percentage' => 0,
                    'total_amount' => $totalAmount,
                    'payment_type' => $isPartial ? 'partial' : 'full',
                    'payment_status' => $isPartial ? 'partial' : 'paid',
                    'status' => 'confirm',
                    'due_amount' => $dueAmount,
                    'notes' => $faker->sentence(),
                    'user_id' => $saleUserId,
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ]);

                foreach ($preparedItems as $itemData) {
                    $itemData['sale_id'] = $sale->id;
                    $itemData['created_at'] = $saleDate;
                    $itemData['updated_at'] = $saleDate;

                    SaleItem::create($itemData);

                    $stock = ProductStock::where('product_id', $itemData['product_id'])->first();
                    if ($stock) {
                        $stock->available_stock = max(0, $stock->available_stock - $itemData['quantity']);
                        $stock->sold_count += $itemData['quantity'];
                        $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                        $stock->save();
                    }
                }
            }
        });
    }
}
