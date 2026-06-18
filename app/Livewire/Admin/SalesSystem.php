<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\DeliverySale;
use App\Models\Payment;
use App\Models\POSSession;
use App\Services\FIFOStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Concerns\WithDynamicLayout;
use App\Livewire\Concerns\WithPagination;



#[Title('Create Sale')]
class SalesSystem extends Component
{
    use WithDynamicLayout;
    // Basic Properties
    public $search = '';
    public $searchResults = [];
    public $customerId = '';

    // Cart Items
    public $cart = [];

    // Customer Properties
    public $customers = [];
    public $selectedCustomer = null;

    // Customer Info Textarea (replaces dropdown)
    public $customerInfo = '';

    // Customer Form (for new customer - only used in modal)
    public $customerName = '';
    public $customerPhone = '';
    public $customerEmail = '';
    public $customerAddress = '';
    public $customerType = 'retail';
    public $businessName = '';

    // Sale Properties
    public $notes = '';

    // Delivery Properties
    public $deliveryMethod = 'Post';
    public $paymentMethod = 'Cash on Delivery';
    public $deliveryCharge = 450;

    // Discount Properties
    public $additionalDiscount = 0;
    public $additionalDiscountType = 'fixed'; // 'fixed' or 'percentage'

    // Price Type Selection
    public $priceType = 'retail'; // 'retail', 'wholesale', or 'distribute'

    // Modals
    public $showSaleModal = false;
    public $showCustomerModal = false;
    public $lastSaleId = null;
    public $createdSale = null;
    // POS Session (to track/update daily totals)
    public $currentSession = null;

    public function mount()
    {
        $this->loadCustomers();
        $this->setDefaultCustomer();
    }

    // Set default walking customer
    public function setDefaultCustomer()
    {
        // Find or create walking customer (only one)
        $walkingCustomer = Customer::where('name', 'Walking Customer')->first();

        if (!$walkingCustomer) {
            $walkingCustomer = Customer::create([
                'name' => 'Walking Customer',
                'phone' => 'xxxxx', // Empty phone number
                'email' => null,
                'address' => 'xxxxx',
                'type' => 'retail',
                'business_name' => null,
            ]);

            $this->loadCustomers(); // Reload customers after creating new one
        }

        $this->customerId = $walkingCustomer->id;
        $this->selectedCustomer = $walkingCustomer;
    }

    // Load customers for dropdown
    public function loadCustomers()
    {
        $this->customers = Customer::orderBy('name')->get();
    }

    // Computed Properties for Totals
    public function getSubtotalProperty()
    {
        return (float) collect($this->cart)->sum('total');
    }

    public function getTotalDiscountProperty()
    {
        return (float) collect($this->cart)->sum(function ($item) {
            return (float) ($item['discount'] * $item['quantity']);
        });
    }

    public function getSubtotalAfterItemDiscountsProperty()
    {
        return (float) $this->subtotal;
    }

    public function getAdditionalDiscountAmountProperty()
    {
        $discount = (float) ($this->additionalDiscount ?: 0);
        if ($discount <= 0) {
            return 0.0;
        }

        if ($this->additionalDiscountType === 'percentage') {
            return (float) ($this->subtotalAfterItemDiscounts * $discount) / 100;
        }

        return (float) min($discount, $this->subtotalAfterItemDiscounts);
    }

    public function getGrandTotalProperty()
    {
        return (float) $this->subtotalAfterItemDiscounts - (float) $this->additionalDiscountAmount + (float) ($this->deliveryCharge ?? 0);
    }

    // When customer is selected from dropdown
    public function updatedCustomerId($value)
    {
        if ($value) {
            $customer = Customer::find($value);
            if ($customer) {
                $this->selectedCustomer = $customer;
            }
        } else {
            // If customer is deselected, set back to walking customer
            $this->setDefaultCustomer();
        }
    }

