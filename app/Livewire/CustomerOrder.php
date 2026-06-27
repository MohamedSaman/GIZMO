<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\Quotation;
use App\Models\CategoryList;
use App\Models\ProductType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.app')]
#[Title('Online Order Placement')]
class CustomerOrder extends Component
{
    public $customerId;
    public $validUntil;
    public $selectedCustomer = null;

    // Search and Filters
    public $search = '';
    public $selectedCategoryId = '';
    public $selectedTypeId = '';

    // Cart
    public $cart = [];

    // Success State
    public $orderSubmitted = false;
    public $createdOrderNo = '';

    // Filter Lists
    public $categories = [];
    public $productTypes = [];

    public function mount()
    {
        $this->customerId = request()->query('customer_id');
        $this->validUntil = request()->query('valid_until') ?: now()->addDays(30)->format('Y-m-d');

        if ($this->customerId) {
            $this->selectedCustomer = Customer::find($this->customerId);
        }

        $this->loadFilters();
    }

    public function loadFilters()
    {
        $this->categories = CategoryList::orderBy('category_name')->get();
        $this->productTypes = ProductType::orderBy('type_name')->get();
    }

    public function getProductsProperty()
    {
        $query = ProductDetail::with(['stock', 'price', 'stocks', 'prices', 'category'])
            ->where('status', 'active');

        // Apply search
        if (strlen($this->search) >= 2) {
            $searchTerm = trim($this->search);
            
            // Barcode scan auto add checking
            $exactMatch = ProductDetail::where(function ($q) use ($searchTerm) {
                    $q->where('barcode', $searchTerm)
                      ->orWhere('code', $searchTerm);
                })
                ->where('status', 'active')
                ->first();

            if ($exactMatch) {
                $formatted = $this->formatProductData($exactMatch);
                if (!empty($formatted)) {
                    $this->addToCart($formatted[0]);
                    $this->search = '';
                }
            } else {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('code', 'like', "%{$searchTerm}%")
                      ->orWhere('model', 'like', "%{$searchTerm}%");
                });
            }
        }

        // Apply Category Filter
        if ($this->selectedCategoryId) {
            $query->where('category_id', $this->selectedCategoryId);
        }

        // Apply Product Type Filter
        if ($this->selectedTypeId) {
            // Find products matching the chosen type name/id
            $typeName = ProductType::where('id', $this->selectedTypeId)->value('type_name');
            if ($typeName) {
                $query->where('type', $typeName);
            }
        }

        $productsList = $query->orderBy('name')->get();
        $formattedResults = [];

        foreach ($productsList as $product) {
            $formattedResults = array_merge($formattedResults, $this->formatProductData($product));
        }

        return $formattedResults;
    }

    private function formatProductData($product)
    {
        $results = [];
        $hasVariants = $product->stocks()->whereNotNull('variant_value')->exists();

        if ($hasVariants) {
            $variantStocks = $product->stocks()->whereNotNull('variant_value')->get();
            foreach ($variantStocks as $variantStock) {
                $variantPrice = $product->prices()->where('variant_id', $variantStock->variant_id)->first();
                $price = $variantPrice ? floatval($variantPrice->retail_price ?? $variantPrice->selling_price ?? 0) : 0;
                $results[] = [
                    'id' => $product->id . '::' . $variantStock->variant_value,
                    'product_id' => $product->id,
                    'variant_id' => $variantStock->variant_id,
                    'variant_value' => $variantStock->variant_value,
                    'name' => $product->name . ' (' . $variantStock->variant_value . ')',
                    'code' => $product->code,
                    'model' => $product->model ?? 'N/A',
                    'price' => $price,
                    'stock' => $variantStock->available_stock ?? 0,
                    'image' => $product->image,
                    'category_name' => $product->category->category_name ?? 'Uncategorized',
                    'type' => $product->type ?? 'General'
                ];
            }
        } else {
            $productPrice = $product->prices()->whereNull('variant_id')->first() ?? $product->price;
            $price = $productPrice ? floatval($productPrice->retail_price ?? $productPrice->selling_price ?? 0) : 0;
            $productStock = $product->stocks()->whereNull('variant_value')->first() ?? $product->stock;
            $results[] = [
                'id' => $product->id,
                'product_id' => $product->id,
                'variant_id' => null,
                'variant_value' => null,
                'name' => $product->name,
                'code' => $product->code,
                'model' => $product->model ?? 'N/A',
                'price' => $price,
                'stock' => $productStock->available_stock ?? 0,
                'image' => $product->image,
                'category_name' => $product->category->category_name ?? 'Uncategorized',
                'type' => $product->type ?? 'General'
            ];
        }

        return $results;
    }

    public function addToCart($product)
    {
        $cartId = $product['id'];
        $existing = collect($this->cart)->firstWhere('id', $cartId);

        if ($existing) {
            $this->cart = collect($this->cart)->map(function ($item) use ($cartId) {
                if ($item['id'] == $cartId) {
                    $item['quantity'] += 1;
                    $item['total'] = $item['price'] * $item['quantity'];
                }
                return $item;
            })->toArray();
        } else {
            array_unshift($this->cart, [
                'id' => $cartId,
                'product_id' => $product['product_id'],
                'variant_id' => $product['variant_id'],
                'variant_value' => $product['variant_value'],
                'name' => $product['name'],
                'code' => $product['code'],
                'model' => $product['model'],
                'price' => floatval($product['price']),
                'quantity' => 1,
                'total' => floatval($product['price']),
                'image' => $product['image'],
            ]);
        }
    }

    public function updateQuantity($index, $quantity)
    {
        $quantity = max(1, intval($quantity));
        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['total'] = $this->cart[$index]['price'] * $quantity;
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function getSubtotalProperty()
    {
        return (float) collect($this->cart)->sum('total');
    }

    public function submitOrder()
    {
        if (empty($this->cart)) {
            $this->js("Swal.fire('Error', 'Your cart is empty. Please add products first.', 'error')");
            return;
        }

        if (!$this->selectedCustomer) {
            $this->js("Swal.fire('Error', 'Invalid customer session. Please request a valid ordering link.', 'error')");
            return;
        }

        try {
            DB::beginTransaction();

            $items = collect($this->cart)->map(function ($item, $index) {
                return [
                    'id' => $index + 1,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'variant_value' => $item['variant_value'],
                    'product_code' => $item['code'],
                    'product_name' => $item['name'],
                    'product_model' => $item['model'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'discount_per_unit' => 0,
                    'total_discount' => 0,
                    'total' => $item['total']
                ];
            })->toArray();

            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateQuotationNumber(),
                'customer_id' => $this->selectedCustomer->id,
                'customer_type' => $this->selectedCustomer->type,
                'customer_name' => $this->selectedCustomer->name,
                'customer_phone' => $this->selectedCustomer->phone ?? 'N/A',
                'customer_email' => $this->selectedCustomer->email ?? null,
                'customer_address' => $this->selectedCustomer->address ?? 'N/A',
                'quotation_date' => now(),
                'valid_until' => $this->validUntil,
                'subtotal' => $this->subtotal,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'shipping_charges' => 0,
                'total_amount' => $this->subtotal,
                'items' => $items,
                'status' => 'sent', // Submitted by customer
                'notes' => 'Placed via online customer order link.',
            ]);

            DB::commit();

            $this->createdOrderNo = $quotation->quotation_number;
            $this->orderSubmitted = true;
            $this->cart = [];

            $this->js("Swal.fire('Success', 'Order Request submitted successfully!', 'success')");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Public order placement failed: ' . $e->getMessage());
            $this->js("Swal.fire('Error', 'Something went wrong while placing your order request.', 'error')");
        }
    }

    public function resetOrder()
    {
        $this->orderSubmitted = false;
        $this->createdOrderNo = '';
    }

    public function render()
    {
        return view('livewire.customer-order', [
            'products' => $this->products
        ]);
    }
}