    // When price type changes
    public function updatedPriceType($value)
    {
        // When price type changes, update all existing items in cart
        foreach ($this->cart as $index => $item) {
            // Get the corresponding product record to get the correct price
            $productRecord = ProductDetail::with(['price', 'prices'])->find($item['id']);
            if (!$productRecord)
                continue;

            // Find correct price record for variant
            $priceRecord = $productRecord->price;
            if ($item['variant_value']) {
                $priceRecord = $productRecord->prices->firstWhere('variant_value', $item['variant_value']) ?? $productRecord->price;
            }

            // Get the new price based on priceType
            $newPrice = $this->getPriceValue($priceRecord);
            $this->cart[$index]['price'] = $newPrice;

            // Apply automatic discount percentage
            $discountPercentage = 0;
            if ($value === 'retail') {
                $discountPercentage = 10;
            } elseif ($value === 'wholesale') {
                $discountPercentage = 25;
            }

            // Update item discount using new percentage
            $discountAmount = ((float) $newPrice * (float) $discountPercentage) / 100;
            $this->cart[$index]['discount'] = (float) round($discountAmount, 2);
            $this->cart[$index]['discount_type'] = 'percentage';
            $this->cart[$index]['discount_percentage'] = (float) $discountPercentage;
            $this->cart[$index]['total'] = (float) (((float) $newPrice - (float) $this->cart[$index]['discount']) * (int) $this->cart[$index]['quantity']);
        }
    }

    // Helper method to get price based on price type
    public function getPriceValue($priceRecord)
    {
        if (!$priceRecord)
            return 0;

        return match ($this->priceType) {
            'retail' => $priceRecord->retail_price ?? 0,
            'wholesale' => $priceRecord->wholesale_price ?? 0,
            'distribute' => $priceRecord->distributor_price ?? $priceRecord->wholesale_price ?? 0,
            default => $priceRecord->retail_price ?? 0,
        };
    }

    // Reset customer fields
    public function resetCustomerFields()
    {
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerEmail = '';
        $this->customerAddress = '';
        $this->customerType = 'retail';
        $this->businessName = '';
    }

    // Open customer modal
    public function openCustomerModal()
    {
        $this->resetCustomerFields();
        $this->showCustomerModal = true;
    }

    // Close customer modal
    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->resetCustomerFields();
    }

    // Create new customer
    public function createCustomer()
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'nullable|string|max:10|unique:customers,phone',
            'customerEmail' => 'nullable|email|unique:customers,email',
            'customerAddress' => 'required|string',
            'customerType' => 'required|in:retail,wholesale,distributor',
        ]);

        try {
            $customer = Customer::create([
                'name' => $this->customerName,
                'phone' => $this->customerPhone ?: null,
                'email' => $this->customerEmail,
                'address' => $this->customerAddress,
                'type' => $this->customerType,
                'business_name' => $this->businessName,
                'user_id' => Auth::id(),
            ]);

            $this->loadCustomers();
            $this->customerId = $customer->id;
            $this->selectedCustomer = $customer;
            $this->closeCustomerModal();

            $this->js("Swal.fire('success', 'Customer created successfully!', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('error', 'Failed to create customer: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    // Search Products
    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $items = [];

            if ($this->isStaff()) {
                // For staff: only show their allocated products
                $staffProducts = \App\Models\StaffProduct::where('staff_id', auth()->id())
                    ->join('product_details', 'staff_products.product_id', '=', 'product_details.id')
                    ->where(function ($query) {
                        $query->where('product_details.name', 'like', '%' . $this->search . '%')
                            ->orWhere('product_details.code', 'like', '%' . $this->search . '%')
                            ->orWhere('product_details.model', 'like', '%' . $this->search . '%')
                            ->orWhere('product_details.barcode', 'like', '%' . $this->search . '%');
                    })
                    ->select(
                        'product_details.id',
                        'product_details.name',
                        'product_details.code',
                        'product_details.model',
                        'product_details.image',
                        'staff_products.unit_price as price',
                        'staff_products.quantity',
                        'staff_products.sold_quantity'
                    )
                    ->take(10)
                    ->get();

                foreach ($staffProducts as $product) {
                    $items[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'code' => $product->code,
                        'model' => $product->model,
                        'price' => $product->price,
                        'stock' => ($product->quantity - $product->sold_quantity),
                        'sold' => $product->sold_quantity,
                        'image' => $product->image,
                        'variant_id' => null,
                        'variant_value' => null,
                    ];
                }
            } else {
                // For admin: show all products, including variants
                $products = ProductDetail::with(['stock', 'price', 'variant', 'stocks', 'prices'])
                    ->where(function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('code', 'like', '%' . $this->search . '%')
                            ->orWhere('model', 'like', '%' . $this->search . '%')
                            ->orWhere('barcode', 'like', '%' . $this->search . '%');
                    })
                    ->take(20)
                    ->get();

                foreach ($products as $product) {
                    // Check if product has variants
                    if ($product->variant_id !== null && $product->stocks && $product->stocks->isNotEmpty()) {
                        // Expand each variant as a separate search result
                        foreach ($product->stocks as $stock) {
                            if (($stock->available_stock ?? 0) <= 0)
                                continue;

                            // Find matching price record for this variant value
                            $priceRecord = $product->prices->firstWhere('variant_value', $stock->variant_value) ?? $product->price;
                            $sellingPrice = $this->getPriceValue($priceRecord);
                            $discountPrice = $priceRecord->discount_price ?? 0;

                            $items[] = [
                                'id' => $product->id,
                                'name' => $product->name . ' (' . $stock->variant_value . ')',
                                'code' => $product->code,
                                'model' => $product->model,
                                'price' => $sellingPrice,
                                'discount_price' => $discountPrice,
                                'stock' => $stock->available_stock ?? 0,
                                'sold' => $stock->sold_count ?? 0,
                                'image' => $product->image,
                                'variant_id' => $stock->variant_id,
                                'variant_value' => $stock->variant_value,
                            ];
                        }
                    } else {
                        // Single product (no variants)
                        $priceRecord = $product->price;
                        $sellingPrice = $this->getPriceValue($priceRecord);

                        $items[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'code' => $product->code,
                            'model' => $product->model,
                            'price' => $sellingPrice,
                            'discount_price' => $product->price->discount_price ?? 0,
                            'stock' => $product->stock->available_stock ?? 0,
                            'sold' => $product->stock->sold_count ?? 0,
                            'image' => $product->image,
                            'variant_id' => null,
                            'variant_value' => null,
                        ];
                    }
                }
            }

            // Limit results
            $this->searchResults = array_slice($items, 0, 15);

            // Check for exact barcode match - auto-add to cart
            if (count($this->searchResults) === 1) {
                $productData = $this->searchResults[0];

                // Check if the search term exactly matches the product code or barcode
                $searchTerm = strtolower(trim($this->search));
                $matchesCode = $searchTerm === strtolower(trim($productData['code']));

                // Also check the barcode field from DB
                $dbProduct = ProductDetail::where('code', $productData['code'])->first();
                $matchesBarcode = $dbProduct && $dbProduct->barcode && $searchTerm === strtolower(trim($dbProduct->barcode));

                if ($matchesCode || $matchesBarcode) {
                    $this->addToCart($productData);
                    $this->dispatch('focus-search');
                    return;
                }
            }
        } else {
            $this->searchResults = [];
        }
    }

    // Add to Cart
    public function addToCart($product)
    {
        // Check stock availability
        if (($product['stock'] ?? 0) <= 0) {
            $this->js("Swal.fire('error', 'Not enough stock available!', 'error')");
            return;
        }

        // Create a unique cart identifier: product_id + variant_value (if variant)
        $cartIdentifier = $product['id'] . '::' . ($product['variant_value'] ?? '');

        $existingIndex = null;
        foreach ($this->cart as $index => $item) {
            if (!empty($item['is_custom']))
                continue; // Skip custom products
            $itemIdentifier = $item['id'] . '::' . ($item['variant_value'] ?? '');
            if ($itemIdentifier === $cartIdentifier) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Check if adding more exceeds stock
            if (($this->cart[$existingIndex]['quantity'] + 1) > $product['stock']) {
                $this->js("Swal.fire('error', 'Not enough stock available!', 'error')");
                return;
            }

            $this->cart[$existingIndex]['quantity'] += 1;
            $this->cart[$existingIndex]['total'] = (float) (((float) $this->cart[$existingIndex]['price'] - (float) $this->cart[$existingIndex]['discount']) * (int) $this->cart[$existingIndex]['quantity']);
        } else {
            // Apply automatic discount percentage based on priceType
            $discountPercentage = 0;
            if ($this->priceType === 'retail') {
                $discountPercentage = 10;
            } elseif ($this->priceType === 'wholesale') {
                $discountPercentage = 25;
            }

            $discountPrice = ($product['price'] * $discountPercentage) / 100;

            $newItem = [
                'key' => uniqid('cart_'),
                'id' => $product['id'],
                'name' => $product['name'],
                'code' => $product['code'],
                'model' => $product['model'],
                'price' => $product['price'],
                'quantity' => 1,
                'discount' => (float) round($discountPrice, 2),
                'discount_type' => 'percentage',
                'discount_percentage' => (float) $discountPercentage,
                'total' => (float) ((float) $product['price'] - (float) round($discountPrice, 2)),
                'stock' => (int) $product['stock'],
                'variant_id' => $product['variant_id'] ?? null,
                'variant_value' => $product['variant_value'] ?? null,
                'is_custom' => false,
            ];

            // Prepend new item to the beginning of the cart so it appears at the top
            array_unshift($this->cart, $newItem);
        }

        $this->search = '';
        $this->searchResults = [];
        $this->dispatch('focus-search');
    }

    // Add Custom Product to Cart (product not in system)
    public function addCustomProduct()
    {
        $newItem = [
            'key' => uniqid('custom_'),
            'id' => 0,
            'name' => 'Custom Product',
            'code' => 'CUSTOM-' . strtoupper(substr(uniqid(), -6)),
            'model' => 'N/A',
            'price' => 0,
            'quantity' => 1,
            'discount' => 0,
            'discount_type' => 'fixed',
            'discount_percentage' => 0,
            'total' => 0,
            'stock' => 9999, // Unlimited stock for custom products
            'variant_id' => null,
            'variant_value' => null,
            'is_custom' => true,
        ];

        // Prepend to top of cart
        array_unshift($this->cart, $newItem);
        $this->dispatch('focus-search');
    }

    // Update custom product name
    public function updateCustomName($index, $name)
    {
        if (isset($this->cart[$index]) && !empty($this->cart[$index]['is_custom'])) {
            $this->cart[$index]['name'] = $name;
        }
    }

    // Update custom product code
    public function updateCustomCode($index, $code)
    {
        if (isset($this->cart[$index]) && !empty($this->cart[$index]['is_custom'])) {
            $this->cart[$index]['code'] = $code;
        }
    }

    // Update Quantity
    public function updateQuantity($index, $quantity)
    {
        if ($quantity < 1)
            $quantity = 1;

        // Check stock availability
        $productStock = $this->cart[$index]['stock'];
        if ($quantity > $productStock) {
            $this->js("Swal.fire('error', 'Not enough stock available! Maximum: ' . $productStock, 'error')");
            return;
        }

        $this->cart[$index]['quantity'] = (int) $quantity;
        $this->cart[$index]['total'] = (float) (((float) $this->cart[$index]['price'] - (float) $this->cart[$index]['discount']) * (int) $quantity);
    }

    // Increment Quantity
    public function incrementQuantity($index)
    {
        $currentQuantity = $this->cart[$index]['quantity'];
        $productStock = $this->cart[$index]['stock'];

        if (($currentQuantity + 1) > $productStock) {
            $this->js("Swal.fire('error', 'Not enough stock available! Maximum: ' . $productStock, 'error')");
            return;
        }

        $this->cart[$index]['quantity'] += 1;
        $this->cart[$index]['total'] = (float) (((float) $this->cart[$index]['price'] - (float) $this->cart[$index]['discount']) * (int) $this->cart[$index]['quantity']);
    }

    // Decrement Quantity
    public function decrementQuantity($index)
    {
        if ($this->cart[$index]['quantity'] > 1) {
            $this->cart[$index]['quantity'] -= 1;
            $this->cart[$index]['total'] = (float) (((float) $this->cart[$index]['price'] - (float) $this->cart[$index]['discount']) * (int) $this->cart[$index]['quantity']);
        }
    }

    // Update Price
    public function updatePrice($index, $price)
    {
        if ($price < 0)
            $price = 0;

        $this->cart[$index]['price'] = (float) $price;
        $this->cart[$index]['total'] = (float) (((float) $price - (float) $this->cart[$index]['discount']) * (int) $this->cart[$index]['quantity']);
    }

    // Update Discount
    public function updateDiscount($index, $discount)
    {
        $isPercentage = false;

        if (is_string($discount) && str_ends_with(trim($discount), '%')) {
            $isPercentage = true;
            $discountValue = (float) str_replace('%', '', $discount);
        } else {
            $discountValue = (float) $discount;
        }

        if ($discountValue < 0)
            $discountValue = 0;

        if ($isPercentage) {
            if ($discountValue > 100)
                $discountValue = 100;
            $amount = ($this->cart[$index]['price'] * $discountValue) / 100;
            $this->cart[$index]['discount'] = round($amount, 2);
            $this->cart[$index]['discount_percentage'] = $discountValue;
            $this->cart[$index]['discount_type'] = 'percentage';
        } else {
            if ($discountValue > $this->cart[$index]['price']) {
                $discountValue = $this->cart[$index]['price'];
            }
            $this->cart[$index]['discount'] = $discountValue;
            $this->cart[$index]['discount_percentage'] = $this->cart[$index]['price'] > 0 ? round(($discountValue / $this->cart[$index]['price']) * 100, 2) : 0;
            $this->cart[$index]['discount_type'] = 'fixed';
        }

        $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $this->cart[$index]['discount']) * $this->cart[$index]['quantity'];
    }

    // Remove from Cart
    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->js("Swal.fire('success', 'Product removed from sale!', 'success')");
    }

    // Clear Cart
    public function clearCart()
    {
        $this->cart = [];
        $this->additionalDiscount = 0;
        $this->additionalDiscountType = 'fixed';
        $this->js("Swal.fire('success', 'Cart cleared!', 'success')");
    }

    // Update additional discount
    public function updatedAdditionalDiscount($value)
    {
        if ($value === '') {
            $this->additionalDiscount = 0;
            return;
        }

        if ($value < 0) {
            $this->additionalDiscount = 0;
            return;
        }

        if ($this->additionalDiscountType === 'percentage' && $value > 100) {
            $this->additionalDiscount = 100;
            return;
        }

        if ($this->additionalDiscountType === 'fixed' && $value > $this->subtotalAfterItemDiscounts) {
            $this->additionalDiscount = $this->subtotalAfterItemDiscounts;
            return;
        }
    }

    public function toggleDiscountType()
    {
        $this->additionalDiscountType = $this->additionalDiscountType === 'percentage' ? 'fixed' : 'percentage';
        $this->additionalDiscount = 0;
    }

    public function removeAdditionalDiscount()
    {
        $this->additionalDiscount = 0;
        $this->js("Swal.fire('success', 'Additional discount removed!', 'success')");
    }

    // Create Sale
    public function createSale()
    {
        if (empty($this->cart)) {
            $this->js("Swal.fire('error', 'Please add at least one product to the sale.', 'error')");
            return;
        }

        // Validate customer info textarea is filled
        if (empty(trim($this->customerInfo))) {
            $this->js("Swal.fire('error', 'Please enter the customer name and address.', 'error')");
            return;
        }

        // If no customer selected, use walking customer
        if (!$this->selectedCustomer && !$this->customerId) {

            $this->js("Swal.fire('error', 'Please select a customer.', 'error')");
            return;
        }

        try {
            DB::beginTransaction();

            // Get customer data - now guaranteed to have a customer
            if ($this->selectedCustomer) {
                $customer = $this->selectedCustomer;
            } else {
                $customer = Customer::find($this->customerId);
            }

            if (!$customer) {
                $this->js("Swal.fire('error', 'Customer not found.', 'error')");
                return;
            }

            // Create sale
            $sale = Sale::create([
                'sale_id' => Sale::generateSaleId(),
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_id' => $customer->id,
                'customer_type' => $customer->type,
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->additionalDiscountAmount,
                'total_amount' => $this->grandTotal,
                'payment_type' => 'full',
                'payment_status' => 'paid',
                'due_amount' => 0,
                'notes' => null,
                'user_id' => Auth::id(),
                'status' => 'confirm',
                'sale_type' => $this->getSaleType()
            ]);

            // Create delivery sale record (delivery_barcode auto-generated by model)
            DeliverySale::create([
                'sale_id' => $sale->id,
                'delivery_method' => $this->deliveryMethod,
                'payment_method' => $this->paymentMethod,
                'delivery_charge' => $this->deliveryCharge ?? 450,
                'status' => 'Processing',
                'customer_details' => $this->customerInfo,
            ]);

            // Create cash payment record for the full amount
            Payment::create([
                'sale_id' => $sale->id,
                'amount' => $this->grandTotal,
                'payment_method' => 'cash',
                'is_completed' => true,
                'payment_date' => now(),
                'status' => 'paid',
                'customer_id' => $customer->id,
                'created_by' => Auth::id(),
            ]);

            // Create sale items and update stock using FIFO
            foreach ($this->cart as $item) {
                // Custom products: skip FIFO, just create sale item directly
                if (!empty($item['is_custom'])) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => null,
                        'product_code' => $item['code'],
                        'product_name' => $item['name'],
                        'product_model' => $item['model'] ?? 'N/A',
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'discount_per_unit' => $item['discount'],
                        'total_discount' => $item['discount'] * $item['quantity'],
                        'discount_type' => $item['discount_type'] ?? 'fixed',
                        'discount_percentage' => $item['discount_percentage'] ?? 0,
                        'total' => ($item['price'] - $item['discount']) * $item['quantity'],
                        'variant_id' => null,
                        'variant_value' => null,
                        'has_warranty' => false,
                        'warranty_duration' => null,
                    ]);

                    Log::info("Custom Product added to Sale", [
                        'name' => $item['name'],
                        'code' => $item['code'],
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                    ]);
                    continue;
                }

                // Update product stock using FIFO method (with variant support)
                try {
                    $variantId = $item['variant_id'] ?? null;
                    $variantValue = $item['variant_value'] ?? null;

                    $fifoResult = FIFOStockService::deductStock(
                        $item['id'],
                        $item['quantity'],
                        $variantId,
                        $variantValue
                    );

                    // Use the manually updated price from cart, or fall back to FIFO selling price
                    $cartUnitPrice = $item['price'] ?? $fifoResult['deductions'][0]['selling_price'] ?? 0;

                    // Create sale items based on batch deductions
                    foreach ($fifoResult['deductions'] as $deduction) {
                        $itemTotal = ($cartUnitPrice - $item['discount']) * $deduction['quantity'];
                        $warrantyThreshold = \App\Models\Setting::where('key', 'warranty_min_amount')->value('value') ?? 1000;
                        $hasWarranty = ($itemTotal / $deduction['quantity']) >= $warrantyThreshold;

                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $item['id'],
                            'product_code' => $item['code'],
                            'product_name' => $item['name'],
                            'product_model' => $item['model'],
                            'quantity' => $deduction['quantity'],
                            'unit_price' => $cartUnitPrice,
                            'discount_per_unit' => $item['discount'],
                            'total_discount' => $item['discount'] * $deduction['quantity'],
                            'discount_type' => $item['discount_type'] ?? 'fixed',
                            'discount_percentage' => $item['discount_percentage'] ?? 0,
                            'total' => $itemTotal,
                            'variant_id' => $variantId,
                            'variant_value' => $variantValue,
                            'has_warranty' => $hasWarranty,
                            'warranty_duration' => $hasWarranty ? '6 Months' : null,
                        ]);
                    }

                    // Log FIFO deduction details
                    Log::info("FIFO Stock Deduction for Product {$item['id']}", [
                        'quantity' => $item['quantity'],
                        'variant_id' => $variantId,
                        'variant_value' => $variantValue,
                        'batches_used' => count($fifoResult['deductions']),
                        'average_cost' => $fifoResult['average_cost'],
                        'deductions' => $fifoResult['deductions']
                    ]);
                } catch (\Exception $e) {
                    // If FIFO fails, throw exception to rollback transaction
                    throw new \Exception("Failed to deduct stock for {$item['name']}: " . $e->getMessage());
                }
            }

            DB::commit();

            // Ensure there is an open POS session for this user and update its totals
            try {
                $this->currentSession = POSSession::getTodaySession(Auth::id());
                if (!$this->currentSession) {
                    // Create a session with zero opening cash so admin sales are still tracked
                    $this->currentSession = POSSession::openSession(Auth::id(), 0);
                }

                // Update session totals from today's sales/payments
                $this->currentSession->updateFromSales();
                // Recalculate expected cash (cash_difference remains until close)
                $this->currentSession->calculateDifference();
            } catch (\Exception $e) {
                Log::error('Failed to update POS session after admin sale: ' . $e->getMessage());
            }

            $this->lastSaleId = $sale->id;
            $this->createdSale = Sale::with(['customer', 'items', 'deliverySale'])->find($sale->id);
            $this->showSaleModal = true;

            $this->js("Swal.fire('success', 'Sale created successfully! Status: Processing', 'success')");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->js("Swal.fire('error', 'Failed to create sale: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    // Download Invoice
    public function downloadInvoice()
    {
        if (!$this->lastSaleId) {
            $this->js("Swal.fire('error', 'No sale found to download.', 'error')");
            return;
        }

        $sale = Sale::with([
            'customer',
            'items',
            'returns' => function ($q) {
                $q->with('product');
            }
        ])->find($this->lastSaleId);

        if (!$sale) {
            $this->js("Swal.fire('error', 'Sale not found.', 'error')");
            return;
        }

        $pdf = PDF::loadView('admin.sales.invoice', compact('sale'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('dpi', 150);
        $pdf->setOption('defaultFont', 'sans-serif');

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            'invoice-' . $sale->invoice_number . '.pdf'
        );
    }

    // Close Modal
    public function closeModal()
    {
        $this->showSaleModal = false;
        $this->lastSaleId = null;
        $this->dispatch('refreshPage');
    }

    // Continue creating new sale
    public function createNewSale()
    {
        $this->resetExcept(['customers']);
        $this->loadCustomers();
        $this->setDefaultCustomer(); // Set walking customer again for new sale
        $this->showSaleModal = false;
    }

    public function render()
    {
        $layoutPath = $this->layout;

        return view('livewire.admin.sales-system', [
            'subtotal' => $this->subtotal,
            'totalDiscount' => $this->totalDiscount,
            'subtotalAfterItemDiscounts' => $this->subtotalAfterItemDiscounts,
            'additionalDiscountAmount' => $this->additionalDiscountAmount,
            'grandTotal' => $this->grandTotal
        ])->layout($layoutPath);
    }
}
